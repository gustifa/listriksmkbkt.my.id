let map = null;
let marker = null;
let circle = null;

function handleAbsensi(prayerKey, label, config) {
    Swal.fire({
        title: label,
        text: 'Konfirmasi kehadiran ibadah?',
        html: document.getElementById('map-template').innerHTML,
        width: '600px',
        showCancelButton: true,
        confirmButtonText: 'Ya, Kirim Absen',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0d6efd',
        allowOutsideClick: false,
        didOpen: () => { 
            setTimeout(() => {
                initMap(config); 
            }, 200);
        },
        preConfirm: () => {
            const lat = document.getElementById('lat_val')?.value;
            const lng = document.getElementById('lng_val')?.value;
            const dist = document.getElementById('dist_val')?.value;

            if (!lat || !lng) { Swal.showValidationMessage('Tunggu lokasi ditemukan!'); return false; }
            if (parseInt(dist) > config.maxRadius) { Swal.showValidationMessage(`Terlalu jauh (${dist}m). Mendekatlah ke Masjid.`); return false; }
            return { lat, lng };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitPrayer(prayerKey, result.value.lat, result.value.lng, config.storeRoute, config.csrfToken);
        }
    });
}

function initMap(config) {
    if (map !== null) {
        map.remove();
        map = null;
    }

    const mapContainer = document.getElementById('map-canvas');
    if (!mapContainer) return;

    map = L.map('map-canvas').setView([config.masjidLat, config.masjidLng], 18);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
        attribution: 'OSM',
        maxZoom: 19
    }).addTo(map);

    L.marker([config.masjidLat, config.masjidLng]).addTo(map).bindPopup("Masjid Sekolah").openPopup();
    circle = L.circle([config.masjidLat, config.masjidLng], { 
        color: 'green', 
        fillColor: '#2ecc71', 
        fillOpacity: 0.2, 
        radius: config.maxRadius 
    }).addTo(map);

    map.invalidateSize();

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            
            L.marker([lat, lng]).addTo(map).bindPopup("Anda").openPopup();
            
            const dist = calculateDistance(lat, lng, config.masjidLat, config.masjidLng);
            updateStatusUI(dist, lat, lng, config.maxRadius);
            
            const group = new L.featureGroup([L.marker([config.masjidLat, config.masjidLng]), L.marker([lat, lng])]);
            map.fitBounds(group.getBounds().pad(0.2));

            setTimeout(() => {
                if (map) map.invalidateSize();
            }, 250);
        }, (err) => {
            const statusText = document.getElementById('status-text');
            if (statusText) statusText.innerHTML = "GPS Error: " + err.message;
        }, { enableHighAccuracy: true });
    }
}

function updateStatusUI(dist, lat, lng, maxRadius) {
    const statusBox = document.getElementById('status-text');
    const container = document.getElementById('location-info');
    if (!statusBox || !container) return;首
    
    const oldLat = document.getElementById('lat_val'); if(oldLat) oldLat.remove();
    const oldLng = document.getElementById('lng_val'); if(oldLng) oldLng.remove();
    const oldDist = document.getElementById('dist_val'); if(oldDist) oldDist.remove();

    if (dist <= maxRadius) {
        statusBox.className = "badge bg-success w-100 p-2 mb-2";
        statusBox.innerHTML = `<i class="fas fa-check-circle me-1"></i> Dalam Radius: ${dist}m`;
    } else {
        statusBox.className = "badge bg-danger w-100 p-2 mb-2";
        statusBox.innerHTML = `<i class="fas fa-times-circle me-1"></i> Luar Radius: ${dist}m`;
    }
    
    container.innerHTML += `<input type="hidden" id="lat_val" value="${lat}"><input type="hidden" id="lng_val" value="${lng}"><input type="hidden" id="dist_val" value="${dist}">`;
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; 
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2); 
    return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
}

function submitPrayer(name, lat, lng, routeUrl, token) {
    Swal.fire({ title: 'Menyimpan...', didOpen: () => Swal.showLoading() });
    $.ajax({
        url: routeUrl,
        type: "POST",
        data: { _token: token, prayer_name: name, latitude: lat, longitude: lng },
        success: function(res) {
            Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
        },
        error: function(xhr) {
            Swal.fire('Gagal', xhr.responseJSON?.message || 'Error', 'error');
        }
    });
}