<?php
include_once '../../admin/include/date.php';
require '../../admin/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_id'])) {
    $staff_id = trim($_POST['staff_id']);
    $meal_date = date('Y-m-d');
    $issuer_id = $_SESSION['order_user'] ?? 0;

    // Staff check
    $stmt = $conn->prepare("SELECT id, name FROM staff WHERE staff_id = ?");
    $stmt->bind_param("s", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($staff = $result->fetch_assoc()) {
        $staff_table_id = $staff['id'];
    } else {
        $insert = $conn->prepare("INSERT INTO staff (staff_id, name) VALUES (?, 'Unregistered Staff')");
        $insert->bind_param("s", $staff_id);
        $insert->execute();
        $staff_table_id = $conn->insert_id;
    }

    // Check if dinner was ordered
    $ordered = false;
    $check = $conn->prepare("SELECT dinner FROM staff_meals WHERE staff_id = ? AND meal_date = ?");
    $check->bind_param("is", $staff_table_id, $meal_date);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows > 0 && $res->fetch_assoc()['dinner'] == 1) {
        $ordered = true;
    }

    // Insert/update dinner record
    $manual_flag = $ordered ? 0 : 1;
    $insert = $conn->prepare("INSERT INTO staff_meals (staff_id, meal_date, dinner, dinner_received, manual_dinner)
                              VALUES (?, ?, 1, 1, ?)
                              ON DUPLICATE KEY UPDATE dinner_received = 1, manual_dinner = VALUES(manual_dinner)");
    $insert->bind_param("isi", $staff_table_id, $meal_date, $manual_flag);
    $insert->execute();

    // Log issuance
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
