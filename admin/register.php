<?php
include_once 'validation/validation.php';
require_once '../phpqrcode/qrlib.php';
require_once 'db.php';
include_once 'include/date.php';

$message = "";

// Registration logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone_number'] ?? '');
  $staff_type = trim($_POST['staff_type'] ?? '');
  $staff_id = trim($_POST['staff_id'] ?? '');

  if ($name && $phone && $staff_type && $staff_id) {
    $full_id = $staff_id;

    // Check if staff_id already exists
    $stmt = $conn->prepare("SELECT id FROM staff WHERE staff_id = ?");
    $stmt->bind_param('s', $full_id);
    $stmt->execute();
    $exists = $stmt->get_result();
    if ($exists->num_rows > 0) {
      $message = "This Staff ID already exists.";
    } else {
      $insert = $conn->prepare("INSERT INTO staff (staff_id, name, phone_number, staff_type) VALUES (?, ?, ?, ?)");
      $insert->bind_param('ssss', $full_id, $name, $phone, $staff_type);
      if ($insert->execute()) {
        $message = "Staff registered successfully.";
      } else {
        $message = "Database error: " . $conn->error;
      }
    }
  } else {
    $message = "All fields are required.";
  }
}


// Load all staff
$staff_result = $conn->query("SELECT * FROM staff ORDER BY id DESC");

function addTextToQRCode($file, $text)
{
  $img = imagecreatefrompng($file);
  $font = 'font/arial.ttf';
  $font_size = 12;
  $black = imagecolorallocate($img, 0, 0, 0);
  $bbox = imagettfbbox($font_size, 0, $font, $text);
  $text_width = $bbox[2] - $bbox[0];
  $img_width = imagesx($img);
  $img_height = imagesy($img);
  $new_height = $img_height + 30;

  $new_img = imagecreatetruecolor($img_width, $new_height);
  $white = imagecolorallocate($new_img, 255, 255, 255);
  imagefill($new_img, 0, 0, $white);
  imagecopy($new_img, $img, 0, 0, 0, 0, $img_width, $img_height);

  $x = ($img_width - $text_width) / 2;
  imagettftext($new_img, $font_size, 0, $x, $new_height - 10, $black, $font, $text);

  imagepng($new_img, $file);
  imagedestroy($img);
  imagedestroy($new_img);
}
?>

<!-- Top Section -->
<?php include 'include/topbar.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Staff Registration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function updatePrefix() {
      const type = document.getElementById('staff_type').value;
      document.getElementById('prefix').innerText = type + "-";
    }

    function combineStaffID() {
      const type = document.getElementById('staff_type').value;
      const input = document.getElementById('staff_id_input').value;
      document.getElementById('full_staff_id').value = type + '-' + input;
    }

    function openRegisterModal() {
      document.getElementById('registerModal').classList.remove('hidden');
    }

    function closeRegisterModal() {
      document.getElementById('registerModal').classList.add('hidden');
    }
  </script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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

    .meal-checkbox:checked+.meal-label {
      background-color: #d1fae5;
      border-color: #10b981;
      font-weight: bold;
    }
  </style>
</head>

