<?php
session_start(); // Start session to access session variables

// Unset all admin session variables
if (isset($_SESSION['admin'])) {
    unset($_SESSION['admin']);
}

// Destroy the session completely
session_destroy();

// Redirect to main index page
header("Location: ../index/index.php");
exit;
