<?php
include_once '../../admin/include/date.php';
require '../../admin/db.php';
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

    // Check if dinner was pre-ordered
    $check = $conn->prepare("SELECT dinner FROM staff_meals WHERE staff_id = ? AND meal_date = ?");
    $check->bind_param("is", $staff_table_id, $meal_date);
    $check->execute();
    $res = $check->get_result();

    $ordered = false;
    if ($res->num_rows > 0 && (int) $res->fetch_assoc()['dinner'] === 1) {
        $ordered = true;
    }

    // Set manual_dinner = 1 only if not pre-ordered
    $manual_flag = $ordered ? 0 : 1;

    $today_date = date('Y-m-d'); // Or use date('Y-m-d H:i:s') if your `date` column is DATETIME

    // Insert or update staff_meals for dinner
    $insert = $conn->prepare("
    INSERT INTO staff_meals (staff_id, meal_date, dinner, dinner_received, manual_dinner, date)
    VALUES (?, ?, 1, 1, ?, ?)
    ON DUPLICATE KEY UPDATE 
        dinner_received = 1,
        manual_dinner = VALUES(manual_dinner),
        date = VALUES(date)
");
    $insert->bind_param("isis", $staff_table_id, $meal_date, $manual_flag, $today_date);
    $insert->execute();


    // Log the issuance
    $method = 'manual';
    $log = $conn->prepare("INSERT INTO meal_issuance_log (staff_id, meal_type, issued_by, method)
                           VALUES (?, 'dinner', ?, ?)");
    $log->bind_param("iis", $staff_table_id, $issuer_id, $method);
    $log->execute();

    echo "success";
    exit;
}

echo "Invalid Request.";
exit;
