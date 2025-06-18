<?php
require_once '../admin/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id = intval($_POST['staff_id']);
    $date = date('Y-m-d');
    $preferences = $_POST['preferences'] ?? [];

    // Debugging: Check incoming data
    error_log("Received staff_id: $staff_id");
    error_log("Received preferences: " . implode(", ", $preferences));

    // Update meal confirmation
    $confirmation_sql = "INSERT INTO staff_meals (staff_id, meal_date, breakfast, lunch, dinner, date, meal_received)
                         VALUES (?, ?, ?, ?, ?, ?, 1)
                         ON DUPLICATE KEY UPDATE
                         breakfast = VALUES(breakfast),
                         lunch = VALUES(lunch),
                         dinner = VALUES(dinner),
                         meal_received = VALUES(meal_received)";

    // Determine meal preferences
    $breakfast = in_array('Breakfast', $preferences) ? 1 : 0;
    $lunch = in_array('Lunch', $preferences) ? 1 : 0;
    $dinner = in_array('Dinner', $preferences) ? 1 : 0;

    // Prepare and execute confirmation query
    $stmt_confirmation = $conn->prepare($confirmation_sql);
    if ($stmt_confirmation === false) {
        error_log("Error preparing confirmation statement: " . $conn->error);
        die("Error preparing confirmation statement: " . $conn->error);
    }

    $stmt_confirmation->bind_param('isssss', $staff_id, $date, $breakfast, $lunch, $dinner, $date);

    if ($stmt_confirmation->execute()) {
        echo "Meal receipt confirmed!";
    } else {
        error_log("Error confirming meal receipt: " . $stmt_confirmation->error);
        echo "Error confirming meal receipt: " . $stmt_confirmation->error;
    }

    $stmt_confirmation->close();
}

$conn->close();
?>
