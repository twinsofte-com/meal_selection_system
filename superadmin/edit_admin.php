<?php
include 'include/validation.php';

$id = intval($_GET['id']);
$msg = "";

// Fetch current admin details
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    die("Admin not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $role     = $_POST['role'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE admins SET name = ?, email = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $email, $role, $hashed, $id);
    } else {
        $stmt = $conn->prepare("UPDATE admins SET name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $role, $id);
    }

    $stmt->execute();
    $msg = "Admin updated successfully!";
    $admin['name'] = $name;
    $admin['email'] = $email;
    $admin['role'] = $role;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Admin - Super Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'include/topbar.php'; ?>

<div class="max-w-xl mx-auto p-6 bg-white mt-6 shadow rounded">
  <h2 class="text-2xl font-bold mb-4">Edit Admin: <?= htmlspecialchars($admin['username']) ?></h2>
  <?php if ($msg): ?>
    <div class="bg-green-100 text-green-800 p-2 rounded mb-4"><?= $msg ?></div>
  <?php endif; ?>
  <form method="POST">
    <label class="block mb-2 font-medium">Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" required class="w-full border p-2 rounded mb-4">

    <label class="block mb-2 font-medium">Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required class="w-full border p-2 rounded mb-4">

    <label class="block mb-2 font-medium">Role</label>
    <select name="role" required class="w-full border p-2 rounded mb-4">
      <option value="admin" <?= $admin['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
      <option value="guard_admin" <?= $admin['role'] === 'guard_admin' ? 'selected' : '' ?>>Guard Admin</option>
      <option value="super_admin" <?= $admin['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
    </select>

    <label class="block mb-2 font-medium">Change Password <small class="text-gray-500">(Leave blank to keep current)</small></label>
    <input type="password" name="password" class="w-full border p-2 rounded mb-4" placeholder="Enter new password">

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Admin</button>
    <a href="manage_admins.php" class="ml-4 text-blue-600 hover:underline">Back</a>
  </form>
</div>
</body>
</html>
