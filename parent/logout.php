<?php
session_start(); // Start session to access session variables

// Unset all parent session variables
if (isset($_SESSION['parent'])) {
    unset($_SESSION['parent']);
}

// Destroy the session completely
session_destroy();

// Redirect to login page (you can change this to index.php if you want)
header("Location: ../index/index.php");
exit;
