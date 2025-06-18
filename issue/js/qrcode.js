let scanner;
let pendingStaffId = '';

function onScanSuccess(decodedText) {
  fetch(`../get_staff_info.php?qr=${encodeURIComponent(decodedText)}`)
    .then(r => r.json())
    .then(data => {
      if (data.error) {
        showAlert(data.error, 'error');
        resetScan();
        return;
      }

      if (data.staff_id) {
        if (data.breakfast_received === '1') {
          showAlert(`Meal already received for ${data.name} (${data.staff_id}).`, 'error');
          resetScan(); // Don't show modal, just reset
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
  if (scanner) scanner.clear().then(() => {
    scanner.render(onScanSuccess, error => console.error(error));
  });
}

function stopCamera() {
  if (scanner) scanner.stop().catch(err => console.error('Stop failed', err));
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('confirmYes').addEventListener('click', () => {
    if (pendingStaffId) {
      const form = new FormData();
      form.append('staff_id', pendingStaffId);
      fetch('../breakfast/confirm_breakfast_issue.php', {
        method: 'POST',
        body: form
      })
      .then(r => r.text())
      .then(text => {
        if (text.includes('success') || text.trim() === '') {
          showAlert('Breakfast issued successfully.');
          setTimeout(() => window.location.reload(), 1000); // Auto refresh
        } else {
          showAlert('Issue failed.', 'error');
        }
        closeConfirmModal();
        resetScan();
      }).catch(() => showAlert('Server error', 'error'));
    }
  });

  scanner = new Html5Qrcode("preview");
  Html5Qrcode.getCameras().then(devices => {
    const select = document.getElementById('cameraSelect');
    devices.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.id;
      opt.textContent = d.label || d.id;
      select.appendChild(opt);
    });
    const camId = devices[0].id;
    scanner.start(camId, { fps: 10, qrbox: 250 }, onScanSuccess);
    select.onchange = () => {
      scanner.stop().then(() => {
        scanner.start(select.value, { fps: 10, qrbox: 250 }, onScanSuccess);
      });
    };
  });
});
