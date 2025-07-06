<?php
include 'include/validation.php'; // Includes session + db connection

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $role     = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if username/email exists
    $check = $conn->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $msg = "Username or Email already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO admins (username, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $name, $email, $password, $role);
        $stmt->execute();
        $msg = "Admin user created successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Admin - Super Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'include/topbar.php'; ?>

<div class="max-w-xl mx-auto p-6 bg-white mt-6 shadow rounded">
  <h2 class="text-2xl font-bold mb-4">Create New Admin</h2>
  <?php if ($msg): ?>
    <div class="bg-yellow-100 text-yellow-800 p-2 rounded mb-4"><?= $msg ?></div>
  <?php endif; ?>
  <form method="POST">
    <label class="block mb-2 font-medium">Username</label>
    <input type="text" name="username" required class="w-full border p-2 rounded mb-4">

    <label class="block mb-2 font-medium">Name</label>
    <input type="text" name="name" required class="w-full border p-2 rounded mb-4">

    <label class="block mb-2 font-medium">Email</label>
    <input type="email" name="email" required class="w-full border p-2 rounded mb-4">

    <label class="block mb-2 font-medium">Password</label>
    <input type="password" name="password" required class="w-full border p-2 rounded mb-4">

    <label class="block mb-2 font-medium">Role</label>
    <select name="role" required class="w-full border p-2 rounded mb-4">
      <option value="admin">Admin</option>
      <option value="guard_admin">Guard Admin</option>
      <option value="super_admin">Super Admin</option>
    </select>

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Create</button>
    <a href="manage_admins.php" class="ml-4 text-blue-600 hover:underline">Back</a>
  </form>
</div>
</body>
</html>
