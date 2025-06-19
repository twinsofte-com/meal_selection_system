<?php
session_start();
include 'db.php';
include_once 'include/date.php';

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Fetch today’s lunch and dinner
$query_today = "SELECT sm.*, s.name, s.staff_id AS staff_code
                FROM staff_meals sm
                JOIN staff s ON sm.staff_id = s.id
                WHERE sm.meal_date = '$today'";
$result_today = mysqli_query($conn, $query_today);

// Fetch tomorrow’s breakfast
$query_tomorrow = "SELECT sm.*, s.name, s.staff_id AS staff_code
                   FROM staff_meals sm
                   JOIN staff s ON sm.staff_id = s.id
                   WHERE sm.meal_date = '$tomorrow'";
$result_tomorrow = mysqli_query($conn, $query_tomorrow);
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

  <!-- Form Section -->
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

          <div class="relative">
            <input type="checkbox" id="meal_breakfast" name="meals[]" value="1" class="meal-checkbox">
            <label for="meal_breakfast" class="meal-label">
              <p class="text-gray-600 font-semibold"><?= date("D d M", strtotime('+1 day')) ?> <span class="text-sm block">හෙට</span></p>
              <h3 class="text-xl mt-2">BREAKFAST<br><span class="text-sm">උදේ</span></h3>
            </label>
          </div>

        </div>

        <!-- Meal Option -->
        <div class="mt-4 hidden" id="meal-option-section">
          <p class="font-semibold mb-2">Select Meal Option:</p>
          <div class="space-y-3">
            <label class="inline-flex items-center">
              <input type="radio" name="meal_option" value="egg" class="form-radio text-yellow-500 w-5 h-5">
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

  <!-- Orders Table -->
  <div class="mt-10 px-4">
    <h2 class="text-2xl font-bold text-green-700 mb-4 text-center">📋 Manual Meal Orders Summary</h2>
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
          </tr>
        </thead>
        <tbody class="divide-y">
  <?php
  $i = 1;

  // Today’s Lunch & Dinner
  while ($row = mysqli_fetch_assoc($result_today)):
    $meal_option = $row['vegetarian'] ? '🥦 Vegetarian' : ($row['egg'] ? '🥚 Egg' : ($row['chicken'] ? '🍗 Chicken' : '❔'));
  ?>
  <tr class="hover:bg-green-50">
    <td class="px-4 py-2 border font-semibold"><?= $i++ ?></td>
    <td class="px-4 py-2 border"><?= htmlspecialchars($row['staff_code']) ?></td>
    <td class="px-4 py-2 border"><?= htmlspecialchars($row['name']) ?></td>
    <td class="px-4 py-2 border text-center">❌</td> <!-- Breakfast is not for today -->
    <td class="px-4 py-2 border text-center"><?= !empty($row['lunch']) && $row['lunch'] == '1' ? '✅' : '❌' ?></td>
    <td class="px-4 py-2 border text-center"><?= !empty($row['dinner']) && $row['dinner'] == '1' ? '✅' : '❌' ?></td>
    <td class="px-4 py-2 border"><?= $meal_option ?></td>
    <td class="px-4 py-2 border"><?= htmlspecialchars($row['meal_date']) ?></td>
  </tr>
  <?php endwhile; ?>

  <!-- Tomorrow’s Breakfast -->
  <?php while ($row = mysqli_fetch_assoc($result_tomorrow)):
    $meal_option = $row['vegetarian'] ? '🥦 Vegetarian' : ($row['egg'] ? '🥚 Egg' : ($row['chicken'] ? '🍗 Chicken' : '❔'));
  ?>
  <tr class="hover:bg-green-50">
    <td class="px-4 py-2 border font-semibold"><?= $i++ ?></td>
    <td class="px-4 py-2 border"><?= htmlspecialchars($row['staff_code']) ?></td>
    <td class="px-4 py-2 border"><?= htmlspecialchars($row['name']) ?></td>    
    <td class="px-4 py-2 border text-center"><?= (int)$row['breakfast'] === 1 ? '✅' : '❌' ?></td>
    <td class="px-4 py-2 border text-center">❌</td> <!-- Lunch is not for tomorrow -->
    <td class="px-4 py-2 border text-center">❌</td> <!-- Dinner is not for tomorrow -->
    <td class="px-4 py-2 border"><?= $meal_option ?></td>
    <td class="px-4 py-2 border"><?= htmlspecialchars($row['meal_date']) ?></td>
  </tr>
  <?php endwhile; ?>
