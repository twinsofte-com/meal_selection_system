<?php
include_once 'validation.php';
require_once '../db.php';

// Save or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pin_code = trim($_POST['pin_code']);
  $role = 'order'; // Role is fixed for this file

  if (isset($_POST['update_id'])) {
    $id = $_POST['update_id'];
    $stmt = $conn->prepare("UPDATE order_pins SET pin_code = ? WHERE id = ?");
    $stmt->bind_param("si", $pin_code, $id);
  } else {
    $stmt = $conn->prepare("INSERT INTO order_pins (role, pin_code) VALUES (?, ?)");
    $stmt->bind_param("ss", $role, $pin_code);
  }

  $stmt->execute();
  header("Location: order_pin.php");
  exit();
}

// Delete logic
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $conn->query("DELETE FROM order_pins WHERE id = $id");
  header("Location: order_pin.php");
  exit();
}

// Fetch all pins
$pins = $conn->query("SELECT * FROM order_pins ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Order PINs</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 bg-gray-50">
    <?php include 'topbar.php'; ?>
  <h1 class="text-2xl font-bold text-[#2E3095] mb-4">Manage Order PINs</h1>

  <form method="POST" class="mb-6 space-y-2">
    <!-- Hidden Role -->
    <input type="hidden" name="role" value="order">
    <!-- PIN Input -->
    <input type="text" name="pin_code" placeholder="Enter PIN Code" required class="border p-2 w-full rounded" />
    <button type="submit" class="bg-[#2E3095] text-white px-4 py-2 rounded">Save</button>
  </form>

  <table class="w-full border bg-white rounded shadow">
    <thead>
      <tr class="bg-gray-200">
        <th class="p-2 text-left">ID</th>
        <th class="p-2 text-left">Role</th>
        <th class="p-2 text-left">PIN Code</th>
        <th class="p-2 text-left">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $pins->fetch_assoc()): ?>
        <tr class="border-t">
          <td class="p-2"><?= $row['id'] ?></td>
          <td class="p-2 text-gray-700"><?= htmlspecialchars($row['role']) ?></td>
          <td class="p-2 font-mono"><?= htmlspecialchars($row['pin_code']) ?></td>
          <td class="p-2">
            <a href="?delete=<?= $row['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Delete this PIN?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>
