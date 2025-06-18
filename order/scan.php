<?php
require_once '../admin/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id = intval($_POST['staff_id']);
    $preferences = isset($_POST['preferences']) ? $_POST['preferences'] : [];
    $date = date('Y-m-d');

    // Debugging: Check incoming data
    error_log("Received staff_id: $staff_id");
    error_log("Received preferences: " . implode(', ', $preferences));

    // Record attendance
    $attendance_sql = "INSERT INTO attendance (staff_id, date) VALUES (?, ?)";

    // Prepare and execute attendance query
    $stmt_attendance = $conn->prepare($attendance_sql);
    if ($stmt_attendance === false) {
        error_log("Error preparing attendance statement: " . $conn->error);
        die("Error preparing attendance statement: " . $conn->error);
    }
    
    $stmt_attendance->bind_param('is', $staff_id, $date);

    if ($stmt_attendance->execute()) {
        // Update meal preferences
        $breakfast = in_array('Breakfast', $preferences) ? 1 : 0;
        $lunch = in_array('Lunch', $preferences) ? 1 : 0;
        $dinner = in_array('Dinner', $preferences) ? 1 : 0;

        $preferences_sql = "INSERT INTO staff_meals (staff_id, meal_date, breakfast, lunch, dinner)
                            VALUES (?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                            breakfast = VALUES(breakfast),
                            lunch = VALUES(lunch),
                            dinner = VALUES(dinner)";

        // Prepare and execute preferences query
        $stmt_preferences = $conn->prepare($preferences_sql);
        if ($stmt_preferences === false) {
            error_log("Error preparing preferences statement: " . $conn->error);
            die("Error preparing preferences statement: " . $conn->error);
        }
        
        $stmt_preferences->bind_param('isiii', $staff_id, $date, $breakfast, $lunch, $dinner);

        if ($stmt_preferences->execute()) {
            echo "Attendance and meal preferences recorded!";
        } else {
            error_log("Error updating meal preferences: " . $conn->error);
            echo "Error updating meal preferences: " . $conn->error;
        }

        $stmt_preferences->close();
    } else {
        error_log("Error recording attendance: " . $conn->error);
        echo "Error recording attendance: " . $conn->error;
    }

    $stmt_attendance->close();
}

$conn->close();
?>
