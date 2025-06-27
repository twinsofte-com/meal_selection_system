<?php
session_start();

if (!isset($_SESSION['issue_user'])) {
    // Redirect to login if session not found
    header("Location: ../index.php");
    exit;
}
?>