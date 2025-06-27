<?php
require_once '../../admin/db.php';
include_once '../../admin/include/date.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_id'])) {
    $staff_id = trim($_POST['staff_id']);
    $meal_date = date('Y-m-d');
    $issuer_id = $_SESSION['order_user'] ?? 0;

    // Check if staff exists
    $stmt = $conn->prepare("SELECT id, name FROM staff WHERE staff_id = ?");
    $stmt->bind_param("s", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // STOP and show error if staff is not registered
        echo "Staff not registered. Please contact HR to register.";
        exit;
    }

    $staff = $result->fetch_assoc();
    $staff_table_id = $staff['id'];

    // Check if lunch was pre-ordered
    $ordered = false;
    $check = $conn->prepare("SELECT lunch FROM staff_meals WHERE staff_id = ? AND meal_date = ?");
    $check->bind_param("is", $staff_table_id, $meal_date);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0 && (int)$res->fetch_assoc()['lunch'] === 1) {
        $ordered = true;
    }

    $manual_flag = $ordered ? 0 : 1;

    // Insert or update the lunch record
    $insert = $conn->prepare("
        INSERT INTO staff_meals (staff_id, meal_date, lunch, lunch_received, manual_lunch)
        VALUES (?, ?, 1, 1, ?)
        ON DUPLICATE KEY UPDATE 
            lunch_received = 1,
            manual_lunch = VALUES(manual_lunch)
    ");
    $insert->bind_param("isi", $staff_table_id, $meal_date, $manual_flag);
    $insert->execute();

    // Log issuance
    $method = 'manual';
    $log = $conn->prepare("INSERT INTO meal_issuance_log (staff_id, meal_type, issued_by, method)
                           VALUES (?, 'lunch', ?, ?)");
    $log->bind_param("iis", $staff_table_id, $issuer_id, $method);
    $log->execute();

    echo "success";
    exit;
}

echo "Invalid Request.";
exit;
