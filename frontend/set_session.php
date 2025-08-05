<?php
session_start();

// Set CORS headers for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    error_log("Raw input received: " . $input);
    
    $data = json_decode($input, true);
    error_log("Decoded data: " . print_r($data, true));

    if (!$data) {
        error_log("JSON decode failed. JSON error: " . json_last_error_msg());
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    // Validate required fields
    if (!isset($data['user']) || !isset($data['token'])) {
        error_log("Missing fields. User exists: " . (isset($data['user']) ? 'yes' : 'no') . ", Token exists: " . (isset($data['token']) ? 'yes' : 'no'));
        error_log("Available keys: " . implode(', ', array_keys($data)));
        throw new Exception('Missing user or token data');
    }

    // Store user data in session
    $_SESSION['user'] = $data['user'];
    $_SESSION['token'] = $data['token'];

    // Optional: Set session timeout (24 hours)
    $_SESSION['login_time'] = time();
    $_SESSION['expires_at'] = time() + (24 * 60 * 60);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Session set successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>