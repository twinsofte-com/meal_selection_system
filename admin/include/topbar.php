<?php
date_default_timezone_set('Asia/Colombo');
?>
<!-- include/topbar.php -->
<div class="w-full bg-white border-b shadow p-4 flex items-center justify-between">
  <!-- Left: Logo and Title -->
  <div class="flex items-center space-x-6">
    <div class="flex items-center space-x-2">
      <div class="text-[#ED1B24] text-2xl font-bold">ECW</div>
      <div class="text-[#2E3095] text-xl font-semibold">MEAL ORDER APP – ADMIN</div>
    </div>

    <!-- Navigation Links -->
    <div class="flex space-x-4 ml-8">
      <a href="dashboard.php" class="text-[#2E3095] hover:underline font-medium">Dashboard</a>
      <a href="register.php" class="text-[#2E3095] hover:underline font-medium">List</a>
      <a href="meanual_order.php" class="text-[#2E3095] hover:underline font-medium">Manual Order</a>
      <a href="meal_list.php" class="text-[#2E3095] hover:underline font-medium">Manual Order List</a>
      <a href="download_report.php" class="text-[#2E3095] hover:underline font-medium">Report</a>
      <a href="pin_manage/order_pin.php" class="text-[#2E3095] hover:underline font-medium">Order PINs</a>
      <a href="pin_manage/issue_pin.php" class="text-[#2E3095] hover:underline font-medium">Issue PINs</a>
    </div>

  </div>

  <!-- Right: Admin Dropdown Menu -->
  <div class="relative">
    <button id="userMenu" class="bg-[#2E3095] text-white px-4 py-2 rounded-lg">Admin ▾</button>
    <div id="dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow-md z-10">
      <a href="edit/edit_profile.php" class="block px-4 py-2 hover:bg-gray-100">Edit Profile</a>
      <a href="edit/change_password.php" class="block px-4 py-2 hover:bg-gray-100">Change Password</a>
      <a href="auth/logout.php" class="block px-4 py-2 hover:bg-gray-100">Logout</a>
    </div>
  </div>
</div>

<script>
  // Toggle the admin dropdown
  document.getElementById("userMenu").addEventListener("click", () => {
    document.getElementById("dropdown").classList.toggle("hidden");
  });
</script>
