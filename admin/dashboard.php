<?php
include_once 'validation/validation.php';
include_once '../phpqrcode/qrlib.php';
require_once 'db.php';
include_once 'include/date.php';

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Count from staff_meals
function getStaffMealCount($conn, $column, $date) {
    $sql = "SELECT COUNT(*) AS count FROM staff_meals WHERE DATE(meal_date) = ? AND $column = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return (int)$res['count'];
}
// Standard meal counts
$lunchToday         = getStaffMealCount($conn, 'lunch', $today);
$dinnerToday        = getStaffMealCount($conn, 'dinner', $today);
$breakfastTomorrow  = getStaffMealCount($conn, 'breakfast', $tomorrow);

$lunchYest          = getStaffMealCount($conn, 'lunch', $yesterday);
$dinnerYest         = getStaffMealCount($conn, 'dinner', $yesterday);
$breakfastYest      = getStaffMealCount($conn, 'breakfast', $yesterday);

// Extra (manual) meal counts for today only
$extraBreakfast = getStaffMealCount($conn, 'manual_breakfast', $today);
$extraLunch     = getStaffMealCount($conn, 'manual_lunch', $today);
$extraDinner    = getStaffMealCount($conn, 'manual_dinner', $today);

// Staff registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_type = $_POST['staff_type'];
    $staff_id = $_POST['staff_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO staff (staff_id, staff_type, name, phone_number, qr_code, meal_preferences) VALUES (?, ?, ?, ?, '', '')");
    $stmt->bind_param("ssss", $staff_id, $staff_type, $name, $phone);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Staff registered successfully!'); window.location='dashboard.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - MEAL ORDER APP</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="text-gray-800 font-sans">
<?php include 'include/topbar.php'; ?>

<!-- Action Buttons -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
  <button onclick="openRegisterModal()" class="bg-[#ED1B24] text-white p-6 rounded-2xl text-xl shadow hover:shadow-lg">Register</button>
  <a href="register.php" class="bg-[#2E3095] text-white p-6 rounded-2xl text-xl shadow hover:shadow-lg text-center">List</a>
  <a href="manual_order.php" class="bg-[#2E3095] text-white p-6 rounded-2xl text-xl shadow hover:shadow-lg text-center">Manual Order</a>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 px-6 pb-6">
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

<!-- Export Buttons -->
<!-- <div class="px-6 pb-12 flex gap-4">
  <a href="exports/weekly_meal_comparison.csv" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Download CSV</a>
  <a href="exports/weekly_meal_comparison.pdf" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Download PDF</a>
</div> -->

<!-- Footer -->
<?php include 'include/footer.php'; ?>
<!-- Chart Script -->
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
          backgroundColor: '#34D399', // teal
          borderRadius: 6,
          barPercentage: 0.5,
          categoryPercentage: 0.5
        },
        {
          label: 'Lunch',
          data: [<?= $lunchYest ?>, <?= $lunchToday ?>],
          backgroundColor: '#EF4444', // red
          borderRadius: 6,
          barPercentage: 0.5,
          categoryPercentage: 0.5
        },
        {
          label: 'Dinner',
          data: [<?= $dinnerYest ?>, <?= $dinnerToday ?>],
          backgroundColor: '#6366F1', // indigo
          borderRadius: 6,
          barPercentage: 0.5,
          categoryPercentage: 0.5
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            font: {
              size: 14,
              weight: 'bold'
            }
          }
        },
        title: {
          display: true,
          text: 'Meal Orders Comparison (Breakfast, Lunch, Dinner)',
          font: {
            size: 18
          }
        },
        tooltip: {
          backgroundColor: '#111827',
          titleFont: { weight: 'bold' },
          bodyFont: { size: 14 }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
            font: { size: 14 }
          },
          title: {
            display: true,
            text: 'Number of Orders',
            font: { size: 16 }
          }
        },
        x: {
          ticks: {
            font: { size: 14 }
          }
        }
      },
      animation: {
        duration: 800,
        easing: 'easeOutBounce'
      }
    }
  });
</script>


</body>
</html>
