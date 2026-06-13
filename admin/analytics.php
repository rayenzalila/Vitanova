<?php
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
    <title>Analytics Pro — Vitanova Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg: #0b0e14;
            --surface: #161b22;
            --surface-accent: #21262d;
            --indigo: #6366f1;
            --indigo-muted: rgba(99, 102, 241, 0.2);
            --emerald: #10b981;
            --emerald-muted: rgba(16, 185, 129, 0.2);
            --rose: #f43f5e;
            --text: #e6edf3;
            --text-muted: #8b949e;
            --border: #30363d;
            --radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Layout ────────────────────────────────────────── */
        .dashboard {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: var(--surface);
            border-right: 1px solid var(--border);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 2rem;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .sidebar .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.5px;
        }
        .sidebar .logo span { color: var(--emerald); }

        .nav-links { display: flex; flex-direction: column; gap: 0.5rem; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            transition: var(--transition);
        }
        .nav-link:hover, .nav-link.active {
            background: var(--surface-accent);
            color: var(--text);
        }
        .nav-link.active { border-left: 3px solid var(--indigo); }

        .main { padding: 2.5rem; max-width: 1400px; margin: 0 auto; width: 100%; }

        /* ── Header ────────────────────────────────────────── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }
        .header h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px; }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .notification-btn {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 0.6rem;
            border-radius: 10px;
            cursor: pointer;
            transition: var(--transition);
        }
        .notification-btn:hover { background: var(--surface-accent); }
        .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--rose);
            color: white;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 2px 5px;
            border-radius: 99px;
            border: 2px solid var(--bg);
            display: none;
        }

        /* ── KPIs ────────────────────────────────────────── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .card:hover { transform: translateY(-4px); border-color: var(--indigo); }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .card-title { color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        .card-icon { font-size: 1.25rem; color: var(--indigo); }

        .card-value { font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; font-family: 'JetBrains Mono', monospace; }
        .card-trend {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .trend-up { color: var(--emerald); }
        .trend-down { color: var(--rose); }

        /* ── Charts ────────────────────────────────────────── */
        .chart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .chart-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            min-height: 400px;
        }
        .chart-card h3 { margin-bottom: 1.5rem; font-size: 1.1rem; font-weight: 700; color: var(--text); }

        .chart-container { position: relative; height: 320px; width: 100%; }

        /* ── Table ─────────────────────────────────────────── */
        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        .table-card h3 { margin-bottom: 1.5rem; font-size: 1.1rem; font-weight: 700; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .data-table th {
            text-align: left;
            padding: 1rem;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
        }
        .data-table td { padding: 1rem; border-bottom: 1px solid var(--border); }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover { background: rgba(255, 255, 255, 0.02); }

        .status-pill {
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .status-completed { background: var(--emerald-muted); color: var(--emerald); }
        .status-shipped { background: var(--indigo-muted); color: var(--indigo); }

        /* ── Loading & Errors ──────────────────────────────── */
        .loader {
            position: fixed;
            inset: 0;
            background: var(--bg);
            z-index: 10000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            transition: opacity 0.5s ease;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--surface-accent);
            border-top-color: var(--indigo);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .error-message {
            background: rgba(244, 63, 94, 0.1);
            color: var(--rose);
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid var(--rose);
            margin-bottom: 2rem;
            display: none;
        }

        /* ── Responsive ────────────────────────────────────── */
        @media (max-width: 1200px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .chart-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .dashboard { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .kpi-grid { grid-template-columns: 1fr; }
            .main { padding: 1.5rem; }
        }
    </style>
</head>
<body>

<div id="loader" class="loader">
    <div class="spinner"></div>
    <p style="color:var(--text-muted); font-weight:500">Initialisation du dashboard...</p>
</div>

<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">Vita<span>nova</span> Admin</div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link">🏠 Accueil Admin</a>
            <a href="analytics.php" class="nav-link active">📊 Analytics Pro</a>
            <a href="produits.php" class="nav-link">📦 Produits</a>
            <a href="commandes.php" class="nav-link">🛒 Commandes</a>
            <a href="utilisateurs.php" class="nav-link">👥 Utilisateurs</a>
            <a href="messages.php" class="nav-link">✉️ Messages</a>
        </nav>
        <div style="margin-top: auto">
            <a href="../" class="nav-link">← Retour au site</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main">
        <div id="error-box" class="error-message"></div>

        <header class="header">
            <div>
                <h1>Dashboard Analytics</h1>
                <p style="color:var(--text-muted); font-size:0.9rem">Mise à jour en temps réel • <span id="last-update">--:--:--</span></p>
            </div>
            <div class="header-actions">
                <button class="notification-btn" title="Messages non lus">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <span id="unread-badge" class="badge">0</span>
                </button>
                <div style="display:flex; align-items:center; gap:0.75rem; background:var(--surface); padding:0.5rem 1rem; border-radius:10px; border:1px solid var(--border)">
                    <div style="width:10px; height:10px; background:var(--emerald); border-radius:50%; box-shadow: 0 0 10px var(--emerald)"></div>
                    <span style="font-size:0.85rem; font-weight:600">Serveur Live</span>
                </div>
            </div>
        </header>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Utilisateurs Totaux</span>
                    <span class="card-icon">👥</span>
                </div>
                <div class="card-value" id="total-users">0</div>
                <div class="card-trend trend-up">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    <span>+12.5%</span>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Revenu Total</span>
                    <span class="card-icon">💰</span>
                </div>
                <div class="card-value" id="total-revenue">0 TND</div>
                <div class="card-trend trend-up">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    <span>+8.2%</span>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Commandes Totales</span>
                    <span class="card-icon">🛒</span>
                </div>
                <div class="card-value" id="total-orders">0</div>
                <div class="card-trend trend-down">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                    <span>-2.1%</span>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Note Moyenne</span>
                    <span class="card-icon">⭐</span>
                </div>
                <div class="card-value" id="avg-rating">0.0</div>
                <div class="card-trend trend-up">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    <span>+0.4</span>
                </div>
            </div>
        </div>

        <!-- Row 1: Charts -->
        <div class="chart-grid">
            <div class="chart-card">
                <h3>Revenue Mensuel</h3>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h3>Statuts des Commandes</h3>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 2: More Charts -->
        <div class="chart-grid">
            <div class="chart-card">
                <h3>Nouveaux Inscrits</h3>
                <div class="chart-container">
                    <canvas id="usersChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h3>Top 5 Ventes</h3>
                <div class="chart-container">
                    <canvas id="productsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 3: Table -->
        <div class="table-card">
            <h3>Commandes Récentes</h3>
            <div style="overflow-x: auto">
                <table class="data-table" id="orders-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)">ID #</th>
                            <th onclick="sortTable(1)">Client</th>
                            <th onclick="sortTable(2)">Montant</th>
                            <th onclick="sortTable(3)">Statut</th>
                            <th onclick="sortTable(4)">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be injected here -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    // ── Configuration & State ──────────────────────────────
    const API_BASE = '../api/analytics.php?endpoint=';
    let charts = {};

    // Chart.js Default Config
    Chart.defaults.color = '#8b949e';
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.borderColor = '#30363d';

    // ── Utility Functions ──────────────────────────────────
    const formatPrice = (val) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 3 }).format(val) + ' TND';
    const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
    
    const animateValue = (id, start, end, duration) => {
        const obj = document.getElementById(id);
        const range = end - start;
        let current = start;
        const increment = end > start ? Math.ceil(range / (duration / 16)) : Math.floor(range / (duration / 16));
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            if (id === 'total-revenue') {
                obj.innerText = formatPrice(current);
            } else if (id === 'avg-rating') {
                obj.innerText = current.toFixed(1);
            } else {
                obj.innerText = current.toLocaleString();
            }
        }, 16);
    };

    // ── Data Fetching ──────────────────────────────────────
    async function fetchData(endpoint) {
        try {
            const response = await fetch(API_BASE + endpoint);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
        } catch (e) {
            console.error(`Error fetching ${endpoint}:`, e);
            document.getElementById('error-box').style.display = 'block';
            document.getElementById('error-box').innerText = `Erreur lors de la récupération des données (${endpoint}). Tentative de reconnexion...`;
            return null;
        }
    }

    // ── Chart Builders ─────────────────────────────────────
    function initCharts(data) {
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        const ctxUsers = document.getElementById('usersChart').getContext('2d');
        const ctxProducts = document.getElementById('productsChart').getContext('2d');

        // Revenue Area Chart
        charts.revenue = new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: data.orders.orders_over_time.map(d => formatDate(d.date)),
                datasets: [{
                    label: 'Revenu',
                    data: data.orders.orders_over_time.map(d => d.revenue),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHoverRadius: 6
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { borderDash: [5, 5] } } } }
        });

        // Order Status Donut
        charts.status = new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: data.orders.status_breakdown.map(s => s.status.replace('_', ' ')),
                datasets: [{
                    data: data.orders.status_breakdown.map(s => s.count),
                    backgroundColor: ['#f59e0b', '#10b981', '#6366f1', '#f43f5e'],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } } } }
        });

        // New Users Line
        charts.users = new Chart(ctxUsers, {
            type: 'line',
            data: {
                labels: data.users.signups.map(d => formatDate(d.date)),
                datasets: [{
                    label: 'Nouveaux Inscrits',
                    data: data.users.signups.map(d => d.count),
                    borderColor: '#10b981',
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#10b981'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // Top Products Bar
        charts.products = new Chart(ctxProducts, {
            type: 'bar',
            data: {
                labels: data.products.top_sellers.map(p => p.name.substring(0, 15) + '...'),
                datasets: [{
                    data: data.products.top_sellers.map(p => p.count),
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                    borderRadius: 8,
                    barThickness: 20
                }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } } } }
        });
    }

    function updateCharts(data) {
        charts.revenue.data.labels = data.orders.orders_over_time.map(d => formatDate(d.date));
        charts.revenue.data.datasets[0].data = data.orders.orders_over_time.map(d => d.revenue);
        charts.revenue.update();

        charts.status.data.labels = data.orders.status_breakdown.map(s => s.status);
        charts.status.data.datasets[0].data = data.orders.status_breakdown.map(s => s.count);
        charts.status.update();

        charts.users.data.labels = data.users.signups.map(d => formatDate(d.date));
        charts.users.data.datasets[0].data = data.users.signups.map(d => d.count);
        charts.users.update();

        charts.products.data.labels = data.products.top_sellers.map(p => p.name.substring(0, 15) + '...');
        charts.products.data.datasets[0].data = data.products.top_sellers.map(p => p.count);
        charts.products.update();
    }

    // ── Main Controller ───────────────────────────────────
    async function updateDashboard(isInitial = false) {
        const [userData, orderData, productData, reviewData, messageData] = await Promise.all([
            fetchData('users'),
            fetchData('orders'),
            fetchData('products'),
            fetchData('reviews'),
            fetchData('messages')
        ]);

        if (!userData || !orderData || !productData || !reviewData || !messageData) return;

        document.getElementById('error-box').style.display = 'none';

        // Update KPIs
        animateValue('total-users', parseInt(document.getElementById('total-users').innerText.replace(/\s/g, '')) || 0, userData.total, 1000);
        animateValue('total-revenue', parseFloat(document.getElementById('total-revenue').innerText.replace(/[^\d.]/g, '')) || 0, orderData.revenue, 1000);
        animateValue('total-orders', parseInt(document.getElementById('total-orders').innerText.replace(/\s/g, '')) || 0, orderData.total, 1000);
        animateValue('avg-rating', parseFloat(document.getElementById('avg-rating').innerText) || 0, reviewData.avg_rating, 1000);

        // Update Charts
        if (isInitial) {
            initCharts({ users: userData, orders: orderData, products: productData });
        } else {
            updateCharts({ users: userData, orders: orderData, products: productData });
        }

        // Update Table
        const tableBody = document.querySelector('#orders-table tbody');
        tableBody.innerHTML = orderData.recent.map(o => `
            <tr>
                <td style="font-family:'JetBrains Mono'; font-weight:700">#${o.id.toString().padStart(5, '0')}</td>
                <td>${o.customer_name}</td>
                <td style="font-weight:700; color:var(--indigo)">${formatPrice(o.total)}</td>
                <td><span class="status-pill status-${o.status === 'en_attente' ? 'pending' : (o.status === 'expediee' ? 'shipped' : 'completed')}">${o.status.replace('_', ' ')}</span></td>
                <td style="color:var(--text-muted)">${new Date(o.created_at).toLocaleDateString()}</td>
            </tr>
        `).join('');

        // Update Unread Badge
        const unreadBadge = document.getElementById('unread-badge');
        if (messageData.unread > 0) {
            unreadBadge.innerText = messageData.unread;
            unreadBadge.style.display = 'block';
        } else {
            unreadBadge.style.display = 'none';
        }

        document.getElementById('last-update').innerText = new Date().toLocaleTimeString();

        if (isInitial) {
            const loader = document.getElementById('loader');
            loader.style.opacity = '0';
            setTimeout(() => loader.style.display = 'none', 500);
        }
    }

    // ── Table Sorting ──────────────────────────────────────
    function sortTable(n) {
        const table = document.getElementById("orders-table");
        let switching = true, shouldSwitch, i;
        let direction = "asc";
        let switchcount = 0;
        
        while (switching) {
            switching = false;
            let rows = table.rows;
            for (i = 1; i < (rows.length - 1); i++) {
                shouldSwitch = false;
                let x = rows[i].getElementsByTagName("TD")[n];
                let y = rows[i + 1].getElementsByTagName("TD")[n];
                
                if (direction == "asc") {
                    if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    }
                } else if (direction == "desc") {
                    if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    }
                }
            }
            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
                switchcount++;
            } else {
                if (switchcount == 0 && direction == "asc") {
                    direction = "desc";
                    switching = true;
                }
            }
        }
    }

    // ── Boot ───────────────────────────────────────────────
    window.addEventListener('load', () => {
        updateDashboard(true);
        setInterval(() => updateDashboard(false), 60000);
    });
</script>
</body>
</html>
