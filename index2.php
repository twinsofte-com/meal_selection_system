<?php
include 'admin/db.php';

if (isset($_GET['staff_id'])) {
    $staff_id = $_GET['staff_id'];

    // Query to fetch the staff details
    $query = "SELECT name FROM staff WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $staff_id);
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
    <title>Attendance Scanner</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="qrscanner.js" defer></script>
</head>
<body>
<header>
    <div class="container">
        <h1><img src="img/logo.png" alt="Logo" style="height: 65px; vertical-align: middle;"> Attendance Scanner</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="confirm_meal.php">Confirm Meals</a>
        </nav>
    </div>
</header>

<main>
    <section id="scanner">
        <div class="container">
            <br>
            <br>
            <h2>Scan Your QR Code</h2>
            <!-- Add a button for camera switching -->
            <button id="toggleCameraButton">Switch Camera</button>
            <div id="reader"></div>
        </div>
    </section>
    
    <div id="popup" class="popup-overlay" style="display: none;">
    <div class="popup-content">
        <h3>Hello <span id="staffName"></span></h3>
        <form id="mealForm">
            <p>Select your meal preferences:</p>
            <label><input type="checkbox" name="preferences[]" value="Breakfast"> Breakfast</label><br>
            <label><input type="checkbox" name="preferences[]" value="Lunch"> Lunch</label><br>
            <label><input type="checkbox" name="preferences[]" value="Dinner"> Dinner</label><br>
            <input type="hidden" name="staff_id" id="staff_id">
            <button type="submit">Submit</button>
            <button type="button" id="closePopup">Close</button>
        </form>
    </div>
</div>
</main>
</body>
</html>
