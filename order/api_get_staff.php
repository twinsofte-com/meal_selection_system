<?php
require_once '../admin/db.php';

header('Content-Type: application/json');

// Validate input
if (!isset($_GET['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing staff ID']);
    exit;
}

$staff_id = $_GET['staff_id'];
$stmt = $conn->prepare("SELECT name FROM staff WHERE staff_id = ?");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $staff = $result->fetch_assoc();
    echo json_encode(['success' => true, 'name' => $staff['name']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Staff not found']);
}
?>
