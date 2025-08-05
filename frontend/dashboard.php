<?php
session_start();

// Include configuration
require_once 'config.php';

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

// Configuration - Deployment-friendly endpoint strategy
$API_BASE_SERVER = 'http://api-gateway';  // For server-side PHP requests

// Auto-detect client-side API base URL based on environment
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// If running in Docker (frontend on port 8080), use API Gateway on port 7000
if (strpos($host, ':8080') !== false) {
    $api_host = str_replace(':8080', ':7000', $host);
    $API_BASE_CLIENT = $protocol . '://' . $api_host;
} else {
    // For local development or other deployments
    $API_BASE_CLIENT = 'http://localhost:7000';
}

$SITE_NAME = 'QuickLink';
$user = $_SESSION['user'];
$token = $_SESSION['token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo $SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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

        .sidebar .nav-link:hover,
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

        .url-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .url-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .short-url {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .original-url {
            color: #6b7280;
            font-size: 0.9rem;
            word-break: break-all;
        }

        .url-stats {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .btn-action {
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
            border-radius: 6px;
            margin-right: 0.5rem;
        }

        .quick-shorten {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .form-control:focus {
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
        }

        .spinner-border {
            width: 1rem;
            height: 1rem;
        }

        .pagination {
            justify-content: center;
        }

        .page-link {
            color: var(--primary-color);
            border-color: #e5e7eb;
        }

        .page-link:hover {
            color: var(--secondary-color);
            background-color: #f8fafc;
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .search-box {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .url-stats {
                flex-direction: column;
                gap: 0.5rem;
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
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="analytics.php">
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
                <!-- Welcome Card -->
                <div class="welcome-card">
                    <h2><i class="fas fa-user-circle me-2"></i>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                    <p class="mb-0">Manage your shortened URLs and track their performance.</p>
                </div>

                <!-- Stats Cards -->
                <div class="row" id="statsContainer">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number text-primary" id="totalUrls">-</div>
                            <div class="stats-label">Total URLs</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number text-success" id="totalClicks">-</div>
                            <div class="stats-label">Total Clicks</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number text-warning" id="todayClicks">-</div>
                            <div class="stats-label">Today's Clicks</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number text-info" id="avgClicks">-</div>
                            <div class="stats-label">Avg. Clicks/URL</div>
                        </div>
                    </div>
                </div>

                <!-- Quick URL Shortener -->
                <div class="quick-shorten">
                    <h4 class="mb-3"><i class="fas fa-plus-circle me-2"></i>Quick Shorten</h4>
                    <form id="quickShortenForm">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <input type="url" class="form-control" id="quickUrl" placeholder="Enter URL to shorten..." required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <span class="loading">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                    </span>
                                    <span class="btn-text">Shorten</span>
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div id="quickResult" class="alert alert-success mt-3" style="display: none;">
                        <i class="fas fa-check-circle me-2"></i>
                        URL shortened successfully! <a href="#" id="quickResultLink" target="_blank">View</a>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="search-box">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="searchUrls" placeholder="Search your URLs...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="sortBy">
                                <option value="created_desc">Newest First</option>
                                <option value="created_asc">Oldest First</option>
                                <option value="clicks_desc">Most Clicks</option>
                                <option value="clicks_asc">Least Clicks</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary w-100" onclick="loadUrls()">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                        </div>
                    </div>
                </div>

                <!-- URLs List -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4><i class="fas fa-list me-2"></i>Your URLs</h4>
                    <div class="text-muted">
                        <small id="urlsCount">Loading...</small>
                    </div>
                </div>

                <div id="urlsList">
                    <!-- URLs will be loaded here -->
                </div>

                <!-- Pagination -->
                <nav aria-label="URLs pagination">
                    <ul class="pagination" id="pagination">
                        <!-- Pagination will be generated here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuration from PHP
        const API_BASE = '<?php echo $API_BASE_CLIENT; ?>';
        const authToken = '<?php echo $token; ?>';
        const userId = '<?php echo $user['id']; ?>';
        const ENVIRONMENT = '<?php echo $ENVIRONMENT; ?>';
        
        // Debug information
        console.log('Environment:', ENVIRONMENT);
        console.log('API Base:', API_BASE);
        
        let currentPage = 1;
        const pageSize = 10;

        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadUrls();
        });

        // Quick shorten form
        document.getElementById('quickShortenForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const url = document.getElementById('quickUrl').value;
            if (!url) return;

            setQuickLoading(true);

            try {
                const response = await fetch(`${API_BASE}/api/url/shorten`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${authToken}`
                    },
                    body: JSON.stringify({
                        OriginalUrl: url,
                        UserId: userId
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    document.getElementById('quickUrl').value = '';
                    document.getElementById('quickResultLink').href = result.ShortUrl;
                    document.getElementById('quickResultLink').textContent = result.ShortUrl;
                    document.getElementById('quickResult').style.display = 'block';
                    
                    // Refresh data
                    loadStats();
                    loadUrls();
                    
                    setTimeout(() => {
                        document.getElementById('quickResult').style.display = 'none';
                    }, 5000);
                } else {
                    alert('Error: ' + (result.error || 'Failed to shorten URL'));
                }
            } catch (error) {
                alert('Network error. Please try again.');
                console.error('Error:', error);
            } finally {
                setQuickLoading(false);
            }
        });

        async function loadStats() {
            try {
                // Load user analytics
                const response = await fetch(`${API_BASE}/api/analytics/user/${userId}`, {
                    headers: {
                        'Authorization': `Bearer ${authToken}`
                    }
                });

                if (response.ok) {
                    const stats = await response.json();
                    
                    document.getElementById('totalUrls').textContent = stats.TotalUrls || 0;
                    document.getElementById('totalClicks').textContent = stats.TotalClicks || 0;
                    document.getElementById('todayClicks').textContent = stats.TodayClicks || 0;
                    
                    const avgClicks = stats.TotalUrls > 0 ? Math.round(stats.TotalClicks / stats.TotalUrls) : 0;
                    document.getElementById('avgClicks').textContent = avgClicks;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        async function loadUrls() {
            try {
                const searchTerm = document.getElementById('searchUrls').value;
                const sortBy = document.getElementById('sortBy').value;
                
                let url = `${API_BASE}/api/url/user/${userId}?page=${currentPage}&pageSize=${pageSize}`;
                
                if (searchTerm) {
                    url += `&search=${encodeURIComponent(searchTerm)}`;
                }
                
                if (sortBy) {
                    url += `&sortBy=${sortBy}`;
                }

                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${authToken}`
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    displayUrls(data.Urls || []);
                    updatePagination(data.TotalCount || 0);
                    
                    document.getElementById('urlsCount').textContent = 
                        `Showing ${data.Urls?.length || 0} of ${data.TotalCount || 0} URLs`;
                } else {
                    document.getElementById('urlsList').innerHTML = 
                        '<div class="alert alert-warning">Failed to load URLs</div>';
                }
            } catch (error) {
                console.error('Error loading URLs:', error);
                document.getElementById('urlsList').innerHTML = 
                    '<div class="alert alert-danger">Network error loading URLs</div>';
            }
        }

        function displayUrls(urls) {
            const container = document.getElementById('urlsList');
            
            if (urls.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-link fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No URLs found</h5>
                        <p class="text-muted">Start by shortening your first URL above!</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = urls.map(url => `
                <div class="url-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="short-url">${url.ShortUrl || `${window.location.origin}/s/${url.ShortCode}`}</div>
                            <div class="original-url">${url.OriginalUrl}</div>
                            <div class="url-stats">
                                <div class="stat-item">
                                    <div class="stat-value">${url.ClickCount || 0}</div>
                                    <div class="stat-label">Clicks</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value">${url.UniqueClicks || 0}</div>
                                    <div class="stat-label">Unique</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value">${formatDate(url.CreatedAt)}</div>
                                    <div class="stat-label">Created</div>
                                </div>
                            </div>
                        </div>
                        <div class="ms-3">
                            <button class="btn btn-outline-primary btn-action" onclick="copyUrl('${url.ShortUrl || `${window.location.origin}/s/${url.ShortCode}`}')">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button class="btn btn-outline-info btn-action" onclick="viewAnalytics('${url.Id}')">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                            <a href="${url.ShortUrl || `${window.location.origin}/s/${url.ShortCode}`}" target="_blank" class="btn btn-outline-success btn-action">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function updatePagination(totalCount) {
            const totalPages = Math.ceil(totalCount / pageSize);
            const pagination = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let paginationHTML = '';
            
            // Previous button
            paginationHTML += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
                </li>
            `;
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    paginationHTML += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                        </li>
                    `;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    paginationHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            // Next button
            paginationHTML += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
                </li>
            `;
            
            pagination.innerHTML = paginationHTML;
        }

        function changePage(page) {
            if (page < 1) return;
            currentPage = page;
            loadUrls();
        }

        function setQuickLoading(loading) {
            const loadingSpinner = document.querySelector('#quickShortenForm .loading');
            const btnText = document.querySelector('#quickShortenForm .btn-text');
            const submitBtn = document.querySelector('#quickShortenForm button[type="submit"]');
            
            if (loading) {
                loadingSpinner.style.display = 'inline-block';
                btnText.textContent = 'Shortening...';
                submitBtn.disabled = true;
            } else {
                loadingSpinner.style.display = 'none';
                btnText.textContent = 'Shorten';
                submitBtn.disabled = false;
            }
        }

        function copyUrl(url) {
            navigator.clipboard.writeText(url).then(() => {
                // Show temporary success message
                const toast = document.createElement('div');
                toast.className = 'alert alert-success position-fixed';
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
                toast.innerHTML = '<i class="fas fa-check me-2"></i>URL copied to clipboard!';
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 3000);
            });
        }

        function viewAnalytics(urlId) {
            window.location.href = `analytics.php?url=${urlId}`;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString();
        }

        // Search functionality
        document.getElementById('searchUrls').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                currentPage = 1;
                loadUrls();
            }
        });

        document.getElementById('sortBy').addEventListener('change', function() {
            currentPage = 1;
            loadUrls();
        });
    </script>
</body>
</html>