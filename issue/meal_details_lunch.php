<?php
date_default_timezone_set('Asia/Colombo');
require '../admin/db.php';
include_once '../admin/include/date.php';
session_start();

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$meal_date = $_GET['debug_date'] ?? date('Y-m-d');
$list = [];

switch ($type) {
  case 'issued':
    // All meals (ordered), show ✔ or ✘ based on lunch_received
    $sql = "SELECT staff.staff_id, staff.name, 
               IF(staff_meals.lunch_received = 1, 'yes', 'no') AS received,
               staff_meals.manual_lunch
        FROM staff_meals
        JOIN staff ON staff.id = staff_meals.staff_id
        WHERE DATE(staff_meals.meal_date) = '$meal_date'
          AND (
            staff_meals.lunch = 1 
            OR (staff_meals.manual_lunch = 1 AND staff_meals.lunch_received = 1)
          )";

    break;

  case 'manual':
    // Same as issued; UI highlights extras separately
    $sql = "SELECT staff.staff_id, staff.name,
                   'yes' AS received,
                   staff_meals.manual_lunch
            FROM staff_meals
            JOIN staff ON staff.id = staff_meals.staff_id
            WHERE staff_meals.lunch_received = 1
              AND DATE(staff_meals.meal_date) = '$meal_date'";
    break;

  case 'pending':
    // Ordered but not yet received
    $sql = "SELECT staff.staff_id, staff.name, 
                   'no' AS received,
                   staff_meals.manual_lunch
            FROM staff_meals
            JOIN staff ON staff.id = staff_meals.staff_id
            WHERE staff_meals.lunch = 1 
              AND staff_meals.lunch_received = 0 
              AND DATE(staff_meals.meal_date) = '$meal_date'";
    break;

  case 'extra':
    // ✅ Fixed: Removed `AND dinner = 1`
    $sql = "SELECT staff.staff_id, staff.name,
                   'yes' AS received,
                   staff_meals.manual_lunch
            FROM staff_meals
            JOIN staff ON staff.id = staff_meals.staff_id
            WHERE staff_meals.lunch_received = 1 
              AND staff_meals.manual_lunch = 1 
              AND DATE(staff_meals.meal_date) = '$meal_date'";
    break;


  default:
    echo json_encode(['error' => 'invalid type']);
    exit;
}

$res = $conn->query($sql);
if (!$res) {
  echo json_encode(['error' => $conn->error]);
  exit;
}

while ($row = $res->fetch_assoc()) {
  $list[] = $row;
}

echo json_encode($list);
