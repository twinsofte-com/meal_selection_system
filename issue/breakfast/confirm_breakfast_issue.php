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

    if ($staff = $result->fetch_assoc()) {
        $staff_table_id = $staff['id'];
        $is_registered = true;
    } else {
        $is_registered = false;
        $insert = $conn->prepare("INSERT INTO staff (staff_id, name) VALUES (?, 'Unregistered Staff')");
        $insert->bind_param("s", $staff_id);
        $insert->execute();
        $staff_table_id = $conn->insert_id;
    }

    // Check if user ordered breakfast
    $ordered = false;
    $check = $conn->prepare("SELECT breakfast FROM staff_meals WHERE staff_id = ? AND meal_date = ?");
    $check->bind_param("is", $staff_table_id, $meal_date);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows > 0 && $res->fetch_assoc()['breakfast'] == 1) {
        $ordered = true;
    }

    // Update or insert breakfast record
    $insert = $conn->prepare("INSERT INTO staff_meals (staff_id, meal_date, breakfast, breakfast_received, manual_order)
                              VALUES (?, ?, 1, 1, 0)
                              ON DUPLICATE KEY UPDATE breakfast_received = 1");
    $insert->bind_param("is", $staff_table_id, $meal_date);
    $insert->execute();

    // Log extra if not ordered
    if (!$ordered) {
        $reason = 'No breakfast ordered';
        // Check if an extra row exists
        $checkExtra = $conn->prepare("SELECT id FROM extra_meal_issues WHERE staff_id = ? AND meal_date = ?");
        $checkExtra->bind_param("is", $staff_table_id, $meal_date);
        $checkExtra->execute();
        $resExtra = $checkExtra->get_result();

        if ($resExtra->num_rows > 0) {
            $row = $resExtra->fetch_assoc();
            $update = $conn->prepare("UPDATE extra_meal_issues SET breakfast = 1 WHERE id = ?");
            $update->bind_param("i", $row['id']);
            $update->execute();
        } else {
            $insertExtra = $conn->prepare("INSERT INTO extra_meal_issues (staff_id, meal_date, breakfast, reason, issued_by_pin)
                                           VALUES (?, ?, 1, ?, ?)");
            $insertExtra->bind_param("issi", $staff_table_id, $meal_date, $reason, $issuer_id);
            $insertExtra->execute();
        }
    }

    // Log general meal issuance
    $method = 'manual';
    $log = $conn->prepare("INSERT INTO meal_issuance_log (staff_id, meal_type, issued_by, method) VALUES (?, 'breakfast', ?, ?)");
    $log->bind_param("iis", $staff_table_id, $issuer_id, $method);
    $log->execute();

    echo "success";
    exit;
}

echo "Invalid Request.";
exit;
