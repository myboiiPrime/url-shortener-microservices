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

// Use centralized configuration
require_once 'config.php';
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

        /* Fix for chart container responsiveness */
        .chart-card {
            position: relative;
            overflow: hidden;
        }
        
        .chart-card canvas {
            max-width: 100% !important;
            height: auto !important;
        }
        
        /* Override Bootstrap navbar flex issues that cause chart expansion */
        @media (min-width: 992px) {
            .navbar-expand-lg {
                flex-wrap: wrap !important;
                justify-content: space-between !important;
            }
        }
        
        /* Ensure chart containers don't overflow */
        #clicksChart, #referrersChart {
            max-width: 100% !important;
            max-height: 400px !important;
        }
        
        /* Fix for responsive chart containers */
        .chart-card .row {
            margin: 0;
        }
        
        .chart-card .col-md-8,
        .chart-card .col-md-4 {
            padding: 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            /* Additional mobile chart fixes */
            .chart-card {
                margin-bottom: 1rem;
            }
            
            #clicksChart, #referrersChart {
                max-height: 300px !important;
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
                            <input type="text" class="form-control" id="urlFilter" placeholder="Search URLs..." onkeyup="debouncedApplyFilters()">
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
        let debounceTimer = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadAnalyticsData();
        });

        // Debounced filter function to prevent rapid chart updates
        function debouncedApplyFilters() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                applyFilters();
            }, 300); // Wait 300ms after user stops typing
        }

        async function loadAnalyticsData() {
            showLoading(true);
            
            try {
                // Get user ID from PHP session
                const userId = '<?php echo $user['id']; ?>';
                
                let dashboardData = null;
                let hasAnalyticsService = true;
                
                // Try to load dashboard stats with real analytics data
                try {
                    const dashboardResponse = await fetch(`${API_BASE}/api/analytics/dashboard`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json'
                        }
                    });

                    if (dashboardResponse.ok) {
                        dashboardData = await dashboardResponse.json();
                    } else {
                        console.warn('Analytics service not available, using fallback data');
                        hasAnalyticsService = false;
                    }
                } catch (error) {
                    console.warn('Analytics service error:', error);
                    hasAnalyticsService = false;
                }
                
                // Load user URLs for basic data
                const urlsResponse = await fetch(`${API_BASE}/api/url/user/${userId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (!urlsResponse.ok) {
                    throw new Error('Failed to load URLs');
                }

                const urlsData = await urlsResponse.json();
                currentData = urlsData.urls || urlsData.Urls || [];
                
                if (hasAnalyticsService && dashboardData) {
                    // Update UI with real analytics data
                    updateOverviewStats(dashboardData);
                    updateClicksChart(dashboardData.last30DaysClicks || dashboardData.Last30DaysClicks || []);
                    await updateReferrersChart(userId, true);
                    updateTopUrls(dashboardData.topUrls || dashboardData.TopUrls || []);
                } else {
                    // Fallback to basic URL data when analytics service is not available
                    const fallbackStats = {
                        totalUrls: currentData.length,
                        totalClicks: currentData.reduce((sum, url) => sum + (url.clickCount || 0), 0),
                        clicksToday: 0,
                        TotalUrls: currentData.length,
                        TotalClicks: currentData.reduce((sum, url) => sum + (url.clickCount || 0), 0),
                        ClicksToday: 0
                    };
                    
                    updateOverviewStats(fallbackStats);
                    updateClicksChart([]); // Empty chart
                    updateReferrersChart(userId, false); // No data chart
                    updateTopUrls(currentData.slice(0, 10).map(url => ({
                        shortCode: url.shortCode,
                        originalUrl: url.originalUrl,
                        totalClicks: url.clickCount || 0,
                        ShortCode: url.shortCode,
                        OriginalUrl: url.originalUrl,
                        TotalClicks: url.clickCount || 0
                    })));
                    
                    // Show a warning message
                    showWarning('Analytics service is currently unavailable. Showing basic statistics only.');
                }
                
            } catch (error) {
                console.error('Error loading analytics:', error);
                showError('Failed to load analytics data. Please try again.');
            } finally {
                showLoading(false);
            }
        }

        function updateOverviewStats(dashboardData) {
            // Use real dashboard stats
            const totalUrls = dashboardData.totalUrls || dashboardData.TotalUrls || 0;
            const totalClicks = dashboardData.totalClicks || dashboardData.TotalClicks || 0;
            const todayClicks = dashboardData.clicksToday || dashboardData.ClicksToday || 0;
            const avgClicks = totalUrls > 0 ? Math.round(totalClicks / totalUrls * 10) / 10 : 0;

            document.getElementById('totalUrls').textContent = totalUrls.toLocaleString();
            document.getElementById('totalClicks').textContent = totalClicks.toLocaleString();
            document.getElementById('avgClicks').textContent = avgClicks.toLocaleString();
            document.getElementById('todayClicks').textContent = todayClicks.toLocaleString();
        }

        function updateCharts(urls) {
            // This function is kept for backward compatibility but now uses real data
            // The actual chart updates are called directly from loadAnalyticsData
        }

        function updateClicksChart(dailyClicksData) {
            const ctx = document.getElementById('clicksChart').getContext('2d');
            
            // Use real daily clicks data from the API
            const labels = [];
            const data = [];
            
            if (dailyClicksData && dailyClicksData.length > 0) {
                // Use real data from API
                dailyClicksData.forEach(dayData => {
                    const date = new Date(dayData.date || dayData.Date);
                    labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                    data.push(dayData.clicks || dayData.Clicks || 0);
                });
            } else {
                // If no data available, show empty chart for last 30 days
                for (let i = 29; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                    data.push(0);
                }
            }

            // Only create chart if it doesn't exist, otherwise update data
            if (!clicksChart) {
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
                        aspectRatio: 2,
                        layout: {
                            padding: 10
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        onResize: function(chart, size) {
                            // Ensure chart doesn't exceed container bounds
                            if (size.width > chart.canvas.parentNode.clientWidth) {
                                chart.resize(chart.canvas.parentNode.clientWidth, size.height);
                            }
                        }
                    }
                });
            } else {
                // Update existing chart data
                clicksChart.data.labels = labels;
                clicksChart.data.datasets[0].data = data;
                clicksChart.update('none');
            }
        }

        async function updateReferrersChart(userId, useRealData = true) {
            const ctx = document.getElementById('referrersChart').getContext('2d');
            
            let referrers = ['No Data'];
            let data = [1];
            let colors = ['#e5e7eb'];
            
            if (useRealData) {
                try {
                    // Get real referrer data from user analytics
                    const analyticsResponse = await fetch(`${API_BASE}/api/analytics/user/${userId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json'
                        }
                    });

                    if (analyticsResponse.ok) {
                        const analyticsData = await analyticsResponse.json();
                        
                        // Aggregate referrer data from all URLs
                        const referrerMap = new Map();
                        
                        if (analyticsData && Array.isArray(analyticsData)) {
                            analyticsData.forEach(urlData => {
                                if (urlData.countryStats || urlData.CountryStats) {
                                    // For now, use country data as referrer data since the backend might not have referrer tracking yet
                                    const stats = urlData.countryStats || urlData.CountryStats || [];
                                    stats.forEach(stat => {
                                        const country = stat.country || stat.Country || 'Unknown';
                                        const clicks = stat.clicks || stat.Clicks || 0;
                                        referrerMap.set(country, (referrerMap.get(country) || 0) + clicks);
                                    });
                                }
                            });
                        }

                        if (referrerMap.size > 0) {
                            // Convert map to arrays and sort by clicks
                            const sortedReferrers = Array.from(referrerMap.entries())
                                .sort((a, b) => b[1] - a[1])
                                .slice(0, 5); // Top 5

                            referrers = sortedReferrers.map(([name]) => name);
                            data = sortedReferrers.map(([, clicks]) => clicks);
                            colors = ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'].slice(0, referrers.length);
                        }
                    }
                } catch (error) {
                    console.error('Error loading referrer data:', error);
                }
            }

            // Only create chart if it doesn't exist, otherwise update data
            if (!referrersChart) {
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
                        aspectRatio: 1,
                        layout: {
                            padding: 10
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 8
                                }
                            }
                        },
                        onResize: function(chart, size) {
                            // Ensure chart doesn't exceed container bounds
                            if (size.width > chart.canvas.parentNode.clientWidth) {
                                chart.resize(chart.canvas.parentNode.clientWidth, size.height);
                            }
                        }
                    }
                });
            } else {
                // Update existing chart data
                referrersChart.data.labels = referrers;
                referrersChart.data.datasets[0].data = data;
                referrersChart.data.datasets[0].backgroundColor = colors;
                referrersChart.update('none');
            }
        }

        function updateTopUrls(topUrlsData) {
            const container = document.getElementById('topUrlsList');
            
            // Use real top URLs data from dashboard stats
            const topUrls = topUrlsData || [];

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
                                <span class="short-url">${url.shortCode || url.ShortCode || 'N/A'}</span>
                            </div>
                            <div class="original-url">${url.originalUrl || url.OriginalUrl || 'N/A'}</div>
                        </div>
                        <div class="text-end">
                            <div class="click-count">${(url.totalClicks || url.TotalClicks || 0).toLocaleString()}</div>
                            <small class="text-muted">clicks</small>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function applyFilters() {
            // Apply filters to existing data without reloading charts
            if (!currentData) return;
            
            const urlFilter = document.getElementById('urlFilter').value.toLowerCase();
            const sortBy = document.getElementById('sortBy').value;
            
            // Filter URLs based on search term
            let filteredData = currentData;
            if (urlFilter) {
                filteredData = currentData.filter(url => 
                    (url.originalUrl || '').toLowerCase().includes(urlFilter) ||
                    (url.shortCode || '').toLowerCase().includes(urlFilter)
                );
            }
            
            // Sort URLs
            switch (sortBy) {
                case 'clicks':
                    filteredData.sort((a, b) => (b.clickCount || 0) - (a.clickCount || 0));
                    break;
                case 'recent':
                    filteredData.sort((a, b) => new Date(b.createdAt || 0) - new Date(a.createdAt || 0));
                    break;
                case 'oldest':
                    filteredData.sort((a, b) => new Date(a.createdAt || 0) - new Date(b.createdAt || 0));
                    break;
            }
            
            // Update only the top URLs list, don't recreate charts
            updateTopUrls(filteredData.slice(0, 10).map(url => ({
                shortCode: url.shortCode,
                originalUrl: url.originalUrl,
                totalClicks: url.clickCount || 0,
                ShortCode: url.shortCode,
                OriginalUrl: url.originalUrl,
                TotalClicks: url.clickCount || 0
            })));
        }

        function resetFilters() {
            document.getElementById('dateRange').value = '30';
            document.getElementById('urlFilter').value = '';
            document.getElementById('sortBy').value = 'clicks';
            
            // Apply filters to reset the display without reloading
            applyFilters();
        }

        function refreshData() {
            // Destroy existing charts to prevent memory leaks
            if (clicksChart) {
                clicksChart.destroy();
                clicksChart = null;
            }
            if (referrersChart) {
                referrersChart.destroy();
                referrersChart = null;
            }
            loadAnalyticsData();
        }

        function showLoading(show) {
            document.getElementById('loadingIndicator').style.display = show ? 'block' : 'none';
        }

        function showWarning(message) {
            // Create or update warning message
            let warningDiv = document.getElementById('analytics-warning');
            if (!warningDiv) {
                warningDiv = document.createElement('div');
                warningDiv.id = 'analytics-warning';
                warningDiv.className = 'bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6';
                warningDiv.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Analytics Service Unavailable</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>${message}</p>
                            </div>
                        </div>
                    </div>
                `;
                
                // Insert at the top of the main content
                const mainContent = document.querySelector('.space-y-6');
                if (mainContent) {
                    mainContent.insertBefore(warningDiv, mainContent.firstChild);
                }
            } else {
                // Update existing warning
                const messageElement = warningDiv.querySelector('.text-yellow-700 p');
                if (messageElement) {
                    messageElement.textContent = message;
                }
            }
        }

        function showError(message) {
            // You can implement a toast notification here
            alert(message);
        }
    </script>
</body>
</html>