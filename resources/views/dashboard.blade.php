<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f1f3ec">
    <title>Tunnel Gauge · Laravel performance console</title>
    <link rel="stylesheet" href="/assets/dashboard.css">
    <script src="/assets/dashboard.js" defer></script>
</head>
<body data-server-time="{{ $snapshot['time'] }}">
    <header class="topbar">
        <a class="brand" href="/" aria-label="Tunnel Gauge home">
            <span class="brand-mark" aria-hidden="true"><i></i><i></i><i></i></span>
            <span>tunnel<span class="brand-light">gauge</span></span>
        </a>
        <div class="topbar-meta">
            <span class="environment"><span class="status-dot"></span>LIVE PROBE</span>
            <span class="topbar-divider"></span>
            <span class="topbar-label">Laravel {{ $snapshot['laravel'] }}</span>
        </div>
    </header>

    <main class="shell">
        <section class="intro" aria-labelledby="page-title">
            <div>
                <p class="eyebrow"><span>01</span> / EDGE PERFORMANCE</p>
                <h1 id="page-title">Your tunnel,<br><em>under the microscope.</em></h1>
                <p class="intro-copy">A live read on the path between this browser and your Laravel app.</p>
            </div>
            <div class="live-address">
                <span class="live-label">CURRENT ORIGIN</span>
                <span class="origin-value" id="origin-value">{{ $snapshot['protocol'] }}://{{ $snapshot['host'] }}</span>
                <span class="live-pulse"><span class="status-dot"></span>Listening on every request</span>
            </div>
        </section>

        <section class="metrics" aria-label="Current page metrics">
            <div class="metric">
                <span class="metric-label">SERVER TIME</span>
                <strong id="server-time">--<small> ms</small></strong>
                <span class="metric-note">Laravel response</span>
            </div>
            <div class="metric">
                <span class="metric-label">ROUND TRIP</span>
                <strong id="round-trip">--<small> ms</small></strong>
                <span class="metric-note">Document request</span>
            </div>
            <div class="metric">
                <span class="metric-label">TRANSFERRED</span>
                <strong id="transfer-size">--<small> KB</small></strong>
                <span class="metric-note">HTML response</span>
            </div>
            <div class="metric">
                <span class="metric-label">SERVER CLOCK</span>
                <strong class="clock-value" id="server-clock">--:--:--</strong>
                <span class="metric-note" id="server-date">{{ $snapshot['time'] }}</span>
            </div>
        </section>

        <div class="workspace">
            <div class="primary-column">
                <section class="panel benchmark-panel" aria-labelledby="benchmark-title">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow"><span>02</span> / SERVER LOAD</p>
                            <h2 id="benchmark-title">CPU benchmark</h2>
                        </div>
                        <span class="panel-index">01 — 500K</span>
                    </div>
                    <p class="panel-copy">Run a repeatable SHA-256 workload on the server. Compare the same iteration count across tunnel and local runs.</p>
                    <div class="control-row">
                        <label class="field-label" for="iterations">Iterations</label>
                        <input id="iterations" type="number" min="1000" max="500000" step="1000" value="100000" inputmode="numeric">
                        <button class="button button-dark" id="run-benchmark" type="button">
                            <span>Run benchmark</span><span class="button-arrow" aria-hidden="true">↗</span>
                        </button>
                    </div>
                    <div class="benchmark-result" id="benchmark-result" aria-live="polite">
                        <div class="result-mark" aria-hidden="true">⌁</div>
                        <div class="result-copy"><strong>Ready when you are</strong><span>Server work and tunnel round trip are measured separately.</span></div>
                        <span class="result-time" id="benchmark-time">—</span>
                    </div>
                </section>

                <section class="panel burst-panel" aria-labelledby="burst-title">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow"><span>03</span> / REPEATED REQUESTS</p>
                            <h2 id="burst-title">Tunnel burst</h2>
                        </div>
                        <label class="burst-count-label" for="burst-count">REQUESTS
                            <select id="burst-count" aria-label="Number of burst requests">
                                <option value="5">05</option>
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                                <option value="40">40</option>
                            </select>
                        </label>
                    </div>
                    <div class="burst-summary">
                        <p class="panel-copy">Parallel no-cache checks against <code>/health</code>.</p>
                        <button class="button button-outline" id="run-burst" type="button"><span>Start burst</span><span aria-hidden="true">↗</span></button>
                    </div>
                    <div class="burst-stats" aria-live="polite">
                        <div><strong id="burst-p50">--</strong><span>MEDIAN</span></div>
                        <div><strong id="burst-p95">--</strong><span>95TH PERCENTILE</span></div>
                        <div><strong id="burst-success">--</strong><span>SUCCEEDED</span></div>
                    </div>
                    <div class="burst-chart" id="burst-chart" aria-label="Recent request timings"></div>
                    <div class="chart-axis"><span>0 ms</span><span id="chart-maximum">Waiting for a burst</span></div>
                </section>
            </div>

            <aside class="side-column">
                <section class="panel request-panel" aria-labelledby="request-title">
                    <div class="panel-heading compact-heading">
                        <div>
                            <p class="eyebrow"><span>04</span> / REQUEST SNAPSHOT</p>
                            <h2 id="request-title">Coming through</h2>
                        </div>
                    </div>
                    <dl class="request-list">
                        <div><dt>METHOD</dt><dd><span class="method-pill">{{ $snapshot['method'] }}</span></dd></div>
                        <div><dt>PROTOCOL</dt><dd>{{ $snapshot['protocol'] }}</dd></div>
                        <div><dt>CLIENT IP</dt><dd class="mono-value">{{ $snapshot['ip'] }}</dd></div>
                        <div><dt>HOST</dt><dd class="mono-value host-value">{{ $snapshot['host'] }}</dd></div>
                        <div><dt>USER AGENT</dt><dd class="user-agent" title="{{ $snapshot['userAgent'] }}">{{ $snapshot['userAgent'] }}</dd></div>
                    </dl>
                    <div class="health-row">
                        <span class="health-indicator"><span class="status-dot" id="health-dot"></span><span id="health-label">Checking endpoint</span></span>
                        <button class="icon-button" id="check-health" type="button" aria-label="Check health endpoint again" title="Check health again">↻</button>
                    </div>
                    <div class="health-details" id="health-details">GET /health</div>
                </section>

                <section class="runtime-strip" aria-label="Runtime details">
                    <span class="runtime-label">RUNTIME</span>
                    <div><span>PHP</span><strong>{{ $snapshot['php'] }}</strong></div>
                    <div><span>LARAVEL</span><strong>{{ $snapshot['laravel'] }}</strong></div>
                    <a href="/health" target="_blank" rel="noreferrer">JSON HEALTH <span aria-hidden="true">↗</span></a>
                </section>

                <p class="footnote">Each probe is uncached. Results include the full browser-to-server path unless noted.</p>
            </aside>
        </div>
        <footer class="page-footer"><span>TUNNEL GAUGE <span class="footer-separator">/</span> REQUESTS ARE THE TEST</span><span id="footer-host">{{ $snapshot['host'] }}</span></footer>
    </main>
</body>
</html>