<?php
// Include the proper configuration
require_once 'config.php';

// Get the short code from URL
$shortCode = $_GET['code'] ?? '';

if (empty($shortCode)) {
    header('Location: index.php');
    exit;
}

// Try to get the URL info first, then redirect
$apiUrl = $API_BASE_SERVER . '/api/url/' . urlencode($shortCode);

try {
    // Create context for the request
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'),
                'X-Forwarded-For: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'),
                'X-Real-IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'),
                'Referer: ' . ($_SERVER['HTTP_REFERER'] ?? ''),
            ],
            'timeout' => 10
        ]
    ]);
    
    // Make the request to get URL info
    $response = file_get_contents($apiUrl, false, $context);
    
    if ($response === false) {
        throw new Exception('Failed to fetch URL info');
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['originalUrl'])) {
        // Now call the redirect endpoint to track the click
        $redirectUrl = $API_BASE_SERVER . '/api/url/redirect/' . urlencode($shortCode);
        
        // Make a request to the redirect endpoint (this will track analytics)
        $redirectContext = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'),
                    'X-Forwarded-For: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'),
                    'X-Real-IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'),
                    'Referer: ' . ($_SERVER['HTTP_REFERER'] ?? ''),
                ],
                'timeout' => 5
            ]
        ]);
        
        // Try to call redirect endpoint for analytics (don't wait for response)
        @file_get_contents($redirectUrl, false, $redirectContext);
        
        // Redirect to the original URL
        header('Location: ' . $data['originalUrl']);
        exit;
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
    <title>URL Not Found - QuickLink</title>
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

        .error-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
        }

        .error-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
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
    </style>
</head>
<body>
    <div class="error-container">
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
    </div>
</body>
</html>