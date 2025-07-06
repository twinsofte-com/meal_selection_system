<?php
require_once 'db.php';
include_once 'include/date.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone_number']);
    $type = trim($_POST['staff_type']);
    $qr_text = trim($_POST['qr_text']);
    $staff_id = trim($_POST['staff_id']);

    if ($id && $name && $phone && $type && $qr_text && $staff_id) {
        // Check for duplicate name
        $stmt_name = $conn->prepare("SELECT id FROM staff WHERE name = ? AND id != ?");
        $stmt_name->bind_param("si", $name, $id);
        $stmt_name->execute();
        $result_name = $stmt_name->get_result();
        if ($result_name->num_rows > 0) {
            header("Location: register.php?error=duplicate_name&id=$id&name=" . urlencode($name) . "&phone=" . urlencode($phone) . "&type=" . urlencode($type) . "&qr=" . urlencode($qr_text));
            exit();
        }

        // Check for duplicate QR
        $stmt_qr = $conn->prepare("SELECT id, name FROM staff WHERE qr_code = ? AND id != ?");
        $stmt_qr->bind_param("si", $qr_text, $id);
        $stmt_qr->execute();
        $result_qr = $stmt_qr->get_result();
        if ($row = $result_qr->fetch_assoc()) {
            header("Location: register.php?error=duplicate_qr&conflict_name=" . urlencode($row['name']) . "&id=$id&name=" . urlencode($name) . "&phone=" . urlencode($phone) . "&type=" . urlencode($type) . "&qr=" . urlencode($qr_text));
            exit();
        }

        // Check for duplicate staff_id
        $stmt_id = $conn->prepare("SELECT id FROM staff WHERE staff_id = ? AND id != ?");
        $stmt_id->bind_param("si", $staff_id, $id);
        $stmt_id->execute();
        $result_id = $stmt_id->get_result();
        if ($result_id->num_rows > 0) {
            header("Location: register.php?error=duplicate_id&id=$id&name=" . urlencode($name) . "&phone=" . urlencode($phone) . "&type=" . urlencode($type) . "&qr=" . urlencode($qr_text));
            exit();
        }

        // Update all fields
        $stmt_update = $conn->prepare("UPDATE staff SET name = ?, phone_number = ?, staff_type = ?, staff_id = ?, qr_code = ? WHERE id = ?");
        $stmt_update->bind_param("sssssi", $name, $phone, $type, $staff_id, $qr_text, $id);

        if ($stmt_update->execute()) {
            header("Location: register.php?success=1");
            exit();
        } else {
            header("Location: register.php?error=update_failed&id=$id");
            exit();
        }

    } else {
        header("Location: register.php?error=missing_fields&id=$id");
        exit();
    }
}
?>
