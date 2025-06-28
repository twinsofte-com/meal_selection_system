<?php
include_once 'validation/validation.php';
require_once 'db.php';

$from_date = $_GET['from_date'] ?? date('Y-m-d');
$to_date = $_GET['to_date'] ?? date('Y-m-d');
$filter = $_GET['meal'] ?? 'all';

$stmt = $conn->prepare("SELECT 
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
    sm.dinner_received,
    sm.egg,
    sm.chicken,
    sm.vegetarian
FROM staff_meals sm
JOIN staff s ON sm.staff_id = s.id
WHERE sm.meal_date BETWEEN ? AND ?
ORDER BY s.name");
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
$total_project = 0;
$total_egg = 0;
$total_chicken = 0;
$total_veg = 0;

$total_breakfast = 0;
$total_lunch = 0;
$total_dinner = 0;

while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
    $total_project++;
    $total_egg += $row['egg'];
    $total_chicken += $row['chicken'];
    $total_veg += $row['vegetarian'];

    $total_breakfast += $row['breakfast'];
    $total_lunch += $row['lunch'];
    $total_dinner += $row['dinner'];
}
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
  <h1 class="text-3xl font-bold text-center mb-6">📅 Meal Report (Date Range)</h1>

  <form method="GET" class="flex flex-wrap justify-center items-center gap-4 mb-6">
    <label for="from_date" class="font-semibold">From:</label>
    <input type="date" id="from_date" name="from_date" value="<?= htmlspecialchars($from_date) ?>" class="p-2 border rounded">
    <label for="to_date" class="font-semibold">To:</label>
    <input type="date" id="to_date" name="to_date" value="<?= htmlspecialchars($to_date) ?>" class="p-2 border rounded">
    <input type="hidden" name="meal" value="<?= htmlspecialchars($filter) ?>">
    <button type="submit" class="bg-gray-700 text-white px-3 py-2 rounded">🔍 Search</button>
  </form>
  <div class="text-center mb-4">
    <a href="?from_date=<?= date('Y-m-d') ?>&to_date=<?= date('Y-m-d') ?>&meal=<?= urlencode($filter) ?>" class="inline-block bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">📅 Today's Orders</a>
  </div>

  <div class="flex justify-center gap-3 mb-4">
    <a href="?from_date=<?= urlencode($from_date) ?>&to_date=<?= urlencode($to_date) ?>&meal=all" class="px-4 py-2 rounded <?= $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">All</a>
    <a href="?from_date=<?= urlencode($from_date) ?>&to_date=<?= urlencode($to_date) ?>&meal=breakfast" class="px-4 py-2 rounded <?= $filter === 'breakfast' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">Breakfast</a>
    <a href="?from_date=<?= urlencode($from_date) ?>&to_date=<?= urlencode($to_date) ?>&meal=lunch" class="px-4 py-2 rounded <?= $filter === 'lunch' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">Lunch</a>
    <a href="?from_date=<?= urlencode($from_date) ?>&to_date=<?= urlencode($to_date) ?>&meal=dinner" class="px-4 py-2 rounded <?= $filter === 'dinner' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">Dinner</a>
  </div>

  <div class="mb-4 text-center font-semibold">
    🧾 Project Total: <?= $total_project ?> |
    🍳 Egg: <?= $total_egg ?> |
    🍗 Chicken: <?= $total_chicken ?> |
    🥬 Vegetarian: <?= $total_veg ?>
  </div>

  <div class="bg-white rounded-lg shadow p-4 mb-10">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold">From <?= htmlspecialchars($from_date) ?> to <?= htmlspecialchars($to_date) ?> (<?= ucfirst($filter) ?>)</h2>
      <a href="generate_report.php?from=<?= urlencode($from_date) ?>&to=<?= urlencode($to_date) ?>&meal=<?= urlencode($filter) ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">⬇ Download PDF</a>
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
        <?php foreach ($rows as $row): ?>
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
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-4 font-semibold">
      <?php if ($filter === 'all' || $filter === 'breakfast'): ?>🍽️ Total Breakfast Ordered: <?= $total_breakfast ?><br><?php endif; ?>
      <?php if ($filter === 'all' || $filter === 'lunch'): ?>🍽️ Total Lunch Ordered: <?= $total_lunch ?><br><?php endif; ?>
      <?php if ($filter === 'all' || $filter === 'dinner'): ?>🍽️ Total Dinner Ordered: <?= $total_dinner ?><br><?php endif; ?>
    </div>

  </div>
</div>

<?php include 'include/footer.php'; ?>
</body>
</html>
