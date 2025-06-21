<?php
require_once '../admin/db.php';
include_once '../admin/include/date.php';

header('Content-Type: application/json');

$staff_id = '';
$staff_db_id = 0;
$name = '';

if (isset($_GET['qr_code'])) {
    $qr_code = trim($_GET['qr_code']);
    $stmt = $conn->prepare("SELECT id, staff_id, name FROM staff WHERE qr_code = ?");
    $stmt->bind_param("s", $qr_code);
} elseif (isset($_GET['staff_id'])) {
    $staff_id = trim($_GET['staff_id']);
    $stmt = $conn->prepare("SELECT id, staff_id, name FROM staff WHERE staff_id = ?");
    $stmt->bind_param("s", $staff_id);
} else {
    echo json_encode(['success' => false, 'message' => 'QR code or Staff ID is required.']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $staff_id = $row['staff_id'];
    $staff_db_id = $row['id'];
    $name = $row['name'];

    // Get meal info for today and tomorrow
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    $stmt2 = $conn->prepare("SELECT * FROM staff_meals WHERE staff_id = ? AND meal_date IN (?, ?)");
    $stmt2->bind_param("iss", $staff_db_id, $today, $tomorrow);
    $stmt2->execute();
    $meal_result = $stmt2->get_result();

    $meals = [];
    while ($meal = $meal_result->fetch_assoc()) {
        $meals[$meal['meal_date']] = $meal;
    }

    echo json_encode([
        'success' => true,
        'staff_id' => $staff_id,
        'name' => $name,
        'db_id' => $staff_db_id,
        'meals' => $meals
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Staff not found.']);
}

$stmt->close();
$conn->close();
