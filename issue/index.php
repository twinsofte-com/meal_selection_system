<?php
require_once '../admin/db.php';
include_once '../admin/include/date.php';
session_start();
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = trim($_POST['pin_code']);

    $stmt = $conn->prepare("SELECT * FROM pin_users WHERE pin_code = ? AND role = 'issue'");
    $stmt->bind_param("s", $pin);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $_SESSION['issue_user'] = $user['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid PIN. Please try again. | වැරදි පින් කේතයකි. නැවත උත්සහ කරන්න.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Issue Login | ආහාර නිකුත් පිවිසුම</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-red: #ED1B24;
      --primary-blue: #2E3095;
    }
  </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-sm border-t-8 border-red-600">
    <div class="text-center mb-6">
      <img src="../img/logo.png" alt="Logo" class="w-16 h-16 mx-auto mb-3">
      <h1 class="text-2xl font-extrabold text-[--primary-blue]">Issue Login<br><span class="text-sm text-gray-600">ආහාර නිකුත් පිවිසුම</span></h1>
      <p class="text-sm text-gray-600">Enter your PIN code below<br><span class="text-xs text-gray-500">ඔබේ පින් කේතය ඇතුළත් කරන්න</span></p>
    </div>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 px-4 py-2 rounded text-center font-semibold mb-4 text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <input
        type="password"
        name="pin_code"
        maxlength="10"
        required
        placeholder="PIN / පින් කේතය"
        class="w-full px-4 py-3 border-2 border-[--primary-blue] rounded-lg text-center text-xl tracking-widest focus:outline-none focus:ring-2 focus:ring-[--primary-red]"
      >

      <button type="submit"
              class="w-full bg-[--primary-red] hover:bg-[--primary-blue] text-white font-bold py-2 rounded-lg transition duration-300">
        Login / පිවිසෙන්න
      </button>
    </form>

    <div class="text-center mt-6">
      <a href="../index.php" class="text-sm text-[--primary-blue] hover:underline">
        ← Back to Main Page / ප්‍රධාන පිටුවට
      </a>
    </div>
  </div>

</body>
</html>
