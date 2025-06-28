<?php
if (!isset($_SESSION)) session_start();

// Detect base URL path dynamically
$baseURL = '/meal_selection_system/guard_admin/'; // ← adjust if your root is different
?>

<nav class="bg-white shadow mb-4">
  <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
    <div class="flex items-center space-x-3">
      <img src="<?= $baseURL ?>../img/logo.png" alt="Logo" class="h-8 w-auto">
      <span class="text-lg font-bold text-red-600">ECW MEAL ORDER APP – GUARD</span>
    </div>
    <div class="space-x-4">
      <a href="<?= $baseURL ?>dashboard.php" class="text-sm font-medium hover:underline">Dashboard</a>
      <a href="<?= $baseURL ?>visitor/visitor_order.php" class="text-sm font-medium hover:underline">Visitor Order</a>
      <a href="<?= $baseURL ?>report/download_report.php" class="text-sm font-medium hover:underline">Report</a>
      <a href="<?= $baseURL ?>logout.php" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">Logout</a>
    </div>
  </div>
</nav>
