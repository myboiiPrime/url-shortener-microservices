<?php
session_start();

echo "<h1>Session Debug</h1>";

echo "<h2>Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Session Status:</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";

if (isset($_SESSION['user'])) {
    echo "<h2>User Data:</h2>";
    echo "User ID: " . $_SESSION['user']['id'] . "<br>";
    echo "Username: " . $_SESSION['user']['username'] . "<br>";
    echo "Email: " . $_SESSION['user']['email'] . "<br>";
}

if (isset($_SESSION['token'])) {
    echo "<h2>Token:</h2>";
    echo "Token: " . $_SESSION['token'] . "<br>";
}

echo "<h2>Cookies:</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";
?>