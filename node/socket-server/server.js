const express = require('express');
const http = require('http');
const cors = require('cors');
const { Server } = require('socket.io');
const jwt = require('jsonwebtoken');
const rateLimit = require('express-rate-limit');
const crypto = require('crypto');
const fs = require('fs');
const path = require('path');
const Redis = require('ioredis');

const app = express();
// Setup allowed origins from env (comma separated) or default to localhost for dev
const ALLOWED_ORIGINS = (process.env.SOCKET_ALLOWED_ORIGINS || 'http://localhost').split(',').map(s => s.trim()).filter(Boolean);
app.use(cors({
    origin: (origin, callback) => {
        // allow non-browser requests (e.g., server-side) where origin is undefined
        if (!origin) { console.log('CORS origin: none (server-side)'); return callback(null, true); }
        if (ALLOWED_ORIGINS.includes('*') || ALLOWED_ORIGINS.includes(origin)) { console.log('CORS origin allowed:', origin); return callback(null, true); }
        console.warn('CORS origin denied:', origin);
        return callback(new Error('CORS policy: Origin not allowed'));
    }
}));
// Capture raw JSON body to compute/verify HMAC
app.use(express.json({
    verify: (req, res, buf, encoding) => {
        req.rawBody = buf && buf.toString(encoding || 'utf8');
    }
}));

const PORT = process.env.PORT || 3000;
const SOCKET_API_KEY = process.env.SOCKET_API_KEY || 'dev-secret-key';
const SOCKET_JWT_SECRET = process.env.SOCKET_JWT_SECRET || 'socket-secret';

const server = http.createServer(app);
const io = new Server(server, { cors: { origin: ALLOWED_ORIGINS.length === 1 ? ALLOWED_ORIGINS[0] : ALLOWED_ORIGINS } });

console.log('Socket server config: PORT=' + PORT + ', ALLOWED_ORIGINS=' + JSON.stringify(ALLOWED_ORIGINS));
// warn and exit in production if the JWT secret is insecure
if ((process.env.NODE_ENV === 'production' || process.env.NODE_ENV === 'prod') && SOCKET_JWT_SECRET === 'socket-secret') {
    console.error('SOCKET_JWT_SECRET is using the default insecure secret. Set SOCKET_JWT_SECRET in environment for production.');
    process.exit(1);
}

// middleware to verify JWT during connection
io.use((socket, next) => {
    const origin = socket.handshake && socket.handshake.headers && socket.handshake.headers.origin;
    const token = (socket.handshake && socket.handshake.auth && socket.handshake.auth.token) || (socket.handshake && socket.handshake.query && socket.handshake.query.token);
    console.log('Socket handshake origin=', origin, ' socketId=', socket.id, ' tokenPresent=', !!token);
    if (!token) return next(new Error('Authentication token missing'));
    try {
        const payload = jwt.verify(token, SOCKET_JWT_SECRET);
        // attach user id to socket
        socket.userId = payload.user_id;
        console.log('Socket jwt validated for user_id=', socket.userId, ' socketId=', socket.id);
        return next();
    } catch (err) {
        console.warn('Socket auth failed for token', (token || '').slice(0, 32), err && err.message);
        return next(new Error('Authentication failed'));
    }
});

io.on('connection', (socket) => {
    const userId = socket.userId || null;
    // Log handshake details (trim token for privacy)
    const origin = socket.handshake && socket.handshake.headers && socket.handshake.headers.origin;
    const authToken = (socket.handshake && socket.handshake.auth && socket.handshake.auth.token) || (socket.handshake && socket.handshake.query && socket.handshake.query.token) || null;
    console.log(`Socket ${socket.id} connection from origin=${origin} userId=${userId || 'anonymous'} token_present=${!!authToken} token_snippet=${(authToken || '').slice(0, 32)}`);
    if (userId) {
        const room = `user:${userId}`;
        socket.join(room);
        // log current members in room
        io.in(room).allSockets().then(set => {
            console.log(`Socket ${socket.id} joined ${room}. members=${set.size}; memberIds=${Array.from(set).slice(0,10).join(',')}`);
        }).catch(e => console.warn('Failed to retrieve room members', e));
    }

    socket.on('disconnect', () => {
        // clean up if needed
    });
});

// simple POST endpoint for PHP to emit events into rooms
// rate limiter for emit endpoint (per API key or per IP)
const emitLimiter = rateLimit({
    windowMs: parseInt(process.env.SOCKET_RATE_WINDOW_MS || '60000', 10),
    max: parseInt(process.env.SOCKET_RATE_MAX || '60', 10),
    keyGenerator: (req) => req.header('X-SOCKET-KEY') || req.ip,
    standardHeaders: true,
    legacyHeaders: false,
    handler: (req, res, next) => {
        // audit and then return rate limited response
        auditLog(req, 'rate_limited', { key: req.header('X-SOCKET-KEY') || null });
        res.status(429).json({ error: 'rate limit exceeded' });
    }
});

