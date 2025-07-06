<?php
date_default_timezone_set('Asia/Colombo');
?>
<div class="w-full bg-white border-b shadow p-4 flex items-center justify-between">
  <!-- Left: Logo and Title -->
  <div class="flex items-center space-x-6">
    <div class="flex items-center space-x-2">
      <div class="text-[#ED1B24] text-2xl font-bold">ECW</div>
      <div class="text-[#2E3095] text-xl font-semibold">MEAL ORDER APP – SUPER ADMIN</div>
    </div>

    <!-- Navigation Links -->
    <div class="flex space-x-4 ml-8">
      <a href="dashboard.php" class="text-[#2E3095] hover:underline font-medium">Dashboard</a>
      <a href="manage_admins.php" class="text-[#2E3095] hover:underline font-medium">Manage Admins</a>
      <a href="create_admin.php" class="text-[#2E3095] hover:underline font-medium">Create Admin</a>
      <a href="../pin_manage/order_pin.php" class="text-[#2E3095] hover:underline font-medium">Order PINs</a>
      <a href="../pin_manage/issue_pin.php" class="text-[#2E3095] hover:underline font-medium">Issue PINs</a>
      <a href="../admin/download_report.php" class="text-[#2E3095] hover:underline font-medium">Reports</a>
    </div>
  </div>

  <!-- Right: Super Admin Dropdown -->
  <div class="relative">
    <button id="userMenu" class="bg-[#2E3095] text-white px-4 py-2 rounded-lg">Super Admin ▾</button>
    <div id="dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow-md z-10">
      <a href="change_password.php" class="block px-4 py-2 hover:bg-gray-100">Change Password</a>
      <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100">Logout</a>
    </div>
  </div>
</div>

<script>
  document.getElementById("userMenu").addEventListener("click", () => {
    document.getElementById("dropdown").classList.toggle("hidden");
  });
</script>
