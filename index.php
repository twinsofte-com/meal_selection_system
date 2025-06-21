<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Meal QR System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900">

  <div class="min-h-screen flex flex-col justify-center items-center text-center px-4">
    <img src="img/logo.png" alt="Logo" class="w-24 h-24 mb-4">

    <h1 class="text-3xl font-bold mb-2">QR Meal Management System</h1>
    <p class="text-lg mb-10 text-gray-600">ඔබගේ පාලනය තෝරන්න</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-2xl w-full">
      
      <!-- Order -->
      <a href="order/order_login.php" class="bg-white rounded-xl shadow hover:shadow-xl transition p-6 text-center">
        <i class="fas fa-utensils text-green-500 text-5xl mb-4"></i>
        <h2 class="text-xl font-semibold">Order ඇනවුම්</h2>
        <p class="text-gray-600 mt-2">Security officer panel</p>
      </a>

      <!-- Issue -->
      <a href="issue/" class="bg-white rounded-xl shadow hover:shadow-xl transition p-6 text-center">
        <i class="fas fa-check-circle text-blue-500 text-5xl mb-4"></i>
        <h2 class="text-xl font-semibold">Issue නිකුත් කිරීම</h2>
        <p class="text-gray-600 mt-2">Dining room panel</p>
      </a>

    </div>

    <footer class="mt-16 text-sm text-gray-500">
      Powered by <a href="https://twinsofte.com" target="_blank" class="text-blue-600">Twinsofte.com</a>. All rights reserved.
    </footer>
  </div>

</body>
</html>
