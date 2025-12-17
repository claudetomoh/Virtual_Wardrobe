const { test, expect } = require('@playwright/test');
const axios = require('axios');
const jwt = require('jsonwebtoken');
const crypto = require('crypto');

const SOCKET_SERVER_URL = process.env.SOCKET_SERVER_URL || 'http://localhost:3000';
const SOCKET_API_KEY = process.env.SOCKET_API_KEY || 'dev-secret-key';
const SOCKET_JWT_SECRET = process.env.SOCKET_JWT_SECRET || 'socket-secret';

function createEmitterJwt(ttl = 60) {
    const iat = Math.floor(Date.now() / 1000);
    return jwt.sign({ api_key: SOCKET_API_KEY, iss: 'test-emitter', iat, exp: iat + ttl }, SOCKET_JWT_SECRET, { algorithm: 'HS256' });
}

function createEmitterHmac(body) { return crypto.createHmac('sha256', SOCKET_JWT_SECRET).update(JSON.stringify(body)).digest('hex'); }

test('rejects emit requests without valid HMAC', async() => {
    const nonce = require('crypto').randomBytes(12).toString('hex');
    const ts = Math.floor(Date.now() / 1000);
    const body = { user_id: 1, event: 'planner_update', payload: { message: 'hmac-test' }, nonce, ts };
    const token = createEmitterJwt();
    // bad hmac
    const resp = await axios.post(`${SOCKET_SERVER_URL}/emit`, body, { headers: { 'X-SOCKET-KEY': SOCKET_API_KEY, 'X-EMITTER-JWT': token, 'X-EMITTER-HMAC': 'deadbeef' }, validateStatus: s => s < 600 });
    expect(resp.status).toBe(403);
    expect(resp.data && resp.data.error).toBeTruthy();
    // verify audit log contains invalid_hmac entry
    const { getRedisIfAvailable } = require('./helpers');
    const rc = await getRedisIfAvailable();
    if (rc) {
        const key = process.env.SOCKET_AUDIT_KEY || 'emitter_audit';
        await new Promise(r => setTimeout(r, 200));
        const items = await rc.lrange(key, 0, 10);
        expect(items.length).toBeGreaterThan(0);
        const parsed = items.map(i => JSON.parse(i));
        const found = parsed.find(e => e && e.type === 'invalid_hmac');
        expect(found).toBeTruthy();
        rc.disconnect();
    } else {
        const path = require('path');
        const fs = require('fs');
        const logPath = path.join(__dirname, '..', 'logs', 'emitter_audit.log');
        await new Promise(r => setTimeout(r, 200));
        const content = fs.readFileSync(logPath, 'utf8').trim();
        const lines = content.split('\n').map(l => l.trim()).filter(Boolean);
        const parsed = lines.map(l => JSON.parse(l));
        const found = parsed.reverse().find(e => e && e.type === 'invalid_hmac');
        expect(found).toBeTruthy();
    }
});