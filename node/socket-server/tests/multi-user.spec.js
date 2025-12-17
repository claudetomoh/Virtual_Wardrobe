const { test, expect } = require('@playwright/test');
const io = require('socket.io-client');
const jwt = require('jsonwebtoken');

const SOCKET_SERVER_URL = process.env.SOCKET_SERVER_URL || 'http://localhost:3000';
const SOCKET_API_KEY = process.env.SOCKET_API_KEY || 'dev-secret-key';
const SOCKET_JWT_SECRET = process.env.SOCKET_JWT_SECRET || 'socket-secret';
const crypto = require('crypto');

function createClientJwt(userId, ttl = 60) {
    const iat = Math.floor(Date.now() / 1000);
    return jwt.sign({ user_id: userId, iat, exp: iat + ttl }, SOCKET_JWT_SECRET, { algorithm: 'HS256' });
}

function createEmitterJwt(ttl = 60) {
    const iat = Math.floor(Date.now() / 1000);
    return jwt.sign({ api_key: SOCKET_API_KEY, iss: 'test-emitter', iat, exp: iat + ttl }, SOCKET_JWT_SECRET, { algorithm: 'HS256' });
}

function createEmitterHmac(body) { return crypto.createHmac('sha256', SOCKET_JWT_SECRET).update(JSON.stringify(body)).digest('hex'); }

const axios = require('axios');

test('emit only reaches intended user', async() => {
    const user1 = 101;
    const user2 = 202;
    const token1 = createClientJwt(user1);
    const token2 = createClientJwt(user2);

    // connect both
    const socket1 = io(SOCKET_SERVER_URL, { auth: { token: token1 }, reconnectionDelay: 0, transports: ['websocket'] });
    const socket2 = io(SOCKET_SERVER_URL, { auth: { token: token2 }, reconnectionDelay: 0, transports: ['websocket'] });

    await new Promise((res) => {
        let connected = 0;
        socket1.on('connect', () => { if (++connected === 2) res(); });
        socket2.on('connect', () => { if (++connected === 2) res(); });
    });

    let received1 = null;
    let received2 = null;
    socket1.on('planner_update', (p) => { received1 = p; });
    socket2.on('planner_update', (p) => { received2 = p; });

    // emit for user1
    const emitterJwt = createEmitterJwt();
    const nonce = require('crypto').randomBytes(12).toString('hex');
    const ts = Math.floor(Date.now() / 1000);
    const body = { user_id: user1, event: 'planner_update', payload: { m: 'hello u1' }, nonce, ts };
    const hmac = createEmitterHmac(body);
    const resp = await axios.post(`${SOCKET_SERVER_URL}/emit`, body, { headers: { 'X-SOCKET-KEY': SOCKET_API_KEY, 'X-EMITTER-JWT': emitterJwt, 'X-EMITTER-HMAC': hmac } });
    expect(resp.data.success).toBe(true);

    // wait for reception
    await new Promise((r) => setTimeout(r, 600));
    expect(received1).toEqual({ m: 'hello u1' });
    expect(received2).toBeNull();

    socket1.disconnect();
    socket2.disconnect();
});