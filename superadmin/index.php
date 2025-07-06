<?php
session_start();
include '../admin/db.php'; // adjust if your DB file is elsewhere

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = $_POST["password"];

    $query = "SELECT * FROM admins WHERE username='$username' AND role='super_admin'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        if (password_verify($password, $admin['password'])) {
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['name'] = $admin['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Invalid super admin credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Super Admin Login</title>
    <style>
        body {
            font-family: Arial;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            padding-top: 100px;
        }
        .login-box {
            background: white;
            padding: 30px;
            box-shadow: 0px 0px 15px #aaa;
            width: 300px;
        }
        input {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
        }
        .btn {
            background: blue;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            width: 100%;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Super Admin Login</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password" required />
        <button class="btn" type="submit">Login</button>
    </form>
    <div class="error"><?php echo $error; ?></div>
</div>
</body>
</html>
