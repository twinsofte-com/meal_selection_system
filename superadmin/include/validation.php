<?php
session_start();
include '../admin/db.php'; // or wherever your DB connection is

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../../index.php");
    exit();
}
?>
