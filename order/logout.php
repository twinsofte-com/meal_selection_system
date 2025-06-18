<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page (adjust the path if needed)
header("Location: ../index.php");
exit;
