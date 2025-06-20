/**
 * Karyawan-specific geolocation functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('👤 Karyawan geolocation module loaded');
    
    // Only initialize on karyawan panel
    if (isKaryawanPanel()) {
        initializeKaryawanGeolocation();
    }
});

function isKaryawanPanel() {
    return window.location.href.includes('/karyawan/');
}

function initializeKaryawanGeolocation() {
    console.log('🚀 Initializing karyawan geolocation...');
    
    // Setup geolocation for attendance
    setupAttendanceGeolocation();
    
    // Setup location validation
    setupLocationValidation();
    
    // Setup automatic location updates
    setupLocationUpdates();
}

function setupAttendanceGeolocation() {
    // Check if we're on an attendance-related page
    if (window.location.href.includes('absensi') || window.location.href.includes('attendance')) {
        console.log('📍 Setting up attendance geolocation...');
        
        // Auto-request location for attendance
        requestLocationForAttendance();
    }
}

function setupLocationValidation() {
    // Setup validation against workplace coordinates
    console.log('✅ Setting up location validation...');
    
    // This would integrate with the geofencing service
    window.validateAttendanceLocation = function(latitude, longitude) {
        return fetch('/karyawan/validate-geofencing', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                latitude: latitude,
                longitude: longitude
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('📍 Location validation result:', data);
            return data;
        })
        .catch(error => {
            console.error('❌ Location validation error:', error);
            return { allowed: false, error: true, message: 'Gagal memvalidasi lokasi' };
        });
    };
}

function setupLocationUpdates() {
    // Setup periodic location updates for real-time tracking
    let watchId = null;
    
    if (navigator.geolocation) {
        watchId = navigator.geolocation.watchPosition(
            function(position) {
                updateCurrentLocation(position);
            },
            function(error) {
                console.warn('⚠️ Location watch error:', error);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 30000
            }
        );
        
        // Store watch ID for cleanup
        window.geolocationWatchId = watchId;
    }
}

function requestLocationForAttendance() {
    console.log('📍 Requesting location for attendance...');
    
    if (!navigator.geolocation) {
        showLocationError('Browser tidak mendukung geolocation');
        return;
    }
    
    // Show loading indicator
    showLocationStatus('Mencari lokasi Anda...', 'loading');
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            handleAttendanceLocationSuccess(position);
        },
        function(error) {
            handleAttendanceLocationError(error);
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 300000
        }
    );
}

function handleAttendanceLocationSuccess(position) {
    const latitude = position.coords.latitude;
    const longitude = position.coords.longitude;
    const accuracy = position.coords.accuracy;
    
    console.log('✅ Attendance location obtained:', {
        latitude: latitude,
        longitude: longitude,
        accuracy: accuracy
    });
    
    // Update global location
    updateCurrentLocation(position);
    
    // Show success status
    showLocationStatus(`Lokasi ditemukan (±${Math.round(accuracy)}m)`, 'success');
    
    // Validate location against workplace
    if (window.validateAttendanceLocation) {
        window.validateAttendanceLocation(latitude, longitude)
            .then(result => {
                handleLocationValidationResult(result);
            });
    }
    
    // Update form fields if they exist
    updateLocationFormFields(latitude, longitude);
}

function handleAttendanceLocationError(error) {
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
    
    console.error('❌ Attendance location error:', error);
    showLocationError(message);
}

function updateCurrentLocation(position) {
    window.currentKaryawanLocation = {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
        accuracy: position.coords.accuracy,
        timestamp: position.timestamp,
        updatedAt: Date.now()
    };
    
    // Update location display
    updateLocationDisplay(window.currentKaryawanLocation);
}

function updateLocationDisplay(location) {
    // Update various location display elements
    const elements = {
        'current-latitude': location.latitude.toFixed(6),
        'current-longitude': location.longitude.toFixed(6),
        'location-accuracy': `±${Math.round(location.accuracy)}m`,
        'location-coordinates': `${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)}`
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    });
}

function updateLocationFormFields(latitude, longitude) {
    // Update form fields with location data
    const latitudeFields = document.querySelectorAll('input[name*="latitude"], input[id*="latitude"]');
    const longitudeFields = document.querySelectorAll('input[name*="longitude"], input[id*="longitude"]');
    
    latitudeFields.forEach(field => {
        field.value = latitude;
        field.dispatchEvent(new Event('input', { bubbles: true }));
    });
    
    longitudeFields.forEach(field => {
        field.value = longitude;
        field.dispatchEvent(new Event('input', { bubbles: true }));
    });
}

function handleLocationValidationResult(result) {
    if (result.allowed) {
        showLocationStatus(`✅ Lokasi valid (${Math.round(result.distance)}m dari kantor)`, 'success');
    } else {
        showLocationStatus(`❌ Lokasi tidak valid (${Math.round(result.distance)}m dari kantor)`, 'error');
    }
}

function showLocationStatus(message, type) {
    const statusElement = document.getElementById('location-status');
    if (statusElement) {
        statusElement.textContent = message;
        statusElement.className = `location-status ${type}`;
    }
}

function showLocationError(message) {
    console.error('Location error:', message);
    showLocationStatus(message, 'error');
}

// Cleanup function
window.addEventListener('beforeunload', function() {
    if (window.geolocationWatchId) {
        navigator.geolocation.clearWatch(window.geolocationWatchId);
    }
});

// Export functions for use in other modules
window.karyawanGeolocation = {
    requestLocationForAttendance,
    updateCurrentLocation,
    updateLocationDisplay,
    handleLocationValidationResult
};
