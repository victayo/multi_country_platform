<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket End-to-End Test</title>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <style>
        :root {
            --bg: #f6f7fb;
            --panel: #ffffff;
            --ink: #1d2433;
            --muted: #5a6478;
            --accent: #0f8a5f;
            --warn: #d97706;
            --error: #dc2626;
            --line: #dde3ef;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--ink);
        }

        .container {
            max-width: 980px;
            margin: 0 auto;
            padding: 20px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 14px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 22px;
        }

        p {
            margin: 6px 0;
            color: var(--muted);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        label {
            font-size: 13px;
            color: var(--muted);
            display: block;
            margin-bottom: 4px;
        }

        input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
        }

        .row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        button {
            border: 0;
            border-radius: 8px;
            padding: 9px 12px;
            cursor: pointer;
            font-weight: 600;
            background: #1f3c88;
            color: #fff;
        }

        button.secondary {
            background: #4b5563;
        }

        .badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-left: 6px;
            background: #eef2ff;
            color: #3730a3;
        }

        .ok { color: var(--accent); }
        .warn { color: var(--warn); }
        .error { color: var(--error); }

        pre {
            margin: 0;
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            max-height: 420px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-word;
            font-size: 12px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="panel">
            <h1>WebSocket End-to-End Test Page</h1>
            <p>Subscribes to employee and checklist channels, prints incoming events below, and can emit test events from the backend.</p>
            <p>Connection status: <strong id="status" class="warn">not connected</strong><span id="socket-id" class="badge">socket: n/a</span></p>
        </div>

        <div class="panel">
            <div class="grid">
                <div>
                    <label for="appKey">Pusher Key</label>
                    <input id="appKey" value="{{ env('PUSHER_APP_KEY', '') }}">
                </div>
                <div>
                    <label for="cluster">Cluster</label>
                    <input id="cluster" value="{{ env('PUSHER_APP_CLUSTER', 'eu') }}">
                </div>
                <div>
                    <label for="country">Country Code</label>
                    <input id="country" value="USA">
                </div>
                <div>
                    <label for="wsHost">Host (optional for local)</label>
                    <input id="wsHost" placeholder="e.g. localhost or soketi">
                </div>
                <div>
                    <label for="wsPort">WS Port</label>
                    <input id="wsPort" type="number" value="6001">
                </div>
                <div>
                    <label for="wssPort">WSS Port</label>
                    <input id="wssPort" type="number" value="443">
                </div>
            </div>

            <div class="row">
                <button id="connectBtn">Connect + Subscribe</button>
                <button id="emitBtn" class="secondary">Emit Test Events</button>
                <button id="clearBtn" class="secondary">Clear Log</button>
            </div>
        </div>

        <div class="panel">
            <pre id="log"></pre>
        </div>
    </div>

    <script>
        const state = {
            echo: null,
            subscribedCountry: null,
        };

        const logEl = document.getElementById('log');
        const statusEl = document.getElementById('status');
        const socketIdEl = document.getElementById('socket-id');

        function nowIso() {
            return new Date().toISOString();
        }

        function writeLog(label, payload, level = 'ok') {
            const line = `[${nowIso()}] ${label}\n${JSON.stringify(payload, null, 2)}\n\n`;
            logEl.textContent = line + logEl.textContent;
            console.log(label, payload);
            statusEl.className = level;
        }

        function qs(id) {
            return document.getElementById(id).value.trim();
        }

        function destroyEcho() {
            if (!state.echo) {
                return;
            }

            try {
                state.echo.disconnect();
            } catch (e) {
                writeLog('disconnect warning', { message: e.message }, 'warn');
            }

            state.echo = null;
            state.subscribedCountry = null;
            socketIdEl.textContent = 'socket: n/a';
            statusEl.textContent = 'not connected';
            statusEl.className = 'warn';
        }

        function buildEcho() {
            const EchoCtor = window.Echo?.default || window.Echo || window.LaravelEcho;
            if (!EchoCtor) {
                throw new Error('Laravel Echo constructor was not found on window.');
            }

            const appKey = qs('appKey');
            const cluster = qs('cluster') || 'eu';
            const wsHost = qs('wsHost');
            const wsPort = Number(qs('wsPort') || 6001);
            const wssPort = Number(qs('wssPort') || 443);

            const useTls = !wsHost;
            const options = {
                broadcaster: 'pusher',
                key: appKey,
                cluster,
                forceTLS: useTls,
                enabledTransports: ['ws', 'wss'],
                disableStats: true,
            };

            if (wsHost) {
                options.wsHost = wsHost;
                options.wsPort = wsPort;
                options.wssPort = wssPort;
                options.forceTLS = false;
            }

            writeLog('echo options', options, 'warn');
            return new EchoCtor(options);
        }

        function connectAndSubscribe() {
            destroyEcho();

            const country = (qs('country') || 'USA').toUpperCase();
            const employeesChannel = `employees.${country}`;
            const checklistChannel = `checklist.${country}`;

            state.echo = buildEcho();
            state.subscribedCountry = country;

            const connection = state.echo.connector.pusher.connection;
            connection.bind('connected', () => {
                const socketId = state.echo.socketId();
                socketIdEl.textContent = `socket: ${socketId || 'unknown'}`;
                statusEl.textContent = 'connected';
                statusEl.className = 'ok';
                writeLog('socket connected', { socketId, country }, 'ok');
            });

            connection.bind('error', (err) => {
                statusEl.textContent = 'connection error';
                statusEl.className = 'error';
                writeLog('socket error', err, 'error');
            });

            connection.bind('state_change', (states) => {
                statusEl.textContent = `state: ${states.current}`;
                statusEl.className = states.current === 'connected' ? 'ok' : 'warn';
                writeLog('socket state changed', states, 'warn');
            });

            state.echo.channel(employeesChannel)
                .subscribed(() => writeLog('subscribed', { channel: employeesChannel }, 'ok'))
                .listen('.employee.updated', (event) => {
                    writeLog('employee.updated received', event, 'ok');
                });

            state.echo.channel(checklistChannel)
                .subscribed(() => writeLog('subscribed', { channel: checklistChannel }, 'ok'))
                .listen('.checklist.updated', (event) => {
                    writeLog('checklist.updated received', event, 'ok');
                });

            writeLog('subscription configured', {
                country,
                channels: [employeesChannel, checklistChannel],
                events: ['employee.updated', 'checklist.updated']
            }, 'warn');
        }

        async function emitTestEvents() {
            const country = (qs('country') || 'USA').toUpperCase();
            const url = `/ws-test/emit?country=${encodeURIComponent(country)}&employee_id=12345`;

            writeLog('emitting test events', { url }, 'warn');

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const payload = await response.json();
            if (!response.ok) {
                writeLog('emit failed', payload, 'error');
                return;
            }

            writeLog('emit endpoint response', payload, 'ok');
        }

        document.getElementById('connectBtn').addEventListener('click', () => {
            try {
                connectAndSubscribe();
            } catch (e) {
                writeLog('connect failed', { message: e.message }, 'error');
            }
        });

        document.getElementById('emitBtn').addEventListener('click', () => {
            emitTestEvents().catch((e) => {
                writeLog('emit request failed', { message: e.message }, 'error');
            });
        });

        document.getElementById('clearBtn').addEventListener('click', () => {
            logEl.textContent = '';
        });

        writeLog('ready', {
            message: 'Click "Connect + Subscribe", then "Emit Test Events". Watch this log and browser console for incoming events.'
        }, 'warn');
    </script>
</body>
</html>
