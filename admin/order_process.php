<?php
require_once 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $staff_code = trim($_POST['staff_id'] ?? '');
    $date = date('Y-m-d'); // This is your meal_date
    $meal_option = $_POST['meal_option'] ?? '';
    $meals = $_POST['meals'] ?? [];

    // Validate staff code
    if (empty($staff_code)) {
        echo "Staff ID is missing!";
        exit;
    }

    // Assign meal types
    $breakfast = in_array("1", $meals) ? 1 : 0;
    $lunch = in_array("2", $meals) ? 1 : 0;
    $dinner = in_array("3", $meals) ? 1 : 0;

    // Meal option
    $egg = $meal_option === 'egg' ? 1 : 0;
    $chicken = $meal_option === 'chicken' ? 1 : 0;
    $vegetarian = $meal_option === 'vegetarian' ? 1 : 0;

    // Look up staff
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

    // Check if already submitted today
    $stmt_check = $conn->prepare("SELECT id FROM staff_meals WHERE staff_id = ? AND meal_date = ?");
    $stmt_check->bind_param("is", $staff_id, $date);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Update
        $stmt_update = $conn->prepare("
            UPDATE staff_meals 
            SET breakfast = ?, lunch = ?, dinner = ?, egg = ?, chicken = ?, vegetarian = ?, date = NOW()
            WHERE staff_id = ? AND meal_date = ?
        ");

        if (!$stmt_update) {
            die("Prepare failed (update): " . $conn->error);
        }

        $stmt_update->bind_param("iiiiiiis", $breakfast, $lunch, $dinner, $egg, $chicken, $vegetarian, $staff_id, $date);

        if ($stmt_update->execute()) {
            header("Location: meanual_order.php");
            exit;
        } else {
            echo "Error updating meal preference: " . $stmt_update->error;
        }

        $stmt_update->close();
    } else {
        // Insert
        $stmt_insert = $conn->prepare("
            INSERT INTO staff_meals (
                staff_id, 
                meal_date, 
                breakfast, 
                lunch, 
                dinner, 
                egg, 
                chicken, 
                vegetarian, 
                date, 
                manual_order
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)
        ");

        if (!$stmt_insert) {
            die("Prepare failed (insert): " . $conn->error);
        }

        $stmt_insert->bind_param("ssiiiiii", 
            $staff_id, 
            $date, // fixed variable used here
            $breakfast, 
            $lunch, 
            $dinner, 
            $egg, 
            $chicken, 
            $vegetarian
        );

        if ($stmt_insert->execute()) {
            header("Location: meanual_order.php");
            exit;
        } else {
            echo "Error inserting meal preference: " . $stmt_insert->error;
        }

        $stmt_insert->close();
    }

    $stmt_check->close();
    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
