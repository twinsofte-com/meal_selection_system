let scanner;
let pendingStaffId = '';

function onScanSuccess(decodedText) {
  fetch(`../get_staff_info.php?qr=${encodeURIComponent(decodedText)}`)
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        showAlert(data.error, 'error');
        resetScan();
        return;
      }

      if (data.staff_id) {
        if (data.breakfast_received === '1') {
          showAlert(`Meal already received for ${data.name} (${data.staff_id}).`, 'error');
          resetScan();
          return;
        }

        document.getElementById('empName').textContent = data.name;
        document.getElementById('empID').textContent = data.staff_id;
        document.getElementById('employee-info').classList.remove('hidden');

        pendingStaffId = data.staff_id;
        document.getElementById('confirmText').textContent = `Confirm issue breakfast to ${data.name} (${data.staff_id})?`;
        document.getElementById('confirmModal').classList.remove('hidden');
      } else {
        showAlert('Staff not found.', 'error');
        resetScan();
      }
    })
    .catch(() => {
      showAlert('Server error.', 'error');
      resetScan();
    });
}

function closeConfirmModal() {
  document.getElementById('confirmModal').classList.add('hidden');
  pendingStaffId = '';
}

function resetScan() {
  document.getElementById('employee-info').classList.add('hidden');
  document.getElementById('empName').textContent = '';
  document.getElementById('empID').textContent = '';

  if (scanner) {
    scanner.clear().then(() => {
      const selectedCam = document.getElementById('cameraSelect').value;
      scanner.start(selectedCam, scannerConfig, onScanSuccess, err => console.error('Scan error:', err));
    }).catch(e => console.error('Clear error:', e));
  }
}

function stopCamera() {
  if (scanner) {
    scanner.stop().catch(err => console.error('Stop camera error:', err));
  }
}

const scannerConfig = {
  fps: 10,
  // qrbox: { width: 250, height: 250 }, // Focused square area
  aspectRatio: 1.7778,
  disableFlip: false
};

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('confirmYes').addEventListener('click', () => {
    if (!pendingStaffId) return;

    const formData = new FormData();
    formData.append('staff_id', pendingStaffId);

    fetch('../breakfast/confirm_breakfast_issue.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.text())
    .then(text => {
      if (text.trim() === 'success' || text.trim() === '') {
        showAlert('Breakfast issued successfully.');
        setTimeout(() => window.location.reload(), 1000);
      } else {
        showAlert(text.trim(), 'error');
      }
      closeConfirmModal();
      resetScan();
    })
    .catch(() => showAlert('Server error', 'error'));
  });

  // Initialize camera
  scanner = new Html5Qrcode("preview");

  Html5Qrcode.getCameras().then(devices => {
    const select = document.getElementById('cameraSelect');
    devices.forEach(device => {
      const option = document.createElement('option');
      option.value = device.id;
      option.textContent = device.label || device.id;
      select.appendChild(option);
    });

    const firstCamId = select.value || devices[0]?.id;
    if (firstCamId) {
      scanner.start(firstCamId, scannerConfig, onScanSuccess, err => console.error('Start scan error:', err));
    }

    select.addEventListener('change', () => {
      scanner.stop().then(() => {
        scanner.start(select.value, scannerConfig, onScanSuccess, err => console.error('Camera switch scan error:', err));
      }).catch(e => console.error('Switch camera error:', e));
    });
  }).catch(err => {
    showAlert("No camera found", "error");
    console.error("Camera init error:", err);
  });
});
