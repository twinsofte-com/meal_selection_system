<?php
include_once '../../admin/include/date.php';
require '../../admin/db.php';
session_start();

if (!isset($_SESSION['order_user'])) {
    header('Location: ../order_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_id'])) {
    $staff_id = trim($_POST['staff_id']);
    $breakfast_date = date('Y-m-d', strtotime('-1 day')); // yesterday

    // Check if staff is registered
    $stmt = $conn->prepare("SELECT id, name FROM staff WHERE staff_id = ?");
    $stmt->bind_param("s", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($staff = $result->fetch_assoc()) {
        // Registered staff, get their ID
        $staff_table_id = $staff['id'];
        $is_registered = true;
    } else {
        // Unregistered staff (extra meal)
        $is_registered = false;
        // Insert unregistered staff into the staff table
        $insert = $conn->prepare("INSERT INTO staff (staff_id, name) VALUES (?, ?)");
        $insert->bind_param("ss", $staff_id, 'Unregistered Staff');
        $insert->execute();
        $staff_table_id = $conn->insert_id; // Get the ID of the newly inserted staff record
    }

    // Insert or update breakfast meal record
    // Set manual_order to 0 by default
    $insert = $conn->prepare("INSERT INTO staff_meals (staff_id, meal_date, breakfast, breakfast_received, manual_order) 
    VALUES (?, ?, 1, 1, 0) ON DUPLICATE KEY UPDATE breakfast_received = 1, manual_order = 0");
    $insert->bind_param("ss", $staff_table_id, $breakfast_date);
    $insert->execute();

    // Log the meal issuance
    $issuer_id = $_SESSION['order_user'] ?? 0;
    $method = 'manual';  // Since the meal is being issued manually
    $log = $conn->prepare("INSERT INTO meal_issuance_log (staff_id, meal_type, issued_by, method) VALUES (?, 'breakfast', ?, ?)");
    $log->bind_param("iis", $staff_table_id, $issuer_id, $method);
    $log->execute();

    if ($is_registered) {
        echo "<div class='alert alert-success'>Meal issued successfully.</div>";
    } else {
        // Red alert for unregistered staff
        echo "<div class='alert alert-danger'>Extra meal issued to unregistered staff. Staff ID: {$staff_id}</div>";
    }
    exit;
}

echo "Invalid Request.";
exit;
?>
