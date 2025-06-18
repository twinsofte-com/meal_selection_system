<?php
require_once 'admin/db.php';

if (isset($_GET['staff_id'])) {
    $staff_id = intval($_GET['staff_id']);

    // Fetch current QR code path from database
    $sql = "SELECT qr_code FROM staff WHERE id = $staff_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $current_qr_code_file = $row['qr_code'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Update QR code logic here if needed
        echo "QR code updated!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit QR Code</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Ensure this path is correct -->
</head>
<body>
    <header>
        <div class="container">
            <h1>Edit QR Code</h1>
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
            <h2>Current QR Code</h2>
            <?php if (file_exists($current_qr_code_file)): ?>
                <img src="<?php echo $current_qr_code_file; ?>" alt="QR Code">
            <?php else: ?>
                <p>QR code not found.</p>
            <?php endif; ?>

            <form method="post" action="">
                <!-- Form fields for editing QR code if needed -->
                <button type="submit">Update QR Code</button>
            </form>
        </div>
    </main>
</body>
</html>
