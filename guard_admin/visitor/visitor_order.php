<?php
session_start();
include_once '../../admin/db.php';
include_once '../../admin/include/date.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $visitor_name = trim($_POST['visitor_name']);
    $breakfast = isset($_POST['breakfast']) ? 1 : 0;
    $lunch = isset($_POST['lunch']) ? 1 : 0;
    $dinner = isset($_POST['dinner']) ? 1 : 0;
    $egg = isset($_POST['preference']) && $_POST['preference'] === 'egg' ? 1 : 0;
    $chicken = isset($_POST['preference']) && $_POST['preference'] === 'chicken' ? 1 : 0;
    $vegetarian = isset($_POST['preference']) && $_POST['preference'] === 'vegetarian' ? 1 : 0;
    $meal_date = date('Y-m-d');
    $ordered_by = $_SESSION['username'] ?? 'admin';

    if ($visitor_name === '') {
        $error = "Visitor name is required.";
    } elseif (!$breakfast && !$lunch && !$dinner) {
        $error = "Please select at least one meal.";
    } elseif ($lunch && !$egg && !$chicken && !$vegetarian) {
        $error = "Please select a meal preference for Lunch.";
    } else {
        $stmt = $conn->prepare("INSERT INTO visitor_orders 
            (visitor_name, meal_date, breakfast, lunch, dinner, egg, chicken, vegetarian, ordered_by_admin)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiiiiiis", $visitor_name, $meal_date, $breakfast, $lunch, $dinner, $egg, $chicken, $vegetarian, $ordered_by);
        if ($stmt->execute()) {
            $success = "Meal order submitted successfully.";
        } else {
            $error = "Failed to submit order.";
        }
        $stmt->close();
    }
}

$today = date('Y-m-d');
$visitors = $conn->query("SELECT * FROM visitor_orders WHERE meal_date = '$today' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Visitor Meal Order</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .card-select {
            border: 2px dashed #ccc;
            background-color: #f8fafc;
            transition: 0.2s ease;
        }

        .card-select.selected {
            border-color: #22c55e;
            background-color: #dcfce7;
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.meal-card');
            const prefs = document.getElementById('preference-section');

            function updateUI() {
                cards.forEach(card => {
                    const input = card.querySelector('input[type=checkbox]');
                    if (input.checked) {
                        card.classList.add('selected');
                    } else {
                        card.classList.remove('selected');
                    }
                });

                const lunchChecked = document.querySelector('input[name="lunch"]').checked;
                if (lunchChecked) {
                    prefs.classList.remove('hidden');
                } else {
                    prefs.classList.add('hidden');
                    document.querySelectorAll('input[name="preference"]').forEach(r => r.checked = false);
                }
            }

            cards.forEach(card => {
                card.addEventListener('click', () => {
                    const input = card.querySelector('input[type=checkbox]');
                    input.checked = !input.checked;
                    updateUI();
                });
            });

            updateUI(); // Init state
        });
    </script>

</head>

<body class="bg-gray-100 text-gray-800">
    <?php include '../include/topbar.php'; ?>

    <div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4 text-center">Visitor Meal Order</h2>

        <?php if ($success): ?>
            <p class="text-green-600 text-center mb-2 font-semibold"><?= $success ?></p>
        <?php elseif ($error): ?>
            <p class="text-red-600 text-center mb-2 font-semibold"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label for="visitor_name" class="font-semibold block mb-1">Visitor Name</label>
                <input type="text" name="visitor_name" id="visitor_name" class="w-full px-3 py-2 border rounded"
                    required>
            </div>

            <div>
                <label class="font-semibold block mb-1">Meals</label>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="meal-card card-select cursor-pointer rounded p-4 shadow" data-target="breakfast">
                        <input type="checkbox" name="breakfast" id="breakfast" class="sr-only">
                        <span class="block font-bold text-lg">Breakfast</span>
                        <span class="text-sm text-gray-500">Today</span>
                    </div>
                    <div class="meal-card card-select cursor-pointer rounded p-4 shadow" data-target="lunch">
                        <input type="checkbox" name="lunch" id="lunch" class="sr-only">
                        <span class="block font-bold text-lg">Lunch</span>
                        <span class="text-sm text-gray-500">Today</span>
                    </div>
                    <div class="meal-card card-select cursor-pointer rounded p-4 shadow" data-target="dinner">
                        <input type="checkbox" name="dinner" id="dinner" class="sr-only">
                        <span class="block font-bold text-lg">Dinner</span>
                        <span class="text-sm text-gray-500">Today</span>
                    </div>
                </div>

            </div>


            <!-- Preferences -->
            <div id="preference-section" class="hidden">
                <label class="font-semibold block mb-1 mt-4">Meal Preference for Lunch <span
                        class="text-red-600">*</span></label>
                <div class="flex flex-col sm:flex-row gap-4 mt-2">
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="preference" value="egg" class="w-4 h-4"> <span>🥚 Egg</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="preference" value="chicken" class="w-4 h-4"> <span>🍗 Chicken</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="preference" value="vegetarian" class="w-4 h-4"> <span>🥬
                            Vegetarian</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 mt-4">Submit
                Order</button>
        </form>
    </div>

    <!-- Visitor Orders Table -->
    <div class="max-w-6xl mx-auto mt-8 mb-16 bg-white p-6 rounded shadow">
        <h3 class="text-xl font-semibold mb-4 text-center">Today's Visitor Orders (<?= date('Y-m-d') ?>)</h3>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-gray-300 text-center">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-3 py-2 border">#</th>
                        <th class="px-3 py-2 border">Name</th>
                        <th class="px-3 py-2 border">Breakfast</th>
                        <th class="px-3 py-2 border">Lunch</th>
                        <th class="px-3 py-2 border">Dinner</th>
                        <th class="px-3 py-2 border">Egg</th>
                        <th class="px-3 py-2 border">Chicken</th>
                        <th class="px-3 py-2 border">Vegetarian</th>
                        <th class="px-3 py-2 border">Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php $count = 1;
                    while ($v = $visitors->fetch_assoc()): ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-3 py-2 border"><?= $count++ ?></td>
                            <td class="px-3 py-2 border"><?= htmlspecialchars($v['visitor_name']) ?></td>
                            <td class="px-3 py-2 border"><?= $v['breakfast'] ? '✅' : '❌' ?></td>
                            <td class="px-3 py-2 border"><?= $v['lunch'] ? '✅' : '❌' ?></td>
                            <td class="px-3 py-2 border"><?= $v['dinner'] ? '✅' : '❌' ?></td>
                            <td class="px-3 py-2 border"><?= $v['egg'] ? '✅' : '❌' ?></td>
                            <td class="px-3 py-2 border"><?= $v['chicken'] ? '✅' : '❌' ?></td>
                            <td class="px-3 py-2 border"><?= $v['vegetarian'] ? '✅' : '❌' ?></td>
                            <td class="px-3 py-2 border text-xs"><?= date('h:i A', strtotime($v['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($count === 1): ?>
                        <tr>
                            <td colspan="9" class="text-center text-gray-500 py-4">No visitor orders today.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include_once '../include/footer.php'; ?>

</body>

</html>