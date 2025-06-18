<?php
session_start(); // Start the session

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not logged in, redirect to login page
    header('Location: index.php');
    exit;
}
require_once 'db.php';

// Handle search query
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch staff data for search
$sql = "SELECT id, name FROM staff WHERE name LIKE '%$search%' ORDER BY name";
$result = $conn->query($sql);

// Handle QR code download request
if (isset($_GET['staff_id'])) {
    $staff_id = intval($_GET['staff_id']);

    // Fetch QR code path from database
    $sql = "SELECT qr_code FROM staff WHERE id = $staff_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $qr_code_file = $row['qr_code'];

        if (file_exists($qr_code_file)) {
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="' . basename($qr_code_file) . '"');
            readfile($qr_code_file);
            exit;
        } else {
            echo "QR code file does not exist.";
        }
    } else {
        echo "Staff member not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download QR Codes</title>
    <link rel="stylesheet" href="css/admin_style.css"> <!-- Link to the CSS file -->
    <style>
        /* Inline CSS for demonstration; ideally, move this to admin_style.css */

        body {
            font-family: 'Roboto', sans-serif; /* Updated font */
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #007bff;
            color: white;
            padding: 15px 0;
            text-align: center;
        }

        header h1 {
            margin: 0;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: inline-block;
        }

        nav a:hover {
            background-color: #0056b3;
            border-radius: 5px;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .search-box {
            margin-bottom: 20px;
            text-align: center;
        }

        .search-box form {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .search-box input[type="text"] {
            width: 250px;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }

        .search-box button {
            padding: 10px 20px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-box button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .footer {
            text-align: center;
            padding: 10px;
            background-color: #007bff;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
<header>
    <div class="container">
        <div class="logo">
            <img src="../img/logo.png" alt="Logo">
        </div>
        <h1>Register Staff</h1>
        <nav class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="register.php">Register Staff</a></li>
                <li><a href="download_qr.php" class="active">Download QR Codes</a></li>
                <li><a href="download_report.php">Download Report</a></li>
            </ul>
        </nav>
    </div>
</header>
    
<main>
    <div class="container">
        <div class="search-box">
            <form method="get" action="">
                <input type="text" name="search" placeholder="Search by name" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><a href="download_qr.php?staff_id=<?php echo $row['id']; ?>">Download QR Code</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">No results found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<footer class="footer">
    Powered by Twinsofte.com. All rights reserved.
</footer>
</body>
</html>
