<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['token'])) {
    header('Location: login.php');
    exit;
}

// Check session expiration
if (isset($_SESSION['expires_at']) && time() > $_SESSION['expires_at']) {
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}

// Configuration - Dual endpoint strategy
$API_BASE_SERVER = 'http://api-gateway';  // For server-side PHP requests
$API_BASE_CLIENT = 'http://localhost:7000'; // For client-side JavaScript requests
$SITE_NAME = 'QuickLink';
$user = $_SESSION['user'];
$token = $_SESSION['token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - <?php echo $SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #06b6d4;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
        }

        body {
            background-color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: white !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            background: white;
            min-height: calc(100vh - 76px);
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
            padding: 0;
        }

        .sidebar .nav-link {
            color: var(--dark-color);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .main-content {
            padding: 2rem;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: #6b7280;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .top-urls-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .url-item {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.3s ease;
        }

        .url-item:hover {
            background-color: #f8fafc;
        }

        .url-item:last-child {
            border-bottom: none;
        }

        .short-url {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .original-url {
            color: #6b7280;
            font-size: 0.85rem;
            word-break: break-all;
        }

        .click-count {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #3730a3;
            transform: translateY(-1px);
        }

        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-link text-primary me-2"></i><?php echo $SITE_NAME; ?>
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['username']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="analytics.php"><i class="fas fa-chart-bar me-2"></i>Analytics</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link active" href="analytics.php">
                        <i class="fas fa-chart-bar me-2"></i>Analytics
                    </a>
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user-cog me-2"></i>Profile
                    </a>
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home me-2"></i>Home
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i>Analytics Dashboard
                    </h1>
                    <button class="btn btn-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh Data
                    </button>
                </div>

                <!-- Filters -->
                <div class="filter-card">
                    <h5 class="mb-3">
                        <i class="fas fa-filter me-2"></i>Filters
                    </h5>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="dateRange" class="form-label">Date Range</label>
                            <select class="form-select" id="dateRange" onchange="applyFilters()">
                                <option value="7">Last 7 days</option>
                                <option value="30" selected>Last 30 days</option>
                                <option value="90">Last 90 days</option>
                                <option value="365">Last year</option>
                                <option value="all">All time</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="urlFilter" class="form-label">URL Filter</label>
                            <input type="text" class="form-control" id="urlFilter" placeholder="Search URLs..." onkeyup="applyFilters()">
                        </div>
                        <div class="col-md-3">
                            <label for="sortBy" class="form-label">Sort By</label>
                            <select class="form-select" id="sortBy" onchange="applyFilters()">
                                <option value="clicks">Most Clicks</option>
                                <option value="recent">Most Recent</option>
                                <option value="oldest">Oldest First</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Overview Stats -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="stats-number text-primary" id="totalUrls">-</div>
                            <div class="stats-label">Total URLs</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="stats-number text-success" id="totalClicks">-</div>
                            <div class="stats-label">Total Clicks</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="stats-number text-warning" id="avgClicks">-</div>
                            <div class="stats-label">Avg Clicks/URL</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="stats-number text-info" id="todayClicks">-</div>
                            <div class="stats-label">Today's Clicks</div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="chart-card">
                            <h5 class="mb-3">
                                <i class="fas fa-chart-line me-2"></i>Clicks Over Time
                            </h5>
                            <canvas id="clicksChart" height="100"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="chart-card">
                            <h5 class="mb-3">
                                <i class="fas fa-chart-pie me-2"></i>Top Referrers
                            </h5>
                            <canvas id="referrersChart" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top URLs -->
                <div class="top-urls-card">
                    <h5 class="mb-3">
                        <i class="fas fa-trophy me-2"></i>Top Performing URLs
                    </h5>
                    <div id="topUrlsList">
                        <!-- URLs will be loaded here -->
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div class="loading" id="loadingIndicator">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading analytics data...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = '<?php echo $API_BASE_CLIENT; ?>';
        const token = '<?php echo $token; ?>';
        
        let clicksChart = null;
        let referrersChart = null;
        let currentData = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadAnalyticsData();
        });

        async function loadAnalyticsData() {
            showLoading(true);
            
            try {
                // Load user URLs with analytics
                const urlsResponse = await fetch(`${API_BASE}/api/urls`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (!urlsResponse.ok) {
                    throw new Error('Failed to load URLs');
                }

                const urls = await urlsResponse.json();
                currentData = urls;
                
                updateOverviewStats(urls);
                updateCharts(urls);
                updateTopUrls(urls);
                
            } catch (error) {
                console.error('Error loading analytics:', error);
                showError('Failed to load analytics data. Please try again.');
            } finally {
                showLoading(false);
            }
        }

        function updateOverviewStats(urls) {
            const totalUrls = urls.length;
            const totalClicks = urls.reduce((sum, url) => sum + (url.clickCount || 0), 0);
            const avgClicks = totalUrls > 0 ? Math.round(totalClicks / totalUrls * 10) / 10 : 0;
            
            // Calculate today's clicks (mock data for now)
            const todayClicks = Math.floor(totalClicks * 0.1);

            document.getElementById('totalUrls').textContent = totalUrls.toLocaleString();
            document.getElementById('totalClicks').textContent = totalClicks.toLocaleString();
            document.getElementById('avgClicks').textContent = avgClicks.toLocaleString();
            document.getElementById('todayClicks').textContent = todayClicks.toLocaleString();
        }

        function updateCharts(urls) {
            updateClicksChart(urls);
            updateReferrersChart();
        }

        function updateClicksChart(urls) {
            const ctx = document.getElementById('clicksChart').getContext('2d');
            
            // Generate mock time series data
            const days = 30;
            const labels = [];
            const data = [];
            
            for (let i = days - 1; i >= 0; i--) {
                const date = new Date();
                date.setDate(date.getDate() - i);
                labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                
                // Mock data based on total clicks
                const totalClicks = urls.reduce((sum, url) => sum + (url.clickCount || 0), 0);
                const dailyClicks = Math.floor(Math.random() * (totalClicks / 10)) + 1;
                data.push(dailyClicks);
            }

            if (clicksChart) {
                clicksChart.destroy();
            }

            clicksChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Clicks',
                        data: data,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        function updateReferrersChart() {
            const ctx = document.getElementById('referrersChart').getContext('2d');
            
            // Mock referrer data
            const referrers = ['Direct', 'Google', 'Facebook', 'Twitter', 'Other'];
            const data = [45, 25, 15, 10, 5];
            const colors = ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'];

            if (referrersChart) {
                referrersChart.destroy();
            }

            referrersChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: referrers,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function updateTopUrls(urls) {
            const container = document.getElementById('topUrlsList');
            
            // Sort URLs by click count
            const sortedUrls = [...urls].sort((a, b) => (b.clickCount || 0) - (a.clickCount || 0));
            const topUrls = sortedUrls.slice(0, 10);

            if (topUrls.length === 0) {
                container.innerHTML = '<p class="text-muted text-center py-4">No URLs found. Create your first short URL!</p>';
                return;
            }

            container.innerHTML = topUrls.map((url, index) => `
                <div class="url-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-secondary me-2">#${index + 1}</span>
                                <span class="short-url">${url.shortCode}</span>
                            </div>
                            <div class="original-url">${url.originalUrl}</div>
                            <small class="text-muted">
                                Created: ${new Date(url.createdAt).toLocaleDateString()}
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="click-count">${(url.clickCount || 0).toLocaleString()}</div>
                            <small class="text-muted">clicks</small>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function applyFilters() {
            if (!currentData) return;
            
            const dateRange = document.getElementById('dateRange').value;
            const urlFilter = document.getElementById('urlFilter').value.toLowerCase();
            const sortBy = document.getElementById('sortBy').value;
            
            let filteredData = [...currentData];
            
            // Apply URL filter
            if (urlFilter) {
                filteredData = filteredData.filter(url => 
                    url.originalUrl.toLowerCase().includes(urlFilter) ||
                    url.shortCode.toLowerCase().includes(urlFilter)
                );
            }
            
            // Apply date filter
            if (dateRange !== 'all') {
                const days = parseInt(dateRange);
                const cutoffDate = new Date();
                cutoffDate.setDate(cutoffDate.getDate() - days);
                
                filteredData = filteredData.filter(url => 
                    new Date(url.createdAt) >= cutoffDate
                );
            }
            
            // Apply sorting
            switch (sortBy) {
                case 'clicks':
                    filteredData.sort((a, b) => (b.clickCount || 0) - (a.clickCount || 0));
                    break;
                case 'recent':
                    filteredData.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
                    break;
                case 'oldest':
                    filteredData.sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt));
                    break;
            }
            
            updateOverviewStats(filteredData);
            updateCharts(filteredData);
            updateTopUrls(filteredData);
        }

        function resetFilters() {
            document.getElementById('dateRange').value = '30';
            document.getElementById('urlFilter').value = '';
            document.getElementById('sortBy').value = 'clicks';
            applyFilters();
        }

        function refreshData() {
            loadAnalyticsData();
        }

        function showLoading(show) {
            document.getElementById('loadingIndicator').style.display = show ? 'block' : 'none';
        }

        function showError(message) {
            // You can implement a toast notification here
            alert(message);
        }
    </script>
</body>
</html>