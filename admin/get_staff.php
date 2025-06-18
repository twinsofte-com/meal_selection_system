<?php
require_once 'db.php';

if (!isset($_GET['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'No ID']);
    exit;
}

$staff_id = trim($_GET['staff_id']);

$stmt = $conn->prepare("SELECT name FROM staff WHERE staff_id = ?");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'name' => $row['name']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Staff not found']);
}

?>
