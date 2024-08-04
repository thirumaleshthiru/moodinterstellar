<?php
session_start();
include 'db.php';

// Destroy the session
session_unset();
session_destroy();

// Delete cookies if they exist
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}
if (isset($_COOKIE['user_name'])) {
    setcookie('user_name', '', time() - 3600, '/');
}
if (isset($_COOKIE['user_email'])) {
    setcookie('user_email', '', time() - 3600, '/');
}
if (isset($_COOKIE['user_role'])) {
    setcookie('user_role', '', time() - 3600, '/');
}

// Redirect to the login page
header("Location: login.php");
exit();
?>
