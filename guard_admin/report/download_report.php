<?php
include_once '../include/validation.php';
require_once '../../admin/db.php';
include_once '../../admin/include/date.php';

$from_date = $_GET['from_date'] ?? date('Y-m-d');
$to_date = $_GET['to_date'] ?? date('Y-m-d');
$filter = $_GET['meal'] ?? 'all';
$type_filter = $_GET['type'] ?? 'both';

// Initialize totals
$total_staff = 0;
$total_visitors = 0;
$total_egg = 0;
$total_chicken = 0;
$total_veg = 0;
$total_breakfast = 0;
$total_lunch = 0;
$total_dinner = 0;

// Staff Meals
$rows = [];
if ($type_filter !== 'visitor') {
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
        $total_staff++;
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
if ($type_filter !== 'staff') {
    $vstmt = $conn->prepare("SELECT * FROM visitor_orders WHERE meal_date BETWEEN ? AND ?");
    $vstmt->bind_param("ss", $from_date, $to_date);
    $vstmt->execute();
    $visitors = $vstmt->get_result();
    while ($vrow = $visitors->fetch_assoc()) {
        $visitor_rows[] = $vrow;
        $total_visitors++;
        $total_egg += $vrow['egg'];
        $total_chicken += $vrow['chicken'];
        $total_veg += $vrow['vegetarian'];
        $total_breakfast += $vrow['breakfast'];
        $total_lunch += $vrow['lunch'];
        $total_dinner += $vrow['dinner'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Full Meal Report</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>@media print {.no-print { display: none; }}</style>
</head>
<body class="bg-gray-100 text-gray-800">
<?php include '../include/topbar.php'; ?>

<div class="max-w-7xl mx-auto p-6">
  <h1 class="text-3xl font-bold text-center mb-6">📊 Full Meal Report</h1>

  <form method="GET" class="no-print flex flex-wrap justify-center gap-3 mb-4">
    <input type="date" name="from_date" value="<?= $from_date ?>" class="p-2 border rounded">
    <input type="date" name="to_date" value="<?= $to_date ?>" class="p-2 border rounded">
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Search</button>
  </form>

  <div class="flex flex-wrap justify-center gap-2 mb-3 no-print">
    <?php foreach (['all', 'breakfast', 'lunch', 'dinner'] as $m): ?>
      <a href="?from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&meal=<?= $m ?>&type=<?= $type_filter ?>" class="px-4 py-2 rounded <?= $filter === $m ? 'bg-blue-700 text-white' : 'bg-gray-300' ?>"><?= ucfirst($m) ?></a>
    <?php endforeach; ?>
  </div>

  <div class="flex flex-wrap justify-center gap-2 mb-4 no-print">
    <?php foreach (['both' => 'All', 'staff' => 'Staff Only', 'visitor' => 'Visitors Only'] as $val => $label): ?>
      <a href="?from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&meal=<?= $filter ?>&type=<?= $val ?>" class="px-4 py-2 rounded <?= $type_filter === $val ? 'bg-blue-700 text-white' : 'bg-gray-300' ?>"><?= $label ?></a>
    <?php endforeach; ?>
  </div>

  <div class="text-center font-semibold mb-3">
    👥 <strong>Staff:</strong> <?= $total_staff ?> |
    👤 <strong>Visitors:</strong> <?= $total_visitors ?> |
    🔍 <strong>Egg:</strong> <?= $total_egg ?> |
    🍗 <strong>Chicken:</strong> <?= $total_chicken ?> |
    🥒 <strong>Veg:</strong> <?= $total_veg ?>
  </div>

  <div class="bg-white shadow rounded p-4 overflow-x-auto">
    <div class="mb-2 font-medium">From <?= $from_date ?> to <?= $to_date ?> (<?= ucfirst($filter) ?>)
<a href="generate_report.php?from=<?= $from_date ?>&to=<?= $to_date ?>&meal=<?= $filter ?>&type=<?= $type_filter ?>" 
   class="no-print bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 ml-2 inline-block">
  ⬇ Download PDF
</a>
</div>
    
    <table class="min-w-full text-sm text-center border border-gray-300">
      <thead class="bg-gray-100">
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
        <?php foreach ($rows as $r): ?>
          <tr class="border-t">
            <td class="border px-3 py-2">Staff</td>
            <td class="border px-3 py-2"><?= htmlspecialchars($r['name']) ?></td>
            <?php foreach (['breakfast', 'lunch', 'dinner'] as $mealType): ?>
              <?php if ($filter === 'all' || $filter === $mealType): ?>
                <td class="border px-3 py-2"><?= $r[$mealType] ? '✅' : '❌' ?> <?= $r["manual_$mealType"] ? '<span class="text-xs text-red-600">(Extra)</span>' : '' ?></td>
                <td class="border px-3 py-2"><?= $r["{$mealType}_received"] ? '🍽️' : '🕒' ?></td>
              <?php endif; ?>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>

        <?php foreach ($visitor_rows as $v): ?>
          <tr class="border-t bg-yellow-50">
            <td class="border px-3 py-2 font-medium text-yellow-800">Visitor</td>
            <td class="border px-3 py-2"><?= htmlspecialchars($v['visitor_name']) ?></td>
            <?php foreach (['breakfast', 'lunch', 'dinner'] as $mealType): ?>
              <?php if ($filter === 'all' || $filter === $mealType): ?>
                <td class="border px-3 py-2"><?= $v[$mealType] ? '✅' : '❌' ?></td>
                <td class="border px-3 py-2"><?= $v[$mealType.'_received'] ? '🍽️' : '🕒' ?></td>
              <?php endif; ?>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="mt-4 font-semibold">
      🔍 <strong>Total Breakfast:</strong> <?= $total_breakfast ?> <br>
      🍗 <strong>Total Lunch:</strong> <?= $total_lunch ?> <br>
      🍛 <strong>Total Dinner:</strong> <?= $total_dinner ?>
    </div>
  </div>
</div>

<?php include '../include/footer.php'; ?>
</body>
</html>
