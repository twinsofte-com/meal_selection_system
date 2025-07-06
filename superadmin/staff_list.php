<?php
include 'include/validation.php';

// Handle delete request
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM staff WHERE id = $id");
    echo "<script>alert('Staff deleted successfully!'); window.location='staff_list.php';</script>";
}

// Fetch all staff
$result = $conn->query("SELECT * FROM staff ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff List - Super Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'include/topbar.php'; ?>

<div class="max-w-6xl mx-auto p-6 mt-6 bg-white shadow rounded">
  <h2 class="text-2xl font-bold mb-4">Staff Management</h2>

  <div class="overflow-x-auto">
    <table class="w-full border table-auto">
      <thead class="bg-gray-200">
        <tr>
          <th class="px-4 py-2">#</th>
          <th class="px-4 py-2">Staff ID</th>
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Phone</th>
          <th class="px-4 py-2">Type</th>
          <th class="px-4 py-2">QR Code Text</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): $i = 1; ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="border-t hover:bg-gray-50">
              <td class="px-4 py-2"><?= $i++ ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($row['staff_id']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($row['name']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($row['phone_number']) ?></td>
              <td class="px-4 py-2"><?= $row['staff_type'] ?></td>
              <td class="px-4 py-2 font-mono text-blue-700"><?= $row['qr_code'] ?: '-' ?></td>
              <td class="px-4 py-2 space-x-2">
                <a href="edit_staff.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this staff member?')" class="text-red-600 hover:underline">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="px-4 py-4 text-center text-gray-500">No staff found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
