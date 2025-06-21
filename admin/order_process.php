<?php
require_once 'db.php';
include_once 'include/date.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_code = trim($_POST['staff_id'] ?? '');
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    $meal_option = $_POST['meal_option'] ?? '';
    $meals = $_POST['meals'] ?? [];

    if (empty($staff_code)) {
        echo "Staff ID is missing!";
        exit;
    }

    // Default manual_order = 0 (since this is manual order page)
    $manual_order = 0;

    // Determine selected meals
    $breakfast = in_array("1", $meals) ? 1 : 0;
    $lunch     = in_array("2", $meals) ? 1 : 0;
    $dinner    = in_array("3", $meals) ? 1 : 0;

    // Meal options (only for lunch)
    $egg = $chicken = $vegetarian = 0;
    if ($lunch) {
        $egg        = $meal_option === 'egg' ? 1 : 0;
        $chicken    = $meal_option === 'chicken' ? 1 : 0;
        $vegetarian = $meal_option === 'vegetarian' ? 1 : 0;
    }

    // Get internal staff ID
    $stmt = $conn->prepare("SELECT id FROM staff WHERE staff_id = ?");
    $stmt->bind_param("s", $staff_code);
    $stmt->execute();
    $staff_result = $stmt->get_result();

    if ($staff_result->num_rows === 0) {
        echo "Staff member not found!";
        exit;
    }

    $staff_row = $staff_result->fetch_assoc();
    $staff_id = $staff_row['id'];

    // --- TODAY: Lunch & Dinner ---
    if ($lunch || $dinner) {
        $stmt_check_today = $conn->prepare("SELECT id FROM staff_meals WHERE staff_id = ? AND meal_date = ?");
        $stmt_check_today->bind_param("is", $staff_id, $today);
        $stmt_check_today->execute();
        $result_today = $stmt_check_today->get_result();

        if ($result_today->num_rows > 0) {
            $stmt_update = $conn->prepare("
                UPDATE staff_meals 
                SET lunch = ?, dinner = ?, egg = ?, chicken = ?, vegetarian = ?, date = NOW(), manual_order = ?
                WHERE staff_id = ? AND meal_date = ?
            ");
            $stmt_update->bind_param("iiiiiiis", $lunch, $dinner, $egg, $chicken, $vegetarian, $manual_order, $staff_id, $today);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $stmt_insert = $conn->prepare("
                INSERT INTO staff_meals 
                (staff_id, meal_date, lunch, dinner, egg, chicken, vegetarian, date, manual_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)
            ");
            $stmt_insert->bind_param("isiiiiii", $staff_id, $today, $lunch, $dinner, $egg, $chicken, $vegetarian, $manual_order);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        $stmt_check_today->close();
    }

    // --- TOMORROW: Breakfast ---
    if ($breakfast) {
        $stmt_check_tomorrow = $conn->prepare("SELECT id FROM staff_meals WHERE staff_id = ? AND meal_date = ?");
        $stmt_check_tomorrow->bind_param("is", $staff_id, $tomorrow);
        $stmt_check_tomorrow->execute();
        $result_tomorrow = $stmt_check_tomorrow->get_result();

        if ($result_tomorrow->num_rows > 0) {
            $stmt_update = $conn->prepare("
                UPDATE staff_meals 
                SET breakfast = 1, date = NOW(), manual_order = ?
                WHERE staff_id = ? AND meal_date = ?
            ");
            $stmt_update->bind_param("iis", $manual_order, $staff_id, $tomorrow);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $stmt_insert = $conn->prepare("
                INSERT INTO staff_meals 
                (staff_id, meal_date, breakfast, date, manual_order)
                VALUES (?, ?, 1, NOW(), ?)
            ");
            $stmt_insert->bind_param("isi", $staff_id, $tomorrow, $manual_order);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        $stmt_check_tomorrow->close();
    }

    header("Location: meanual_order.php?success=1");
    exit;
}

$conn->close();
