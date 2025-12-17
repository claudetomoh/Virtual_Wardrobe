const axios = require('axios');
const jwt = require('jsonwebtoken');
const crypto = require('crypto');

const SERVER = process.env.SOCKET_SERVER_URL || 'http://localhost:3000';
const API_KEY = process.env.SOCKET_API_KEY || 'dev-secret-key';
const JWT_SECRET = process.env.SOCKET_JWT_SECRET || 'socket-secret';

const argv = require('yargs')
    .option('user', { alias: 'u', type: 'number', default: 1, description: 'User id to emit to' })
    .option('event', { alias: 'e', type: 'string', default: 'planner_update', description: 'Event name' })
    .option('msg', { alias: 'm', type: 'string', default: 'test', description: 'Message payload (string)' })
    .help()
    .argv;

function createEmitterToken(ttl = 60) {
    const iat = Math.floor(Date.now() / 1000);
    const payload = { api_key: API_KEY, iss: 'test-emitter', iat, exp: iat + ttl };
    return jwt.sign(payload, JWT_SECRET, { algorithm: 'HS256' });
}

async function send() {
    try {
        const token = createEmitterToken(60);
        const url = `${SERVER.replace(/\/$/, '')}/emit`;
        const nonce = crypto.randomBytes(12).toString('hex');
        const ts = Math.floor(Date.now() / 1000);
        const body = { user_id: argv.user, event: argv.event, payload: { message: argv.msg }, nonce, ts };
        const raw = JSON.stringify(body);
        const hmac = crypto.createHmac('sha256', JWT_SECRET).update(raw).digest('hex');
        const resp = await axios.post(url, body, {
            headers: {
                'X-SOCKET-KEY': API_KEY,
                'X-EMITTER-JWT': token,
                'X-EMITTER-HMAC': hmac,
                'Content-Type': 'application/json'
            },
            timeout: 3000
        });
        console.log('Emit response:', resp.data);
    } catch (err) {
        if (err.response) console.error('Error from server:', err.response.status, err.response.data);
        else console.error('Emit error:', err.message || err);
        process.exit(1);
    }
}

send();