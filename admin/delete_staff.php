<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        header("Location: register.php?error=InvalidID");
        exit;
    }

    // Step 1: Delete from meal_issuance_log
    $stmt1 = $conn->prepare("DELETE FROM meal_issuance_log WHERE staff_id = ?");
    $stmt1->bind_param('i', $id);
    $stmt1->execute();
    $stmt1->close();

    // Step 2: Delete from staff_meals
    $stmt2 = $conn->prepare("DELETE FROM staff_meals WHERE staff_id = ?");
    $stmt2->bind_param('i', $id);
    $stmt2->execute();
    $stmt2->close();

    // Step 3: Delete from staff
    $stmt3 = $conn->prepare("DELETE FROM staff WHERE id = ?");
    $stmt3->bind_param('i', $id);
    $stmt3->execute();
    $stmt3->close();

    header("Location: register.php?deleted=1");
    exit;
}
