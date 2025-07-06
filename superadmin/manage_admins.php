<?php
include 'include/validation.php'; // already includes db.php

// Handle delete if requested
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);

    // Prevent self-deletion
    if ($_SESSION['username'] === getUsernameById($conn, $deleteId)) {
        echo "<script>alert('You cannot delete yourself!');</script>";
    } else {
        // Check if the target is a super_admin
        $check = $conn->prepare("SELECT role FROM admins WHERE id = ?");
        $check->bind_param("i", $deleteId);
        $check->execute();
        $roleCheck = $check->get_result()->fetch_assoc();
        if ($roleCheck['role'] === 'super_admin') {
            echo "<script>alert('You cannot delete another super admin!');</script>";
        } else {
            $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->bind_param("i", $deleteId);
            $stmt->execute();
            echo "<script>alert('Admin deleted successfully!'); window.location='manage_admins.php';</script>";
        }
    }
}

// Helper to get username by ID
function getUsernameById($conn, $id) {
    $stmt = $conn->prepare("SELECT username FROM admins WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['username'];
}

// Fetch all admins
$result = $conn->query("SELECT id, username, name, email, role FROM admins ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Admins - Super Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
<?php include 'include/topbar.php'; ?>

<div class="p-6">
  <h1 class="text-2xl font-bold mb-4">Manage Admin Users</h1>
  <a href="create_admin.php" class="mb-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Add New Admin</a>
  
  <div class="bg-white shadow rounded-lg overflow-x-auto">
    <table class="min-w-full table-auto border border-gray-200">
      <thead class="bg-gray-200">
        <tr>
          <th class="px-4 py-2 text-left">#</th>
          <th class="px-4 py-2 text-left">Username</th>
          <th class="px-4 py-2 text-left">Name</th>
          <th class="px-4 py-2 text-left">Email</th>
          <th class="px-4 py-2 text-left">Role</th>
          <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr class="border-t">
            <td class="px-4 py-2"><?= htmlspecialchars($row['id']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['username']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['name']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
            <td class="px-4 py-2 font-medium"><?= strtoupper($row['role']) ?></td>
            <td class="px-4 py-2 space-x-2">
              <a href="edit_admin.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
              <a href="?delete=<?= $row['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this admin?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
