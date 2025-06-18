<?php
session_start();
if (!isset($_SESSION['issue_user'])) {
    header("Location: issue_login.php");
    exit;
}

date_default_timezone_set('Asia/Colombo');

$todayDate = date("Y-m-d");
$dayOfWeek = date("l");
$currentTime = date("h:i A");

// Sinhala day names
$sinhalaDays = [
    "Monday" => "සදුදා",
    "Tuesday" => "අඟහරුවාදා",
    "Wednesday" => "බදාදා",
    "Thursday" => "බ්‍රහස්පතින්දා",
    "Friday" => "සිකුරාදා",
    "Saturday" => "සෙනසුරාදා",
    "Sunday" => "ඉරිදා"
];

$sinhalaDate = $sinhalaDays[$dayOfWeek];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Meal Issue Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-blue: #2E3095;
      --purple: #6B21A8;
    }

    .card-hover:hover {
      transform: scale(1.02);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .touch-card {
      cursor: pointer;
    }

    .footer-text {
      font-size: 0.95rem;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col justify-between">

  <!-- Header -->
  <header class="bg-white shadow py-4 px-6 flex justify-between items-center">
    <div class="text-xl font-bold text-[--primary-blue]">ECW</div>
    <a href="logout.php"
       class="bg-[--purple] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-purple-800">
      Logout
    </a>
  </header>

  <!-- Main Content -->
  <main class="px-6 py-8 max-w-5xl mx-auto flex-grow">
    <!-- Titles -->
    <div class="text-center mb-10">
      <h1 class="text-3xl font-bold text-gray-800">MEAL ISSUE</h1>
      <h2 class="text-xl text-gray-600 mt-1">ඞිකුත් කිරීම</h2>
    </div>

    <!-- Meal Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Breakfast -->
      <div onclick="location.href='breakfast/issue_breakfast.php'" class="bg-white border-l-8 border-yellow-400 shadow p-6 rounded-xl transition transform touch-card card-hover">
        <div class="text-sm text-gray-500"><?= $todayDate ?> (<?= $dayOfWeek ?>)</div>
        <h3 class="text-2xl font-bold text-yellow-600 mt-2">Breakfast</h3>
        <p class="text-gray-600 text-lg">උදේ</p>
      </div>

      <!-- Lunch -->
      <div onclick="location.href='issue_lunch.php'" class="bg-white border-l-8 border-green-500 shadow p-6 rounded-xl transition transform touch-card card-hover">
        <div class="text-sm text-gray-500"><?= $todayDate ?> (<?= $dayOfWeek ?>)</div>
        <h3 class="text-2xl font-bold text-green-600 mt-2">Lunch</h3>
        <p class="text-gray-600 text-lg">දවල්</p>
      </div>

      <!-- Dinner -->
      <div onclick="location.href='issue_dinner.php'" class="bg-white border-l-8 border-blue-500 shadow p-6 rounded-xl transition transform touch-card card-hover">
        <div class="text-sm text-gray-500"><?= $todayDate ?> (<?= $dayOfWeek ?>)</div>
        <h3 class="text-2xl font-bold text-blue-600 mt-2">Dinner</h3>
        <p class="text-gray-600 text-lg">රෑ</p>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-white text-center py-4 shadow-inner">
    <div class="footer-text text-gray-700">
      <p>Date: <?= $todayDate ?> | දිනය: <?= $sinhalaDate ?></p>
      <p>Time: <?= $currentTime ?></p>
    </div>
    <?php include('include/footer.php'); ?>
  </footer>

</body>
</html>
