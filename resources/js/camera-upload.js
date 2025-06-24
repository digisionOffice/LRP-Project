/**
 * Camera upload functionality for attendance system
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('📸 Camera upload module loaded');
    
    if (isAttendancePage()) {
        initializeCameraUpload();
    }
});

function isAttendancePage() {
    return window.location.href.includes('/karyawan/') && 
           (window.location.href.includes('absensi') || window.location.href.includes('attendance'));
}

function initializeCameraUpload() {
    console.log('🚀 Initializing camera upload for attendance...');
    
    // Initialize camera controls
    setupCameraControls();
    
    // Initialize file upload handlers
    setupFileUploadHandlers();
}

function setupCameraControls() {
    const startCameraBtn = document.getElementById('start-camera-btn');
    const stopCameraBtn = document.getElementById('stop-camera-btn');
    const captureBtn = document.getElementById('capture-photo-btn');
    
    if (startCameraBtn) {
        startCameraBtn.addEventListener('click', startCamera);
    }
    
    if (stopCameraBtn) {
        stopCameraBtn.addEventListener('click', stopCamera);
    }
    
    if (captureBtn) {
        captureBtn.addEventListener('click', capturePhoto);
    }
}

function setupFileUploadHandlers() {
    const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', handleFileUpload);
    });
}

function startCamera() {
    console.log('📸 Starting camera...');
    
    const video = document.getElementById('camera-video');
    if (!video) {
        console.error('❌ Video element not found');
        return;
    }
    
    // Request camera access
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            facingMode: 'user',
            width: { ideal: 1280 },
            height: { ideal: 720 }
        } 
    })
    .then(stream => {
        video.srcObject = stream;
        video.play();
        
        // Store stream reference for later cleanup
        window.currentCameraStream = stream;
        
        // Show camera controls
        showCameraControls();
        
        console.log('✅ Camera started successfully');
    })
    .catch(error => {
        console.error('❌ Error accessing camera:', error);
        showCameraError('Gagal mengakses kamera: ' + error.message);
    });
}

function stopCamera() {
    console.log('📸 Stopping camera...');
    
    if (window.currentCameraStream) {
        window.currentCameraStream.getTracks().forEach(track => {
            track.stop();
        });
        window.currentCameraStream = null;
    }
    
    const video = document.getElementById('camera-video');
    if (video) {
        video.srcObject = null;
    }
    
    // Hide camera controls
    hideCameraControls();
    
    console.log('✅ Camera stopped');
}

function capturePhoto() {
    console.log('📸 Capturing photo...');
    
    const video = document.getElementById('camera-video');
    const canvas = document.getElementById('photo-canvas') || document.createElement('canvas');
    
    if (!video || !video.videoWidth) {
        console.error('❌ Video not ready for capture');
        return;
    }
    
    // Set canvas dimensions to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw video frame to canvas
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Convert to blob
    canvas.toBlob(blob => {
        if (blob) {
            handleCapturedPhoto(blob);
        } else {
            console.error('❌ Failed to capture photo');
        }
    }, 'image/jpeg', 0.8);
}

function handleCapturedPhoto(blob) {
    console.log('✅ Photo captured successfully');
    
    // Create preview
    const previewImg = document.getElementById('photo-preview');
    if (previewImg) {
        const url = URL.createObjectURL(blob);
        previewImg.src = url;
        previewImg.style.display = 'block';
    }
    
    // Store captured photo data
    window.capturedPhotoBlob = blob;
    
    // Show photo actions
    showPhotoActions();
    
    // Extract metadata
    extractPhotoMetadata(blob);
}

function handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    console.log('📁 File selected:', file.name);
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        showUploadError('File harus berupa gambar');
        return;
    }
    
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        showUploadError('Ukuran file maksimal 5MB');
        return;
    }
    
    // Extract metadata
    extractPhotoMetadata(file);
    
    console.log('✅ File upload handled successfully');
}

function extractPhotoMetadata(file) {
    console.log('🔍 Extracting photo metadata...');
    
    const metadata = {
        filename: file.name || 'captured-photo.jpg',
        size: file.size,
        type: file.type,
        lastModified: file.lastModified || Date.now(),
        capturedAt: new Date().toISOString()
    };
    
    // Add location data if available
    if (window.currentLocation) {
        metadata.location = {
            latitude: window.currentLocation.latitude,
            longitude: window.currentLocation.longitude,
            accuracy: window.currentLocation.accuracy
        };
    }
    
    // Store metadata
    window.currentPhotoMetadata = metadata;
    
    console.log('✅ Photo metadata extracted:', metadata);
    
    // Update metadata display
    updateMetadataDisplay(metadata);
}

function updateMetadataDisplay(metadata) {
    const metadataElement = document.getElementById('photo-metadata');
    if (!metadataElement) return;
    
    let html = '<h4>Metadata Foto:</h4>';
    html += `<p>Nama file: ${metadata.filename}</p>`;
    html += `<p>Ukuran: ${(metadata.size / 1024).toFixed(1)} KB</p>`;
    html += `<p>Waktu: ${new Date(metadata.capturedAt).toLocaleString('id-ID')}</p>`;
    
    if (metadata.location) {
        html += `<p>Lokasi: ${metadata.location.latitude.toFixed(6)}, ${metadata.location.longitude.toFixed(6)}</p>`;
        html += `<p>Akurasi: ±${Math.round(metadata.location.accuracy)}m</p>`;
    }
    
    metadataElement.innerHTML = html;
}

function showCameraControls() {
    const controls = document.getElementById('camera-controls');
    if (controls) controls.style.display = 'block';
}

function hideCameraControls() {
    const controls = document.getElementById('camera-controls');
    if (controls) controls.style.display = 'none';
}

function showPhotoActions() {
    const actions = document.getElementById('photo-actions');
    if (actions) actions.style.display = 'block';
}

function showCameraError(message) {
    const errorElement = document.getElementById('camera-error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

function showUploadError(message) {
    const errorElement = document.getElementById('upload-error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

// Export functions for use in other modules
window.cameraUpload = {
    startCamera,
    stopCamera,
    capturePhoto,
    handleFileUpload,
    extractPhotoMetadata
};
