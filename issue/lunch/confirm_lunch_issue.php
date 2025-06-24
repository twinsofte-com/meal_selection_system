<?php
require_once '../../admin/db.php';
require_once '../../admin/functions.php';

$staff_id = $_GET['staff_id'] ?? '';
$manual = $_GET['manual'] ?? 0;
$already = $_GET['already'] ?? 0;

$stmt = $conn->prepare("SELECT name FROM staff WHERE staff_id = ?");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$name = $row['name'] ?? 'Unknown';

$bgColor = ($already == 1) ? '#FFFF99' : ($manual ? '#FFCCCC' : '#CCFFCC');
$message = ($already == 1) ? "Lunch Already Issued" : ($manual ? "Manual Lunch Issued" : "Lunch Issued");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lunch Issued</title>
    <style>
        body {
            background-color: <?= $bgColor ?>;
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 100px;
        }
        .box {
            font-size: 24px;
            padding: 20px;
            display: inline-block;
            border: 2px solid #000;
            background-color: white;
        }
    </style>
</head>
<body>
    <div class="box">
        <p><strong><?= htmlspecialchars($name) ?></strong></p>
        <p><?= $message ?></p>
    </div>
</body>
</html>
