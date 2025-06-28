<?php
require_once '../../admin/db.php';
require_once '../../admin/include/date.php';

$today = date('Y-m-d');
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM visitor_orders WHERE meal_date = ? ";
if ($search !== '') {
    $query .= "AND visitor_name LIKE ? ";
}
$query .= "ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if ($search !== '') {
    $like = "%{$search}%";
    $stmt->bind_param("ss", $today, $like);
} else {
    $stmt->bind_param("s", $today);
}
$stmt->execute();
$result = $stmt->get_result();
$visitors = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($visitors as $v):
?>
<tr class="hover:bg-gray-50 text-center">
  <td class="px-4 py-2 font-medium text-gray-800 whitespace-nowrap"><?= htmlspecialchars($v['visitor_name']) ?></td>
  <?php foreach (['breakfast', 'lunch', 'dinner'] as $meal): ?>
    <td class="px-4 py-2 text-xl"><?= $v[$meal] ? '✅' : '❌' ?></td>
  <?php endforeach; ?>
  <td class="px-4 py-2 flex justify-center gap-2 flex-wrap">
    <?php foreach (['breakfast', 'lunch', 'dinner'] as $meal): ?>
      <?php if ($v[$meal]): ?>
        <?php if (!$v[$meal . '_received']): ?>
          <form method="POST" action="" class="inline">
            <input type="hidden" name="visitor_id" value="<?= $v['id'] ?>">
            <input type="hidden" name="meal" value="<?= $meal ?>">
            <button type="submit" class="text-xs bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-700 transition">
              <?= ucfirst($meal) ?>
            </button>
          </form>
        <?php else: ?>
          <span class="inline-block text-green-600 text-xs font-medium"><?= ucfirst($meal) ?> 🍽️</span>
        <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </td>
</tr>
<?php endforeach;

if (empty($visitors)): ?>
<tr><td colspan="5" class="text-center text-gray-500 py-4">No matching visitor found.</td></tr>
<?php endif; ?>
