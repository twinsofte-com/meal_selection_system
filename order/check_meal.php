<?php
include_once '../admin/include/date.php';
require_once '../admin/db.php';
header('Content-Type: application/json');

$staff_code = $_GET['staff_id'] ?? '';

if (empty($staff_code)) {
    echo json_encode(['exists' => false]);
    exit;
}

// Get staff ID
$stmt = $conn->prepare("SELECT id FROM staff WHERE staff_id = ?");
$stmt->bind_param("s", $staff_code);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['exists' => false]);
    exit;
}

$staff_row = $res->fetch_assoc();
$staff_id = $staff_row['id'];

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$stmt2 = $conn->prepare("SELECT * FROM staff_meals WHERE staff_id = ? AND (meal_date = ? OR meal_date = ?)");
$stmt2->bind_param("iss", $staff_id, $today, $tomorrow);
$stmt2->execute();
$meal_result = $stmt2->get_result();

$response = [
    'exists' => false,
    'today' => null,
    'tomorrow' => null
];

while ($row = $meal_result->fetch_assoc()) {
    if ($row['meal_date'] === $today) {
        $response['today'] = $row;
    } elseif ($row['meal_date'] === $tomorrow) {
        $response['tomorrow'] = $row;
    }
}

$response['exists'] = ($response['today'] || $response['tomorrow']);
echo json_encode($response);
