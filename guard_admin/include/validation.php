<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'guard_admin') {
    header("Location: ../index.php");
    exit();
}
