<?php
require_once '../admin/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id = intval($_POST['staff_id']);
    $date = date('Y-m-d'); // Today's date

    // Check if the user exists in the staff_meals table with today's date
    $check_staff_sql = "SELECT * FROM staff_meals WHERE staff_id = ? AND meal_date = ?";
    $stmt_check_staff = $conn->prepare($check_staff_sql);
    if ($stmt_check_staff === false) {
        error_log("Error preparing staff check statement: " . $conn->error);
        die("Error preparing staff check statement: " . $conn->error);
    }

    $stmt_check_staff->bind_param('is', $staff_id, $date);
    $stmt_check_staff->execute();
    $result_staff = $stmt_check_staff->get_result();

    if ($result_staff->num_rows === 0) {
        // User is not in staff_meals for today, show an error message
        echo "You are not registered for meal preferences today. Please register first.";
    } else {
        // Proceed to check meal confirmation status
        $check_sql = "SELECT * FROM meal_confirmation WHERE staff_id = ? AND meal_date = ?";
        $stmt_check = $conn->prepare($check_sql);
        if ($stmt_check === false) {
            error_log("Error preparing check statement: " . $conn->error);
            die("Error preparing check statement: " . $conn->error);
        }

        $stmt_check->bind_param('is', $staff_id, $date);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows === 0) {
            // Insert new confirmation record
            $confirmation_sql = "INSERT INTO meal_confirmation (staff_id, meal_date, confirmed)
                                 VALUES (?, ?, 1)";

            $stmt_confirmation = $conn->prepare($confirmation_sql);
            if ($stmt_confirmation === false) {
                error_log("Error preparing confirmation statement: " . $conn->error);
                die("Error preparing confirmation statement: " . $conn->error);
            }

            $stmt_confirmation->bind_param('is', $staff_id, $date);

            if ($stmt_confirmation->execute()) {
                echo "Meal receipt confirmed!";
            } else {
                error_log("Error confirming meal receipt: " . $conn->error);
                echo "Error confirming meal receipt: " . $conn->error;
            }

        } else {
            // Update existing confirmation record
            $update_sql = "UPDATE meal_confirmation SET confirmed = 1 WHERE staff_id = ? AND meal_date = ?";

            $stmt_update = $conn->prepare($update_sql);
            if ($stmt_update === false) {
                error_log("Error preparing update statement: " . $conn->error);
                die("Error preparing update statement: " . $conn->error);
            }

            $stmt_update->bind_param('is', $staff_id, $date);

            if ($stmt_update->execute()) {
                echo "Meal receipt re-confirmed!";
            } else {
                error_log("Error re-confirming meal receipt: " . $conn->error);
                echo "Error re-confirming meal receipt: " . $conn->error;
            }
        }

        $stmt_check->close();
    }

    $stmt_check_staff->close();
}

$conn->close();
?>
