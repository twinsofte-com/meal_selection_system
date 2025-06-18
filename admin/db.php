<?php
$servername = "localhost";
$username = "root"; // Change if necessary
$password = "";     // Change if necessary
$dbname = "attendance_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
