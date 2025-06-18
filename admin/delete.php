<?php
require_once 'db.php';

// Check if the staff ID is provided
if (isset($_GET['staff_id']) && is_numeric($_GET['staff_id'])) {
    $staff_id = intval($_GET['staff_id']);

    // Prepare and execute the deletion query
    $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
    $stmt->bind_param('i', $staff_id);

    if ($stmt->execute()) {
        $message = "Staff member deleted successfully.";
    } else {
        $message = "Error: " . $conn->error;
    }

    $stmt->close();
} else {
    $message = "Invalid staff ID.";
}

// Redirect back to the register page with a message
header("Location: register.php?message=" . urlencode($message));
exit;
