<?php
require_once 'db.php';
include_once 'include/date.php';

if (!isset($_GET['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'No ID']);
    exit;
}

$staff_id = trim($_GET['staff_id']);

// Fetch staff info
$stmt = $conn->prepare("SELECT id, name FROM staff WHERE staff_id = ?");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $staff_db_id = $row['id'];
    $name = $row['name'];
    
    // Fetch today's meals
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
        'name' => $name,
        'db_id' => $staff_db_id,
        'meals' => $meals
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Staff not found']);
}