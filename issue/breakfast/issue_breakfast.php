<?php
/** @var \mysqli $conn */
date_default_timezone_set('Asia/Colombo');
require '../../admin/db.php';
include_once '../../admin/include/date.php';
include_once '../validate_issue_session.php';

$issue_date = date('Y-m-d');

// Summary values
$q = fn($sql) => (int) ($conn->query($sql)->fetch_assoc()['cnt'] ?? 0);

// ✅ Only count pre-ordered meals that are not already issued
$totalOrdered = $q("SELECT COUNT(*) cnt FROM staff_meals 
                    WHERE breakfast = 1 
                      AND manual_breakfast = 0 
                      AND meal_date = '$issue_date'");

$totalIssued = $q("SELECT COUNT(*) cnt FROM staff_meals 
                   WHERE breakfast_received = 1 
                     AND meal_date = '$issue_date'");

$totalExtra = $q("SELECT COUNT(*) cnt FROM staff_meals 
                  WHERE breakfast_received = 1 
                    AND manual_breakfast = 1 
                    AND meal_date = '$issue_date'");

$balance = $totalOrdered - $totalIssued; // Prevent negative
$pending = $q("SELECT COUNT(*) cnt FROM staff_meals 
               WHERE breakfast = 1 
                 AND breakfast_received = 0 
                 AND manual_breakfast = 0 
                 AND meal_date = '$issue_date'");

$summary = [
  'issued' => ['value' => $balance, 'total' => $totalOrdered], // Balance / Total
  'manual' => ['value' => $totalIssued], // All issued (pre + extra)
  'pending' => ['value' => $pending],
  'extra' => ['value' => $totalExtra],
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Issue Breakfast — <?= $issue_date ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/html5-qrcode"></script>
</head>

<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col p-2">

  <!-- Alert -->
  <div id="alertBox"
    class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-xs p-3 rounded text-white text-center font-semibold">
  </div>

  <!-- Show alert if extra meals were issued -->
  <?php if ($totalExtra > 0): ?>
    <div
      class="w-full max-w-5xl mx-auto mt-4 px-4 py-3 border-l-4 border-red-500 bg-red-100 text-red-800 rounded shadow text-sm font-medium font-sans flex items-center justify-between">
      <span>
        ⚠️ <?= $totalExtra ?> extra meal<?= $totalExtra > 1 ? 's have' : ' has' ?> been issued. Please arrange additional
        meals accordingly.
      </span>
      <button onclick="this.parentElement.remove()"
        class="text-red-600 hover:text-red-800 text-xl leading-none font-bold">&times;</button>
    </div>
  <?php endif; ?>

  <!-- Container -->
  <div class="bg-white rounded-lg shadow-md flex flex-col md:flex-row overflow-hidden w-full max-w-5xl mx-auto mt-4">

    <!-- Scanner & Manual -->
    <div class="w-full md:w-1/2 p-4 border-b md:border-b-0 md:border-r border-gray-300">
      <h2 class="font-bold text-lg mb-2">Scan or Manual Entry</h2>
      <label for="cameraSelect" class="block font-medium mb-1">Camera</label>
      <select id="cameraSelect" class="w-full border rounded p-2 mb-3"></select>

      <!-- Camera Preview -->
      <div id="preview"
        class="h-40 sm:h-48 md:h-64 max-h-[250px] border-4 border-dashed rounded bg-gray-50 overflow-hidden mb-3"></div>

      <!-- Employee Info -->
      <div id="employee-info" class="hidden text-center mb-3">
        <p><strong>Name:</strong> <span id="empName"></span></p>
        <p><strong>ID:</strong> <span id="empID"></span></p>
        <button onclick="resetScan()" class="mt-2 px-4 py-2 bg-yellow-500 text-white rounded">Reset</button>
      </div>

      <!-- Buttons -->
      <div class="flex flex-col gap-2">
        <button onclick="openManualModal()" class="w-full py-2 bg-gray-200 rounded text-sm">Manual Entry</button>
        <button onclick="stopCamera()" class="w-full py-2 bg-red-600 text-white rounded text-sm">Stop Camera</button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="w-full md:w-1/2 p-4">
      <h2 class="text-lg font-bold mb-4">Summary — <?= $issue_date ?></h2>
      <div class="grid grid-cols-2 gap-4 text-white">
        <?php
        $colors = ['issued' => 'green', 'manual' => 'blue', 'pending' => 'yellow', 'extra' => 'red'];
        $titles = [
          'issued' => 'Balance / Total',
          'manual' => 'Issued',
          'pending' => 'Pending',
          'extra' => 'Extra Orders'
        ];

        foreach ($summary as $k => $v): ?>
          <div onclick="openDetailsModal('<?= $k ?>')"
            class="p-4 rounded cursor-pointer bg-<?= $colors[$k] ?>-500 hover:opacity-90 transition">
            <div class="text-sm font-semibold"><?= $titles[$k] ?></div>
            <div class="text-xl font-bold">
              <?= $k === 'issued' ? "{$v['value']} / {$v['total']}" : $v['value'] ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Modals -->
  <div id="manualModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <form id="manualForm" class="bg-white p-6 rounded shadow-lg w-80">
      <h2 class="font-bold text-lg mb-4">Manual Entry</h2>
      <input id="manualStaffID" name="staff_id" placeholder="Staff ID" required class="border p-2 w-full mb-4" />
      <input type="hidden" name="manual" value="1" />
      <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded">Submit</button>
      <button type="button" onclick="closeManualModal()" class="mt-2 text-gray-600 w-full">Cancel</button>
    </form>
  </div>

  <div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-80 text-center shadow-lg">
      <p id="confirmText" class="mb-4 text-lg font-semibold text-gray-800"></p>
      <div class="flex justify-center space-x-4">
        <button id="confirmYes" class="px-4 py-2 bg-green-600 text-white rounded">Yes</button>
        <button onclick="closeConfirmModal()" class="px-4 py-2 bg-gray-400 text-white rounded">No</button>
      </div>
    </div>
  </div>

  <div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center">
    <div class="bg-white max-w-md w-full mx-4 rounded-lg shadow-lg p-6 overflow-y-auto max-h-[80vh]">
      <div class="flex justify-between items-center mb-4">
        <h3 id="detailTitle" class="text-lg font-semibold text-gray-700"></h3>
        <div class="flex items-center gap-2">
          <button onclick="downloadDetailPDF()"
            class="text-sm px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
            Download PDF
          </button>
          <button onclick="closeDetailModal()" class="text-gray-500 hover:text-red-500 text-xl">&times;</button>
        </div>
      </div>

      <ul id="detailList" class="space-y-2 text-sm"></ul>
    </div>
  </div>
  <!-- Scripts -->
  <script>
    const MEAL_TYPE = 'breakfast';
    const CONFIRM_SCRIPT = 'confirm_breakfast_issue.php';
  </script>

  <script src="../js/qrcode.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script>
    let currentStaffId = null;
    let isManual = false;

    function showAlert(msg, type = 'success') {
      const alertBox = document.getElementById('alertBox');
      alertBox.textContent = msg;
      alertBox.className = `fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-xs p-3 rounded text-white text-center font-semibold ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
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

    function resetScan() {
      currentStaffId = null;
      document.getElementById('employee-info').classList.add('hidden');
      document.getElementById('empName').textContent = '';
      document.getElementById('empID').textContent = '';
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

    function openDetailsModal(type) {
      const titles = {
        issued: 'Issued All',
        manual: 'Issued All',
        pending: 'Pending',
        extra: 'Extra Issued',
      };

      const colors = {
        issued: 'text-green-600',
        manual: 'text-blue-600',
        pending: 'text-yellow-600',
        extra: 'text-red-600',
      };

      const detailModal = document.getElementById('detailModal');
      const detailTitle = document.getElementById('detailTitle');
      const detailList = document.getElementById('detailList');

      fetch(`../meal_details.php?type=${type}`, {
        cache: 'no-store'
      })
        .then(res => res.json())
        .then(data => {
          if (!Array.isArray(data) || data.length === 0) {
            detailList.innerHTML = `<li class="text-gray-500 italic">No records found.</li>`;
          } else {
            detailList.innerHTML = data.map(item => {
              const isExtra = parseInt(item.manual_breakfast) === 1 && item.received === 'yes';

              const tag = isExtra
                ? '<span class="ml-2 text-xs bg-red-200 text-red-800 px-2 py-1 rounded">Extra</span>'
                : '';

              return `<li class="border p-2 rounded bg-gray-50 ${isExtra ? 'text-red-600 font-bold' : 'text-gray-800'}">
                ${item.staff_id} — ${item.name} ${tag}
                <span class="font-bold float-right">${item.received === 'yes' ? '✔' : '✘'}</span>
              </li>`;
            }).join('');


          }

          detailTitle.innerHTML = `<span class="${colors[type]}">${titles[type]}</span>`;
          detailModal.classList.remove('hidden');
          detailModal.classList.add('flex');
        })
        .catch(error => {
          console.error('Error fetching data:', error);
          detailList.innerHTML = `<li class="text-gray-500 italic">Error fetching records.</li>`;
          detailModal.classList.remove('hidden');
          detailModal.classList.add('flex');
        });
    }

    function closeDetailModal() {
      document.getElementById('detailModal').classList.add('hidden');
      document.getElementById('detailModal').classList.remove('flex');
    }

    async function downloadDetailPDF() {
      const modalContent = document.querySelector('#detailModal > div');

      const { jsPDF } = window.jspdf;
      const doc = new jsPDF('p', 'pt', 'a4');

      await html2canvas(modalContent, { scale: 2 }).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const pageWidth = doc.internal.pageSize.getWidth();
        const imgWidth = pageWidth - 40;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;

        doc.addImage(imgData, 'PNG', 20, 20, imgWidth, imgHeight);
        doc.save('Meal_Issue_Report.pdf');
      });
    }


  </script>
  <!-- Footer -->
  <?php include '../include/footer.php'; ?>
</body>

</html>