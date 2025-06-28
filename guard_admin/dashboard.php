<?php
include 'include/validation.php';
include '../admin/db.php';
include 'include/topbar.php';
include_once '../admin/include/date.php';


$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Total visitor orders today
$visitorSql = $conn->prepare("SELECT 
    SUM(breakfast) AS v_breakfast,
    SUM(lunch) AS v_lunch,
    SUM(dinner) AS v_dinner
    FROM visitor_orders WHERE meal_date = ?");
$visitorSql->bind_param("s", $today);
$visitorSql->execute();
$visitorData = $visitorSql->get_result()->fetch_assoc();

// Total extra manual orders from staff
$staffSql = $conn->prepare("SELECT 
    SUM(manual_breakfast) AS s_extra_bf,
    SUM(manual_lunch) AS s_extra_lunch,
    SUM(manual_dinner) AS s_extra_dinner
    FROM staff_meals WHERE meal_date = ?");
$staffSql->bind_param("s", $today);
$staffSql->execute();
$staffData = $staffSql->get_result()->fetch_assoc();

// Total normal staff orders
$staffOrdersSql = $conn->prepare("SELECT 
    SUM(breakfast) AS bf,
    SUM(lunch) AS lunch,
    SUM(dinner) AS dinner
    FROM staff_meals WHERE meal_date = ?");
$staffOrdersSql->bind_param("s", $today);
$staffOrdersSql->execute();
$staffOrders = $staffOrdersSql->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Guard Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-7xl mx-auto px-4">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded shadow text-center">
      <p class="font-bold text-lg text-red-600">Today Lunch</p>
      <p class="text-xl font-semibold"><?= $staffOrders['lunch'] + $visitorData['v_lunch'] ?> meals ordered</p>
    </div>
    <div class="bg-white p-4 rounded shadow text-center">
      <p class="font-bold text-lg text-blue-600">Today Dinner</p>
      <p class="text-xl font-semibold"><?= $staffOrders['dinner'] + $visitorData['v_dinner'] ?> meals ordered</p>
    </div>
    <div class="bg-white p-4 rounded shadow text-center">
      <p class="font-bold text-lg text-green-600">Tomorrow Breakfast</p>
      <?php
      $tmrwSql = $conn->prepare("SELECT SUM(breakfast) FROM staff_meals WHERE meal_date = ?");
      $tmrwSql->bind_param("s", $tomorrow);
      $tmrwSql->execute();
      $tmrwTotal = $tmrwSql->get_result()->fetch_row()[0];
      ?>
      <p class="text-xl font-semibold"><?= $tmrwTotal ?> meals ordered</p>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-yellow-100 p-4 rounded shadow text-center">
      <p class="font-bold">Extra Breakfast Orders</p>
      <p><?= $staffData['s_extra_bf'] ?> meals</p>
    </div>
    <div class="bg-yellow-100 p-4 rounded shadow text-center">
      <p class="font-bold">Extra Lunch Orders</p>
      <p><?= $staffData['s_extra_lunch'] ?> meals</p>
    </div>
    <div class="bg-yellow-100 p-4 rounded shadow text-center">
      <p class="font-bold">Extra Dinner Orders</p>
      <p><?= $staffData['s_extra_dinner'] ?> meals</p>
    </div>
  </div>

  <div class="bg-white rounded shadow p-6 mb-10">
    <h3 class="text-lg font-bold mb-4 text-center">Meal Orders Comparison (Today)</h3>
    <canvas id="mealChart"></canvas>
  </div>
</div>

<script>
  const ctx = document.getElementById('mealChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Breakfast', 'Lunch', 'Dinner'],
      datasets: [{
        label: 'Total Orders',
        data: [
          <?= $staffOrders['breakfast'] + $visitorData['v_breakfast'] ?>,
          <?= $staffOrders['lunch'] + $visitorData['v_lunch'] ?>,
          <?= $staffOrders['dinner'] + $visitorData['v_dinner'] ?>
        ],
        backgroundColor: ['#34d399', '#f87171', '#6366f1']
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, precision: 0 }
      }
    }
  });
</script>

<?php include 'include/footer.php'; ?>
</body>
</html>
