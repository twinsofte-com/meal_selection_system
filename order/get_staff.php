<?php
require_once '../admin/db.php';
header('Content-Type: application/json');

if (isset($_GET['qr_code'])) {
    $qr_code = trim($_GET['qr_code']);
    $stmt = $conn->prepare("SELECT staff_id, name FROM staff WHERE qr_code = ?");
    $stmt->bind_param("s", $qr_code);
} elseif (isset($_GET['staff_id'])) {
    $staff_id = trim($_GET['staff_id']);
    $stmt = $conn->prepare("SELECT staff_id, name FROM staff WHERE staff_id = ?");
    $stmt->bind_param("s", $staff_id);
} else {
    echo json_encode(['success' => false, 'message' => 'QR code or Staff ID is required.']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $staff = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'staff_id' => $staff['staff_id'],
        'name' => $staff['name']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Staff not found.']);
}
