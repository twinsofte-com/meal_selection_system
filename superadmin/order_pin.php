<?php
include 'include/validation.php';

$msg = "";

// Add new order PIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pin_code'])) {
    $pin_code = trim($_POST['pin_code']);
    if (!empty($pin_code)) {
        $stmt = $conn->prepare("INSERT INTO order_pins (role, pin_code) VALUES ('order', ?)");
        $stmt->bind_param("s", $pin_code);
        $stmt->execute();
        $msg = "New order PIN added successfully!";
    } else {
        $msg = "PIN code cannot be empty.";
    }
}

// Delete PIN
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM order_pins WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $msg = "Order PIN deleted successfully!";
}

// Fetch all order PINs
$result = $conn->query("SELECT * FROM order_pins WHERE role = 'order' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Order PINs</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'include/topbar.php'; ?>

<div class="max-w-3xl mx-auto p-6 mt-6 bg-white shadow rounded">
  <h2 class="text-2xl font-bold mb-4">Order PIN Management</h2>

  <?php if ($msg): ?>
    <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded mb-4"><?= $msg ?></div>
  <?php endif; ?>

  <!-- Add New PIN -->
  <form method="POST" class="mb-6">
    <label class="block mb-2 font-medium">New Order PIN</label>
    <div class="flex">
      <input type="text" name="pin_code" required class="flex-1 border p-2 rounded-l" placeholder="Enter 6-digit PIN">
      <button type="submit" class="bg-blue-600 text-white px-4 rounded-r hover:bg-blue-700">Add</button>
    </div>
  </form>

  <!-- Existing PINs -->
  <div class="overflow-x-auto">
    <table class="w-full table-auto border border-gray-300">
      <thead class="bg-gray-200">
        <tr>
          <th class="px-4 py-2 text-left">#</th>
          <th class="px-4 py-2 text-left">PIN Code</th>
          <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): $i = 1; ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="border-t">
              <td class="px-4 py-2"><?= $i++ ?></td>
              <td class="px-4 py-2 font-mono font-semibold text-blue-800"><?= htmlspecialchars($row['pin_code']) ?></td>
              <td class="px-4 py-2">
                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this PIN?')" class="text-red-600 hover:underline">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="3" class="px-4 py-4 text-center text-gray-500">No order PINs available.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
