document.addEventListener('DOMContentLoaded', function() {
    const html5QrCode = new Html5Qrcode("reader");
    const toggleCameraButton = document.getElementById('toggleCameraButton');
    let currentCameraId = null;
    let cameras = [];
    let currentCameraIndex = 0;

    function onScanSuccess(decodedText, decodedResult) {
        const staffId = decodedText.split('staff_id=')[1];

        // Send an AJAX request to fetch the staff details
        fetch(`index.php?staff_id=${staffId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('staff_id').value = staffId;
                    document.getElementById('staffName').innerText = data.name;

                    // Show the popup with the staff's name
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

    function onScanFailure(error) {
        console.warn(`QR code scan error: ${error}`);
    }

    // Function to start scanning with a specific camera
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

    // Function to initialize the camera list and start the default camera
    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length > 0) {
            cameras = devices; // Store all available cameras
            currentCameraId = cameras[currentCameraIndex].id; // Start with the first available camera
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
            // Stop the current camera
            html5QrCode.stop().then(() => {
                // Switch to the next camera
                currentCameraIndex = (currentCameraIndex + 1) % cameras.length;
                currentCameraId = cameras[currentCameraIndex].id;
                startScanning(currentCameraId); // Start scanning with the new camera
            }).catch(err => {
                console.error('Error stopping the current camera:', err);
            });
        } else {
            alert('Only one camera found on this device.');
        }
    });

    // Ensure elements exist before attaching event listeners for the form
    const mealForm = document.getElementById('mealForm');
    const closePopup = document.getElementById('closePopup');

    if (mealForm) {
        mealForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent form submission

            const formData = new FormData(this); // Collect form data
            fetch('qrscanner_process.php', {
                method: 'POST',
                body: formData
            }).then(response => response.text())
              .then(data => {
                  alert(data); // Display server response
                  document.getElementById('popup').style.display = 'none'; // Hide popup
              })
              .catch(error => console.error('Error:', error)); // Handle errors
        });
    }

    if (closePopup) {
        closePopup.addEventListener('click', function() {
            document.getElementById('popup').style.display = 'none'; // Close popup on button click
        });
    }
});
