const { test, expect, chromium } = require('@playwright/test');
const jwt = require('jsonwebtoken');
const axios = require('axios');

const SOCKET_SERVER_URL = process.env.SOCKET_SERVER_URL || 'http://localhost:3000';
const SOCKET_API_KEY = process.env.SOCKET_API_KEY || 'dev-secret-key';
const SOCKET_JWT_SECRET = process.env.SOCKET_JWT_SECRET || 'socket-secret';
const crypto = require('crypto');
const APP_URL = process.env.APP_URL || 'http://localhost:8080/Virtual_Wardrobe';

function createEmitterJwt(ttl = 60) {
    const iat = Math.floor(Date.now() / 1000);
    return jwt.sign({ api_key: SOCKET_API_KEY, iss: 'test-emitter', iat, exp: iat + ttl }, SOCKET_JWT_SECRET, { algorithm: 'HS256' });
}

function createEmitterHmac(body) { return crypto.createHmac('sha256', SOCKET_JWT_SECRET).update(JSON.stringify(body)).digest('hex'); }

// This test logs into the PHP app and asserts that the dashboard client receives a planner_update
// event via the Node socket server which increments the dashboard's liveBadge. Skip locally if DB not configured.
test.skip(process.env.SKIP_DASHBOARD_UI === 'true', 'Skipping Dashboard UI test via env SKIP_DASHBOARD_UI');

test('full app: dashboard client receives planner_update via socket.io', async() => {
    const browser = await chromium.launch();
    const context = await browser.newContext();
    const page = await context.newPage();
    // Capture page console messages to aid debugging in CI
    page.on('console', msg => console.log('[page-console] ' + msg.type() + ': ' + msg.text()));
    page.on('pageerror', err => console.log('[page-error] ' + err.toString()));

    // Navigate to login page and login
    await page.goto(`${APP_URL}/src/auth/login.php`);
    // verify the socket server health endpoint is reachable from the browser (debugging CORS/network issues)
    try {
        await page.waitForFunction(async(url) => {
            try { const res = await fetch(url + '/health'); if (!res.ok) return false; const j = await res.json(); return j && j.ok; } catch (e) { return false; }
        }, SOCKET_SERVER_URL, { timeout: 15000 });
    } catch (e) { console.warn('Socket server health check failed from browser'); }
    await page.fill('input[name="email"]', 'demo@wardrobe.local');
    await page.fill('input[name="password"]', 'demopass');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type="submit"]')
    ]);

    // Ensure we are on dashboard or rendered page, navigate explicitly
    await page.goto(`${APP_URL}/src/dashboard.php`);

    // if no liveBadge present, fail early
    await expect(page.locator('#liveBadge')).toBeVisible();

    // Ensure socket.io client is available and has connected (avoid race condition)
    try { await page.addScriptTag({ url: 'https://cdn.socket.io/4.7.2/socket.io.min.js' }); } catch (e) { /* ignore loading duplicate */ }
    await page.waitForFunction(() => window.socket && window.socket.connected === true, { timeout: 15000 });

    // Ensure socket client has connected (avoid race condition)
    try { await page.addScriptTag({ url: 'https://cdn.socket.io/4.7.2/socket.io.min.js' }); } catch (e) { /* ignore loading duplicate */ }
    await page.waitForFunction(() => window.socket && window.socket.connected === true, { timeout: 15000 });

    // Log client socket id and server meta
    const metaUrl = await page.evaluate(() => document.querySelector('meta[name="socket-server-url"]').getAttribute('content'));
    const sockId = await page.evaluate(() => (window.socket && window.socket.id) || null);
    console.log('[test] socket-server-url=', metaUrl, ' page socketId=', sockId);

    // Extract user id from meta
    const userId = await page.evaluate(() => document.querySelector('meta[name="user-id"]').content);
    expect(parseInt(userId)).toBeGreaterThan(0);
    const emitterToken = createEmitterJwt();
    const nonce = require('crypto').randomBytes(12).toString('hex');
    const ts = Math.floor(Date.now() / 1000);
    const body = { user_id: parseInt(userId), event: 'planner_update', payload: { message: 'dashboard ui e2e test' }, nonce, ts };
    const hmac = createEmitterHmac(body);
    const resp = await axios.post(`${SOCKET_SERVER_URL}/emit`, body, { headers: { 'X-SOCKET-KEY': SOCKET_API_KEY, 'X-EMITTER-JWT': emitterToken, 'X-EMITTER-HMAC': hmac } });
    expect(resp.data && resp.data.success).toBe(true);

    // Wait for lastPlannerUpdate to be set and for counter to increment
    await page.waitForFunction(() => !!window.__lastPlannerUpdate, { timeout: 15000 });
    await page.waitForFunction((s) => parseInt(document.getElementById('liveBadge').innerText) > s, startVal, { timeout: 15000 });

    const val = await page.evaluate(() => parseInt(document.getElementById('liveBadge').innerText));
    expect(val).toBeGreaterThan(startVal);

    await browser.close();
});