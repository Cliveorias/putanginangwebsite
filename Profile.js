document.addEventListener('DOMContentLoaded', () => {
    // --- BURGER MENU TOGGLE ---[cite: 10]
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const sideNav = document.getElementById('side-nav');
    const overlay = document.getElementById('overlay');
    const closeBtn = document.getElementById('close-btn');

    const toggleMenu = (isOpen) => {
        sideNav.classList.toggle('active', isOpen);
        overlay.classList.toggle('active', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : 'auto';
    };

    hamburgerBtn?.addEventListener('click', () => toggleMenu(true));
    closeBtn?.addEventListener('click', () => toggleMenu(false));
    overlay?.addEventListener('click', () => toggleMenu(false));

    // --- UPDATE CART BADGE (ANTI-011 LOGIC) ---[cite: 10]
    const updateBadge = () => {
        const key = (typeof cartKey !== 'undefined') ? cartKey : 'myCart';
        const cart = JSON.parse(localStorage.getItem(key)) || [];
        const total = cart.reduce((sum, item) => sum + parseInt(item.qty || 0), 0);
        
        const badge = document.getElementById('cart-badge');
        if (badge) {
            badge.innerText = total;
            badge.style.display = total > 0 ? 'flex' : 'none';
        }
    };
    updateBadge();

    // --- PASSWORD CHANGE VALIDATION ---[cite: 10]
    const currPass = document.getElementById('curr_pass');
    const newPass = document.getElementById('new_pass');
    const confPass = document.getElementById('conf_pass');

    const validatePassFields = () => {
        const anyFieldFilled = currPass.value || newPass.value || confPass.value;
        if (anyFieldFilled) {
            currPass.required = true;
            newPass.required = true;
            confPass.required = true;
        } else {
            currPass.required = false;
            newPass.required = false;
            confPass.required = false;
        }
    };

    [currPass, newPass, confPass].forEach(input => {
        input?.addEventListener('input', validatePassFields);
    });

    // --- MAP & GPS LOGIC (WITH AUTO-RESET) ---[cite: 10, 11]
    let currentTargetField = 'home_addr'; 
    const btnHome = document.getElementById('select-home');
    const btnWork = document.getElementById('select-work');
    const gpsBtn = document.getElementById('btn-pin-location');

    // Helper function para i-reset ang GPS button[cite: 11]
    const resetGpsBtn = () => {
        if (gpsBtn) {
            gpsBtn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> Pin My Current Location';
            gpsBtn.classList.remove('pinned');
        }
    };

    // Toggle para sa Home/Work na may kasamang reset[cite: 11]
    btnHome?.addEventListener('click', () => {
        currentTargetField = 'home_addr';
        btnHome.style.background = '#F4A42B'; btnHome.style.color = '#fff';
        btnWork.style.background = '#ccc'; btnWork.style.color = '#333';
        resetGpsBtn(); // Kusa itong mag-re-reset tuwing lilipat[cite: 11]
    });

    btnWork?.addEventListener('click', () => {
        currentTargetField = 'work_addr';
        btnWork.style.background = '#F4A42B'; btnWork.style.color = '#fff';
        btnHome.style.background = '#ccc'; btnHome.style.color = '#333';
        resetGpsBtn(); // Kusa itong mag-re-reset tuwing lilipat[cite: 11]
    });

    const mapElement = document.getElementById('map');
    if (mapElement) {
        var map = L.map('map').setView([14.5547, 121.0244], 16); 

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        var marker = L.marker([14.5547, 121.0244], {
            draggable: true
        }).addTo(map);

        function fetchAddress(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                        const targetField = document.getElementById(currentTargetField);
                        if (targetField) {
                            targetField.value = data.display_name;
                        }
                    }
                })
                .catch(error => console.error('Geocoding Error:', error));
        }

        gpsBtn?.addEventListener('click', () => {
            if (navigator.geolocation) {
                gpsBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Finding you...';
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    map.setView([lat, lng], 18);
                    marker.setLatLng([lat, lng]);
                    fetchAddress(lat, lng);

                    gpsBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Location Pinned!';
                    gpsBtn.classList.add('pinned');
                }, function(error) {
                    alert("Pakibuksan ang GPS para ma-pin ang location mo.");
                    resetGpsBtn();
                });
            }
        });

        marker.on('dragend', function () {
            var position = marker.getLatLng();
            fetchAddress(position.lat, position.lng);
            resetGpsBtn(); // I-reset ang button kung ginalaw ang pin manually[cite: 11]
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            fetchAddress(e.latlng.lat, e.latlng.lng);
            resetGpsBtn(); // I-reset ang button kung nag-click sa mapa[cite: 11]
        });

        // --- FORWARD GEOCODING: Address textbox → Map pin ---
        let geocodeTimer = null;
        function forwardGeocode(address) {
            if (!address || address.trim().length < 5) return;
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1&countrycodes=ph`)
                .then(r => r.json())
                .then(results => {
                    if (results && results.length > 0) {
                        const lat = parseFloat(results[0].lat);
                        const lng = parseFloat(results[0].lon);
                        map.setView([lat, lng], 17);
                        marker.setLatLng([lat, lng]);
                        resetGpsBtn();
                    }
                })
                .catch(err => console.error('Forward geocode error:', err));
        }

        // Debounced input listeners on address textareas
        const homeAddrField = document.getElementById('home_addr');
        const workAddrField = document.getElementById('work_addr');

        function attachGeocodeListener(field) {
            if (!field) return;
            field.addEventListener('input', function() {
                clearTimeout(geocodeTimer);
                geocodeTimer = setTimeout(() => {
                    forwardGeocode(field.value);
                }, 1000); // 1 second debounce
            });
        }

        attachGeocodeListener(homeAddrField);
        attachGeocodeListener(workAddrField);
    }

    // --- SAVE ADDRESS BUTTON FUNCTION ---[cite: 10]
    const saveAddrBtn = document.querySelector('button[name="btn_save_profile"]');
    if (saveAddrBtn) {
        saveAddrBtn.addEventListener('click', (e) => {
            const form = document.getElementById('profileUpdateForm');
            if (form) {
                form.submit();
            }
        });
    }
});

// --- TOGGLE PASSWORD VISIBILITY ---[cite: 10]
function togglePassVisibility(inputId, eyeIcon) {
    const inputField = document.getElementById(inputId);
    if (inputField.type === "password") {
        inputField.type = "text";
        eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        inputField.type = "password";
        eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// --- PHOTO LOGIC ---[cite: 10]
function showPhotoOptions() { document.getElementById('photoModalOverlay').style.display = 'flex'; }
function closePhotoOptions() { document.getElementById('photoModalOverlay').style.display = 'none'; }
function openGallery() { closePhotoOptions(); document.getElementById('profile_pic_input').click(); }
function openCamera() { closePhotoOptions(); document.getElementById('profile_pic_camera').click(); }

function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('confirm_image_preview').src = e.target.result;
            document.getElementById('confirmPhotoOverlay').style.display = 'flex';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function cancelPhotoSelection() { 
    document.getElementById('profile_pic_input').value = ""; 
    document.getElementById('confirmPhotoOverlay').style.display = 'none'; 
}

function confirmAndUpload() { 
    document.getElementById('profileUpdateForm').submit(); 
}