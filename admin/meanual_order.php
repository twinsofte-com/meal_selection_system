<?php
session_start();

include 'db.php';

$today = date('Y-m-d');

$query = "SELECT sm.*, s.name, s.staff_id AS staff_code
          FROM staff_meals sm
          JOIN staff s ON sm.staff_id = s.id
          WHERE sm.meal_date = CURDATE() AND sm.manual_order = 1;
          ";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manual Meal Order</title>
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
    <h1 class="text-2xl font-bold text-center text-green-600">🍱 Manual Meal Order</h1>

    <!-- Staff ID Input -->
    <div>
      <label class="block text-gray-700 font-semibold mb-1">Enter Staff ID:</label>
      <div class="flex flex-col sm:flex-row gap-2">
        <div class="flex items-center border rounded px-2 py-1 bg-gray-100">
          <span id="prefix" class="font-bold text-lg">ECW-</span>
        </div>
        <input type="text" id="manualID" class="border p-2 rounded w-full" placeholder="e.g. 1234">
        <button onclick="confirmManual()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">Confirm</button>
      </div>
      <div class="mt-2 text-sm text-gray-600">
        Select prefix:        
        <button onclick="selectPrefix('ECW-')" class="ml-1 px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded">ECW</button>
        <button onclick="selectPrefix('INT-')" class="ml-1 px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded">INT</button>
      </div>
    </div>

    <!-- Employee Info -->
    <div id="employee-info" class="hidden border rounded p-4 bg-gray-50">
      <p class="font-semibold">👤 Name: <span id="empName" class="text-green-700">-</span></p>
      <p class="font-semibold">🆔 ID: <span id="empID" class="text-green-700">-</span></p>
    </div>

    <!-- Order Form -->
    <form action="order_process.php" method="POST" id="orderForm" class="hidden space-y-6">
      <input type="hidden" name="staff_id" id="staff_id">

      <!-- Meal Selection -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="relative">
          <input type="checkbox" id="meal_breakfast" name="meals[]" value="1" class="meal-checkbox">
          <label for="meal_breakfast" class="meal-label">
            <p class="text-gray-600 font-semibold"><?= date("D d M") ?> <span class="text-sm block">හෙට</span></p>
            <h3 class="text-xl mt-2">BREAKFAST<br><span class="text-sm">උදේ</span></h3>
          </label>
        </div>

        <div class="relative">
          <input type="checkbox" id="meal_lunch" name="meals[]" value="2" class="meal-checkbox">
          <label for="meal_lunch" class="meal-label">
            <p class="text-gray-600 font-semibold"><?= date("D d M") ?> <span class="text-sm block">අද</span></p>
            <h3 class="text-xl mt-2">LUNCH<br><span class="text-sm">දහවල්</span></h3>
          </label>
        </div>

        <div class="relative">
          <input type="checkbox" id="meal_dinner" name="meals[]" value="3" class="meal-checkbox">
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
            <input type="radio" name="meal_option" value="egg" class="form-radio text-yellow-500 w-5 h-5" required>
            <span class="ml-3 font-semibold text-lg">🥚 Egg</span>
          </label><br>
          <label class="inline-flex items-center">
            <input type="radio" name="meal_option" value="chicken" class="form-radio text-red-600 w-5 h-5">
            <span class="ml-3 font-semibold text-lg">🍗 Chicken</span>
          </label><br>
          <label class="inline-flex items-center">
            <input type="radio" name="meal_option" value="vegetarian" class="form-radio text-green-600 w-5 h-5">
            <span class="ml-3 font-semibold text-lg">🥦 Vegetarian / ශාකාහාරයෙක්ද?</span>
          </label>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl text-xl font-bold w-full">
          CONFIRM / තහවුරු කරන්න
        </button>
        <button type="button" onclick="clearSelections()" id="reset-btn" class="bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl text-xl font-bold hidden w-full">
          RESET / යළි සකසන්න
        </button>
      </div>
    </form>
  </div>
  </div>

  <div class="mt-10 px-4">
  <h2 class="text-2xl font-bold text-green-700 mb-4 text-center">📋 Today’s Manual Meal Orders (<?= date('Y-m-d') ?>)</h2>
  <div class="overflow-x-auto bg-white rounded-xl shadow-lg">
    <table class="w-full text-sm text-left text-gray-700">
      <thead class="bg-green-600 text-white">
        <tr>
          <th class="px-4 py-3 border">#</th>
          <th class="px-4 py-3 border">Staff ID</th>
          <th class="px-4 py-3 border">Name</th>
          <th class="px-4 py-3 border text-center">🥣 Breakfast</th>
          <th class="px-4 py-3 border text-center">🍛 Lunch</th>
          <th class="px-4 py-3 border text-center">🍽️ Dinner</th>
          <th class="px-4 py-3 border">Option</th>
          <th class="px-4 py-3 border">Date</th>
          <!-- <th class="px-4 py-3 border text-center">✏️ Action</th> -->
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php
        $i = 1;
        mysqli_data_seek($result, 0); // in case it was already used above
        while ($row = mysqli_fetch_assoc($result)): 
            // Fetch meal option if available
            $meal_option = '';
            if ($row['vegetarian']) {
              $meal_option = '🥦 Vegetarian';
            } elseif ($row['egg']) {
              $meal_option = '🥚 Egg';
            } elseif ($row['chicken']) {
              $meal_option = '🍗 Chicken';
            } else {
              $meal_option = '❔';
            }
        ?>
        <tr class="hover:bg-green-50">
          <td class="px-4 py-2 border font-semibold"><?= $i++ ?></td>
          <td class="px-4 py-2 border"><?= htmlspecialchars($row['staff_code']) ?></td>
          <td class="px-4 py-2 border"><?= htmlspecialchars($row['name']) ?></td>
          <td class="px-4 py-2 border text-center">
            <span class="<?= $row['breakfast'] ? 'text-green-600 font-bold' : 'text-red-600 font-bold' ?>">
              <?= $row['breakfast'] ? '✅' : '❌' ?>
            </span>
          </td>
          <td class="px-4 py-2 border text-center">
            <span class="<?= $row['lunch'] ? 'text-green-600 font-bold' : 'text-red-600 font-bold' ?>">
              <?= $row['lunch'] ? '✅' : '❌' ?>
            </span>
          </td>
          <td class="px-4 py-2 border text-center">
            <span class="<?= $row['dinner'] ? 'text-green-600 font-bold' : 'text-red-600 font-bold' ?>">
              <?= $row['dinner'] ? '✅' : '❌' ?>
            </span>
          </td>
          <td class="px-4 py-2 border"><?= $meal_option ?></td>
          <td class="px-4 py-2 border"><?= htmlspecialchars($row['date']) ?></td>
          <!-- <td class="px-4 py-2 border text-center">
            <a href="edit_manual_order.php?id=<?= $row['id'] ?>" class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-md font-semibold text-sm">Edit</a>
          </td> -->
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>



  <script>
    function confirmManual() {
      const prefix = document.getElementById("prefix").innerText;
      const manualID = document.getElementById("manualID").value.trim();
      const fullID = prefix + manualID;

      if (!manualID) {
        alert("Please enter a valid ID.");
        return;
      }

      fetch(`get_staff_info.php?staff_id=${fullID}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.getElementById("staff_id").value = fullID;
            document.getElementById("empName").innerText = data.name;
            document.getElementById("empID").innerText = fullID;
            document.getElementById("employee-info").classList.remove("hidden");
            document.getElementById("orderForm").classList.remove("hidden");
            checkMealPreference(fullID);
          } else {
            alert("User not found.");
          }
        })
        .catch(() => alert("Error retrieving employee info."));
    }

    function selectPrefix(prefix) {
      document.getElementById("prefix").textContent = prefix;
    }

    function checkMealPreference(staffID) {
      fetch(`check_meal.php?staff_id=${staffID}`)
        .then(res => res.json())
        .then(response => {
          if (response.exists) {
            if (confirm("You have already added meal preferences for today. Do you want to update them?")) {
              document.getElementById("reset-btn").classList.remove("hidden");
            }
          } else {
            document.getElementById("reset-btn").classList.remove("hidden");
          }
        })
        .catch(() => alert("Error checking meal preference."));
    }

    function clearSelections() {
      document.querySelectorAll('.meal-checkbox').forEach(cb => cb.checked = false);
      document.querySelectorAll('input[name="meal_option"]').forEach(rb => rb.checked = false);
    }
  </script>
</body>
</html>
