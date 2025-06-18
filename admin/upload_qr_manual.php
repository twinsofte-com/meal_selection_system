<?php
include '../admin/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_id = $_POST['staff_id'];
    $qr_text = trim($_POST['qr_text']);

    if (!empty($staff_id) && !empty($qr_text)) {
        // Check for duplicate QR code
        $checkStmt = $conn->prepare("SELECT id, name FROM staff WHERE qr_code = ? AND id != ?");
        $checkStmt->bind_param("si", $qr_text, $staff_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $conflict_name = urlencode($row['name']);
            // Redirect with error and name
            header("Location: register.php?error=duplicate_qr&staff_id=$staff_id&conflict_name=$conflict_name");
            exit;
        }
        $checkStmt->close();

        // Update the QR code
        $stmt = $conn->prepare("UPDATE staff SET qr_code = ? WHERE id = ?");
        $stmt->bind_param("si", $qr_text, $staff_id);
        if ($stmt->execute()) {
            header("Location: register.php?success=1");
            exit;
        } else {
            header("Location: register.php?error=db");
            exit;
        }
    } else {
        header("Location: register.php?error=missing");
        exit;
    }
}