<body class="bg-gray-100">

  <div class="p-6">
    <?php if ($message): ?>
      <div class="bg-blue-100 border border-blue-300 text-blue-700 p-3 rounded mb-4">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <button onclick="openRegisterModal()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">+ Register
      Staff</button>

    <div class="mt-6 bg-white rounded shadow p-4">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-[#2E3095]">Registered Staff</h2>
        <input type="text" id="searchInput" placeholder="Search Staff..." class="border rounded p-2 w-64"
          onkeyup="filterStaff()" />
      </div>
      <div class="overflow-x-auto">
        <table class="table-auto w-full text-sm text-left">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2">Staff ID</th>
              <th class="p-2">Name</th>
              <th class="p-2">Phone</th>
              <th class="p-2">Type</th>
              <th class="p-2">QR</th>
              <th class="p-2">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $staff_result->fetch_assoc()): ?>
              <tr class="border-t">
                <td class="p-2"><?= htmlspecialchars($row['staff_id']) ?></td>
                <td class="p-2"><?= htmlspecialchars($row['name']) ?></td>
                <td class="p-2"><?= htmlspecialchars($row['phone_number']) ?></td>
                <td class="p-2"><?= htmlspecialchars($row['staff_type']) ?></td>
                <td class="p-2">
                  <?php if ($row['qr_code']): ?>
                    <?= $row['qr_code'] ?>
                  <?php else: ?>
                    <button onclick="openQrUploadModal(<?= $row['id'] ?>)"
                      class="text-[#2E3095] text-2xl font-bold">+</button>
                  <?php endif; ?>
                </td>
                <td class="p-2">
                  <button onclick='openEditModal(<?= json_encode($row) ?>)'
                    class="bg-yellow-500 text-white px-2 py-1 rounded text-sm mr-2 hover:bg-yellow-600">Edit</button>
                  <!-- <button onclick="openDeleteModal(<?= $row['id'] ?>)"
                    class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600">Delete</button> -->
                </td>

              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Register Modal -->
  <div id="registerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl w-full max-w-md p-6 relative">
      <h3 class="text-xl font-semibold text-[#2E3095] mb-4">Register New Staff</h3>
      <form method="POST" action="register.php" onsubmit="combineStaffID()">
        <div class="mb-4">
          <label class="block text-gray-700">Name</label>
          <input type="text" name="name" required class="w-full border p-2 rounded-lg" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700">Phone Number</label>
          <input type="text" name="phone_number" required class="w-full border p-2 rounded-lg" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700">Staff Type</label>
          <select name="staff_type" id="staff_type" onchange="updatePrefix()" required
            class="w-full border p-2 rounded-lg">
            <option value="ECW">ECW (Internal)</option>
            <option value="INT">INT (External)</option>
          </select>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700">Staff ID</label>
          <div class="flex">
            <span id="prefix" class="px-3 bg-gray-100 border border-r-0 rounded-l-lg">ECW-</span>
            <input type="text" id="staff_id_input" class="flex-1 border rounded-r-lg p-2" required />
          </div>
          <input type="hidden" name="staff_id" id="full_staff_id" />
        </div>
        <button type="submit" class="w-full bg-red-500 text-white py-2 rounded hover:bg-red-700">Register</button>
      </form>
      <button class="absolute top-2 right-2 text-xl" onclick="closeRegisterModal()">&times;</button>
    </div>
  </div>

  <!-- Manual QR Upload Modal -->
  <div id="qrUploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl w-full max-w-md p-6 relative">
      <h3 class="text-xl font-semibold text-[#2E3095] mb-4">Enter QR Code Text</h3>
      <!-- Showing error duplicate entry -->
      <?php if (isset($_GET['error']) && $_GET['error'] === 'duplicate_qr'): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
          This QR text is already in use by
          <strong><?= htmlspecialchars($_GET['conflict_name'] ?? 'another staff member') ?></strong>.
          Please enter a unique one.
        </div>
      <?php endif; ?>
      <!-- End -->

      <form method="POST" action="upload_qr_manual.php">
        <input type="hidden" name="staff_id" id="qr_staff_id">

        <div class="mb-4">
          <label for="qr_text" class="block text-sm font-medium text-gray-700">QR Code Text</label>
          <input type="text" id="qr_text" name="qr_text" required
            class="mt-1 block w-full border p-2 rounded-md focus:ring focus:border-blue-300" placeholder="2025-225">
        </div>

        <button type="submit" class="w-full bg-[#ED1B24] text-white py-2 rounded hover:bg-red-700">Save</button>
      </form>
      <button class="absolute top-2 right-2 text-xl" onclick="closeQrUploadModal()">&times;</button>
    </div>
  </div>

  <div id="editModal"
    class="<?= isset($_GET['error']) ? '' : 'hidden' ?> fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl w-full max-w-md p-6 relative">
      <h3 class="text-xl font-semibold text-[#2E3095] mb-4">Edit Staff</h3>

      <?php if (isset($_GET['error'])): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
          <?php if ($_GET['error'] === 'duplicate_qr'): ?>
            This QR text is already in use by <strong><?= htmlspecialchars($_GET['conflict_name']) ?></strong>. Please enter
            a unique one.
          <?php elseif ($_GET['error'] === 'duplicate_name'): ?>
            This name is already used by another staff member. Please enter a different name.
          <?php elseif ($_GET['error'] === 'update_failed'): ?>
            Failed to update the staff. Please try again.
          <?php elseif ($_GET['error'] === 'missing_fields'): ?>
            Please fill in all required fields.
          <?php else: ?>
            Unknown error occurred.
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="edit_staff.php" onsubmit="combineEditStaffID()">
        <input type="hidden" name="id" id="edit_id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>" />
        <div class="mb-3">
          <label class="block text-gray-700">Name</label>
          <input type="text" name="name" id="edit_name" required class="w-full border p-2 rounded" />
        </div>
        <div class="mb-3">
          <label class="block text-gray-700">Phone</label>
          <input type="text" name="phone_number" id="edit_phone" required class="w-full border p-2 rounded" />
        </div>
        <div class="mb-3">
          <label class="block text-gray-700">Staff Type</label>
          <select name="staff_type" id="edit_type" required onchange="updateEditPrefix()"
            class="w-full border p-2 rounded">
            <option value="ECW">ECW</option>
            <option value="INT">INT</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="block text-gray-700">Staff ID</label>
          <div class="flex">
            <span id="edit_prefix" class="px-3 bg-gray-100 border border-r-0 rounded-l-lg">ECW-</span>
            <input type="text" id="edit_staff_id_input" class="flex-1 border rounded-r-lg p-2" required />
          </div>
          <input type="hidden" name="staff_id" id="edit_full_staff_id" />
        </div>
        <div class="mb-3">
          <label class="block text-gray-700">QR Code</label>
          <input type="text" name="qr_text" id="edit_qr" required class="w-full border p-2 rounded" />
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Update</button>
      </form>
      <button class="absolute top-2 right-2 text-xl" onclick="closeEditModal()">&times;</button>
    </div>
  </div>


  <!-- Delete Modal -->
  <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl w-full max-w-sm p-6 relative text-center">
      <h3 class="text-lg font-semibold text-red-600 mb-4">Are you sure you want to delete this staff?</h3>
      <form method="POST" action="delete_staff.php">
        <input type="hidden" name="id" id="delete_id">
        <div class="flex justify-center space-x-4 mt-4">
          <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Yes, Delete</button>
          <button type="button" onclick="closeDeleteModal()"
            class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancel</button>
        </div>
      </form>
      <button class="absolute top-2 right-2 text-xl" onclick="closeDeleteModal()">&times;</button>
    </div>
  </div>

  <!-- Footer -->
  <?php include 'include/footer.php'; ?>

  <script>
    function openQrUploadModal(staffId) {
      document.getElementById('qr_staff_id').value = staffId;
      document.getElementById('qrUploadModal').classList.remove('hidden');
    }

    function closeQrUploadModal() {
      document.getElementById('qrUploadModal').classList.add('hidden');
    }
    function openEditModal(staff) {
      const [type, idPart] = staff.staff_id.split('-');
      document.getElementById('edit_id').value = staff.id;
      document.getElementById('edit_name').value = staff.name;
      document.getElementById('edit_phone').value = staff.phone_number;
      document.getElementById('edit_type').value = type;
      document.getElementById('edit_prefix').innerText = type + '-';
      document.getElementById('edit_staff_id_input').value = idPart;
      document.getElementById('edit_qr').value = staff.qr_code;
      document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
      document.getElementById('editModal').classList.add('hidden');
    }

    function openDeleteModal(id) {
      document.getElementById('delete_id').value = id;
      document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
      document.getElementById('deleteModal').classList.add('hidden');
    }

    function filterStaff() {
      const input = document.getElementById("searchInput").value.toLowerCase();
      const rows = document.querySelectorAll("table tbody tr");

      rows.forEach(row => {
        const cells = row.querySelectorAll("td");
        let match = false;
        cells.forEach(cell => {
          if (cell.innerText.toLowerCase().includes(input)) {
            match = true;
          }
        });
        row.style.display = match ? "" : "none";
      });
    }

    window.addEventListener('DOMContentLoaded', function () {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.has('error') && urlParams.get('staff_id')) {
        document.getElementById('qr_staff_id').value = urlParams.get('staff_id');
        document.getElementById('qrUploadModal').classList.remove('hidden');
      }

      if (new URLSearchParams(window.location.search).has('error')) {
        document.getElementById('editModal').classList.remove('hidden');
      }
    });

    function updateEditPrefix() {
      const type = document.getElementById('edit_type').value;
      document.getElementById('edit_prefix').innerText = type + '-';
    }

    function combineEditStaffID() {
      const type = document.getElementById('edit_type').value;
      const input = document.getElementById('edit_staff_id_input').value;
      document.getElementById('edit_full_staff_id').value = type + '-' + input;
    }


  </script>

</body>

</html>