<?php
session_start();
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch existing manual meal order data
    $query = "SELECT sm.*, s.name, s.staff_id AS staff_code
              FROM staff_meals sm
              JOIN staff s ON sm.staff_id = s.id
              WHERE sm.id = $id";

    $result = mysqli_query($conn, $query);
    $order = mysqli_fetch_assoc($result);

    if (!$order) {
        die("Order not found.");
    }

    // Get meal_option value from database (or from POST if form is submitted)
    $meal_option = isset($_POST['meal_option']) ? $_POST['meal_option'] : (isset($order['meal_option']) ? $order['meal_option'] : '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = $_POST['staff_id'];
    $breakfast = isset($_POST['breakfast']) ? 1 : 0;
    $lunch = isset($_POST['lunch']) ? 1 : 0;
    $dinner = isset($_POST['dinner']) ? 1 : 0;
    $meal_option = $_POST['meal_option'];

    // Update the manual meal order
    $updateQuery = "UPDATE staff_meals
                    SET breakfast = $breakfast, lunch = $lunch, dinner = $dinner, meal_option = '$meal_option'
                    WHERE id = $id";
    if (mysqli_query($conn, $updateQuery)) {
        header("Location: manual_order.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Manual Meal Order</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .meal-checkbox {
            position: absolute;
            opacity: 0;
            height: 0;
            width: 0;
        }
        .meal-label {
            display: block;
            cursor: pointer;
            border: 2px solid #d1d5db;
            border-radius: 0.75rem;
            padding: 1rem;
            text-align: center;
            transition: all 0.2s ease-in-out;
            background-color: white;
        }
        .meal-checkbox:checked + .meal-label {
            background-color: #d1fae5;
            border-color: #10b981;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include 'include/topbar.php'; ?>

    <div class="flex justify-center mt-10 px-4">
        <div class="bg-white shadow-xl rounded-xl p-6 w-full max-w-3xl">
            <h1 class="text-2xl font-bold text-center text-green-600">🍱 Edit Manual Meal Order</h1>

            <!-- Employee Info -->
            <div class="border rounded p-4 bg-gray-50">
                <p class="font-semibold">👤 Name: <span class="text-green-700"><?= htmlspecialchars($order['name']) ?></span></p>
                <p class="font-semibold">🆔 ID: <span class="text-green-700"><?= htmlspecialchars($order['staff_code']) ?></span></p>
            </div>

            <!-- Edit Form -->
            <form action="edit_manual_order.php?id=<?= $order['id'] ?>" method="POST" class="space-y-6 mt-4">
                <input type="hidden" name="staff_id" value="<?= $order['staff_code'] ?>">

                <!-- Meal Selection -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative">
                        <input type="checkbox" id="meal_breakfast" name="breakfast" value="1" class="meal-checkbox" <?= $order['breakfast'] ? 'checked' : '' ?>>
                        <label for="meal_breakfast" class="meal-label">
                            <p class="text-gray-600 font-semibold"><?= date("D d M") ?> <span class="text-sm block">හෙට</span></p>
                            <h3 class="text-xl mt-2">BREAKFAST<br><span class="text-sm">උදේ</span></h3>
                        </label>
                    </div>

                    <div class="relative">
                        <input type="checkbox" id="meal_lunch" name="lunch" value="1" class="meal-checkbox" <?= $order['lunch'] ? 'checked' : '' ?>>
                        <label for="meal_lunch" class="meal-label">
                            <p class="text-gray-600 font-semibold"><?= date("D d M") ?> <span class="text-sm block">අද</span></p>
                            <h3 class="text-xl mt-2">LUNCH<br><span class="text-sm">දහවල්</span></h3>
                        </label>
                    </div>

                    <div class="relative">
                        <input type="checkbox" id="meal_dinner" name="dinner" value="1" class="meal-checkbox" <?= $order['dinner'] ? 'checked' : '' ?>>
                        <label for="meal_dinner" class="meal-label">
                            <p class="text-gray-600 font-semibold"><?= date("D d M") ?> <span class="text-sm block">අද</span></p>
                            <h3 class="text-xl mt-2">DINNER<br><span class="text-sm">රාත්‍රී</span></h3>
                        </label>
                    </div>
                </div>

                <!-- Meal Option -->
                <div class="mt-4">
                    <p class="font-semibold mb-2">Select Meal Option:</p>
                    <div class="space-y-3">
                        <label class="inline-flex items-center">
                            <input type="radio" name="meal_option" value="egg" class="form-radio text-yellow-500 w-5 h-5" 
                            <?= $meal_option === 'egg' ? 'checked' : '' ?> required>
                            <span class="ml-3 font-semibold text-lg">🥚 Egg</span>
                        </label><br>
                        <label class="inline-flex items-center">
                            <input type="radio" name="meal_option" value="chicken" class="form-radio text-red-600 w-5 h-5" 
                            <?= $meal_option === 'chicken' ? 'checked' : '' ?>>
                            <span class="ml-3 font-semibold text-lg">🍗 Chicken</span>
                        </label><br>
                        <label class="inline-flex items-center">
                            <input type="radio" name="meal_option" value="vegetarian" class="form-radio text-green-600 w-5 h-5" 
                            <?= $meal_option === 'vegetarian' ? 'checked' : '' ?>>
                            <span class="ml-3 font-semibold text-lg">🥦 Vegetarian / ශාකාහාරයෙක්ද?</span>
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl text-xl font-bold w-full">
                        UPDATE ORDER / නවීකරණය කරන්න
                    </button>
                    <a href="manual_order.php" class="bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl text-xl font-bold w-full text-center">
                        CANCEL / වළක්වන්න
                    </a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
