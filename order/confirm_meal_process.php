<?php
require_once '../admin/db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id = intval($_POST['staff_id']);
    $date = date('Y-m-d'); // Today's date

    // Step 1: Check if staff registered for today's meals
    $check_staff_sql = "SELECT * FROM staff_meals WHERE staff_id = ? AND meal_date = ?";
    $stmt_check_staff = $conn->prepare($check_staff_sql);
    if (!$stmt_check_staff) {
        die("Error preparing staff check: " . $conn->error);
    }
    $stmt_check_staff->bind_param('is', $staff_id, $date);
    $stmt_check_staff->execute();
    $result_staff = $stmt_check_staff->get_result();

    if ($result_staff->num_rows === 0) {
        echo "You are not registered for meal preferences today. Please register first.";
    } else {
        // Determine current meal type based on time
        $hour = date('H');
        $meal_column = '';
        if ($hour >= 5 && $hour < 10) {
            $meal_column = 'breakfast_received';
        } elseif ($hour >= 10 && $hour < 15) {
            $meal_column = 'lunch_received';
        } elseif ($hour >= 15 && $hour < 22) {
            $meal_column = 'dinner_received';
        } else {
            echo "⚠️ Meal confirmation is allowed only during meal hours.";
            exit;
        }

        // Step 2: Update meal_confirmation table
        $check_sql = "SELECT * FROM meal_confirmation WHERE staff_id = ? AND meal_date = ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param('is', $staff_id, $date);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows === 0) {
            $confirmation_sql = "INSERT INTO meal_confirmation (staff_id, meal_date, confirmed) VALUES (?, ?, 1)";
            $stmt_confirmation = $conn->prepare($confirmation_sql);
            $stmt_confirmation->bind_param('is', $staff_id, $date);
            $stmt_confirmation->execute();
        } else {
            $update_sql = "UPDATE meal_confirmation SET confirmed = 1 WHERE staff_id = ? AND meal_date = ?";
            $stmt_update = $conn->prepare($update_sql);
            $stmt_update->bind_param('is', $staff_id, $date);
            $stmt_update->execute();
        }

        // Step 3: Update staff_meals.{meal}_received = 1
        $update_meal_sql = "UPDATE staff_meals SET $meal_column = 1 WHERE staff_id = ? AND meal_date = ?";
        $stmt_meal = $conn->prepare($update_meal_sql);
        if (!$stmt_meal) {
            die("Error preparing meal update: " . $conn->error);
        }
        $stmt_meal->bind_param('is', $staff_id, $date);
        if ($stmt_meal->execute()) {
            echo "🎉 Meal receipt confirmed successfully!";
        } else {
            echo "❌ Failed to update meal receipt.";
        }

        $stmt_meal->close();
        $stmt_check->close();
    }

    $stmt_check_staff->close();
}

$conn->close();
?>
