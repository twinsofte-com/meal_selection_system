<?php
require_once '../admin/db.php';
include_once '../admin/include/date.php';

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

    $breakfast = in_array("1", $meals) ? 1 : 0;
    $lunch     = in_array("2", $meals) ? 1 : 0;
    $dinner    = in_array("3", $meals) ? 1 : 0;

    $egg        = $meal_option === 'egg' ? 1 : 0;
    $chicken    = $meal_option === 'chicken' ? 1 : 0;
    $vegetarian = $meal_option === 'vegetarian' ? 1 : 0;

    // Fetch staff internal ID
    $stmt = $conn->prepare("SELECT id FROM staff WHERE staff_id = ?");
    $stmt->bind_param("s", $staff_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Staff member not found!";
        exit;
    }

    $staff_id = $result->fetch_assoc()['id'];

    // ✅ Process Today (Lunch/Dinner)
    if ($lunch || $dinner) {
        $stmt_check_today = $conn->prepare("SELECT id FROM staff_meals WHERE staff_id = ? AND meal_date = ?");
        $stmt_check_today->bind_param("is", $staff_id, $today);
        $stmt_check_today->execute();
        $res_today = $stmt_check_today->get_result();

        if ($res_today->num_rows > 0) {
            // Update today's record
            $stmt_update = $conn->prepare("
                UPDATE staff_meals 
                SET lunch = ?, dinner = ?, egg = ?, chicken = ?, vegetarian = ?, date = NOW()
                WHERE staff_id = ? AND meal_date = ?
            ");
            $stmt_update->bind_param("iiiiiis", $lunch, $dinner, $egg, $chicken, $vegetarian, $staff_id, $today);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // Insert new record for today
            $stmt_insert = $conn->prepare("
                INSERT INTO staff_meals 
                (staff_id, meal_date, lunch, dinner, egg, chicken, vegetarian, date, lunch_received, dinner_received)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0, 0)
            ");
            $stmt_insert->bind_param("isiiiii", $staff_id, $today, $lunch, $dinner, $egg, $chicken, $vegetarian);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        $stmt_check_today->close();
    }

    // ✅ Process Tomorrow (Breakfast)
    if ($breakfast) {
        $stmt_check_tomorrow = $conn->prepare("SELECT id FROM staff_meals WHERE staff_id = ? AND meal_date = ?");
        $stmt_check_tomorrow->bind_param("is", $staff_id, $tomorrow);
        $stmt_check_tomorrow->execute();
        $res_tomorrow = $stmt_check_tomorrow->get_result();

        if ($res_tomorrow->num_rows > 0) {
            // Update tomorrow's record
            $stmt_update = $conn->prepare("
                UPDATE staff_meals 
                SET breakfast = ?, egg = ?, chicken = ?, vegetarian = ?, date = NOW()
                WHERE staff_id = ? AND meal_date = ?
            ");
            $stmt_update->bind_param("iiiiis", $breakfast, $egg, $chicken, $vegetarian, $staff_id, $tomorrow);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // Insert new record for tomorrow
            $stmt_insert = $conn->prepare("
                INSERT INTO staff_meals 
                (staff_id, meal_date, breakfast, egg, chicken, vegetarian, date, breakfast_received)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 0)
            ");
            $stmt_insert->bind_param("isiiii", $staff_id, $tomorrow, $breakfast, $egg, $chicken, $vegetarian);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        $stmt_check_tomorrow->close();
    }

    $stmt->close();
    header("Location: dashboard.php?success=1");
    exit;
} else {
    echo "Invalid request.";
}

$conn->close();
