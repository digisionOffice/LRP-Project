// Absensi Geolocation JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // For karyawan panel, only enable geolocation validation, disable form blocking
    const isKaryawanPanel = window.location.href.includes('/karyawan/');
    if (isKaryawanPanel) {
        console.log("🔧 Karyawan panel: enabling geolocation validation only");
        // Skip the aggressive form blocking for karyawan panel
        window.skipFormBlocking = true;
    }

    console.log('🚀 Absensi Geolocation script loaded');

    const demo = document.getElementById("location-demo");

    // Initialize geolocation functions
    window.getLocation = function () {
        if (navigator.geolocation) {
            demo.innerHTML = "🔍 Mengambil lokasi Anda...<br><small>Pastikan GPS aktif dan izinkan akses lokasi</small>";
            demo.className = "p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 mb-3";

            const options = {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 300000
            };

            navigator.geolocation.getCurrentPosition(success, error, options);
        } else {
            demo.innerHTML = "❌ Geolocation tidak didukung browser ini.<br>Silakan gunakan tombol Jakarta.";
            demo.className = "p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 mb-3";
        }
    };

    function success(position) {
        const lat = position.coords.latitude.toFixed(6);
        const lng = position.coords.longitude.toFixed(6);
        const accuracy = Math.round(position.coords.accuracy);

        demo.innerHTML = "✅ Lokasi berhasil dideteksi!<br>" +
            "Latitude: " + lat + "<br>" +
            "Longitude: " + lng + "<br>" +
            "Akurasi: ±" + accuracy + " meter<br>" +
            "<small>🔍 Memvalidasi lokasi...</small>";
        demo.className = "p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 mb-3";

        // Store coordinates globally
        window.currentCoordinates = {
            latitude: lat,
            longitude: lng,
            isValid: true
        };

        setCoordinates(lat, lng);
        validateLocationDistance(lat, lng);
        hideManualInput();
        console.log("🎉 Geolocation success:", { lat: lat, lng: lng, accuracy: accuracy });
    }

    function error(err) {
        let message = "❌ Gagal mendapatkan lokasi. ";

        if (err) {
            switch (err.code) {
                case err.PERMISSION_DENIED:
                    message += "Izin akses lokasi ditolak.<br>Silakan aktifkan izin lokasi di browser atau gunakan tombol Jakarta.";
                    break;
                case err.POSITION_UNAVAILABLE:
                    message += "Lokasi tidak tersedia.<br>Pastikan GPS aktif atau gunakan tombol Jakarta.";
                    break;
                case err.TIMEOUT:
                    message += "Waktu permintaan habis.<br>Silakan coba lagi atau gunakan tombol Jakarta.";
                    break;
                default:
                    message += "Terjadi kesalahan.<br>Silakan gunakan tombol Jakarta.";
                    break;
            }
        } else {
            message += "Silakan gunakan tombol Jakarta.";
        }

        demo.innerHTML = message;
        demo.className = "p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 mb-3";
        console.log("❌ Geolocation error:", err);
    }

    window.useJakarta = function () {
        const lat = -6.200000;
        const lng = 106.816666;

        demo.innerHTML = "✅ Menggunakan koordinat Jakarta<br>Latitude: " + lat + "<br>Longitude: " + lng + "<br><small>🔍 Memvalidasi lokasi...</small>";
        demo.className = "p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 mb-3";

        // Store coordinates globally
        window.currentCoordinates = {
            latitude: lat,
            longitude: lng,
            isValid: true
        };

        setCoordinates(lat, lng);
        validateLocationDistance(lat, lng);
        hideManualInput();
    };

    window.showManualInput = function () {
        document.getElementById("manual-input-container").style.display = "block";
        document.getElementById("manual-lat").focus();
    };

    window.hideManualInput = function () {
        document.getElementById("manual-input-container").style.display = "none";
        document.getElementById("manual-lat").value = "";
        document.getElementById("manual-lng").value = "";
    };

    window.useManualCoords = function () {
        const lat = parseFloat(document.getElementById("manual-lat").value);
        const lng = parseFloat(document.getElementById("manual-lng").value);

        if (isNaN(lat) || isNaN(lng)) {
            Swal.fire({
                icon: "error",
                title: "Input Tidak Valid! ❌",
                text: "Silakan masukkan koordinat yang valid (angka)",
                confirmButtonText: "OK",
                confirmButtonColor: "#EF4444"
            });
            return;
        }

        if (lat < -90 || lat > 90) {
            Swal.fire({
                icon: "error",
                title: "Latitude Tidak Valid! ❌",
                text: "Latitude harus antara -90 dan 90",
                confirmButtonText: "OK",
                confirmButtonColor: "#EF4444"
            });
            return;
        }

        if (lng < -180 || lng > 180) {
            Swal.fire({
                icon: "error",
                title: "Longitude Tidak Valid! ❌",
                text: "Longitude harus antara -180 dan 180",
                confirmButtonText: "OK",
                confirmButtonColor: "#EF4444"
            });
            return;
        }

        demo.innerHTML = "✅ Menggunakan koordinat manual<br>Latitude: " + lat + "<br>Longitude: " + lng + "<br><small>🔍 Memvalidasi lokasi...</small>";
        demo.className = "p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 mb-3";

        // Store coordinates globally
        window.currentCoordinates = {
            latitude: lat,
            longitude: lng,
            isValid: true
        };

        setCoordinates(lat, lng);
        validateLocationDistance(lat, lng);
        hideManualInput();
    };

    function setCoordinates(lat, lng) {
        console.log("🔍 Setting coordinates:", { lat, lng });

        // Try multiple approaches to find and set the inputs
        const approaches = [
            // Approach 1: Direct name attribute
            () => {
                const latInput = document.querySelector("input[name=latitude]");
                const lngInput = document.querySelector("input[name=longitude]");
                return { latInput, lngInput, method: "name attribute" };
            },
            // Approach 2: Wire model (fixed selector)
            () => {
                const allInputs = document.querySelectorAll("input");
                let latInput = null, lngInput = null;

                allInputs.forEach(input => {
                    const wireModel = input.getAttribute("wire:model");
                    if (wireModel && wireModel.includes("latitude")) latInput = input;
                    if (wireModel && wireModel.includes("longitude")) lngInput = input;
                });

                return { latInput, lngInput, method: "wire:model search" };
            },
            // Approach 3: ID contains
            () => {
                const latInput = document.querySelector("input[id*=latitude]");
                const lngInput = document.querySelector("input[id*=longitude]");
                return { latInput, lngInput, method: "id contains" };
            },
            // Approach 4: Look for any input with latitude/longitude in various attributes
            () => {
                const allInputs = document.querySelectorAll("input");
                let latInput = null, lngInput = null;

                allInputs.forEach(input => {
                    const attrs = [input.name, input.id, input.getAttribute("wire:model"), input.getAttribute("x-model")].join(" ").toLowerCase();
                    if (attrs.includes("latitude") && !latInput) latInput = input;
                    if (attrs.includes("longitude") && !lngInput) lngInput = input;
                });

                return { latInput, lngInput, method: "attribute search" };
            }
        ];

        let latInput = null, lngInput = null, successMethod = null;

        // Try each approach until we find the inputs
        for (const approach of approaches) {
            const result = approach();
            if (result.latInput && result.lngInput) {
                latInput = result.latInput;
                lngInput = result.lngInput;
                successMethod = result.method;
                console.log("✅ Found inputs using:", successMethod);
                break;
            }
        }

        if (latInput && lngInput) {
            // Set values
            latInput.value = lat;
            lngInput.value = lng;

            // Trigger multiple events for maximum compatibility
            const events = ["input", "change", "blur", "keyup"];
            events.forEach(eventType => {
                latInput.dispatchEvent(new Event(eventType, { bubbles: true }));
                lngInput.dispatchEvent(new Event(eventType, { bubbles: true }));
            });

            // Try Alpine.js if available
            if (window.Alpine) {
                try {
                    latInput.dispatchEvent(new CustomEvent("input", { bubbles: true }));
                    lngInput.dispatchEvent(new CustomEvent("input", { bubbles: true }));
                } catch (e) {
                    console.log("Alpine events failed:", e);
                }
            }

            console.log("📍 Coordinates set successfully:", { lat, lng, method: successMethod });
            console.log("📍 Final values:", {
                latValue: latInput.value,
                lngValue: lngInput.value
            });

            // Store coordinates globally for validation
            window.currentCoordinates = {
                latitude: lat,
                longitude: lng,
                isValid: true
            };

            return true;
        } else {
            console.error("❌ Could not find latitude/longitude inputs");
            return false;
        }
    }

    // Validate location distance against workplace
    function validateLocationDistance(lat, lng) {
        console.log("🔍 Validating location distance...");

        // Make AJAX request to validate geofencing
        fetch("/karyawan/validate-geofencing", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]")?.getAttribute("content") || ""
            },
            body: JSON.stringify({
                latitude: lat,
                longitude: lng
            })
        })
            .then(response => response.json())
            .then(data => {
                console.log("📍 Geofencing validation result:", data);

                const demo = document.getElementById("location-demo");
                if (!demo) return;

                if (data.allowed) {
                    demo.innerHTML = "✅ Lokasi valid untuk absensi!<br>" +
                        "Latitude: " + lat + "<br>" +
                        "Longitude: " + lng + "<br>" +
                        "Jarak dari kantor: " + Math.round(data.distance || 0) + "m<br>" +
                        "<small class=\"text-green-600\">✓ Dalam radius " + (data.radius || 0) + "m yang diperbolehkan</small>";
                    demo.className = "p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 mb-3";

                    // Store validation result for form submission
                    window.lastLocationValidation = {
                        allowed: true,
                        distance: Math.round(data.distance || 0),
                        radius: data.radius || 0,
                        entitas: data.entitas_name || "Lokasi Kerja"
                    };
                } else {
                    demo.innerHTML = "❌ Lokasi tidak valid untuk absensi!<br>" +
                        "Latitude: " + lat + "<br>" +
                        "Longitude: " + lng + "<br>" +
                        "Jarak dari kantor: " + Math.round(data.distance || 0) + "m<br>" +
                        "<small class=\"text-red-600\">✗ Melebihi radius " + (data.radius || 0) + "m yang diperbolehkan</small><br>" +
                        "<strong>Pesan:</strong> " + (data.message || "Lokasi tidak valid");
                    demo.className = "p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 mb-3";

                    // Store validation result for form submission
                    window.lastLocationValidation = {
                        allowed: false,
                        distance: Math.round(data.distance || 0),
                        radius: data.radius || 0,
                        entitas: data.entitas_name || "Lokasi Kerja",
                        message: data.message || "Anda berada di luar radius yang diperbolehkan untuk melakukan absensi."
                    };
                }
            })
            .catch(error => {
                console.error("❌ Error validating geofencing:", error);
                const demo = document.getElementById("location-demo");
                if (demo) {
                    demo.innerHTML = "⚠️ Tidak dapat memvalidasi lokasi<br>" +
                        "Latitude: " + lat + "<br>" +
                        "Longitude: " + lng + "<br>" +
                        "<small>Silakan coba lagi atau hubungi administrator</small>";
                    demo.className = "p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 mb-3";
                }
            });
    }

    // Auto-start geolocation when page loads
    setTimeout(function () {
        console.log("🚀 Auto-starting geolocation...");
        if (window.getLocation) {
            getLocation();
        }
    }, 1000);

    // Store coordinates globally for validation
    window.currentCoordinates = {
        latitude: null,
        longitude: null,
        isValid: false
    };
});
