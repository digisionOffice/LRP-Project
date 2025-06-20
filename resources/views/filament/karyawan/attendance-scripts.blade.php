{{-- Attendance System JavaScript Includes --}}

{{-- Geolocation Scripts --}}
<script src="{{ asset('js/absensi-geolocation.js') }}"></script>

{{-- Camera and Photo Upload Scripts --}}
<script src="{{ asset('js/camera-metadata.js') }}"></script>

{{-- Vite Assets for Attendance --}}
@vite([
    'resources/js/geolocation.js',
    'resources/js/camera-upload.js',
    'resources/js/karyawan-geolocation.js'
])

{{-- Attendance System Styles --}}
<style>
    /* Location Status Styles */
    .location-status {
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        font-weight: 500;
    }
    
    .location-status.loading {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fbbf24;
    }
    
    .location-status.success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }
    
    .location-status.error {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }
    
    .location-status.warning {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #f59e0b;
    }

    /* Camera Interface Styles */
    .camera-interface {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .camera-video {
        width: 100%;
        max-width: 400px;
        height: auto;
        border-radius: 0.5rem;
        background: #000;
    }
    
    .camera-controls {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }
    
    .camera-btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.375rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .camera-btn.primary {
        background-color: #3b82f6;
        color: white;
    }
    
    .camera-btn.primary:hover {
        background-color: #2563eb;
    }
    
    .camera-btn.secondary {
        background-color: #6b7280;
        color: white;
    }
    
    .camera-btn.secondary:hover {
        background-color: #4b5563;
    }
    
    .camera-btn.danger {
        background-color: #ef4444;
        color: white;
    }
    
    .camera-btn.danger:hover {
        background-color: #dc2626;
    }

    /* Photo Preview Styles */
    .photo-preview {
        max-width: 300px;
        height: auto;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        margin-top: 1rem;
    }
    
    .photo-metadata {
        background: #f3f4f6;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-top: 1rem;
        font-size: 0.875rem;
    }
    
    .photo-metadata h4 {
        margin: 0 0 0.5rem 0;
        font-weight: 600;
        color: #374151;
    }
    
    .photo-metadata p {
        margin: 0.25rem 0;
        color: #6b7280;
    }

    /* Geolocation Display Styles */
    .location-display {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .location-coordinates {
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: #059669;
    }
    
    .location-accuracy {
        color: #6b7280;
        font-size: 0.875rem;
    }

    /* Manual Input Styles */
    .manual-input-container {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-top: 1rem;
    }
    
    .manual-input-container input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        margin-bottom: 0.5rem;
    }
    
    .manual-input-container button {
        background-color: #f59e0b;
        color: white;
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        margin-right: 0.5rem;
    }
    
    .manual-input-container button:hover {
        background-color: #d97706;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .camera-controls {
            flex-direction: column;
        }
        
        .camera-btn {
            width: 100%;
            text-align: center;
        }
        
        .location-display {
            font-size: 0.875rem;
        }
    }
</style>

{{-- Attendance System JavaScript Initialization --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Attendance system scripts loaded');
        
        // Initialize attendance system if on relevant pages
        if (window.location.href.includes('/karyawan/')) {
            console.log('👤 Karyawan panel detected, initializing attendance features');
            
            // Set up CSRF token for AJAX requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                window.csrfToken = csrfToken.getAttribute('content');
            }
            
            // Initialize global attendance state
            window.attendanceSystem = {
                initialized: true,
                currentLocation: null,
                lastValidation: null,
                photoMetadata: null
            };
        }
    });
</script>