const nonceCache = new Map(); // nonce -> expiresAtMs (fallback)
const SOCKET_REDIS_URL = process.env.SOCKET_REDIS_URL || null;
let redisClient = null;
const SOCKET_REDIS_HOST = process.env.SOCKET_REDIS_HOST || null;
const SOCKET_REDIS_PORT = process.env.SOCKET_REDIS_PORT ? parseInt(process.env.SOCKET_REDIS_PORT, 10) : null;
const SOCKET_REDIS_PASSWORD = process.env.SOCKET_REDIS_PASSWORD || null;
if (SOCKET_REDIS_URL || SOCKET_REDIS_HOST) {
    try {
        if (SOCKET_REDIS_URL) {
            const opts = { // keep retries low and avoid offline queueing during tests/dev
                maxRetriesPerRequest: parseInt(process.env.SOCKET_REDIS_MAX_RETRIES || '1', 10),
                enableOfflineQueue: false,
            };
            if (SOCKET_REDIS_PASSWORD) opts.password = SOCKET_REDIS_PASSWORD;
            redisClient = new Redis(SOCKET_REDIS_URL, opts);
            console.log('Using Redis URL for nonce and audit storage');
        } else {
            const rOpts = { host: SOCKET_REDIS_HOST, port: SOCKET_REDIS_PORT || 6379, maxRetriesPerRequest: parseInt(process.env.SOCKET_REDIS_MAX_RETRIES || '1', 10), enableOfflineQueue: false };
            if (SOCKET_REDIS_PASSWORD) rOpts.password = SOCKET_REDIS_PASSWORD;
            redisClient = new Redis(rOpts);
            console.log('Using Redis host/port for nonce and audit storage: ' + SOCKET_REDIS_HOST + ':' + (SOCKET_REDIS_PORT || 6379));
        }
        // Attach an 'error' handler to avoid unhandled 'error' events from ioredis
        redisClient.on('error', (e) => console.warn('Redis connection error', e));
        // Listen to 'ready' so we can log successful connections (helpful in CI)
        redisClient.on('ready', () => console.log('Redis client ready'));
    } catch (e) {
        console.warn('Failed to connect to Redis, using in-memory map for nonces', e);
        redisClient = null;
    }
}
const EMIT_TTL_SECONDS = parseInt(process.env.SOCKET_EMIT_TTL || '60', 10);
const NONCE_CLEANUP_INTERVAL_MS = 30 * 1000;

// cleanup expired nonces periodically
setInterval(() => {
    const now = Date.now();
    for (const [k, v] of nonceCache.entries()) {
        if (v <= now) nonceCache.delete(k);
    }
}, NONCE_CLEANUP_INTERVAL_MS);

// Setup basic JSON-line audit file
const LOG_DIR = process.env.SOCKET_LOG_DIR || path.join(__dirname, 'logs');
const LOG_FILE = path.join(LOG_DIR, process.env.SOCKET_LOG_FILE || 'emitter_audit.log');
if (!fs.existsSync(LOG_DIR)) fs.mkdirSync(LOG_DIR, { recursive: true });

function auditLog(req, type, data = {}) {
    try {
        const entry = {
            ts: new Date().toISOString(),
            type,
            ip: req.ip || (req.connection && req.connection.remoteAddress) || null,
            method: req.method,
            path: req.path,
            headers: {
                'x-socket-key': req.header('X-SOCKET-KEY') || null,
                'x-emitter-jwt': !!req.header('X-EMITTER-JWT')
            },
            body: req.body || null,
            data
        };
        // Also attempt to write to Redis list (as primary)
        if (redisClient) {
            try {
                const key = process.env.SOCKET_AUDIT_KEY || 'emitter_audit';
                // LPUSH then LTRIM to keep list length bounded
                redisClient.lpush(key, JSON.stringify(entry)).catch((e) => console.error('Redis LPUSH failed', e));
                const maxLen = parseInt(process.env.SOCKET_AUDIT_LIST_MAX || '10000', 10);
                redisClient.ltrim(key, 0, maxLen - 1).catch((e) => console.error('Redis LTRIM failed', e));
            } catch (e) {
                console.error('Failed to write audit to Redis', e);
            }
        }
        // Always append to local file too (fallback and integration)
        fs.appendFile(LOG_FILE, JSON.stringify(entry) + '\n', (err) => {
            if (err) console.error('Failed to write audit log', err);
        });
    } catch (err) { console.error('auditLog error', err); }
}

