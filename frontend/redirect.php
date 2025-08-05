<?php
// Include the proper configuration
require_once 'config.php';

// Get the short code from URL
$shortCode = $_GET['code'] ?? '';

if (empty($shortCode)) {
    header('Location: index.php');
    exit;
}

// Check if this is an immediate redirect request
if (isset($_GET['redirect']) && $_GET['redirect'] === 'now') {
    // Direct redirect through API (this will track the click)
    // Use API_BASE_CLIENT for browser redirects (accessible from user's browser)
    $redirectUrl = $API_BASE_CLIENT . '/api/url/redirect/' . urlencode($shortCode);
    header('Location: ' . $redirectUrl);
    exit;
}

// Try to get the URL info first, then show redirect page
$apiUrl = $API_BASE_SERVER . '/api/url/' . urlencode($shortCode);
$originalUrl = null;
$urlTitle = null;

try {
    // Create context for the request with SSL options
    $contextOptions = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'),
                'X-Forwarded-For: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'),
                'X-Real-IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'),
                'Referer: ' . ($_SERVER['HTTP_REFERER'] ?? ''),
            ],
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ];
    
    // Add SSL context options if using HTTPS
    if (strpos($apiUrl, 'https://') === 0) {
        $contextOptions['ssl'] = [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ];
    }
    
    $context = stream_context_create($contextOptions);
    
    // Make the request to get URL info
    $response = file_get_contents($apiUrl, false, $context);
    
    if ($response === false) {
        throw new Exception('Failed to fetch URL info');
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['originalUrl'])) {
        $originalUrl = $data['originalUrl'];
        $urlTitle = $data['title'] ?? 'Redirecting...';
    } else {
        throw new Exception('Invalid response from API');
    }
    
} catch (Exception $e) {
    // Log error and show 404 page
    error_log('Redirect error: ' . $e->getMessage());
    error_log('API URL attempted: ' . $apiUrl);
    error_log('Environment: ' . $ENVIRONMENT);
    error_log('API Server: ' . $API_BASE_SERVER);
    error_log('Short Code: ' . $shortCode);
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $originalUrl ? 'Redirecting...' : 'URL Not Found'; ?> - QuickLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #06b6d4;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .redirect-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
        }

        .redirect-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            animation: spin 2s linear infinite;
        }

        .error-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .countdown {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 1rem 0;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            margin: 1rem 0;
        }

        .progress-bar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            transition: width 1s linear;
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

        .destination-url {
            background: rgba(79, 70, 229, 0.1);
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
            word-break: break-all;
            font-family: monospace;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="redirect-container">
        <?php if ($originalUrl): ?>
            <!-- Redirect page with countdown -->
            <div class="redirect-icon">
                <i class="fas fa-sync-alt"></i>
            </div>
            <h1 class="h2 mb-3">Redirecting...</h1>
            <p class="text-muted mb-3">
                <?php echo htmlspecialchars($urlTitle); ?>
            </p>
            
            <div class="destination-url">
                <i class="fas fa-external-link-alt me-2"></i>
                <?php echo htmlspecialchars($originalUrl); ?>
            </div>
            
            <div class="countdown" id="countdown">6</div>
            
            <div class="progress">
                <div class="progress-bar" id="progressBar" style="width: 100%"></div>
            </div>
            
            <p class="text-muted mb-4">
                You will be redirected automatically in <span id="countdownText">6</span> seconds...
            </p>
            
            <div class="d-grid gap-2">
                <button onclick="redirectNow()" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-2"></i>Go Now
                </button>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="fas fa-home me-2"></i>Cancel & Go Home
                </a>
            </div>

            <script>
                let countdown = 6;
                const countdownElement = document.getElementById('countdown');
                const countdownTextElement = document.getElementById('countdownText');
                const progressBar = document.getElementById('progressBar');
                const shortCode = <?php echo json_encode($shortCode); ?>;

                function updateCountdown() {
                    countdownElement.textContent = countdown;
                    countdownTextElement.textContent = countdown;
                    
                    // Update progress bar
                    const progress = ((6 - countdown) / 6) * 100;
                    progressBar.style.width = (100 - progress) + '%';
                    
                    if (countdown <= 0) {
                        redirectNow();
                    } else {
                        countdown--;
                        setTimeout(updateCountdown, 1000);
                    }
                }

                function redirectNow() {
                    // Redirect through the API to ensure click tracking
                    window.location.href = '/redirect.php?code=' + encodeURIComponent(shortCode) + '&redirect=now';
                }

                // Start countdown
                setTimeout(updateCountdown, 1000);
            </script>
        <?php else: ?>
            <!-- Error page -->
            <div class="error-icon">
                <i class="fas fa-unlink"></i>
            </div>
            <h1 class="h2 mb-3">URL Not Found</h1>
            <p class="text-muted mb-4">
                The short URL you're looking for doesn't exist or has expired.
                <br>
                <small>Short code: <code><?php echo htmlspecialchars($shortCode); ?></code></small>
            </p>
            <div class="d-grid gap-2">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Go to Homepage
                </a>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>Create New Short URL
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>