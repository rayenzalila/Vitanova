<?php
/**
 * Vitanova — Admin Dashboard (Analytics Pro)
 * Responsive to global dark/light theme.
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord — Vitanova Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      (function() {
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', savedTheme);
      })();
    </script>
    <style>
        :root {
            /* Dashboard Specific Overrides that adapt to variables */
            --db-card-bg: var(--white);
            --db-border: var(--border);
            --db-text-muted: var(--muted);
        }

        body { background-color: var(--clr-surface); }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .main { flex: 1; padding: 2.5rem; max-width: 1400px; margin: 0 auto; width: 100%; min-width: 0; }


        /* ── Header ── */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; }
        .header h1 { font-size: 1.75rem; font-weight: 800; color: var(--text); }

        /* ── KPIs ── */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .card {
            background: var(--white); border: 1px solid var(--border); border-radius: var(--radius);
            padding: 1.5rem; transition: var(--transition); position: relative;
            box-shadow: var(--shadow-sm);
        }
        .card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); border-color: var(--green-light); }
        [data-theme="dark"] .card:hover { box-shadow: var(--shadow-hover); }

        .card-title { color: var(--muted); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        .card-value { font-size: 1.8rem; font-weight: 800; margin-top: 0.5rem; color: var(--text); font-family: 'JetBrains Mono', monospace; }
        
        /* ── Charts ── */
        .chart-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; }
        .chart-card { background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; min-height: 400px; box-shadow: var(--shadow-sm); }
        .chart-card h3 { margin-bottom: 1.5rem; font-size: 1.1rem; font-weight: 700; color: var(--text); }
        .chart-container { position: relative; height: 320px; }

        /* ── Table ── */
        .table-card { background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; margin-top: 1.5rem; box-shadow: var(--shadow-sm); }
        .data-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .data-table th { text-align: left; padding: 1rem; color: var(--muted); border-bottom: 1px solid var(--border); }
        .data-table td { padding: 1rem; border-bottom: 1px solid var(--border); color: var(--text); }
        
        .status-pill { padding: 4px 10px; border-radius: 99px; font-size: 0.75rem; font-weight: 700; }
        .status-en_attente { background: #fef3c7; color: #92400e; }
        .status-livree { background: #d1fae5; color: #065f46; }

        [data-theme="dark"] .status-en_attente { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        [data-theme="dark"] .status-livree { background: rgba(16, 185, 129, 0.15); color: #34d399; }

        /* ── Loader ── */
        .loader { position: fixed; inset: 0; background: var(--white); z-index: 10000; display: flex; align-items: center; justify-content: center; transition: opacity 0.4s; }
        .spinner { width: 40px; height: 40px; border: 3px solid var(--green-pale); border-top-color: var(--green-dark); border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 1024px) {
            .main { padding: 1.5rem; }
            .admin-burger { display: block !important; }
        }

        @media (max-width: 768px) {
            .header { margin-bottom: 1.5rem; gap: 0.5rem; }
            .header h1 { font-size: 1.25rem; }
            .chart-grid { grid-template-columns: 1fr; }
            .chart-card { min-height: 350px; padding: 1rem; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        }

        @media (max-width: 480px) {
            .main { padding: 1rem; overflow-x: hidden; }
            .header h1 { font-size: 1.1rem; }
            .header > div { gap: 0.75rem !important; }
            .kpi-grid { grid-template-columns: 1fr; }
            .card-value { font-size: 1.5rem; }
            .theme-toggle { padding: 0.5rem !important; }
            .header span { font-size: 0.75rem !important; }
        }
    </style>
</head>
<body>

<div id="loader" class="loader"><div class="spinner"></div></div>

<div class="admin-layout">
    <aside class="admin-sidebar" id="admin-sidebar">

        <div class="logo">Vita<span>nova</span> Admin</div>
        <nav class="admin-nav" style="margin-top:1rem; flex:1">
            <a href="index.php" class="active">📊 Tableau de bord</a>
            <a href="produits.php">📦 Produits</a>
            <a href="commandes.php">🛒 Commandes</a>
            <a href="utilisateurs.php">👥 Utilisateurs</a>
            <a href="messages.php">✉️ Messages</a>
        </nav>
        <div style="padding:1.5rem; border-top:1px solid rgba(255,255,255,.15)">
            <a href="../" style="color:rgba(255,255,255,.7); font-size:.85rem">← Voir le site</a>
        </div>
    </aside>

    <div class="admin-sidebar-overlay" id="sidebar-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;display:none"></div>

    <main class="main">
        <header class="header">
            <button class="admin-burger" id="admin-burger" aria-label="Menu" style="display:none; background:none; border:none; color:var(--clr-primary); padding:0.5rem; margin-left:-0.5rem; cursor:pointer">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <h1>Analytics Dashboard</h1>

            <div class="admin-header-actions" style="display:flex; align-items:center; gap:0.75rem">
                <!-- Theme Toggle -->
                <button class="theme-toggle" id="theme-toggle">
                    <svg class="moon-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    <svg class="sun-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <div style="display:flex; align-items:center; gap:0.5rem; background:var(--white); padding:0.5rem 1rem; border-radius:10px; border:1px solid var(--border)">
                    <span style="font-size:0.85rem; font-weight:600; color:var(--green-dark)">Live Data</span>
                </div>
            </div>
        </header>

        <div class="kpi-grid">
            <div class="card">
                <div class="card-title">Utilisateurs</div>
                <div class="card-value" id="total-users">0</div>
            </div>
            <div class="card">
                <div class="card-title">Chiffre d'affaires</div>
                <div class="card-value" id="total-revenue">0 TND</div>
            </div>
            <div class="card">
                <div class="card-title">Commandes</div>
                <div class="card-value" id="total-orders">0</div>
            </div>
            <div class="card">
                <div class="card-title">Note Clients</div>
                <div class="card-value" id="avg-rating">0.0</div>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                    <h3>Revenue</h3>
                    <select id="timeframe-selector" class="status-sel" style="min-width:140px;padding:.3rem .75rem">
                        <option value="hourly">Horaire</option>
                        <option value="daily" selected>Journalier</option>
                        <option value="weekly">Hebdomadaire</option>
                        <option value="monthly">Mensuel</option>
                        <option value="annually">Annuel</option>
                    </select>
                </div>
                <div class="chart-container"><canvas id="revenueChart"></canvas></div>
            </div>

            <div class="chart-card">
                <h3>Ventes par Statut</h3>
                <div class="chart-container"><canvas id="statusChart"></canvas></div>
            </div>
        </div>

        <div class="table-card">
            <h3>Dernières Commandes</h3>
            <div style="overflow-x: auto">
                <table class="data-table" id="orders-table">
                    <thead><tr><th>N°</th><th>Client</th><th>Montant</th><th>Statut</th><th>Date</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    const API_BASE = '../api/analytics.php?endpoint=';
    let charts = {};

    function updateChartColors() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const color = isDark ? '#94a3b8' : '#4a5568';
        const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';
        
        Chart.defaults.color = color;
        Chart.defaults.borderColor = gridColor;
        
        Object.values(charts).forEach(c => {
            if (c.options.scales) {
                if (c.options.scales.x) c.options.scales.x.grid.color = gridColor;
                if (c.options.scales.y) c.options.scales.y.grid.color = gridColor;
            }
            c.update();
        });
    }

    document.getElementById('theme-toggle').addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateChartColors();
    });

    const burger = document.getElementById('admin-burger');
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function toggleSidebar() {
        sidebar.classList.toggle('open');
        overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
    }

    burger.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);


    const formatPrice = (val) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 3 }).format(val) + ' TND';

    async function updateDashboard(isInitial = false) {
        const timeframe = document.getElementById('timeframe-selector').value;
        const res = await Promise.all(['users','orders','products','reviews'].map(e => fetch(API_BASE + e + '&timeframe=' + timeframe).then(r => r.json())));
        const [u, o, p, r] = res;

        document.getElementById('total-users').innerText = u.total.toLocaleString();
        document.getElementById('total-revenue').innerText = formatPrice(o.revenue);
        document.getElementById('total-orders').innerText = o.total.toLocaleString();
        document.getElementById('avg-rating').innerText = r.avg_rating.toFixed(1);

        if (isInitial) {
            const ctxRev = document.getElementById('revenueChart');
            charts.revenue = new Chart(ctxRev, {
                type: 'line',
                data: { labels: [], datasets: [{ label: 'Revenu', data: [], borderColor: '#10b981', tension: 0.4, fill: true, backgroundColor: 'rgba(16, 185, 129, 0.1)', pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#10b981' }] },
                options: { 
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', padding: 12, cornerRadius: 8, callbacks: { label: (c) => 'Revenu: ' + formatPrice(c.parsed.y) } } },
                    scales: { y: { ticks: { callback: (v) => v.toLocaleString() + ' TND' } } }
                }
            });

            const ctxStat = document.getElementById('statusChart');
            charts.status = new Chart(ctxStat, {
                type: 'doughnut',
                data: { labels: [], datasets: [{ data: [], backgroundColor: ['#f59e0b', '#10b981', '#6366f1', '#f43f5e'] }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%' }
            });
            updateChartColors();
            document.getElementById('loader').style.opacity = '0';
            setTimeout(() => document.getElementById('loader').style.display = 'none', 300);
        }

        // Update Charts Data
        charts.revenue.data.labels = o.orders_over_time.map(d => d.date);
        charts.revenue.data.datasets[0].data = o.orders_over_time.map(d => d.revenue);
        charts.revenue.update();

        charts.status.data.labels = o.status_breakdown.map(s => s.status);
        charts.status.data.datasets[0].data = o.status_breakdown.map(s => s.count);
        charts.status.update();

        document.querySelector('#orders-table tbody').innerHTML = o.recent.map(ord => `
            <tr>
                <td>#${ord.id}</td>
                <td>${ord.customer_name}</td>
                <td style="font-weight:700">${formatPrice(ord.total)}</td>
                <td><span class="status-pill status-${ord.status}">${ord.status.replace('_', ' ')}</span></td>
                <td>${new Date(ord.created_at).toLocaleDateString()}</td>
            </tr>
        `).join('');
    }

    document.getElementById('timeframe-selector').addEventListener('change', () => updateDashboard());
    updateDashboard(true);
    updateChartColors();

    // Live Sync between tabs
    const bc = new BroadcastChannel('vitanova_live_sync');
    bc.onmessage = (ev) => {
        if (ev.data === 'refresh_analytics') {
            console.log('Live Sync: Refreshing data...');
            updateDashboard(false);
        }
    };

    // Refresh every 10 seconds for a "live" feel
    setInterval(() => updateDashboard(false), 10000);
</script>

</body>
</html>
