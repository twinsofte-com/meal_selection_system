<?php
include '../admin/db.php';

if (isset($_GET['staff_id'])) {
    $staff_id = $_GET['staff_id'];

    // Query to fetch the staff details
    $query = "SELECT name FROM staff WHERE staff_id = ?"; // Use staff_id instead of id
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $staff_id);  // 's' for string, since staff_id is a string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        echo json_encode(['success' => true, 'name' => $staff['name']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Staff member not found']);
    }
    exit; // Stop further execution as this is an AJAX request
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Meal Receipt</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Ensure this path is correct -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="qrconfirm.js" defer></script>
</head>
<body>
<header>
    <div class="container">
        <h1><img src="img/logo.png" alt="Logo" style="height: 65px; vertical-align: middle;"> Confirm Meals</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="confirm_meal.php">Confirm Meals</a>
        </nav>
    </div>
</header>

    
    <main>
    <section id="scanner">
        <div class="container">
            <h2>Scan Your QR Code</h2>
            <!-- Add a button for camera switching -->
            <button id="toggleCameraButton">Switch Camera</button>
            <div id="reader"></div>
        </div>
    </section>
        
        <div id="popup" class="popup-overlay">
            <div class="popup-content">
            <h3>Hello <span id="staffName"></span></h3>
                <h3>Meal Confirmation</h3>
                <form id="confirmForm">
                    <input type="hidden" name="staff_id" id="staff_id">
                    <button type="submit">Confirm Receipt</button>
                    <button type="button" id="closePopup">Close</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
