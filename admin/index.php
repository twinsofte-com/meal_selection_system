<?php
session_start();
require_once 'db.php';
include_once 'include/date.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM admins WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-red: #ED1B24;
            --primary-blue: #2E3095;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-white">
    <div class="w-full max-w-md border-t-4 border-[var(--primary-blue)] shadow-lg rounded-xl p-8">
        <h2 class="text-2xl font-bold text-center text-[var(--primary-red)] mb-6">Admin Login</h2>

        <form method="POST" class="space-y-4">
            <div>
                <input type="text" name="username" placeholder="Username" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--primary-blue)]">
            </div>
            <div>
                <input type="password" name="password" placeholder="Password" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--primary-blue)]">
            </div>
            <div>
                <button type="submit"
                        class="w-full bg-[var(--primary-red)] text-white font-semibold py-3 rounded-md hover:bg-red-700 transition">
                    Login
                </button>
            </div>
        </form>

        <?php if (isset($error)): ?>
            <div class="mt-4 text-center text-red-600 font-medium">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
