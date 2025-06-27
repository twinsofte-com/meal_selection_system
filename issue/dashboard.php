<?php
include_once '../admin/include/date.php';
include_once 'validate_issue_session_index.php';

$todayDate = date("Y-m-d");
$dayOfWeek = date("l");

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
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

    .footer-fixed {
      position: fixed;
      bottom: 0;
      width: 100%;
      background-color: #f3f4f6;
      border-top: 1px solid #d1d5db;
      padding: 6px 0;
      text-align: center;
      font-size: 0.875rem;
      color: #374151;
      z-index: 50;
    }
  </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

  <!-- Header -->
  <header class="bg-white shadow py-3 px-4 flex justify-between items-center">
    <div class="text-lg font-bold text-[--primary-blue]">ECW</div>
    <a href="logout.php"
       class="bg-blue-500 text-white px-3 py-1 rounded-lg text-sm font-semibold hover:bg-blue-800">
      Logout
    </a>
  </header>

  <!-- Content Wrapper -->
  <main class="flex-grow px-4 pt-6 pb-24 max-w-5xl mx-auto w-full">
    <!-- Titles -->
    <div class="text-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">MEAL ISSUE</h1>
      <h2 class="text-lg text-gray-600 mt-1">ඞිකුත් කිරීම</h2>
      <p class="text-sm text-gray-500 mt-1"><?= $todayDate ?> (<?= $sinhalaDate ?>)</p>
    </div>

    <!-- Meal Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Breakfast -->
      <div onclick="location.href='breakfast/issue_breakfast.php'" class="bg-white border-l-8 border-yellow-400 shadow p-5 rounded-xl transition transform touch-card card-hover">
        <h3 class="text-2xl font-bold text-yellow-600">🍳 Breakfast</h3>
        <p class="text-gray-600 text-lg">උදේ</p>
      </div>

      <!-- Lunch -->
      <div onclick="location.href='lunch/issue_lunch.php'" class="bg-white border-l-8 border-green-500 shadow p-5 rounded-xl transition transform touch-card card-hover">
        <h3 class="text-2xl font-bold text-green-600">🍛 Lunch</h3>
        <p class="text-gray-600 text-lg">දවල්</p>
      </div>

      <!-- Dinner -->
      <div onclick="location.href='dinner/issue_dinner.php'" class="bg-white border-l-8 border-blue-500 shadow p-5 rounded-xl transition transform touch-card card-hover">
        <h3 class="text-2xl font-bold text-blue-600">🍽️ Dinner</h3>
        <p class="text-gray-600 text-lg">රෑ</p>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <?php include 'include/footer.php'; ?>

  <script>
    function updateClock() {
      const now = new Date();
      const time = now.toLocaleTimeString('en-GB');
      const date = now.toLocaleDateString('en-GB', {
        weekday: 'long', year: 'numeric', month: 'short', day: 'numeric'
      });
      document.getElementById('live-clock').textContent = `${date} - ${time}`;
    }
    setInterval(updateClock, 1000);
    updateClock();
  </script>

</body>
</html>
