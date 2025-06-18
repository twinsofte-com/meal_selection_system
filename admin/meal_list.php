<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'db.php';

// Check for submitted date or use today
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$today = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');


// Fetch meal data for selected date
$stmt = $conn->prepare("
    SELECT 
        s.staff_id,
        s.name,
        sm.breakfast,
        sm.lunch,
        sm.dinner,
        sm.vegetarian,
        sm.egg,
        sm.chicken,
        sm.meal_date
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
  <title>Meal Orders</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
<?php include 'include/topbar.php'; ?>

<div class="max-w-7xl mx-auto p-6">
  <h1 class="text-3xl font-bold text-center mb-6">🍽️ Meal Orders (<?= htmlspecialchars($selected_date) ?>)</h1>

  <div class="bg-white rounded-lg shadow p-4 mb-10">
    <!-- Date Picker -->
    <form method="get" class="flex items-center gap-4 mb-4">
      <label for="date" class="font-medium">Select Date:</label>
      <input type="date" id="date" name="date" value="<?= htmlspecialchars($selected_date) ?>" class="border p-2 rounded" required>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">🔍 View</button>
      <a href="meals_list_download.php?date=<?= urlencode($selected_date) ?>" class="ml-auto bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">⬇ Download Report</a>
    </form>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left border border-gray-300">
        <thead class="bg-gray-200 text-gray-700">
          <tr>
            <th class="px-4 py-2 border">Staff ID</th>
            <th class="px-4 py-2 border">Name</th>
            <th class="px-4 py-2 border">Breakfast</th>
            <th class="px-4 py-2 border">Lunch</th>
            <th class="px-4 py-2 border">Dinner</th>
            <th class="px-4 py-2 border">Vegetarian</th>
            <th class="px-4 py-2 border">Egg</th>
            <th class="px-4 py-2 border">Chicken</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="border-t">
              <td class="px-4 py-2 border"><?= htmlspecialchars($row['staff_id']) ?></td>
              <td class="px-4 py-2 border"><?= htmlspecialchars($row['name']) ?></td>
              <td class="px-4 py-2 border text-center"><?= $row['breakfast'] ? '✅' : '❌' ?></td>
              <td class="px-4 py-2 border text-center"><?= $row['lunch'] ? '✅' : '❌' ?></td>
              <td class="px-4 py-2 border text-center"><?= $row['dinner'] ? '✅' : '❌' ?></td>
              <td class="px-4 py-2 border text-center"><?= $row['vegetarian'] ? '✅' : '❌' ?></td>
              <td class="px-4 py-2 border text-center"><?= $row['egg'] ? '✅' : '❌' ?></td>
              <td class="px-4 py-2 border text-center"><?= $row['chicken'] ? '✅' : '❌' ?></td>
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
