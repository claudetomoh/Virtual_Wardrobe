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

// This test emulates the dashboard behavior by maintaining a liveBadge element
// that increments when the planner_update event arrives.

test('dashboard client receives planner events and increments live badge', async() => {
    const browser = await chromium.launch();
    const context = await browser.newContext();
    const page = await context.newPage();

    await page.goto('about:blank');
    await page.addScriptTag({ url: 'https://cdn.socket.io/4.7.2/socket.io.min.js' });

    // add liveBadge element and socket handler
    const userId = 5;
    const clientJwt = createClientJwt(userId);
    await page.evaluate(({ url, token }) => {
        document.body.innerHTML = '<div id="liveBadge">0</div>';
        window.socket = io(url, { auth: { token }, transports: ['websocket'] });
        window.socket.on('planner_update', (payload) => {
            const el = document.getElementById('liveBadge');
            if (!el) return;
            el.innerText = (parseInt(el.innerText || '0') + 1).toString();
        });
        return true;
    }, { url: SOCKET_SERVER_URL, token: clientJwt });

    await page.waitForTimeout(200);
    // emit event for this user
    const emitterToken = createEmitterJwt();
    const nonce = require('crypto').randomBytes(12).toString('hex');
    const ts = Math.floor(Date.now() / 1000);
    const body = { user_id: userId, event: 'planner_update', payload: { message: 'dashboard test' }, nonce, ts };
    const hmac = createEmitterHmac(body);
    const resp = await axios.post(`${SOCKET_SERVER_URL}/emit`, body, { headers: { 'X-SOCKET-KEY': SOCKET_API_KEY, 'X-EMITTER-JWT': emitterToken, 'X-EMITTER-HMAC': hmac } });
    expect(resp.data.success).toBe(true);

    await page.waitForFunction(() => parseInt(document.getElementById('liveBadge').innerText) > 0, null, { timeout: 5000 });
    const val = await page.evaluate(() => parseInt(document.getElementById('liveBadge').innerText));
    expect(val).toBeGreaterThan(0);

    await browser.close();
});