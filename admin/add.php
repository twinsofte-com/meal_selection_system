<?php
require_once 'db.php';

$username = 'admin';
$password = password_hash('password123', PASSWORD_BCRYPT);

$query = "INSERT INTO admins (username, password) VALUES ('$username', '$password')";
mysqli_query($conn, $query);
?>
