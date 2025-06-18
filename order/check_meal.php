<?php
require_once '../admin/db.php';

// Retrieve staff ID from GET parameter
$staff_code = $_GET['staff_id'] ?? '';

if (empty($staff_code)) {
    echo json_encode(['exists' => false]);
    exit;
}

// Look up staff ID
$stmt = $conn->prepare("SELECT id FROM staff WHERE staff_id = ?");
$stmt->bind_param("s", $staff_code);
$stmt->execute();
$staff_result = $stmt->get_result();

if ($staff_result->num_rows === 0) {
    echo json_encode(['exists' => false]);
    exit;
}

$staff_row = $staff_result->fetch_assoc();
$staff_id = $staff_row['id'];

// Check if meal record exists for today
$date = date('Y-m-d');
$stmt_check = $conn->prepare("SELECT id FROM staff_meals WHERE staff_id = ? AND meal_date = ?");
$stmt_check->bind_param("is", $staff_id, $date);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

// Return response indicating if meal exists
if ($result_check->num_rows > 0) {
    echo json_encode(['exists' => true]);
} else {
    echo json_encode(['exists' => false]);
}

$stmt_check->close();
$stmt->close();
$conn->close();
?>
