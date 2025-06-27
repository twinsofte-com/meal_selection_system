<?php
require_once '../db.php';
session_start();

// Fetch admin record
$query = $conn->query("SELECT * FROM admins LIMIT 1");
$admins = $query->fetch_assoc();

// Password change logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $error = "New passwords do not match!";
    } elseif (!password_verify($current, $admins['password'])) {
        $error = "Current password is incorrect!";
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $admins['id']);
        if ($stmt->execute()) {
            echo "<script>alert('Password updated successfully!'); window.location='../dashboard.php';</script>";
        } else {
            $error = "Failed to update password!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Change Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold text-[#ED1B24] mb-4">Change Password</h2>
    <?php if (!empty($error)): ?>
      <p class="text-red-500 mb-4"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST">
      <label class="block mb-2 text-gray-700">Current Password</label>
      <input type="password" name="current_password" required class="w-full p-2 border rounded mb-4" />

      <label class="block mb-2 text-gray-700">New Password</label>
      <input type="password" name="new_password" required class="w-full p-2 border rounded mb-4" />

      <label class="block mb-2 text-gray-700">Confirm New Password</label>
      <input type="password" name="confirm_password" required class="w-full p-2 border rounded mb-4" />

      <button type="submit" class="bg-[#ED1B24] text-white px-4 py-2 rounded">Change Password</button>
      <a href="../dashboard.php" class="ml-4 text-gray-600">Cancel</a>
    </form>
  </div>
</body>
</html>
