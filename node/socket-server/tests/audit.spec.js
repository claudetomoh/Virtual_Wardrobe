const { test, expect } = require('@playwright/test');
const axios = require('axios');
const fs = require('fs');
const path = require('path');

const SOCKET_SERVER_URL = process.env.SOCKET_SERVER_URL || 'http://localhost:3000';
const LOG_FILE = process.env.SOCKET_LOG_FILE || path.join(__dirname, '..', 'logs', 'emitter_audit.log');

// Ensure we target the proper file name as used by the server

test('audit log writes invalid_key attempts', async() => {
    // Send invalid key
    const resp = await axios.post(`${SOCKET_SERVER_URL}/emit`, { user_id: 999, event: 'planner_update', payload: {} }, { headers: { 'X-SOCKET-KEY': 'wrong-key' }, validateStatus: s => s < 600 });
    expect(resp.status).toBe(403);
    expect(resp.data && resp.data.error).toBe('invalid key');

    // Give server a moment to write
    await new Promise(r => setTimeout(r, 200));

    // Read log file or use Redis if reachable
    const { getRedisIfAvailable } = require('./helpers');
    const rc = await getRedisIfAvailable();
    if (rc) {
        const key = process.env.SOCKET_AUDIT_KEY || 'emitter_audit';
        await new Promise(r => setTimeout(r, 200));
        const items = await rc.lrange(key, 0, 10);
        expect(items.length).toBeGreaterThan(0);
        const parsed = items.map(i => JSON.parse(i));
        const found = parsed.find(e => e && e.type === 'invalid_key');
        expect(found).toBeTruthy();
        expect(found.headers['x-socket-key']).toBe('wrong-key');
        rc.disconnect();
    } else {
        const file = LOG_FILE;
        expect(fs.existsSync(file)).toBeTruthy();
        const content = fs.readFileSync(file, 'utf8').trim();
        expect(content.length).toBeGreaterThan(0);
        const lines = content.split('\n').map(l => l.trim()).filter(Boolean);
        const parsed = lines.map(l => JSON.parse(l));
        const found = parsed.reverse().find(e => e && e.type === 'invalid_key');
        expect(found).toBeTruthy();
        expect(found.headers['x-socket-key']).toBe('wrong-key');
    }
});