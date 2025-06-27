<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'db.php';

$selected_date = $_GET['selected_date'] ?? date('Y-m-d');
$filter = $_GET['meal'] ?? 'all';

$stmt = $conn->prepare("
    SELECT 
        s.staff_id,
        s.name,
        sm.breakfast,
        sm.lunch,
        sm.dinner,
        sm.manual_breakfast,
        sm.manual_lunch,
        sm.manual_dinner,
        sm.breakfast_received,
        sm.lunch_received,
        sm.dinner_received
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

  <form method="GET" class="flex justify-center items-center gap-4 mb-6">
    <label for="selected_date" class="font-semibold">Select Date:</label>
    <input type="date" id="selected_date" name="selected_date" value="<?= htmlspecialchars($selected_date) ?>" class="p-2 border rounded" />
    <input type="hidden" name="meal" value="<?= htmlspecialchars($filter) ?>" />
    <button type="submit" class="bg-gray-700 text-white px-3 py-2 rounded">🔍 Search</button>
  </form>

  <div class="flex justify-center gap-3 mb-6">
    <a href="?selected_date=<?= urlencode($selected_date) ?>&meal=all" class="px-4 py-2 rounded <?= $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">All</a>
    <a href="?selected_date=<?= urlencode($selected_date) ?>&meal=breakfast" class="px-4 py-2 rounded <?= $filter === 'breakfast' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">Breakfast</a>
    <a href="?selected_date=<?= urlencode($selected_date) ?>&meal=lunch" class="px-4 py-2 rounded <?= $filter === 'lunch' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">Lunch</a>
    <a href="?selected_date=<?= urlencode($selected_date) ?>&meal=dinner" class="px-4 py-2 rounded <?= $filter === 'dinner' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">Dinner</a>
  </div>

  <div class="bg-white rounded-lg shadow p-4 mb-10">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold">Date: <?= htmlspecialchars($selected_date) ?> (<?= ucfirst($filter) ?>)</h2>
      <a href="generate_report.php?date=<?= urlencode($selected_date) ?>&meal=<?= urlencode($filter) ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">⬇ Download PDF</a>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left border border-gray-300">
        <thead class="bg-gray-200 text-gray-700">
          <tr>
            <th class="px-4 py-2 border">Staff ID</th>
            <th class="px-4 py-2 border">Name</th>
            <?php if ($filter === 'all' || $filter === 'breakfast'): ?>
              <th class="px-4 py-2 border">Breakfast</th>
              <th class="px-4 py-2 border">Breakfast Received</th>
            <?php endif; ?>
            <?php if ($filter === 'all' || $filter === 'lunch'): ?>
              <th class="px-4 py-2 border">Lunch</th>
              <th class="px-4 py-2 border">Lunch Received</th>
            <?php endif; ?>
            <?php if ($filter === 'all' || $filter === 'dinner'): ?>
              <th class="px-4 py-2 border">Dinner</th>
              <th class="px-4 py-2 border">Dinner Received</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr class="border-t">
            <td class="px-4 py-2 border"><?= htmlspecialchars($row['staff_id']) ?></td>
            <td class="px-4 py-2 border"><?= htmlspecialchars($row['name']) ?></td>

            <?php if ($filter === 'all' || $filter === 'breakfast'): ?>
              <td class="px-4 py-2 border text-center <?= $row['manual_breakfast'] ? 'text-red-600 font-semibold' : '' ?>">
                <?= $row['breakfast'] ? '✅' : '❌' ?>
                <?= $row['manual_breakfast'] ? '<span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded ml-1">Extra</span>' : '' ?>
              </td>
              <td class="px-4 py-2 border text-center"><?= $row['breakfast_received'] ? '🍜' : '🕓' ?></td>
            <?php endif; ?>

            <?php if ($filter === 'all' || $filter === 'lunch'): ?>
              <td class="px-4 py-2 border text-center <?= $row['manual_lunch'] ? 'text-red-600 font-semibold' : '' ?>">
                <?= $row['lunch'] ? '✅' : '❌' ?>
                <?= $row['manual_lunch'] ? '<span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded ml-1">Extra</span>' : '' ?>
              </td>
              <td class="px-4 py-2 border text-center"><?= $row['lunch_received'] ? '🍜' : '🕓' ?></td>
            <?php endif; ?>

            <?php if ($filter === 'all' || $filter === 'dinner'): ?>
              <td class="px-4 py-2 border text-center <?= $row['manual_dinner'] ? 'text-red-600 font-semibold' : '' ?>">
                <?= $row['dinner'] ? '✅' : '❌' ?>
                <?= $row['manual_dinner'] ? '<span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded ml-1">Extra</span>' : '' ?>
              </td>
              <td class="px-4 py-2 border text-center"><?= $row['dinner_received'] ? '🍜' : '🕓' ?></td>
            <?php endif; ?>
          </tr>
        <?php endwhile; ?>
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
