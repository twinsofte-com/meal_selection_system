<?php
session_start();
require_once '../admin/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? AND role = 'guard_admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['role'] = $admin['role'];
        $_SESSION['name'] = $admin['name'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Guard Admin Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="w-full max-w-md bg-white p-8 rounded shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-center">🔐 Guard Room Admin Login</h2>

    <?php if ($error): ?>
      <p class="text-red-600 font-semibold mb-4 text-center"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
      <label class="block mb-2 font-medium">Username</label>
      <input type="text" name="username" required class="w-full px-4 py-2 border rounded mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

      <label class="block mb-2 font-medium">Password</label>
      <input type="password" name="password" required class="w-full px-4 py-2 border rounded mb-6 focus:outline-none focus:ring-2 focus:ring-blue-400">

      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Login</button>
    </form>
  </div>
</body>
</html>
