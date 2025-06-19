<?php
require_once 'db.php';
include_once 'include/date.php';

header('Content-Type: application/json');

$staff_code = $_GET['staff_id'] ?? '';

if (empty($staff_code)) {
    echo json_encode(['exists' => false, 'can_update' => false]);
    exit;
}

// Get staff ID
$stmt = $conn->prepare("SELECT id FROM staff WHERE staff_id = ?");
$stmt->bind_param("s", $staff_code);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['exists' => false, 'can_update' => false]);
    exit;
}

$staff_row = $res->fetch_assoc();
$staff_id = $staff_row['id'];

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Check for meals today
$query = "SELECT breakfast, lunch, dinner, meal_date FROM staff_meals 
          WHERE staff_id = ? AND (meal_date = ? OR meal_date = ?)";
$stmt2 = $conn->prepare($query);
$stmt2->bind_param("iss", $staff_id, $today, $tomorrow);
$stmt2->execute();
$result = $stmt2->get_result();

$response = [
    'exists' => false,
    'can_update' => false,  // To indicate if preferences can be updated
    'breakfast' => false,
    'lunch' => false,
    'dinner' => false
];

while ($row = $result->fetch_assoc()) {
    if ($row['meal_date'] == $today) {
        if ($row['lunch']) $response['lunch'] = true;
        if ($row['dinner']) $response['dinner'] = true;
    } elseif ($row['meal_date'] == $tomorrow) {
        if ($row['breakfast']) $response['breakfast'] = true;
    }
}

// Set 'exists' flag if any meal found
$response['exists'] = $response['breakfast'] || $response['lunch'] || $response['dinner'];

// Check if the preferences can be updated (if already exists for today)
if ($response['exists'] && !$response['breakfast'] && !$response['lunch'] && !$response['dinner']) {
    $response['can_update'] = true;
}

echo json_encode($response);

$stmt2->close();
$stmt->close();
$conn->close();
