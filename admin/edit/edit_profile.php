<?php
require_once '../db.php';
session_start();

// Initialize empty variables
$name = $email = '';
$admin_id = null;

// Fetch admin record (assuming only one admin)
$query = $conn->query("SELECT * FROM admins LIMIT 1");
if ($query && $query->num_rows > 0) {
    $admins = $query->fetch_assoc();
    $name = $admins['name'];
    $email = $admins['email'];
    $admin_id = $admins['id'];
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $admin_id !== null) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE admins SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $email, $admin_id);

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location='../dashboard.php';</script>";
    } else {
        echo "<script>alert('Failed to update profile!');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold text-[#2E3095] mb-4">Edit Admin Profile</h2>
    <form method="POST">
      <label class="block mb-2 text-gray-700">Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required class="w-full p-2 border rounded mb-4" />

      <label class="block mb-2 text-gray-700">Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required class="w-full p-2 border rounded mb-4" />

      <button type="submit" class="bg-[#2E3095] text-white px-4 py-2 rounded">Update Profile</button>
      <a href="../dashboard.php" class="ml-4 text-gray-600">Cancel</a>
    </form>
  </div>
</body>
</html>
