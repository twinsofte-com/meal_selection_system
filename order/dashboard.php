<?php
require_once '../admin/db.php';
include_once '../admin/include/date.php';
session_start();
if (!isset($_SESSION['order_user'])) {
  header("Location: order_login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Order Dashboard | ඇනවුම් පුවරුව</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-red: #ED1B24;
      --primary-blue: #2E3095;
    }

    .meal-box {
      position: relative;
    }

    .meal-checkbox {
      position: absolute;
      pointer-events: none;
      opacity: 0;
    }

    /* Base meal card styling */
    .meal-label {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      height: 280px;
      padding: 1rem;
      border-radius: 1.5rem;
      border: 3px dashed #cbd5e1;
      /* Gray dashed frame by default */
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      transition: border-color 0.3s, background-color 0.3s;
      cursor: pointer;
      background-color: #f9fafb;
    }

    /* When checkbox is checked: green solid frame + soft background */
    .meal-checkbox:checked+.meal-label {
      border: 3px solid #22c55e;
      background-color: #f0fdf4;
    }

    /* Checkmark styling (remains hidden unless checked) */
    .meal-label .checkmark {
      width: 72px;
      height: 72px;
      border-radius: 1rem;
      align-items: center;
      justify-content: center;
      display: flex;
      border: 3px dashed #cbd5e1;
      /* Gray border always */
      background-color: transparent;
      transition: border 0.3s, background-color 0.3s;
    }

    /* When checkbox is checked – make checkmark box green */
    .meal-checkbox:checked+.meal-label .checkmark {
      background-color: #22c55e;
      border: 3px solid #22c55e;
    }

    /* Checkmark icon size */
    .meal-label .checkmark svg {
      width: 36px;
      height: 36px;
      color: white;
    }

    /* Only show the tick icon when checked */
    .meal-checkbox:checked+.meal-label .checkmark svg {
      display: block;
    }

    /* Show checkmark when checked */
    .meal-checkbox:checked+.meal-label .checkmark {
      display: flex;
    }
  </style>

</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
  <header class="bg-blue-700 text-white py-4 px-6 flex justify-between items-center shadow">
    <h1 class="text-xl font-bold">Order Dashboard <span class="block text-sm">ඇනවුම් පුවරුව</span></h1>
    <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Logout</a>
  </header>

  <main class="flex-grow flex flex-col md:flex-row">
    <!-- Left Panel -->
    <div class="md:w-1/3 w-full bg-white p-4 border-r">
      <div id="qr-preview-box"
        class="h-48 w-full border-4 border-dashed border-gray-400 rounded-lg flex items-center justify-center mb-4">
        <div id="preview" class="w-full h-full rounded"></div>
      </div>

      <div id="employee-info" class="hidden">
        <h2 class="text-lg font-bold text-[--primary-blue]">Employee Details</h2>
        <p class="mt-2 text-gray-800"><strong>Name:</strong> <span id="empName">-</span></p>
        <p class="text-gray-800"><strong>ID:</strong> <span id="empID">-</span></p>
        <button onclick="resetScan()" class="mt-3 bg-yellow-500 text-white px-4 py-2 rounded w-full">Reset / යළි පරීක්ෂා
          කරන්න</button>
      </div>

      <div class="mt-6 space-y-2">
        <button onclick="openManualModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded w-full">Forgot Meal
          Card?</button>
        <button onclick="openManualModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded w-full">Manual
          Order</button>
      </div>
    </div>

    <!-- Right Panel -->
    <div class="flex-1 p-6">
      <form action="order_process.php" method="POST" id="orderForm" class="hidden">
        <input type="hidden" name="staff_id" id="staff_id">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

          <!-- LUNCH -->
          <!-- LUNCH -->
          <div class="meal-box">
            <input type="checkbox" id="meal_lunch" name="meals[]" value="2" class="meal-checkbox">
            <label for="meal_lunch" class="meal-label bg-yellow-100"> <!-- FIXED -->
              <div class="text-center">
                <p class="meal-date"><?= date("D d M") ?><br><span class="text-sm">අද</span></p>
                <h3 class="meal-title">LUNCH</h3>
                <span class="meal-sub">දහවල්</span>
              </div>
              <div class="checkmark">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </label>
          </div>

          <!-- DINNER -->
          <div class="meal-box">
            <input type="checkbox" id="meal_dinner" name="meals[]" value="3" class="meal-checkbox">
            <label for="meal_dinner" class="meal-label bg-orange-100"> <!-- FIXED -->
              <div class="text-center">
                <p class="meal-date"><?= date("D d M") ?><br><span class="text-sm">අද</span></p>
                <h3 class="meal-title">DINNER</h3>
                <span class="meal-sub">රාත්‍රී</span>
              </div>
              <div class="checkmark">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </label>
          </div>

          <!-- BREAKFAST -->
          <div class="meal-box">
            <input type="checkbox" id="meal_breakfast" name="meals[]" value="1" class="meal-checkbox">
            <label for="meal_breakfast" class="meal-label bg-blue-100"> <!-- FIXED -->
              <div class="text-center">
                <p class="meal-date"><?= date("D d M", strtotime("+1 day")) ?><br><span class="text-sm">හෙට</span></p>
                <h3 class="meal-title">BREAKFAST</h3>
                <span class="meal-sub">උදේ</span>
              </div>
              <div class="checkmark">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </label>
          </div>


        </div>
        <div id="meal-options" class="mt-6 space-y-4 hidden">
          <label class="inline-flex items-center">
            <input type="radio" name="meal_option" value="egg" class="form-radio text-yellow-500 w-5 h-5">
            <span class="ml-3 font-semibold text-lg">🥚 Egg</span>
          </label>
          <br>
          <label class="inline-flex items-center">
            <input type="radio" name="meal_option" value="chicken" class="form-radio text-red-600 w-5 h-5">
            <span class="ml-3 font-semibold text-lg">🍗 Chicken</span>
          </label>
          <br>
          <label class="inline-flex items-center">
            <input type="radio" name="meal_option" value="vegetarian" class="form-radio text-green-600 w-5 h-5">
            <span class="ml-3 font-semibold text-lg">🥦 Vegetarian / ශාකාහාරයෙක්ද?</span>
          </label>
        </div>
        <div class="mt-6">
          <button type="submit"
            class="bg-green-500 hover:bg-green-600 text-white w-full py-3 rounded-xl text-xl font-bold">
            CONFIRM / තහවුරු කරන්න
          </button>
        </div>


      </form>

      <div id="reset-btn" class="hidden">
        <div class="mt-4">
          <button onclick="clearSelections()"
            class="bg-red-500 text-white px-4 py-2 rounded-xl hover:bg-red-600 transition">
            Clear Selection
          </button>
        </div>
      </div>

    </div>
  </main>

  <footer class="text-center text-sm text-gray-500 py-4">
    Powered by Twinsofte.com. All rights reserved.
  </footer>

  <!-- Modal for Manual Order -->
  <div id="manualModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
      <h2 class="text-lg font-bold mb-4">Manual Order</h2>
      <div class="flex space-x-4 mb-4">
        <button onclick="selectPrefix('ECW-')" class="tab-btn bg-blue-100 px-3 py-1 rounded">ECW-</button>
        <button onclick="selectPrefix('INT-')" class="tab-btn bg-gray-100 px-3 py-1 rounded">INT-</button>
      </div>
      <div>
        <label class="block mb-2 font-bold">Enter ID</label>
        <div class="flex mb-2">
          <span id="prefix" class="bg-gray-200 px-3 py-2 rounded-l">ECW-</span>
          <input type="text" id="manualID" class="w-full border px-3 py-2 rounded-r" placeholder="e.g., 0099">
        </div>
        <div class="text-sm text-gray-600 mb-2">Name: <span id="manualName">-</span></div>
      </div>
      <div class="grid grid-cols-3 gap-2 mt-4">
        <?php for ($i = 1; $i <= 9; $i++): ?>
          <button onclick="appendNumber(<?= $i ?>)" class="bg-gray-200 py-2 rounded text-lg font-bold"><?= $i ?></button>
        <?php endfor; ?>
        <button onclick="clearNumber()" class="bg-red-200 py-2 rounded text-lg">C</button>
        <button onclick="appendNumber(0)" class="bg-gray-200 py-2 rounded text-lg font-bold">0</button>
        <button onclick="confirmManual()" class="bg-green-500 text-white py-2 rounded text-lg">OK</button>
      </div>
      <button onclick="closeManualModal()"
        class="mt-4 w-full text-center text-sm text-red-600 underline">Cancel</button>
    </div>
  </div>

  <script src="https://unpkg.com/html5-qrcode"></script>
  <script>
    let scanner;
    const maxRetries = 3;
    let retryCount = 0;

    function onScanSuccess(decodedText) {
      const qrCode = decodedText.trim();
      fetch(`get_staff.php?qr_code=${encodeURIComponent(qrCode)}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.getElementById("staff_id").value = data.staff_id;
            document.getElementById("empID").innerText = data.staff_id;
            document.getElementById("empName").innerText = data.name;
            document.getElementById("employee-info").classList.remove("hidden");
            document.getElementById("orderForm").classList.remove("hidden");
            document.getElementById("reset-btn").classList.remove("hidden");
            checkMealPreference(data.staff_id);
          } else {
            alert("Staff not found for this QR code.");
            handleScanError();
          }
        })
        .catch(() => {
          alert("Error retrieving staff info.");
          handleScanError();
        });
    }

    function confirmManual() {
      const prefix = document.getElementById("prefix").innerText;
      const manualID = document.getElementById("manualID").value.trim();
      const fullID = prefix + manualID;

      if (!manualID) {
        alert("Please enter a valid ID.");
        return;
      }

      fetch(`get_staff.php?staff_id=${fullID}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.getElementById("staff_id").value = fullID;
            document.getElementById("empName").innerText = data.name;
            document.getElementById("empID").innerText = fullID;
            document.getElementById("employee-info").classList.remove("hidden");
            document.getElementById("orderForm").classList.remove("hidden");
            document.getElementById("reset-btn").classList.remove("hidden");
            closeManualModal();
            checkMealPreference(fullID);
          } else {
            alert("User not found.");
          }
        })
        .catch(() => {
          alert("Error retrieving employee info.");
        });
    }

    function checkMealPreference(staffID) {
      fetch(`check_meal.php?staff_id=${staffID}`)
        .then(res => res.json())
        .then(response => {
          if (!response.exists) return;

          clearSelections();

          const today = response.today || {};
          const tomorrow = response.tomorrow || {};

          document.querySelectorAll('.meal-checkbox').forEach(cb => cb.disabled = false);
          document.querySelectorAll('input[name="meal_option"]').forEach(rb => rb.disabled = false);

          if (today.lunch == 1) {
            document.getElementById("meal_lunch").checked = true;
            document.getElementById("meal-options").classList.remove("hidden");
            toggleMealOptionRequired(true);
          }

          if (today.dinner == 1) {
            document.getElementById("meal_dinner").checked = true;
          }

          if (tomorrow.breakfast == 1) {
            document.getElementById("meal_breakfast").checked = true;
          }

          const options = ['egg', 'chicken', 'vegetarian'];
          options.forEach(opt => {
            const radio = document.querySelector(`input[name="meal_option"][value="${opt}"]`);
            if (today[opt] == 1 || tomorrow[opt] == 1) {
              radio.checked = true;
            }
          });

          document.getElementById("reset-btn").classList.remove("hidden");
        })
        .catch(() => {
          alert("Error checking meal preference.");
        });
    }

    function handleScanError() {
      retryCount++;
      if (retryCount < maxRetries) {
        alert("User not found. Retrying...");
        setTimeout(resetScan, 1000);
      } else {
        alert("Maximum retries reached.");
        resetScan();
      }
    }

    function resetScan() {
      document.getElementById("employee-info").classList.add("hidden");
      document.getElementById("orderForm").classList.add("hidden");
      document.getElementById("staff_id").value = '';
      document.getElementById("empName").innerText = '-';
      document.getElementById("empID").innerText = '-';
      document.getElementById("reset-btn").classList.add("hidden");
      clearSelections();
      scanner.render(onScanSuccess);
    }

    function clearSelections() {
      document.querySelectorAll('.meal-checkbox').forEach(cb => {
        cb.checked = false;
        cb.disabled = false;
      });

      document.querySelectorAll('input[name="meal_option"]').forEach(rb => {
        rb.checked = false;
        rb.disabled = false;
        rb.required = false;
      });

      document.getElementById("meal-options").classList.add("hidden");
    }

    function openManualModal() {
      document.getElementById("manualModal").classList.remove("hidden");
    }

    function closeManualModal() {
      document.getElementById("manualModal").classList.add("hidden");
      clearNumber();
    }

    function selectPrefix(prefix) {
      document.getElementById("prefix").textContent = prefix;
    }

    function appendNumber(num) {
      document.getElementById("manualID").value += num;
    }

    function clearNumber() {
      document.getElementById("manualID").value = '';
    }

    function toggleMealOptionRequired(status) {
      const radios = document.querySelectorAll('input[name="meal_option"]');
      radios.forEach(radio => {
        radio.required = status;
      });
    }

    window.onload = function () {
      scanner = new Html5QrcodeScanner("preview", { fps: 10, qrbox: 250 });
      scanner.render(onScanSuccess);

      // Lunch checkbox toggle meal options visibility & required flag
      const lunchCheckbox = document.getElementById("meal_lunch");
      const mealOptions = document.getElementById("meal-options");

      lunchCheckbox.addEventListener("change", function () {
        if (this.checked) {
          mealOptions.classList.remove("hidden");
          toggleMealOptionRequired(true);
        } else {
          mealOptions.classList.add("hidden");
          toggleMealOptionRequired(false);
          document.querySelectorAll('input[name="meal_option"]').forEach(rb => rb.checked = false);
        }
      });
    };
  </script>