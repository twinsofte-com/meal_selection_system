let scanner;
let isScanning = false; // Prevent multiple scans

window.onload = function () {
  scanner = new Html5QrcodeScanner("preview", { fps: 10, qrbox: 250 });
  scanner.render(onScanSuccess);
};

async function onScanSuccess(decodedText) {
  if (isScanning) return;
  isScanning = true;

  try {
    const response = await fetch('get_staff_info.php?qr=' + encodeURIComponent(decodedText));
    const data = await response.json();

    if (data && data.staff_id) {
      document.getElementById('empName').textContent = data.name;
      document.getElementById('empID').textContent = data.staff_id;
      document.getElementById('employee-info').classList.remove('hidden');

      const confirmIssue = confirm(`Confirm to issue breakfast to ${data.name} (${data.staff_id})?`);

      if (confirmIssue) {
        const issueResponse = await fetch('confirm_breakfast_issue.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'staff_id=' + encodeURIComponent(data.staff_id)
        });

        const result = await issueResponse.json();

        if (result.status === 'success') {
          alert("Breakfast Issued Successfully!");
          window.location.reload();
        } else {
          alert("Error: " + (result.error || "Unknown error"));
          isScanning = false;
        }
      } else {
        isScanning = false;
      }
    } else {
      alert("Staff not found!");
      isScanning = false;
    }
  } catch (err) {
    console.error("Scan Error:", err);
    alert("An error occurred. Please try again.");
    isScanning = false;
  }
}

function resetScan() {
  isScanning = false;
  scanner.clear().then(() => {
    scanner.render(onScanSuccess);
  });
}
