<?php
include_once '../../admin/db.php';
include_once '../../admin/include/date.php';
include_once '../validate_issue_session_index.php';

$today = date('Y-m-d');
$issued_by = $_SESSION['username'] ?? 'guard';

$success = $error = '';
$search = $_GET['search'] ?? '';

// Handle POST for marking as issued
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['visitor_id']);
    $meal = $_POST['meal'];

    if (!in_array($meal, ['breakfast', 'lunch', 'dinner'])) {
        $error = "Invalid meal type.";
    } else {
        $col = $meal . "_received";
        $stmt = $conn->prepare("UPDATE visitor_orders SET $col = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = ucfirst($meal) . " marked as received.";
        } else {
            $error = "Failed to update.";
        }
        $stmt->close();
    }
}

// Fetch today's visitors
$query = "SELECT * FROM visitor_orders WHERE meal_date = ?";
if ($search !== '') {
    $query .= " AND visitor_name LIKE ?";
}
$query .= " ORDER BY created_at DESC";

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Visitor Meal Issuance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800">
  <div class="max-w-6xl mx-auto p-4 sm:p-6">
    <h1 class="text-3xl font-bold text-center text-blue-800 mb-4">🍽️ Visitor Meal Issuance</h1>

    <?php if ($success): ?>
      <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-2 rounded mb-4 text-center">
        <?= $success ?>
      </div>
    <?php elseif ($error): ?>
      <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-2 rounded mb-4 text-center">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <!-- Search bar -->
    <form method="GET" class="mb-4 flex flex-col sm:flex-row gap-2 items-center justify-center">
      <input type="text" name="search" placeholder="Search visitor name..." value="<?= htmlspecialchars($search) ?>"
             class="w-full sm:w-1/2 px-4 py-2 border rounded shadow-sm focus:ring focus:ring-blue-300">
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-800">🔍 Search</button>
      <?php if ($search): ?>
        <a href="?" class="text-sm text-red-600 hover:underline">Clear</a>
      <?php endif; ?>
    </form>

    <?php if (empty($visitors)): ?>
      <p class="text-center text-gray-600 text-lg">No visitor orders found<?= $search ? " for '$search'" : "" ?>.</p>
    <?php else: ?>
      <div class="overflow-x-auto bg-white rounded shadow-lg p-4">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-100 text-gray-700 font-semibold text-center">
            <tr>
              <th class="px-4 py-2">Visitor</th>
              <th class="px-4 py-2">Breakfast</th>
              <th class="px-4 py-2">Lunch</th>
              <th class="px-4 py-2">Dinner</th>
              <th class="px-4 py-2">Issued</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($visitors as $v): ?>
              <tr class="hover:bg-gray-50 text-center">
                <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($v['visitor_name']) ?></td>
                <?php foreach (['breakfast', 'lunch', 'dinner'] as $meal): ?>
                  <td class="px-4 py-2 text-xl">
                    <?= $v[$meal] ? '✅' : '❌' ?>
                  </td>
                <?php endforeach; ?>
                <td class="px-4 py-2 flex justify-center gap-2 flex-wrap">
                  <?php foreach (['breakfast', 'lunch', 'dinner'] as $meal): ?>
                    <?php if ($v[$meal]): ?>
                      <?php if (!$v[$meal . '_received']): ?>
                        <form method="POST" class="inline">
                          <input type="hidden" name="visitor_id" value="<?= $v['id'] ?>">
                          <input type="hidden" name="meal" value="<?= $meal ?>">
                          <button type="submit" class="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-800 transition">
                            <?= ucfirst($meal) ?>
                          </button>
                        </form>
                      <?php else: ?>
                        <span class="inline-block text-green-600 text-xs font-medium">
                          <?= ucfirst($meal) ?> 🍽️
                        </span>
                      <?php endif; ?>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <div class="text-center mt-6">
      <a href="../dashboard.php" class="text-blue-600 font-semibold hover:underline text-sm">← Back to Dashboard</a>
    </div>
  </div>
</body>
</html>
