<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'db.php';

// Get selected date or default to today
$selected_date = $_GET['selected_date'] ?? date('Y-m-d');

// Fetch meal data
$stmt = $conn->prepare("
    SELECT 
        s.staff_id,
        s.name,
        sm.breakfast,
        sm.lunch,
        sm.dinner,
        (SELECT confirmed FROM meal_issuance WHERE staff_id = sm.staff_id AND meal_type = 'breakfast' AND meal_date = sm.meal_date) AS breakfast_received,
        (SELECT confirmed FROM meal_issuance WHERE staff_id = sm.staff_id AND meal_type = 'lunch' AND meal_date = sm.meal_date) AS lunch_received,
        (SELECT confirmed FROM meal_issuance WHERE staff_id = sm.staff_id AND meal_type = 'dinner' AND meal_date = sm.meal_date) AS dinner_received
    FROM staff_meals sm
    JOIN staff s ON sm.staff_id = s.id
    WHERE sm.meal_date = ?
    ORDER BY s.name
");

$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Meal Report</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
<?php include 'include/topbar.php'; ?>

<div class="max-w-7xl mx-auto p-6">
  <h1 class="text-3xl font-bold text-center mb-6">📅 Daily Meal Report</h1>

  <!-- Date selection -->
  <form method="GET" class="flex justify-center items-center gap-4 mb-6">
    <label for="selected_date" class="font-semibold">Select Date:</label>
    <input type="date" id="selected_date" name="selected_date" value="<?= htmlspecialchars($selected_date) ?>" class="p-2 border rounded" onchange="this.form.submit()" />
  </form>

  <div class="bg-white rounded-lg shadow p-4 mb-10">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold">Date: <?= htmlspecialchars($selected_date) ?></h2>
      <a href="generate_report.php?date=<?= urlencode($selected_date) ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        ⬇ Download PDF
      </a>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left border border-gray-300">
        <thead class="bg-gray-200 text-gray-700">
          <tr>
            <th class="px-4 py-2 border">Staff ID</th>
            <th class="px-4 py-2 border">Name</th>
            <th class="px-4 py-2 border">Breakfast</th>
            <th class="px-4 py-2 border">Breakfast Received</th>
            <th class="px-4 py-2 border">Lunch</th>
            <th class="px-4 py-2 border">Lunch Received</th>
            <th class="px-4 py-2 border">Dinner</th>
            <th class="px-4 py-2 border">Dinner Received</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="border-t">
              <td class="px-4 py-2 border"><?= htmlspecialchars($row['staff_id']) ?></td>
              <td class="px-4 py-2 border"><?= htmlspecialchars($row['name']) ?></td>
              <td class="px-4 py-2 border text-center"><?= $row['breakfast'] ? '✅' : '❌' ?></td>
              <td class="px-4 py-2 border text-center"><?= $row['breakfast_received'] ? '🎯' : '🕓' ?></td>
              <td class="px-4 py-2 border text-center"><?= $row['lunch'] ? '✅' : '❌' ?></td>
              <td class="px-4 py-2 border text-center"><?= $row['lunch_received'] ? '🎯' : '🕓' ?></td>
              <td class="px-4 py-2 border text-center"><?= $row['dinner'] ? '✅' : '❌' ?></td>
              <td class="px-4 py-2 border text-center"><?= $row['dinner_received'] ? '🎯' : '🕓' ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center p-4">No meal data found for this date.</td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<footer class="bg-blue-600 text-white text-center py-3 mt-8">
  Powered by Twinsofte.com. All rights reserved.
</footer>

</body>
</html>
