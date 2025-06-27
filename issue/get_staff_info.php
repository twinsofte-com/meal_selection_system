<?php
include_once '../admin/include/date.php';
require '../admin/db.php';
header('Content-Type: application/json');

// Validate QR parameter
$qr = $_GET['qr'] ?? '';
if (empty($qr)) {
    echo json_encode(['error' => 'Invalid QR code']);
    exit;
}

// Prepare and execute staff lookup
$stmt = $conn->prepare("SELECT id, staff_id, name FROM staff WHERE qr_code = ?");
if (!$stmt) {
    echo json_encode(['error' => 'DB prepare error']);
    exit;
}
$stmt->bind_param("s", $qr);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    // Staff not registered
    echo json_encode(['error' => 'Staff not registered. Please contact HR to register.']);
    exit;
}

$staff = $result->fetch_assoc();
$staff_table_id = $staff['id'];
$staff_id = $staff['staff_id'];
$staff_name = $staff['name'];

// Check breakfast issuance
$today = date('Y-m-d');
$stmt2 = $conn->prepare("SELECT breakfast_received FROM staff_meals WHERE staff_id = ? AND meal_date = ?");
if (!$stmt2) {
    echo json_encode(['error' => 'DB prepare error (breakfast check)']);
    exit;
}
$stmt2->bind_param("is", $staff_table_id, $today);
$stmt2->execute();
$mealResult = $stmt2->get_result();

$breakfast_received = '0';
if ($mealResult && $mealResult->num_rows > 0) {
    $mealData = $mealResult->fetch_assoc();
    $breakfast_received = $mealData['breakfast_received'];
}

echo json_encode([
    'staff_id' => $staff_id,
    'name' => $staff_name,
    'breakfast_received' => $breakfast_received
]);
exit;
