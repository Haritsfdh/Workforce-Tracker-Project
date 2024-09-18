<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear cookies by setting their expiry to a past time
if (isset($_COOKIE['id']) && isset($_COOKIE['key'])) {
    setcookie('id', '', time() - 86400 * 30 * 3); // set to expire in the past
    setcookie('key', '', time() - 86400 * 30 * 3); // set to expire in the past
}

// Redirect to login page or any other desired page
header("Location: login.php");
exit;
?>
