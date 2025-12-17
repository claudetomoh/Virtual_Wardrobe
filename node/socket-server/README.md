# Virtual Wardrobe - Socket.io Server

This simple Node-based Socket.io server allows PHP backends to emit events to connected clients via an HTTP POST endpoint.

Run locally:

```bash
cd node/socket-server
npm install
npm start
```

Set the `SOCKET_API_KEY` env var to secure the `/emit` endpoint. The default port is `3000`.

PHP should POST to `/emit` with header `X-SOCKET-KEY` and payload `{ user_id, event, payload }`.
For added security the server validates the following headers on the emitter request:

- `X-EMITTER-JWT`: a short-lived HS256 JWT signed with `SOCKET_JWT_SECRET` (contains `api_key` and `iss` claims).
- `X-EMITTER-HMAC`: an HMAC-SHA256 hex digest of the raw JSON body, computed with `SOCKET_JWT_SECRET`.
Additionally the server provides replay protection and rate limiting. Audit logs are written to `node/socket-server/logs/emitter_audit.log` by default.

- Replay protection: emitter requests must include `nonce` and `ts` fields in the JSON body; the server rejects requests with an already-used `nonce` or a `ts` outside the permitted window (default `SOCKET_EMIT_TTL=60` seconds).
- Rate limiting: the `/emit` endpoint is rate limited per emitter API key or per IP (see environment variables below).
	For distributed deployments set `SOCKET_REDIS_URL` to enable Redis-backed nonce storage to protect against replay attacks across instances.

Environment variables used for tuning and Redis audit/storage:

- `SOCKET_EMIT_TTL` (seconds): TTL for timestamp and nonce, defaults to 60.
- `SOCKET_RATE_MAX` (integer): number of allowed emit requests per window (defaults to 60).
- `SOCKET_RATE_WINDOW_MS` (ms): length of rate limiting window in milliseconds (defaults to 60000).
- `SOCKET_REDIS_PASSWORD` (string): optional Redis password when not using a URL with embedded credentials.
- `SOCKET_AUDIT_KEY` (string): Redis list key used for audit logs (defaults to `emitter_audit`).
- `SOCKET_AUDIT_LIST_MAX` (int): max number of audit entries to keep in the Redis list (defaults to 10000).
