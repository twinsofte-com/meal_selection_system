<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    header("Location: register.php?deleted=1");
    exit;
}
