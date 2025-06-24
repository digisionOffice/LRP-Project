/**
 * Geolocation functionality for attendance system
 */

// Initialize geolocation when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🌍 Geolocation module loaded');
    
    // Auto-detect location if on attendance page
    if (isAttendancePage()) {
        initializeGeolocation();
    }
});

function isAttendancePage() {
    return window.location.href.includes('/karyawan/') && 
           (window.location.href.includes('absensi') || window.location.href.includes('attendance'));
}

function initializeGeolocation() {
    console.log('🚀 Initializing geolocation for attendance...');
    
    // Check if geolocation is supported
    if (!navigator.geolocation) {
        console.error('❌ Geolocation is not supported by this browser');
        showGeolocationError('Browser tidak mendukung geolocation');
        return;
    }

    // Get current position
    getCurrentPosition();
}

function getCurrentPosition() {
    const options = {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 300000
    };

    navigator.geolocation.getCurrentPosition(
        handleLocationSuccess,
        handleLocationError,
        options
    );
}

function handleLocationSuccess(position) {
    const latitude = position.coords.latitude;
    const longitude = position.coords.longitude;
    const accuracy = position.coords.accuracy;

    console.log('✅ Location obtained:', {
        latitude: latitude,
        longitude: longitude,
        accuracy: accuracy
    });

    // Store location globally
    window.currentLocation = {
        latitude: latitude,
        longitude: longitude,
        accuracy: accuracy,
        timestamp: Date.now()
    };

    // Update UI if elements exist
    updateLocationDisplay(latitude, longitude, accuracy);
    
    // Validate location against workplace
    validateWorkplaceLocation(latitude, longitude);
}

function handleLocationError(error) {
    let message = 'Gagal mendapatkan lokasi: ';
    
    switch(error.code) {
        case error.PERMISSION_DENIED:
            message += 'Izin akses lokasi ditolak';
            break;
        case error.POSITION_UNAVAILABLE:
            message += 'Lokasi tidak tersedia';
            break;
        case error.TIMEOUT:
            message += 'Timeout mendapatkan lokasi';
            break;
        default:
            message += 'Error tidak diketahui';
            break;
    }

    console.error('❌ Geolocation error:', error);
    showGeolocationError(message);
}

function updateLocationDisplay(latitude, longitude, accuracy) {
    // Update location display elements if they exist
    const latElement = document.getElementById('current-latitude');
    const lngElement = document.getElementById('current-longitude');
    const accuracyElement = document.getElementById('location-accuracy');

    if (latElement) latElement.textContent = latitude.toFixed(6);
    if (lngElement) lngElement.textContent = longitude.toFixed(6);
    if (accuracyElement) accuracyElement.textContent = `±${Math.round(accuracy)}m`;
}

function validateWorkplaceLocation(latitude, longitude) {
    // This would typically make an API call to validate the location
    console.log('🔍 Validating workplace location...');
    
    // For now, just log the validation attempt
    console.log('Location validation would be performed here');
}

function showGeolocationError(message) {
    console.error('Geolocation error:', message);
    
    // Show error in UI if error element exists
    const errorElement = document.getElementById('geolocation-error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

// Export functions for use in other modules
window.geolocation = {
    getCurrentPosition,
    handleLocationSuccess,
    handleLocationError,
    updateLocationDisplay,
    validateWorkplaceLocation
};
