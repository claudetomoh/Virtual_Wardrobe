const Redis = require('ioredis');

const SOCKET_REDIS_URL = process.env.SOCKET_REDIS_URL || null;
const SOCKET_REDIS_PASSWORD = process.env.SOCKET_REDIS_PASSWORD || null;

async function getRedisIfAvailable() {
    if (!SOCKET_REDIS_URL) return null;
    const opts = { maxRetriesPerRequest: parseInt(process.env.SOCKET_REDIS_MAX_RETRIES || '1', 10), enableOfflineQueue: false };
    if (SOCKET_REDIS_PASSWORD) opts.password = SOCKET_REDIS_PASSWORD;
    const rc = new Redis(SOCKET_REDIS_URL, opts);
    try {
        await rc.ping();
        return rc;
    } catch (e) {
        try { rc.disconnect(); } catch (_) {}
        return null;
    }
}

module.exports = { getRedisIfAvailable };