<?php
include 'include/validation.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone_number']);
    $type = trim($_POST['staff_type']);
    $qr_text = trim($_POST['qr_code']);
    $staff_id = trim($_POST['staff_id']);

    if ($id && $name && $phone && $type && $qr_text && $staff_id) {
        // Check duplicate name
        $stmt_name = $conn->prepare("SELECT id FROM staff WHERE name = ? AND id != ?");
        $stmt_name->bind_param("si", $name, $id);
        $stmt_name->execute();
        $result_name = $stmt_name->get_result();
        if ($result_name->num_rows > 0) {
            header("Location: edit_staff.php?id=$id&error=duplicate_name");
            exit();
        }

        // Check duplicate QR
        $stmt_qr = $conn->prepare("SELECT id, name FROM staff WHERE qr_code = ? AND id != ?");
        $stmt_qr->bind_param("si", $qr_text, $id);
        $stmt_qr->execute();
        $result_qr = $stmt_qr->get_result();
        if ($row = $result_qr->fetch_assoc()) {
            $conflict_name = urlencode($row['name']);
            header("Location: edit_staff.php?id=$id&error=duplicate_qr&conflict_name=$conflict_name");
            exit();
        }

        // Check duplicate staff_id
        $stmt_id = $conn->prepare("SELECT id FROM staff WHERE staff_id = ? AND id != ?");
        $stmt_id->bind_param("si", $staff_id, $id);
        $stmt_id->execute();
        $result_id = $stmt_id->get_result();
        if ($result_id->num_rows > 0) {
            header("Location: edit_staff.php?id=$id&error=duplicate_id");
            exit();
        }

        // Update staff record
        $stmt_update = $conn->prepare("UPDATE staff SET name = ?, phone_number = ?, staff_type = ?, staff_id = ?, qr_code = ? WHERE id = ?");
        $stmt_update->bind_param("sssssi", $name, $phone, $type, $staff_id, $qr_text, $id);
        if ($stmt_update->execute()) {
            header("Location: staff_list.php?success=1");
            exit();
        } else {
            header("Location: edit_staff.php?id=$id&error=update_failed");
            exit();
        }
    } else {
        header("Location: edit_staff.php?id=$id&error=missing_fields");
        exit();
    }
}

// On GET: Show form
$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM staff WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) die("Staff not found");

$error = $_GET['error'] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Edit Staff</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'include/topbar.php'; ?>

<div class="max-w-xl mx-auto mt-6 bg-white p-6 shadow rounded">
  <h2 class="text-xl font-bold mb-4">Edit Staff: <?= htmlspecialchars($data['staff_id']) ?></h2>

  <?php if ($error): ?>
    <div class="bg-red-100 text-red-800 p-2 rounded mb-4">
      <?php
      if ($error === 'duplicate_name') echo "This name already exists.";
      elseif ($error === 'duplicate_qr') echo "QR code is already used by <strong>" . htmlspecialchars($_GET['conflict_name'] ?? '') . "</strong>.";
      elseif ($error === 'duplicate_id') echo "This Staff ID is already used.";
      elseif ($error === 'missing_fields') echo "Please fill in all required fields.";
      elseif ($error === 'update_failed') echo "Update failed. Please try again.";
      else echo "Unknown error.";
      ?>
    </div>
  <?php endif; ?>

  <form method="POST">
    <input type="hidden" name="id" value="<?= $data['id'] ?>" />

    <label class="block mb-1 font-medium">Staff ID</label>
    <input type="text" name="staff_id" value="<?= $data['staff_id'] ?>" class="w-full border p-2 rounded mb-4" required>

    <label class="block mb-1 font-medium">Name</label>
    <input type="text" name="name" value="<?= $data['name'] ?>" class="w-full border p-2 rounded mb-4" required>

    <label class="block mb-1 font-medium">Phone</label>
    <input type="text" name="phone_number" value="<?= $data['phone_number'] ?>" class="w-full border p-2 rounded mb-4">

    <label class="block mb-1 font-medium">Staff Type</label>
    <select name="staff_type" class="w-full border p-2 rounded mb-4">
      <option value="ECW" <?= $data['staff_type'] === 'ECW' ? 'selected' : '' ?>>ECW</option>
      <option value="INT" <?= $data['staff_type'] === 'INT' ? 'selected' : '' ?>>INT</option>
    </select>

    <label class="block mb-1 font-medium">QR Code (as text)</label>
    <input type="text" name="qr_code" value="<?= $data['qr_code'] ?>" class="w-full border p-2 rounded mb-4" required>

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Staff</button>
    <a href="staff_list.php" class="ml-4 text-blue-600 hover:underline">Back</a>
  </form>
</div>
</body>
</html>
