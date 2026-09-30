const byId = (id) => document.getElementById(id);
const numberFormat = new Intl.NumberFormat();
const durations = [];
const serverClockAtRender = Date.parse(document.body.dataset.serverTime);
const clientClockAtRender = Date.now();

function showMilliseconds(value) {
    return `${Number(value).toFixed(1)} ms`;
}

function updatePageMetrics() {
    const navigation = performance.getEntriesByType('navigation')[0];
    if (!navigation) return;

    const serverTime = navigation.serverTiming?.find((entry) => entry.name === 'app')?.duration;
    const roundTrip = navigation.responseStart - navigation.requestStart;
    const bytes = navigation.transferSize || navigation.encodedBodySize;

    if (Number.isFinite(serverTime)) byId('server-time').innerHTML = `${serverTime.toFixed(1)}<small> ms</small>`;
    if (Number.isFinite(roundTrip)) byId('round-trip').innerHTML = `${roundTrip.toFixed(1)}<small> ms</small>`;
    if (bytes > 0) byId('transfer-size').innerHTML = `${(bytes / 1024).toFixed(1)}<small> KB</small>`;
}

function updateClock() {
    const elapsed = Date.now() - clientClockAtRender;
    const serverNow = Number.isFinite(serverClockAtRender)
        ? new Date(serverClockAtRender + elapsed)
        : new Date();

    byId('server-clock').textContent = new Intl.DateTimeFormat(undefined, {
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
    }).format(serverNow);
}

async function checkHealth() {
    const label = byId('health-label');
    const dot = byId('health-dot');
    const started = performance.now();
    label.textContent = 'Checking endpoint';
    dot.parentElement.classList.remove('is-error');

    try {
        const response = await fetch(`/health?probe=${Date.now()}`, { cache: 'no-store' });
        const result = await response.json();
        if (!response.ok || result.status !== 'ok') throw new Error('Health check failed');
        label.textContent = 'Endpoint responding';
        byId('health-details').textContent = `HTTP ${response.status} · ${showMilliseconds(performance.now() - started)}`;
    } catch {
        label.textContent = 'Endpoint unreachable';
        dot.parentElement.classList.add('is-error');
        byId('health-details').textContent = 'Could not reach GET /health';
    }
}

async function runBenchmark() {
    const button = byId('run-benchmark');
    const result = byId('benchmark-result');
    const resultTitle = result.querySelector('.result-copy strong');
    const resultDetail = result.querySelector('.result-copy span');
    const resultTime = byId('benchmark-time');
    const iterations = Math.min(500000, Math.max(1000, Number.parseInt(byId('iterations').value, 10) || 1000));
    const started = performance.now();

    byId('iterations').value = iterations;
    button.disabled = true;
    button.querySelector('span:first-child').textContent = 'Running…';
    resultTitle.textContent = 'Working on the server';
    resultDetail.textContent = `${numberFormat.format(iterations)} SHA-256 iterations`;

    try {
        const response = await fetch(`/benchmark?iterations=${iterations}&probe=${Date.now()}`, { cache: 'no-store' });
        const payload = await response.json();
        if (!response.ok) throw new Error('Benchmark request failed');
        resultTitle.textContent = `${numberFormat.format(payload.iterations)} iterations complete`;
        resultDetail.textContent = `Server ${showMilliseconds(payload.durationMs)} · ${payload.memoryMb} MB peak · ${payload.checksum}`;
        resultTime.textContent = showMilliseconds(performance.now() - started);
    } catch {
        resultTitle.textContent = 'Benchmark request failed';
        resultDetail.textContent = 'The endpoint did not return a valid result.';
        resultTime.textContent = '—';
    } finally {
        button.disabled = false;
        button.querySelector('span:first-child').textContent = 'Run benchmark';
    }
}

function renderBurstChart(values) {
    const chart = byId('burst-chart');
    chart.replaceChildren();
    const maximum = Math.max(...values, 1);

    for (const value of values) {
        const bar = document.createElement('span');
        bar.className = `chart-bar ${value < maximum * 0.55 ? 'is-fast' : ''} ${value > maximum * 0.85 ? 'is-slow' : ''}`;
        bar.style.height = `${Math.max(4, (value / maximum) * 76)}px`;
        bar.title = showMilliseconds(value);
        chart.append(bar);
    }

    byId('chart-maximum').textContent = `${showMilliseconds(maximum)} max`;
}

async function runBurst() {
    const button = byId('run-burst');
    const count = Number.parseInt(byId('burst-count').value, 10);
    button.disabled = true;
    button.querySelector('span:first-child').textContent = 'Running…';

    const results = await Promise.all(Array.from({ length: count }, async (_, index) => {
        const started = performance.now();
        try {
            const response = await fetch(`/health?burst=${Date.now()}-${index}`, { cache: 'no-store' });
            if (!response.ok) throw new Error('Health request failed');
            await response.json();
            return { duration: performance.now() - started, ok: true };
        } catch {
            return { duration: performance.now() - started, ok: false };
        }
    }));

    const passed = results.filter((result) => result.ok).map((result) => result.duration).sort((a, b) => a - b);
    const p50 = passed[Math.floor((passed.length - 1) * 0.5)];
    const p95 = passed[Math.ceil(passed.length * 0.95) - 1];
    byId('burst-p50').textContent = p50 === undefined ? '—' : showMilliseconds(p50);
    byId('burst-p95').textContent = p95 === undefined ? '—' : showMilliseconds(p95);
    byId('burst-success').textContent = `${passed.length}/${count}`;
    renderBurstChart(results.map((result) => result.duration));

    button.disabled = false;
    button.querySelector('span:first-child').textContent = 'Run again';
}

byId('run-benchmark').addEventListener('click', runBenchmark);
byId('run-burst').addEventListener('click', runBurst);
byId('check-health').addEventListener('click', checkHealth);
updatePageMetrics();
updateClock();
checkHealth();
window.setInterval(updateClock, 1000);