<?php
require_once 'admin/db.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';

if (isset($_GET['staff_id']) && is_numeric($_GET['staff_id'])) {
    $staff_id = intval($_GET['staff_id']);

    // Temporarily disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // Fetch QR code path from database
    $sql = "SELECT qr_code FROM staff WHERE id = $staff_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $qr_code_file = $row['qr_code'];

        if (file_exists($qr_code_file)) {
            if (unlink($qr_code_file)) {
                // Delete the staff record from the database
                $conn->query("DELETE FROM staff WHERE id = $staff_id");
                $message = "Staff member and QR code deleted successfully!";
            } else {
                $message = "Failed to delete the QR code file.";
            }
        } else {
            $message = "QR code file does not exist.";
        }
    } else {
        $message = "Staff member not found.";
    }

    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
} else {
    $message = "Invalid or missing staff ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete QR Code</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Ensure this path is correct -->
</head>
<body>
    <header>
        <div class="container">
            <h1>Delete QR Code</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="register.php">Register Staff</a>
                <a href="download_qr.php">Download QR Codes</a>
                <a href="download_report.php">Download Report</a>
            </nav>
        </div>
    </header>
    
    <main>
        <div class="container">
            <h2>QR Code Deletion Result</h2>
            <p><?php echo $message; ?></p>
            <a href="download_qr.php">Go back to QR Codes List</a>
        </div>
    </main>
</body>
</html>
