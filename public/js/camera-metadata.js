/**
 * Enhanced Camera Upload with Metadata for Attendance
 * Captures photo with geolocation and timestamp metadata
 */

// Prevent infinite loops and multiple loads
if (window.cameraMetadataLoaded) {
    console.log("📸 Camera metadata script already loaded, skipping...");
} else {
    window.cameraMetadataLoaded = true;

    document.addEventListener("DOMContentLoaded", function() {
        // Initialize camera interface after DOM is ready
        setTimeout(function() {
            if (isAttendancePage()) {
                initializeCameraInterface();
            }
        }, 1000);
    });
}

function isAttendancePage() {
    return window.location.href.includes('/karyawan/absensis/create') ||
           window.location.href.includes('/karyawan/attendance/create') ||
           document.getElementById('camera-interface-container') !== null;
}

function initializeCameraInterface() {
    console.log("🚀 Initializing camera interface...");

    // Check if elements exist
    const requiredElements = [
        "camera-interface-container",
        "start-camera-btn",
        "capture-photo-btn",
        "stop-camera-btn",
        "camera-container",
        "camera-video",
        "preview-container",
        "photo-preview",
        "use-photo-btn",
        "retake-photo-btn"
    ];

    let missingElements = [];
    requiredElements.forEach(id => {
        if (!document.getElementById(id)) {
            missingElements.push(id);
        }
    });

    if (missingElements.length > 0) {
        console.error("❌ Missing elements:", missingElements);
        return;
    }

    console.log("✅ All camera elements found");

    let stream = null;
    let currentLocation = null;
    let capturedImageData = null;
    let metadata = {};

    // Get current location
    getCurrentLocation();

    // Start live clock
    startLiveClock();

    // Event listeners
    document.getElementById("start-camera-btn").addEventListener("click", startCamera);
    document.getElementById("capture-photo-btn").addEventListener("click", capturePhoto);
    document.getElementById("stop-camera-btn").addEventListener("click", stopCamera);
    document.getElementById("use-photo-btn").addEventListener("click", usePhoto);
    document.getElementById("retake-photo-btn").addEventListener("click", retakePhoto);

    console.log("✅ Camera interface initialized successfully");

    function getCurrentLocation() {
        console.log("🔍 Requesting GPS location...");

        if (!navigator.geolocation) {
            console.error("❌ Geolocation not supported by this browser");
            updateCameraStatus("GPS tidak didukung oleh browser ini", "warning");
            return;
        }

        // Show loading status
        updateCameraStatus("📍 Mencari lokasi GPS...", "info");

        navigator.geolocation.getCurrentPosition(
            function(position) {
                currentLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    timestamp: position.timestamp
                };
                console.log("✅ GPS Location obtained:", currentLocation);
                updateCameraStatus(`📍 GPS ditemukan! Akurasi: ${Math.round(currentLocation.accuracy)}m`, "success");

                // Update live coordinates immediately
                updateLiveCoordinates();
            },
            function(error) {
                console.error("❌ Failed to get location:", error);
                let errorMessage = "Gagal mendapatkan lokasi GPS: ";

                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += "Izin lokasi ditolak";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += "Lokasi tidak tersedia";
                        break;
                    case error.TIMEOUT:
                        errorMessage += "Timeout mencari lokasi";
                        break;
                    default:
                        errorMessage += "Error tidak diketahui";
                        break;
                }

                console.warn(errorMessage);
                updateCameraStatus(errorMessage + ". Foto tetap bisa diambil tanpa GPS.", "warning");
            },
            {
                enableHighAccuracy: true,
                timeout: 20000, // Increased timeout
                maximumAge: 60000 // Reduced max age for fresher location
            }
        );

        // Also try to watch position for continuous updates
        if (navigator.geolocation.watchPosition) {
            navigator.geolocation.watchPosition(
                function(position) {
                    if (!currentLocation ||
                        Math.abs(currentLocation.latitude - position.coords.latitude) > 0.0001 ||
                        Math.abs(currentLocation.longitude - position.coords.longitude) > 0.0001) {

                        currentLocation = {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: position.coords.accuracy,
                            timestamp: position.timestamp
                        };
                        console.log("🔄 GPS Location updated:", currentLocation);
                        updateLiveCoordinates();
                    }
                },
                function(error) {
                    console.warn("GPS watch error:", error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 30000
                }
            );
        }
    }

    function updateLiveCoordinates() {
        const coordsElement = document.getElementById("live-coordinates");
        if (coordsElement && currentLocation) {
            coordsElement.textContent = `📍 ${currentLocation.latitude.toFixed(6)}, ${currentLocation.longitude.toFixed(6)} (±${Math.round(currentLocation.accuracy)}m)`;
            coordsElement.style.color = currentLocation.accuracy < 50 ? '#00FF88' : '#FFAA00';
        }
    }

    function startLiveClock() {
        setInterval(function() {
            const now = new Date();
            const timeElement = document.getElementById("current-time");
            if (timeElement) {
                // Format time in Indonesia timezone
                const indonesiaTimeDisplay = now.toLocaleString("id-ID", {
                    timeZone: "Asia/Jakarta",
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }) + ' WIB';
                timeElement.textContent = indonesiaTimeDisplay;
            }

            // Update live coordinates using the new function
            updateLiveCoordinates();
        }, 1000);
    }

    function updateCameraStatus(message, type) {
        const statusElement = document.getElementById("camera-status");
        if (statusElement) {
            statusElement.textContent = message;
            statusElement.className = `camera-status ${type}`;
        }
    }

    function startCamera() {
        console.log("📸 Starting camera...");
        // Camera implementation would go here
        updateCameraStatus("Kamera dimulai", "success");
    }

    function capturePhoto() {
        console.log("📸 Capturing photo...");
        // Photo capture implementation would go here
        updateCameraStatus("Foto diambil", "success");
    }

    function stopCamera() {
        console.log("📸 Stopping camera...");
        // Stop camera implementation would go here
        updateCameraStatus("Kamera dihentikan", "info");
    }

    function usePhoto() {
        console.log("📸 Using captured photo...");
        // Use photo implementation would go here
    }

    function retakePhoto() {
        console.log("📸 Retaking photo...");
        // Retake photo implementation would go here
    }
}
