<?php
date_default_timezone_set('Asia/Colombo');
require '../../admin/db.php';
include_once '../../admin/include/date.php';

$meal_date = date('Y-m-d');
$q = fn($s)=>(int)$conn->query($s)->fetch_assoc()['cnt'];
$summary = [
  'issued'  => $q("SELECT COUNT(*) cnt FROM staff_meals WHERE lunch_received=1 AND meal_date='$meal_date'"),
  'manual'  => $q("SELECT COUNT(*) cnt FROM staff_meals WHERE lunch_received=1 AND manual_order=1 AND meal_date='$meal_date'"),
  'pending' => $q("SELECT COUNT(*) cnt FROM staff_meals WHERE lunch=1 AND lunch_received=0 AND meal_date='$meal_date'"),
  'extra'   => $q("SELECT COUNT(*) cnt FROM staff_meals WHERE meal_date=CURDATE() AND lunch=1 AND manual_order=1")
];
?>
<!DOCTYPE html><html lang="en"><head>…same as breakfast… name it *Issue Lunch* … update script src to `confirm_lunch_issue.php` and `qrcode.js` logic same.</head><body>…UI same layout… summary titles changed to Lunch…calls `confirm_lunch_issue.php` on confirm.  
</body></html>
