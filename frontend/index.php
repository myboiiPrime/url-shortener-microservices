<?php
session_start();

// Configuration - Dual endpoint strategy
$API_BASE_SERVER = 'http://api-gateway';  // For server-side PHP requests
$API_BASE_CLIENT = 'http://localhost:7000'; // For client-side JavaScript requests
$SITE_NAME = 'QuickLink';
$SITE_DESCRIPTION = 'Fast, reliable URL shortening service';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user']) && isset($_SESSION['token']);
$user = $isLoggedIn ? $_SESSION['user'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $SITE_NAME; ?> - URL Shortener</title>
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
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .hero-section {
            padding: 80px 0;
            color: white;
            text-align: center;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .url-shortener-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 2rem;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .result-card {
            background: #f8fafc;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
            display: none;
            color: #1f2937; /* Add black text color */
        }

        .result-card h5 {
            color: #1f2937; /* Ensure the success message is black */
        }

        .short-url {
            background: var(--success-color);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            word-break: break-all;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .loading {
            display: none;
        }

        .spinner-border {
            width: 1rem;
            height: 1rem;
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .url-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

        .copy-btn {
            background: var(--secondary-color);
            border: none;
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .copy-btn:hover {
            background: #0891b2;
            color: white;
        }

        .feature-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            margin-bottom: 2rem;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .url-shortener-card {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-link text-primary me-2"></i><?php echo $SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics.php">Analytics</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['username']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white px-3 ms-2" href="register.php">Sign Up</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Shorten Your URLs</h1>
            <p class="hero-subtitle"><?php echo $SITE_DESCRIPTION; ?></p>
            
            <!-- URL Shortener Form -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="url-shortener-card">
                        <form id="shortenForm">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <input type="url" class="form-control form-control-lg" id="originalUrl" 
                                           placeholder="Enter your long URL here..." required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <span class="loading">
                                            <span class="spinner-border spinner-border-sm me-2"></span>
                                        </span>
                                        <span class="btn-text">Shorten URL</span>
                                    </button>
                                </div>
                            </div>
                            
                            <?php if ($isLoggedIn): ?>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="expirationDate" class="form-label text-muted">Expiration Date (Optional)</label>
                                    <input type="datetime-local" class="form-control" id="expirationDate">
                                </div>
                            </div>
                            <?php endif; ?>
                        </form>
                        
                        <!-- Result Display -->
                        <div id="resultCard" class="result-card">
                            <h5 class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>URL Shortened Successfully!</h5>
                            <div class="row">
                                <div class="col-md-8">
                                    <label class="form-label">Your Short URL:</label>
                                    <div class="short-url" id="shortUrl"></div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn copy-btn w-100" onclick="copyToClipboard()">
                                        <i class="fas fa-copy me-1"></i>Copy URL
                                    </button>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Original URL: <span id="originalUrlDisplay"></span>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Error Display -->
                        <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="errorMessage"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5" style="background: rgba(255, 255, 255, 0.1);">
        <div class="container">
            <div class="row text-center text-white mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold mb-3">Why Choose <?php echo $SITE_NAME; ?>?</h2>
                    <p class="lead">Fast, secure, and feature-rich URL shortening service</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h4>Lightning Fast</h4>
                        <p class="text-muted">Instant URL shortening with our optimized microservices architecture</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Detailed Analytics</h4>
                        <p class="text-muted">Track clicks, geographic data, and user behavior with comprehensive analytics</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Secure & Reliable</h4>
                        <p class="text-muted">Enterprise-grade security with 99.9% uptime guarantee</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 text-center text-white">
        <div class="container">
            <p>&copy; 2024 <?php echo $SITE_NAME; ?>. All rights reserved.</p>
            <p class="small">Built with microservices architecture for maximum performance and scalability.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = '<?php echo $API_BASE_CLIENT; ?>';
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        const authToken = '<?php echo isset($_SESSION['token']) ? $_SESSION['token'] : ''; ?>';
        const userId = '<?php echo isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : ''; ?>';

        document.getElementById('shortenForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const originalUrl = document.getElementById('originalUrl').value;
            const expirationDate = document.getElementById('expirationDate')?.value;
            
            if (!originalUrl) {
                showError('Please enter a valid URL');
                return;
            }

            setLoading(true);
            hideResults();

            try {
                const requestData = {
                    OriginalUrl: originalUrl
                };

                if (isLoggedIn) {
                    requestData.UserId = userId;
                    if (expirationDate) {
                        requestData.ExpiresAt = new Date(expirationDate).toISOString();
                    }
                }

                const headers = {
                    'Content-Type': 'application/json'
                };

                if (isLoggedIn && authToken) {
                    headers['Authorization'] = `Bearer ${authToken}`;
                }

                const response = await fetch(`${API_BASE}/api/url/shorten`, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(requestData)
                });

                const result = await response.json();

                if (response.ok) {
                    showResult(result, originalUrl);
                } else {
                    showError(result.error || 'Failed to shorten URL');
                }
            } catch (error) {
                showError('Network error. Please try again.');
                console.error('Error:', error);
            } finally {
                setLoading(false);
            }
        });

        function setLoading(loading) {
            const loadingSpinner = document.querySelector('.loading');
            const btnText = document.querySelector('.btn-text');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            if (loading) {
                loadingSpinner.style.display = 'inline-block';
                btnText.textContent = 'Shortening...';
                submitBtn.disabled = true;
            } else {
                loadingSpinner.style.display = 'none';
                btnText.textContent = 'Shorten URL';
                submitBtn.disabled = false;
            }
        }

        function showResult(result, originalUrl) {
            // Always use the frontend domain for the short URL display
            const frontendBaseUrl = '<?php echo $FRONTEND_BASE_URL; ?>';
            const shortUrl = `${frontendBaseUrl}/s/${result.ShortCode}`;
            
            document.getElementById('shortUrl').textContent = shortUrl;
            document.getElementById('originalUrlDisplay').textContent = originalUrl;
            document.getElementById('resultCard').style.display = 'block';
            
            // Store for copy function
            window.currentShortUrl = shortUrl;
            
            console.log('Short URL created:', shortUrl);
            console.log('Short Code:', result.ShortCode);
        }

        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorAlert').style.display = 'block';
        }

        function hideResults() {
            document.getElementById('resultCard').style.display = 'none';
            document.getElementById('errorAlert').style.display = 'none';
        }

        function copyToClipboard() {
            if (window.currentShortUrl) {
                navigator.clipboard.writeText(window.currentShortUrl).then(() => {
                    const copyBtn = document.querySelector('.copy-btn');
                    const originalText = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
                    copyBtn.style.background = 'var(--success-color)';
                    
                    setTimeout(() => {
                        copyBtn.innerHTML = originalText;
                        copyBtn.style.background = 'var(--secondary-color)';
                    }, 2000);
                });
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.style.display !== 'none') {
                    alert.style.display = 'none';
                }
            });
        }, 5000);
    </script>
</body>
</html>