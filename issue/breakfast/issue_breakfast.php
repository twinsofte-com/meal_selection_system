<?php
date_default_timezone_set('Asia/Colombo');
require '../../admin/db.php';
// session_start();
// if (!isset($_SESSION['order_user'])) header('Location: ../order_login.php');

$today = date('Y-m-d');
$q = fn($sql) => (int)$conn->query($sql)->fetch_assoc()['cnt'];
$summary = [
  'issued'  => $q("SELECT COUNT(*) cnt FROM staff_meals WHERE breakfast_received=1 AND meal_date='$today'"),
  'manual'  => $q("SELECT COUNT(*) cnt FROM staff_meals WHERE breakfast_received=1 AND manual_order=1 AND meal_date='$today'"),
  'pending' => $q("SELECT COUNT(*) cnt FROM staff_meals WHERE breakfast=1 AND breakfast_received=0 AND meal_date='$today'"),
  'extra'   => $q("SELECT COUNT(*) cnt FROM staff_meals WHERE date='$today' AND breakfast='1' AND manual_order ='1'")
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Issue Breakfast — <?= $today ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body class="bg-gray-100 p-4">
  <div id="alertBox" class="hidden mb-4 max-w-4xl mx-auto p-4 rounded text-white text-center font-semibold"></div>

  <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="md:flex">
      <!-- Left: QR Scanner + Manual -->
      <div class="md:w-1/3 p-4 border-r">
        <label for="cameraSelect" class="block font-semibold">Camera:</label>
        <select id="cameraSelect" class="w-full border p-2 mb-2"></select>
        <div id="preview" class="h-48 border-4 border-dashed rounded mb-4"></div>
        <div id="employee-info" class="hidden text-center">
          <p><strong>Name:</strong> <span id="empName"></span></p>
          <p><strong>ID:</strong> <span id="empID"></span></p>
          <button onclick="resetScan()" class="mt-2 px-4 py-2 bg-yellow-500 text-white rounded">Reset</button>
        </div>
        <div class="flex flex-col gap-2">
          <button onclick="openManualModal()" class="w-full py-2 bg-gray-200 rounded">Manual Entry</button>
          <button onclick="stopCamera()" class="w-full py-2 bg-red-600 text-white rounded">Stop Camera</button>
        </div>
      </div>

      <!-- Right: Summary -->
      <div class="md:flex-1 p-4">
        <h2 class="text-xl font-semibold mb-4">Breakfast Summary — <?= $today ?></h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-white">
          <?php
          $colors = ['issued'=>'green','manual'=>'red','pending'=>'yellow','extra'=>'blue'];
          $titles = [
            'issued' => 'Issued Meals',
            'manual' => 'Manual Issued',
            'pending' => 'Pending Meals',
            'extra' => 'Manual Order Total'
          ];
          foreach ($summary as $k => $v): ?>
            <div onclick="showDetails('<?= $k ?>')" class="p-4 rounded cursor-pointer bg-<?= $colors[$k] ?>-500">
              <div class="font-bold"><?= $titles[$k] ?></div>
              <div class="text-2xl"><?= $v ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Manual Entry Modal -->
  <div id="manualModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <form id="manualForm" class="bg-white p-6 rounded shadow-lg w-80">
      <h2 class="font-bold text-lg mb-4">Manual Entry</h2>
      <input id="manualStaffID" name="staff_id" placeholder="Staff ID" required class="border p-2 w-full mb-4"/>
      <input type="hidden" name="manual" value="1"/>
      <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded">Submit</button>
      <button type="button" onclick="closeManualModal()" class="mt-2 text-gray-600">Cancel</button>
    </form>
  </div>

  <!-- Confirm Modal -->
  <div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-80 text-center shadow-lg">
      <p id="confirmText" class="mb-4 text-lg font-semibold text-gray-800"></p>
      <div class="flex justify-center space-x-4">
        <button id="confirmYes" class="px-4 py-2 bg-green-600 text-white rounded">Yes</button>
        <button onclick="closeConfirmModal()" class="px-4 py-2 bg-gray-400 text-white rounded">No</button>
      </div>
    </div>
  </div>

  <!-- Detail Modal -->
  <div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center">
    <div class="bg-white max-w-md w-full mx-4 rounded-lg shadow-lg p-6 overflow-y-auto max-h-[80vh]">
      <div class="flex justify-between items-center mb-4">
        <h3 id="detailTitle" class="text-lg font-semibold text-gray-700"></h3>
        <button onclick="closeDetailModal()" class="text-gray-500 hover:text-red-500 text-xl">&times;</button>
      </div>
      <ul id="detailList" class="space-y-2 text-sm"></ul>
    </div>
  </div>

  <script src="../js/qrcode.js"></script>
  <script>
    let currentStaffId = null;
    let isManual = false;

    function showAlert(msg, type='success') {
      const alertBox = document.getElementById('alertBox');
      alertBox.textContent = msg;
      alertBox.className = `fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md p-4 rounded text-white text-center font-semibold ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
      alertBox.classList.remove('hidden');
      setTimeout(() => alertBox.classList.add('hidden'), 3000);
    }

    function openManualModal() {
      document.getElementById('manualModal').classList.remove('hidden');
    }

    function closeManualModal() {
      document.getElementById('manualModal').classList.add('hidden');
    }

    function closeConfirmModal() {
      document.getElementById('confirmModal').classList.add('hidden');
    }

    function showDetails(type) {
        const titles = {
            issued: 'Issued Meals (Green)',
            manual: 'Admin Manual Requests (Red)',
            pending: 'Pending Meals (Yellow)',
            extra: 'Manual Order Total (Blue)',
        };
        const colors = {
            issued: 'text-green-600',
            manual: 'text-red-600',
            pending: 'text-yellow-600',
            extra: 'text-blue-600',
        };

        fetch(`../meal_details.php?type=${type}`)
            .then(res => res.json())
            .then(data => {
            document.getElementById('detailTitle').innerHTML = `<span class="${colors[type]}">${titles[type]}</span>`;
            const list = document.getElementById('detailList');
            list.innerHTML = '';

            if (!data.length) {
                list.innerHTML = `<li class="text-gray-500 italic">No records found.</li>`;
            } else {
                data.forEach(item => {
                const li = document.createElement('li');
                li.className = `border p-2 rounded ${colors[type]} bg-gray-50`;
                li.innerHTML = `${item.staff_id} — ${item.name} 
                                <span class="font-bold">${item.received === 'yes' ? '✔' : '✘'}</span>`;
                list.appendChild(li);
                });
            }

            const modal = document.getElementById('detailModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            });
        }

    function closeDetailModal() {
      const modal = document.getElementById('detailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('manualForm').addEventListener('submit', function (e) {
      e.preventDefault();
      const staffId = document.getElementById('manualStaffID').value.trim();
      if (!staffId) {
        showAlert("Please enter a staff ID", "error");
        return;
      }
      currentStaffId = staffId;
      isManual = true;
      closeManualModal();
      document.getElementById('confirmText').textContent = `Confirm issuing breakfast to ${staffId}?`;
      document.getElementById('confirmModal').classList.remove('hidden');
    });

    document.getElementById('confirmYes').addEventListener('click', function () {
      if (!currentStaffId) return;
      fetch('confirm_breakfast_issue.php', {
        method: 'POST',
        body: new URLSearchParams({
          staff_id: currentStaffId,
          manual: isManual ? '1' : '0'
        })
      })
      .then(res => res.text())
      .then(text => {
        if (text.trim() === 'success') {
          showAlert("Breakfast issued successfully", "success");
          setTimeout(() => location.reload(), 1000);
        } else {
          showAlert(text.trim(), "error");
        }
        closeConfirmModal();
        resetScan();
      }).catch(err => {
        showAlert("Server error: " + err.message, "error");
      });
    });

    function resetScan() {
      currentStaffId = null;
      document.getElementById('employee-info').classList.add('hidden');
      document.getElementById('empName').textContent = '';
      document.getElementById('empID').textContent = '';
    }
  </script>
</body>
</html>
