<?php
session_start();

// Example check: only allow if session admin is set
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php'); // or your admin login page
    exit();
}
?>
