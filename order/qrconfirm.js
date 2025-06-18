document.addEventListener('DOMContentLoaded', function() {
    const html5QrCode = new Html5Qrcode("reader");
    const toggleCameraButton = document.getElementById('toggleCameraButton');
    let currentCameraId = null;
    let cameras = [];
    let currentCameraIndex = 0;

    // Function to handle successful QR code scan
    function onScanSuccess(decodedText, decodedResult) {
        const staffId = decodedText.split('staff_id=')[1];

        // Fetch staff information using the scanned ID
        fetch(`confirm_meal.php?staff_id=${staffId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('staff_id').value = staffId;
                    document.getElementById('staffName').innerText = data.name;
                    document.getElementById('popup').style.display = 'flex';
                } else {
                    alert(data.message || 'Staff member not found.');
                }
            })
            .catch(error => {
                console.error('Error fetching staff details:', error);
                alert('An error occurred. Please try again.');
            });
    }

    // Function to handle QR code scan failures
    function onScanFailure(error) {
        console.warn(`QR code scan error: ${error}`);
    }

    // Function to start QR code scanning with a specific camera
    function startScanning(cameraId) {
        html5QrCode.start(
            { deviceId: { exact: cameraId } },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            onScanFailure
        ).catch(err => {
            console.error(`Failed to start QR code scanning: ${err}`);
        });
    }

    // Initialize camera list and start scanning
    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length > 0) {
            cameras = devices;
            currentCameraId = cameras[currentCameraIndex].id;
            startScanning(currentCameraId);
        } else {
            console.error('No cameras found.');
            alert('No cameras found on this device.');
        }
    }).catch(err => {
        console.error(`Error getting cameras: ${err}`);
    });

    // Toggle camera function
    toggleCameraButton.addEventListener('click', function() {
        if (cameras.length > 1) {
            html5QrCode.stop().then(() => {
                currentCameraIndex = (currentCameraIndex + 1) % cameras.length;
                currentCameraId = cameras[currentCameraIndex].id;
                startScanning(currentCameraId);
            }).catch(err => {
                console.error('Error stopping the current camera:', err);
            });
        } else {
            alert('Only one camera found on this device.');
        }
    });

    // Handle form submission for meal confirmation
    const confirmForm = document.getElementById('confirmForm');
    if (confirmForm) {
        confirmForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            fetch('confirm_meal_process.php', {
                method: 'POST',
                body: formData
            }).then(response => response.text())
              .then(data => {
                  alert(data);
                  if (data.includes("confirmed")) {
                      document.getElementById('popup').style.display = 'none';
                  }
              })
              .catch(error => {
                  console.error('Error:', error);
                  alert('An error occurred while confirming the meal.');
              });
        });
    }

    // Close the popup
    const closePopup = document.getElementById('closePopup');
    if (closePopup) {
        closePopup.addEventListener('click', function() {
            document.getElementById('popup').style.display = 'none';
        });
    }
});
