<?php
include_once 'include/date.php';
include_once 'validation/validation.php';
require_once 'db.php';

$from_date = $_GET['from_date'] ?? date('Y-m-d');
$to_date = $_GET['to_date'] ?? date('Y-m-d');
$filter = $_GET['meal'] ?? 'all';
$type = $_GET['type'] ?? 'all'; // all | staff | visitors

// Staff Meals
$rows = [];
$total_project = 0;
$total_egg = 0;
$total_chicken = 0;
$total_veg = 0;
$total_breakfast = 0;
$total_lunch = 0;
$total_dinner = 0;

if ($type === 'all' || $type === 'staff') {
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
}

// Visitor Meals
$visitor_rows = [];
$total_visitors = 0;

if ($type === 'all' || $type === 'visitors') {
    $vstmt = $conn->prepare("SELECT * FROM visitor_orders WHERE meal_date BETWEEN ? AND ?");
    $vstmt->bind_param("ss", $from_date, $to_date);
    $vstmt->execute();
    $vresult = $vstmt->get_result();
    while ($v = $vresult->fetch_assoc()) {
        $visitor_rows[] = $v;
        $total_visitors++;
        $total_egg += $v['egg'];
        $total_chicken += $v['chicken'];
        $total_veg += $v['vegetarian'];
        $total_breakfast += $v['breakfast'];
        $total_lunch += $v['lunch'];
        $total_dinner += $v['dinner'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Full Meal Report</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
<?php include 'include/topbar.php'; ?>

<div class="max-w-7xl mx-auto p-6">
  <h1 class="text-3xl font-bold text-center mb-6">📊 Full Meal Report</h1>

  <form method="GET" class="flex flex-wrap justify-center gap-4 mb-6">
    <input type="date" name="from_date" value="<?= $from_date ?>" class="border rounded p-2">
    <input type="date" name="to_date" value="<?= $to_date ?>" class="border rounded p-2">
    <input type="hidden" name="meal" value="<?= htmlspecialchars($filter) ?>">
    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Search</button>
  </form>

  <div class="flex justify-center flex-wrap gap-2 mb-4">
    <?php foreach (['all', 'breakfast', 'lunch', 'dinner'] as $m): ?>
      <a href="?from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&meal=<?= $m ?>&type=<?= $type ?>"
         class="px-4 py-2 rounded <?= $filter === $m ? 'bg-blue-700 text-white' : 'bg-gray-300' ?>">
         <?= ucfirst($m) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="flex justify-center gap-2 mb-4">
    <?php foreach (['all' => 'All', 'staff' => 'Staff Only', 'visitors' => 'Visitors Only'] as $k => $label): ?>
      <a href="?from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&meal=<?= $filter ?>&type=<?= $k ?>"
         class="px-4 py-2 rounded <?= $type === $k ? 'bg-blue-700 text-white' : 'bg-gray-300' ?>">
         <?= $label ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="text-center mb-4 font-semibold">
    👥 Staff: <?= $total_project ?> | 👤 Visitors: <?= $total_visitors ?> |
    🍳 Egg: <?= $total_egg ?> | 🍗 Chicken: <?= $total_chicken ?> | 🥬 Veg: <?= $total_veg ?>
  </div>

  <div class="bg-white rounded shadow p-4 mb-10">
    <div class="flex justify-between mb-4">
      <h2 class="text-lg font-semibold">From <?= $from_date ?> to <?= $to_date ?> (<?= ucfirst($filter) ?>)</h2>
      <a href="generate_report.php?from=<?= $from_date ?>&to=<?= $to_date ?>&meal=<?= $filter ?>&type=<?= $type ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">⬇ PDF</a>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border border-gray-300 text-center">
        <thead class="bg-gray-200">
          <tr>
            <th class="border px-3 py-2">Type</th>
            <th class="border px-3 py-2">Name</th>
            <?php if ($filter === 'all' || $filter === 'breakfast'): ?>
              <th class="border px-3 py-2">Breakfast</th>
              <th class="border px-3 py-2">Received</th>
            <?php endif; ?>
            <?php if ($filter === 'all' || $filter === 'lunch'): ?>
              <th class="border px-3 py-2">Lunch</th>
              <th class="border px-3 py-2">Received</th>
            <?php endif; ?>
            <?php if ($filter === 'all' || $filter === 'dinner'): ?>
              <th class="border px-3 py-2">Dinner</th>
              <th class="border px-3 py-2">Received</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if ($type === 'all' || $type === 'staff'): ?>
            <?php foreach ($rows as $r): ?>
              <tr class="border-t">
                <td class="border px-3 py-2">Staff</td>
                <td class="border px-3 py-2"><?= htmlspecialchars($r['name']) ?></td>
                <?php if ($filter === 'all' || $filter === 'breakfast'): ?>
                  <td class="border px-3 py-2"><?= $r['breakfast'] ? '✅' : '❌' ?><?= $r['manual_breakfast'] ? '<span class="text-red-600 text-xs ml-1">(Extra)</span>' : '' ?></td>
                  <td class="border px-3 py-2"><?= $r['breakfast_received'] ? '🍽️' : '🕒' ?></td>
                <?php endif; ?>
                <?php if ($filter === 'all' || $filter === 'lunch'): ?>
                  <td class="border px-3 py-2"><?= $r['lunch'] ? '✅' : '❌' ?><?= $r['manual_lunch'] ? '<span class="text-red-600 text-xs ml-1">(Extra)</span>' : '' ?></td>
                  <td class="border px-3 py-2"><?= $r['lunch_received'] ? '🍽️' : '🕒' ?></td>
                <?php endif; ?>
                <?php if ($filter === 'all' || $filter === 'dinner'): ?>
                  <td class="border px-3 py-2"><?= $r['dinner'] ? '✅' : '❌' ?><?= $r['manual_dinner'] ? '<span class="text-red-600 text-xs ml-1">(Extra)</span>' : '' ?></td>
                  <td class="border px-3 py-2"><?= $r['dinner_received'] ? '🍽️' : '🕒' ?></td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>

          <?php if ($type === 'all' || $type === 'visitors'): ?>
            <?php foreach ($visitor_rows as $v): ?>
              <tr class="border-t bg-yellow-50">
                <td class="border px-3 py-2 font-semibold text-yellow-700">Visitor</td>
                <td class="border px-3 py-2"><?= htmlspecialchars($v['visitor_name']) ?></td>
                <?php if ($filter === 'all' || $filter === 'breakfast'): ?>
                  <td class="border px-3 py-2"><?= $v['breakfast'] ? '✅' : '❌' ?></td>
                  <td class="border px-3 py-2"><?= $v['breakfast_received'] ? '🍽️' : '🕒' ?></td>
                <?php endif; ?>
                <?php if ($filter === 'all' || $filter === 'lunch'): ?>
                  <td class="border px-3 py-2"><?= $v['lunch'] ? '✅' : '❌' ?></td>
                  <td class="border px-3 py-2"><?= $v['lunch_received'] ? '🍽️' : '🕒' ?></td>
                <?php endif; ?>
                <?php if ($filter === 'all' || $filter === 'dinner'): ?>
                  <td class="border px-3 py-2"><?= $v['dinner'] ? '✅' : '❌' ?></td>
                  <td class="border px-3 py-2"><?= $v['dinner_received'] ? '🍽️' : '🕒' ?></td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-4 font-semibold">
      <?php if ($filter === 'all' || $filter === 'breakfast'): ?>
        🍳 Total Breakfast: <?= $total_breakfast ?><br>
      <?php endif; ?>
      <?php if ($filter === 'all' || $filter === 'lunch'): ?>
        🍗 Total Lunch: <?= $total_lunch ?><br>
      <?php endif; ?>
      <?php if ($filter === 'all' || $filter === 'dinner'): ?>
        🍛 Total Dinner: <?= $total_dinner ?><br>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'include/footer.php'; ?>
</body>
</html>
