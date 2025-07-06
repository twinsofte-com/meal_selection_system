<?php
include 'include/validation.php'; // checks for super_admin role
include '../phpqrcode/qrlib.php';
include '../admin/db.php';
include 'include/date.php';

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Count meals from staff_meals table
function getStaffMealCount($conn, $column, $date) {
    $sql = "SELECT COUNT(*) AS count FROM staff_meals WHERE DATE(meal_date) = ? AND $column = 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return (int)$res['count'];
}

// Meal counts
$lunchToday         = getStaffMealCount($conn, 'lunch', $today);
$dinnerToday        = getStaffMealCount($conn, 'dinner', $today);
$breakfastTomorrow  = getStaffMealCount($conn, 'breakfast', $tomorrow);

$lunchYest          = getStaffMealCount($conn, 'lunch', $yesterday);
$dinnerYest         = getStaffMealCount($conn, 'dinner', $yesterday);
$breakfastYest      = getStaffMealCount($conn, 'breakfast', $yesterday);

// Manual (extra) orders
$extraBreakfast = getStaffMealCount($conn, 'manual_breakfast', $today);
$extraLunch     = getStaffMealCount($conn, 'manual_lunch', $today);
$extraDinner    = getStaffMealCount($conn, 'manual_dinner', $today);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Super Admin Dashboard - MEAL ORDER APP</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="text-gray-800 font-sans">
<?php include 'include/topbar.php'; ?>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
  <div class="border-l-4 border-[#ED1B24] p-4 bg-white shadow rounded-xl">
    <h2 class="text-lg font-semibold text-[#ED1B24]">Today Lunch</h2>
    <p class="text-2xl font-bold"><?= $lunchToday ?> meals ordered</p>
  </div>
  <div class="border-l-4 border-[#2E3095] p-4 bg-white shadow rounded-xl">
    <h2 class="text-lg font-semibold text-[#2E3095]">Today Dinner</h2>
    <p class="text-2xl font-bold"><?= $dinnerToday ?> meals ordered</p>
  </div>
  <div class="border-l-4 border-green-600 p-4 bg-white shadow rounded-xl">
    <h2 class="text-lg font-semibold text-green-700">Tomorrow Breakfast</h2>
    <p class="text-2xl font-bold"><?= $breakfastTomorrow ?> meals ordered</p>
  </div>
</div>

<!-- Extra Meal Orders -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 px-6 pb-6">
  <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 shadow rounded-xl">
    <h2 class="text-lg font-semibold text-yellow-700">Extra Breakfast Orders</h2>
    <p class="text-2xl font-bold"><?= $extraBreakfast ?> meals</p>
  </div>
  <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 shadow rounded-xl">
    <h2 class="text-lg font-semibold text-yellow-700">Extra Lunch Orders</h2>
    <p class="text-2xl font-bold"><?= $extraLunch ?> meals</p>
  </div>
  <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 shadow rounded-xl">
    <h2 class="text-lg font-semibold text-yellow-700">Extra Dinner Orders</h2>
    <p class="text-2xl font-bold"><?= $extraDinner ?> meals</p>
  </div>
</div>

<!-- Chart -->
<div class="px-6 pb-12">
  <canvas id="mealChart" height="100"></canvas>
</div>

<!-- Footer -->
<?php include '../admin/include/footer.php'; ?>

<script>
  const ctx = document.getElementById('mealChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Yesterday', 'Today'],
      datasets: [
        {
          label: 'Breakfast',
          data: [<?= $breakfastYest ?>, <?= $breakfastTomorrow ?>],
          backgroundColor: '#34D399',
          borderRadius: 6
        },
        {
          label: 'Lunch',
          data: [<?= $lunchYest ?>, <?= $lunchToday ?>],
          backgroundColor: '#EF4444',
          borderRadius: 6
        },
        {
          label: 'Dinner',
          data: [<?= $dinnerYest ?>, <?= $dinnerToday ?>],
          backgroundColor: '#6366F1',
          borderRadius: 6
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' },
        title: {
          display: true,
          text: 'Meal Orders Comparison',
          font: { size: 18 }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: { display: true, text: 'Meal Count' }
        }
      }
    }
  });
</script>
</body>
</html>
