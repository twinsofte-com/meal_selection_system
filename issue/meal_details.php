<?php
include_once '../admin/include/date.php';
require '../admin/db.php';
session_start();
if (!isset($_SESSION['order_user'])) exit;

$type = $_GET['type'] ?? '';
$meal_date = date('Y-m-d', strtotime('-1 day')); // yesterday
$list = [];

switch($type){
  case 'issued':
    $sql = "SELECT staff.staff_id, staff.name,
                   IF(staff_meals.breakfast_received = 1, 'yes', 'no') AS received
            FROM staff_meals
            JOIN staff ON staff.id = staff_meals.staff_id
            WHERE staff_meals.breakfast_received = 1 AND staff_meals.meal_date = '$meal_date'";
    break;

  case 'manual':
    $sql = "SELECT staff.staff_id, staff.name,
                   IF(staff_meals.breakfast_received = 1, 'yes', 'no') AS received
            FROM staff_meals
            JOIN staff ON staff.id = staff_meals.staff_id
            WHERE staff_meals.manual_order = 1 AND staff_meals.breakfast_received = 1 AND staff_meals.meal_date = '$meal_date'";
    break;

  case 'pending':
    $sql = "SELECT staff.staff_id, staff.name, staff_meals.manual_order
            FROM staff_meals
            JOIN staff ON staff.id = staff_meals.staff_id
            WHERE staff_meals.breakfast = 1 AND staff_meals.breakfast_received = 0 AND staff_meals.meal_date = '$meal_date'";
    break;

  case 'extra':
    $sql = "SELECT staff.staff_id, staff.name,
                   IF(staff_meals.breakfast_received = 1, 'yes', 'no') AS received
            FROM staff_meals
            JOIN staff ON staff.id = staff_meals.staff_id
            WHERE staff_meals.meal_date = '$meal_date' AND staff_meals.breakfast = 1 AND staff_meals.manual_order = 1";
    break;

  default:
    exit;
}

$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) {
  $list[] = $r;
}

header('Content-Type: application/json');
echo json_encode($list);
?>
