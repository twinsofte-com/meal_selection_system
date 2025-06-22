<?php
include_once '../../admin/include/date.php';
require '../../admin/db.php';
session_start();

if (!isset($_SESSION['order_user'])) {
  header('Location: ../order_login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['staff_id'])) {
  $staff_id = trim($_POST['staff_id']);
  $meal_date = date('Y-m-d');

  $stmt = $conn->prepare("SELECT id, name FROM staff WHERE staff_id = ?");
  $stmt->bind_param("s", $staff_id);
  $stmt->execute();
  $staff = $stmt->get_result()->fetch_assoc();

  $is_registered = !!$staff;
  $staff_table_id = $is_registered ? $staff['id'] : null;
  if (!$is_registered) {
    $ins = $conn->prepare("INSERT INTO staff (staff_id, name) VALUES (?, 'Unregistered Staff')");
    $ins->bind_param("s", $staff_id);
    $ins->execute();
    $staff_table_id = $conn->insert_id;
  }

  $insMeal = $conn->prepare("INSERT INTO staff_meals (staff_id, meal_date, lunch, lunch_received, manual_order) VALUES (?, ?, 1, 1, 0) ON DUPLICATE KEY UPDATE lunch_received=1, manual_order=0");
  $insMeal->bind_param("is", $staff_table_id, $meal_date);
  $insMeal->execute();

  $issuer = $_SESSION['order_user'];
  $log = $conn->prepare("INSERT INTO meal_issuance_log (staff_id, meal_type, issued_by, method) VALUES (?, 'lunch', ?, 'manual')");
  $log->bind_param("ii", $staff_table_id, $issuer);
  $log->execute();

  echo $is_registered
    ? "<div class='alert alert-success'>Lunch issued successfully.</div>"
    : "<div class='alert alert-danger'>Extra lunch issued to unregistered staff. ID: {$staff_id}</div>";
  exit;
}
echo "Invalid Request.";