app.post('/emit', emitLimiter, async(req, res) => {
    const key = req.header('X-SOCKET-KEY');
    const emitterJwt = req.header('X-EMITTER-JWT') || (req.header('Authorization') ? req.header('Authorization').split(' ')[1] : null);
    const emitterHmac = req.header('X-EMITTER-HMAC');
    if (!key || key !== SOCKET_API_KEY) {
        auditLog(req, 'invalid_key', { key });
        return res.status(403).json({ error: 'invalid key' });
    }
    if (!emitterJwt) {
        auditLog(req, 'missing_emitter_jwt', {});
        return res.status(403).json({ error: 'missing emitter jwt' });
    }
    try {
        const epayload = jwt.verify(emitterJwt, SOCKET_JWT_SECRET);
        if (!epayload || epayload.api_key !== SOCKET_API_KEY) {
            console.warn('Emitter JWT failed validation (api key mismatch).', { api_key: epayload && epayload.api_key });
            auditLog(req, 'invalid_emitter_token', { api_key: epayload && epayload.api_key });
            return res.status(403).json({ error: 'invalid emitter token' });
        }
        console.log('Emitter JWT validated (iss=', epayload.iss, ', api_key=', epayload.api_key, ')');
    } catch (err) {
        auditLog(req, 'invalid_emitter_jwt', { message: err.message });
        return res.status(403).json({ error: 'invalid emitter jwt' });
    }
    // Verify HMAC over raw JSON body
    if (!emitterHmac) { auditLog(req, 'missing_hmac', {}); return res.status(403).json({ error: 'missing emitter hmac' }); }
    try {
        const raw = (req.rawBody && req.rawBody.length) ? req.rawBody : JSON.stringify(req.body || {});
        const expected = crypto.createHmac('sha256', SOCKET_JWT_SECRET).update(raw).digest('hex');
        const got = emitterHmac.startsWith('hex:') ? emitterHmac.slice(4) : emitterHmac;
        const a = Buffer.from(got, 'hex');
        const b = Buffer.from(expected, 'hex');
        if (a.length !== b.length || !crypto.timingSafeEqual(a, b)) {
            console.warn('Emitter HMAC mismatch', { expected, got });
            auditLog(req, 'invalid_hmac', { expected, got });
            return res.status(403).json({ error: 'invalid emitter hmac' });
        }
    } catch (err) {
        return res.status(403).json({ error: 'hmac verification failed' });
    }
    // Next: check timestamp and nonce to prevent replay attacks
    const ts = parseInt(req.body && (req.body.ts || req.body.timestamp) || 0, 10);
    const nonce = req.body && req.body.nonce || null;
    const nowSec = Math.floor(Date.now() / 1000);
    if (!ts || Math.abs(nowSec - ts) > EMIT_TTL_SECONDS) { auditLog(req, 'invalid_timestamp', { ts, nowSec }); return res.status(400).json({ error: 'invalid or expired timestamp' }); }
    if (!nonce) { auditLog(req, 'nonce_required', {}); return res.status(400).json({ error: 'nonce required' }); }
    if (redisClient) {
        try {
            const redisKey = `nonce:${nonce}`;
            // attempt to set with NX and expiry
            const setRes = await redisClient.set(redisKey, '1', 'EX', EMIT_TTL_SECONDS, 'NX');
            if (setRes !== 'OK') {
                auditLog(req, 'replay_detected', { nonce });
                return res.status(403).json({ error: 'replay detected' });
            }
        } catch (e) {
            console.warn('Redis nonce check error', e);
            auditLog(req, 'redis_error', { message: e.message });
            // fallback to in-memory
            if (nonceCache.has(nonce)) { auditLog(req, 'replay_detected', { nonce }); return res.status(403).json({ error: 'replay detected' }); }
            nonceCache.set(nonce, Date.now() + (EMIT_TTL_SECONDS * 1000));
        }
    } else {
        if (nonceCache.has(nonce)) { auditLog(req, 'replay_detected', { nonce }); return res.status(403).json({ error: 'replay detected' }); }
        // register nonce
        nonceCache.set(nonce, Date.now() + (EMIT_TTL_SECONDS * 1000));
    }
    const { user_id, event = 'planner_update', payload = {} } = req.body || {};
    if (!user_id) return res.status(400).json({ error: 'user_id is required' });
    const room = `user:${user_id}`;
    console.log(`Emitter emitted event=${event} user_id=${user_id} payload=${JSON.stringify(payload)}`);
    auditLog(req, 'emit', { user_id, event, payload });
    io.to(room).emit(event, payload);
    // log how many sockets are currently in the target room
    try {
        const members = await io.in(room).allSockets();
        console.log(`Emit: room=${room} members=${members.size}; sockets=${Array.from(members).slice(0,10).join(',')}`);
    } catch (e) {
        console.warn('Emit: failed to count room members', e);
    }
    return res.json({ success: true });
});

// simple health endpoint for CI or uptime checks
app.get('/health', (req, res) => {
    res.json({ ok: true, time: Date.now() });
});

server.listen(PORT, () => console.log(`Socket server listening on ${PORT}`));