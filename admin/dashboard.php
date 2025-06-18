<?php
include_once '../phpqrcode/qrlib.php';
require_once 'db.php';

// Get meal counts for Lunch and Dinner
$lunch_count_query = "SELECT COUNT(*) AS lunch_count FROM meal_issuance WHERE meal_type = 'Lunch' AND confirmed = 1 AND meal_date = CURDATE()";
$lunch_count_result = $conn->query($lunch_count_query);
$lunch_count = $lunch_count_result->fetch_assoc()['lunch_count'];

$dinner_count_query = "SELECT COUNT(*) AS dinner_count FROM meal_issuance WHERE meal_type = 'Dinner' AND confirmed = 1 AND meal_date = CURDATE()";
$dinner_count_result = $conn->query($dinner_count_query);
$dinner_count = $dinner_count_result->fetch_assoc()['dinner_count'];

// Handle staff registration via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_type = $_POST['staff_type'];
    $staff_id = $_POST['staff_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];

    $full_id = $staff_id;
    $default_preferences = '';
    $empty_qr_value = '';

    $stmt = $conn->prepare("INSERT INTO staff (staff_id, staff_type, name, phone_number, qr_code, meal_preferences) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $full_id, $staff_type, $name, $phone, $empty_qr_value, $default_preferences);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Staff registered successfully!'); window.location='dashboard.php';</script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - MEAL ORDER APP</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="text-gray-800 font-sans">

  <!-- Top Section -->
  <?php include 'include/topbar.php'; ?>

  <!-- Main Actions -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
    <button onclick="openRegisterModal()" class="bg-[#ED1B24] text-white p-6 rounded-2xl text-xl shadow hover:shadow-lg">Register</button>
    <a href="register.php" class="bg-[#2E3095] text-white p-6 rounded-2xl text-xl shadow hover:shadow-lg text-center">List</a>
    <a href="manual_order.php" class="bg-[#2E3095] text-white p-6 rounded-2xl text-xl shadow hover:shadow-lg text-center">Manual Order</a>
  </div>

  <!-- Order Summary -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-6 pb-10">
    <div class="border-l-4 border-[#ED1B24] p-4 bg-white shadow rounded-xl">
      <h2 class="text-lg font-semibold text-[#ED1B24]">Today Lunch</h2>
      <p class="text-2xl font-bold"><?php echo $lunch_count; ?> meals ordered</p>
    </div>
    <div class="border-l-4 border-[#2E3095] p-4 bg-white shadow rounded-xl">
      <h2 class="text-lg font-semibold text-[#2E3095]">Today Dinner</h2>
      <p class="text-2xl font-bold"><?php echo $dinner_count; ?> meals ordered</p>
    </div>
  </div>

  <!-- Register Modal -->
  <div id="registerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl w-full max-w-md p-6 relative">
      <h3 class="text-xl font-semibold text-[#2E3095] mb-4">Register New Staff</h3>
      <form method="POST" action="">
        <div class="mb-4">
          <label class="block text-gray-700">Staff Type</label>
          <select name="staff_type" id="staff_type" required class="w-full border p-2 rounded-lg">
              <option value="ECW">ECW (Internal)</option>
              <option value="INT">INT (External)</option>
          </select>
        </div>

        <div class="mb-4">
          <label class="block text-gray-700">Staff ID</label>
          <div class="flex">
              <span id="prefix" class="flex items-center px-3 bg-gray-100 border border-r-0 rounded-l-lg">ECW-</span>
              <input type="text" id="staff_id_input" class="flex-1 border rounded-r-lg p-2" required />
          </div>
          <input type="hidden" name="staff_id" id="full_staff_id" />
        </div>

        <div class="mb-4">
          <label class="block text-gray-700">Name</label>
          <input type="text" name="name" required class="w-full border p-2 rounded-lg" />
        </div>

        <div class="mb-4">
          <label class="block text-gray-700">Phone Number</label>
          <input type="text" name="phone" required class="w-full border p-2 rounded-lg" />
        </div>

        <div class="flex justify-end">
          <button type="submit" class="bg-[#2E3095] text-white px-4 py-2 rounded-lg hover:bg-[#1d2475]">Register</button>
        </div>
      </form>
      <button class="absolute top-2 right-2 text-xl" onclick="closeRegisterModal()">&times;</button>
    </div>
  </div>

  <!-- Manual Order Modal -->
<div id="manualModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white w-11/12 max-w-md rounded-lg shadow-lg p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Manual Order</h2>
    <form action="order_process.php" method="POST" onsubmit="return handleManualSubmit(this)">
      <label class="block mb-2 font-medium">Enter Staff ID</label>
      <input type="text" name="staff_id" class="w-full px-3 py-2 border border-gray-300 rounded mb-4" required>

      <label class="block mb-2 font-medium">Select Meals</label>
      <div class="flex space-x-3 mb-4">
        <label class="inline-flex items-center">
          <input type="checkbox" name="meals[]" value="Breakfast" class="mr-2"> Breakfast
        </label>
        <label class="inline-flex items-center">
          <input type="checkbox" name="meals[]" value="Lunch" class="mr-2"> Lunch
        </label>
        <label class="inline-flex items-center">
          <input type="checkbox" name="meals[]" value="Dinner" class="mr-2"> Dinner
        </label>
      </div>

      <label class="inline-flex items-center mb-4">
        <input type="checkbox" name="vegetarian" value="1" class="mr-2"> Vegetarian
      </label>

      <div class="flex justify-between">
        <button type="button" onclick="closeManualModal()" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Submit</button>
      </div>
    </form>
  </div>
</div>


  <script>
    function openRegisterModal() {
      document.getElementById("registerModal").classList.remove("hidden");
    }

    function closeRegisterModal() {
      document.getElementById("registerModal").classList.add("hidden");
    }

    const staffType = document.getElementById('staff_type');
    const staffIdInput = document.getElementById('staff_id_input');
    const fullStaffId = document.getElementById('full_staff_id');
    const prefix = document.getElementById('prefix');

    function updateStaffId() {
        const type = staffType.value;
        prefix.innerText = type + '-';
        fullStaffId.value = `${type}-${staffIdInput.value}`;
    }

    staffType.addEventListener('change', updateStaffId);
    staffIdInput.addEventListener('input', updateStaffId);

    
  </script>

</body>
</html>