</tbody>


      </table>
    </div>
  </div>

  <!-- Scripts -->
<!-- Scripts -->
<script>
  function confirmManual() {
    const prefix = document.getElementById("prefix").innerText;
    const manualID = document.getElementById("manualID").value.trim();
    const fullID = prefix + manualID;

    if (!manualID) return alert("Please enter a valid ID.");

    fetch(`get_staff.php?staff_id=${fullID}`)
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          document.getElementById("staff_id").value = fullID;
          document.getElementById("empName").innerText = data.name;
          document.getElementById("empID").innerText = fullID;
          document.getElementById("employee-info").classList.remove("hidden");
          document.getElementById("orderForm").classList.remove("hidden");

          const today = "<?= date('Y-m-d') ?>";
          const tomorrow = "<?= date('Y-m-d', strtotime('+1 day')) ?>";

          // Reset checkboxes first
          clearSelections();

          const mealData = data.meals;

          if (mealData[today]) {
            const todayMeal = mealData[today];
            if (todayMeal.lunch == "1") document.getElementById("meal_lunch").checked = true;
            if (todayMeal.dinner == "1") document.getElementById("meal_dinner").checked = true;

           // Set meal option radios
            if (todayMeal.vegetarian == "1") {
              document.querySelector('input[name="meal_option"][value="vegetarian"]').checked = true;
            } else if (todayMeal.egg == "1") {
              document.querySelector('input[name="meal_option"][value="egg"]').checked = true;
            } else if (todayMeal.chicken == "1") {
              document.querySelector('input[name="meal_option"][value="chicken"]').checked = true;
            }

            // Toggle meal option visibility correctly based on lunch checkbox
            const lunchCheckbox = document.getElementById("meal_lunch");
            const mealOptionSection = document.getElementById("meal-option-section");
            if (lunchCheckbox.checked) {
              mealOptionSection.classList.remove("hidden");
            } else {
              mealOptionSection.classList.add("hidden");
            }
          }

          if (mealData[tomorrow] && mealData[tomorrow].breakfast == "1") {
            document.getElementById("meal_breakfast").checked = true;
          }
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
        // If meal preferences already exist, show options to update them
        if (response.exists) {
          if (response.can_update) {
            if (confirm("You have already added meal preferences for today. Do you want to update them?")) {
              document.getElementById("reset-btn").classList.remove("hidden");
            }
          } else {
            // Meal preferences are already set, don't show the update prompt
            document.getElementById("reset-btn").classList.add("hidden");
          }
        } else {
          // No meal preferences, allow selecting
          document.getElementById("reset-btn").classList.remove("hidden");
        }
      })
      .catch(() => alert("Error checking meal preference."));
  }

  function clearSelections() {
    document.querySelectorAll('.meal-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[name="meal_option"]').forEach(rb => rb.checked = false);
    document.getElementById("meal-option-section").classList.add("hidden");
  }


  // Show/hide meal options based on Lunch checkbox
  document.addEventListener("DOMContentLoaded", () => {
    const lunchCheckbox = document.getElementById("meal_lunch");
    const mealOptionSection = document.getElementById("meal-option-section");

    function toggleMealOptionSection() {
      if (lunchCheckbox.checked) {
        mealOptionSection.classList.remove("hidden");
      } else {
        mealOptionSection.classList.add("hidden");

        // Uncheck all meal option radios if not needed
        document.querySelectorAll('input[name="meal_option"]').forEach(rb => rb.checked = false);
      }
    }

    lunchCheckbox.addEventListener("change", toggleMealOptionSection);

    // Also trigger on page load (important if data is pre-filled)
    toggleMealOptionSection();
  });

  // Form validation logic
  document.addEventListener("DOMContentLoaded", () => {
    const orderForm = document.getElementById("orderForm");

    orderForm.addEventListener("submit", function (e) {
      const lunchChecked = document.getElementById("meal_lunch").checked;
      const dinnerChecked = document.getElementById("meal_dinner").checked;
      const breakfastChecked = document.getElementById("meal_breakfast").checked;

      if (!lunchChecked && !dinnerChecked && !breakfastChecked) {
        e.preventDefault();
        alert("Please select at least one meal (Lunch, Dinner, or Breakfast).");
        return;
      }

      if (lunchChecked) {
        const mealOptionSelected = document.querySelector('input[name="meal_option"]:checked');
        if (!mealOptionSelected) {
          e.preventDefault();
          alert("Please select a meal option (Egg, Chicken, Vegetarian) for Lunch.");
        }
      }
    });
  });
</script>


</body>
</html>
