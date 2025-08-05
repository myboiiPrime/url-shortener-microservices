<?php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Clear any cookies if they exist
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to home page with logout message
header('Location: index.php?logout=1');
exit;
?>