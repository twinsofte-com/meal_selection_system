<?php
date_default_timezone_set('Asia/Colombo');
require '../../admin/db.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['order_user'])) {
    header('Location: ../order_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_id'])) {
    $staff_id = trim($_POST['staff_id']);
    $today = date('Y-m-d');

    // Step 1: Check if staff exists
    $stmt = $conn->prepare("SELECT id FROM staff WHERE staff_id = ?");
    $stmt->bind_param("s", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($staff = $result->fetch_assoc()) {
        $staff_table_id = $staff['id'];

        // Step 2: Check if meal record exists
        $check = $conn->prepare("SELECT breakfast, breakfast_received FROM staff_meals WHERE staff_id = ? AND meal_date = ?");
        $check->bind_param("is", $staff_table_id, $today);
        $check->execute();
        $meal_result = $check->get_result();

        if ($meal = $meal_result->fetch_assoc()) {

            if ($meal['breakfast'] != 1) {
                echo "No breakfast record found for this user.";
                exit;
            }

            if ($meal['breakfast_received'] == 1) {
                echo "The staff already received the breakfast.";
                exit;
            }

            // Step 3: Proceed to issue breakfast
            if (isset($_POST['manual']) && $_POST['manual'] == '1') {
                $update = $conn->prepare("UPDATE staff_meals SET breakfast_received = 1, manual_order = 1 WHERE staff_id = ? AND meal_date = ?");
                $method = 'manual';
            } else {
                $update = $conn->prepare("UPDATE staff_meals SET breakfast_received = 1 WHERE staff_id = ? AND meal_date = ?");
                $method = 'scan';
            }

            $update->bind_param("is", $staff_table_id, $today);

            if ($update->execute()) {
                // Step 4: Log to meal_issuance_log
                $issuer_id = $_SESSION['order_user'] ?? 0;
                $log = $conn->prepare("INSERT INTO meal_issuance_log (staff_id, meal_type, issued_by, method) VALUES (?, 'breakfast', ?, ?)");
                $log->bind_param("iis", $staff_table_id, $issuer_id, $method);
                $log->execute();

                echo "success";
                exit;
            } else {
                echo "Issue failed. Please try again.";
                exit;
            }

        } else {
            echo "No breakfast record found for this user.";
            exit;
        }

    } else {
        echo "Invalid Staff ID.";
        exit;
    }
}

echo "Invalid Request.";
exit;
