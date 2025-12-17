const { test, expect, chromium } = require('@playwright/test');
const jwt = require('jsonwebtoken');
const axios = require('axios');

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

test('emit reaches browser listener via socket.io', async() => {
    const browser = await chromium.launch();
    const context = await browser.newContext();
    const page = await context.newPage();

    await page.goto('about:blank');
    // inject socket.io client
    await page.addScriptTag({ url: 'https://cdn.socket.io/4.7.2/socket.io.min.js' });

    const userId = 1;
    const clientToken = createClientJwt(userId);
    await page.evaluate(({ url, token }) => {
        return new Promise((resolve) => {
            window.socket = io(url, { auth: { token }, transports: ['websocket'] });
            window.__received = null;
            window.socket.on('planner_update', (payload) => { window.__received = payload; });
            window.socket.on('connect', () => resolve(true));
        });
    }, { url: SOCKET_SERVER_URL, token: clientToken });

    // broadcast emit
    const emitterToken = createEmitterJwt();
    const nonce = require('crypto').randomBytes(12).toString('hex');
    const ts = Math.floor(Date.now() / 1000);
    const body = { user_id: userId, event: 'planner_update', payload: { message: 'e2e test' }, nonce, ts };
    const hmac = createEmitterHmac(body);
    const resp = await axios.post(`${SOCKET_SERVER_URL}/emit`, body, { headers: { 'X-SOCKET-KEY': SOCKET_API_KEY, 'X-EMITTER-JWT': emitterToken, 'X-EMITTER-HMAC': hmac } });
    expect(resp.data.success).toBe(true);

    // wait for event on browser
    const received = await page.waitForFunction(() => !!window.__received, null, { timeout: 5000 });
    expect(await page.evaluate(() => window.__received)).toEqual({ message: 'e2e test' });

    await browser.close();
});