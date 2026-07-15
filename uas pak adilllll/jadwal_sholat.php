<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Sholat - Smart Event Campus</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <style>
        .prayer-container {
            max-width: 800px;
            margin: 3rem auto;
            text-align: center;
        }
        .prayer-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        .prayer-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
        }
        .prayer-time {
            font-size: 1.75rem;
            font-weight: 700;
            color: #fff;
            margin: 0.5rem 0;
        }
        .prayer-name {
            font-size: 1rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .prayer-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .location-info {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            display: inline-block;
        }
        .disclaimer {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 2rem;
            font-style: italic;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <svg class="logo-img" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="100" height="100" rx="20" fill="url(#paint0_linear)"/>
                <path d="M50 25L20 40L50 55L80 40L50 25Z" fill="white"/>
                <path d="M20 55V70L50 85L80 70V55L50 70L20 55Z" fill="white" fill-opacity="0.8"/>
                <defs>
                    <linearGradient id="paint0_linear" x1="0" y1="0" x2="100" y2="100" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#10b981"/>
                        <stop offset="1" stop-color="#f59e0b"/>
                    </linearGradient>
                </defs>
            </svg>
            Smart Event Campus
        </a>
    </nav>

    <div class="container">
        <a href="index.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
        </a>
        <div class="prayer-container">
            <h1 style="color: #fff; font-size: 2.5rem; margin-bottom: 1rem;">
                <i class="fa-solid fa-mosque" style="color: var(--primary);"></i> Jadwal Waktu Sholat
            </h1>
            <p style="color: var(--text-muted); margin-bottom: 2rem; font-size: 1.1rem;">
                Dapatkan jadwal sholat presisi berdasarkan lokasi Anda saat ini.
            </p>

            <button id="btnGetLocation" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem; border-radius: 2rem; box-shadow: 0 10px 20px rgba(16,185,129,0.4); margin-bottom: 2rem;">
                <i class="fa-solid fa-location-crosshairs"></i> Deteksi Lokasi Saya
            </button>

            <div id="prayerLoading" style="display: none; color: var(--text-muted); font-size: 1.1rem;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem; display: block;"></i> 
                Menganalisis koordinat dan mengambil data...
            </div>
            
            <div id="prayerError" class="alert alert-danger" style="display: none; text-align: left; max-width: 500px; margin: 0 auto 2rem auto;"></div>

            <!-- Menampilkan Nama Lokasi -->
            <div id="locationWrapper" style="display: none;">
                <div class="location-info">
                    <i class="fa-solid fa-map-location-dot"></i> <strong>Lokasi Terdeteksi:</strong> <span id="locationName"></span><br>
                    <small style="opacity: 0.8;"><span id="locationCoords"></span></small>
                </div>
            </div>

            <div id="prayerGrid" style="display: none; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <!-- Grid Jadwal -->
            </div>

            <p id="disclaimerText" class="disclaimer" style="display: none;">
                *Waktu sholat di atas telah ditambah <strong>3 menit</strong> dari hasil kalkulasi asli untuk kehati-hatian (Ihtiyat).<br>
                Metode: Majlis Ugama Islam / Standard Aladhan (Method 11).
            </p>

        </div>
    </div>

    <!-- Floating CS Button -->
    <a href="bantuan_support.php" title="Hubungi Customer Service" style="position:fixed;bottom:2rem;right:2rem;background:linear-gradient(135deg,#10b981,#f59e0b);color:#fff;width:65px;height:65px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.6rem;box-shadow:0 6px 25px rgba(16,185,129,0.6);cursor:pointer;z-index:9999;text-decoration:none;">
        <i class="fa-solid fa-headset"></i>
    </a>

    <script>
    const btnLocation = document.getElementById('btnGetLocation');
    const prayerLoading = document.getElementById('prayerLoading');
    const prayerError = document.getElementById('prayerError');
    const prayerGrid = document.getElementById('prayerGrid');
    const locationWrapper = document.getElementById('locationWrapper');
    const locationNameEl = document.getElementById('locationName');
    const locationCoordsEl = document.getElementById('locationCoords');
    const disclaimerText = document.getElementById('disclaimerText');

    btnLocation.addEventListener('click', () => {
        if (!navigator.geolocation) {
            showError('Geolocation tidak didukung oleh browser Anda.');
            return;
        }

        // Reset UI
        prayerLoading.style.display = 'block';
        prayerError.style.display = 'none';
        prayerGrid.style.display = 'none';
        locationWrapper.style.display = 'none';
        disclaimerText.style.display = 'none';
        
        btnLocation.style.display = 'none'; // Sembunyikan tombol saat loading

        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                // Fetch nama kota via OpenStreetMap Nominatim
                let locationName = "Lokasi Tidak Diketahui";
                try {
                    const geoRes = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`);
                    const geoData = await geoRes.json();
                    if(geoData && geoData.address) {
                        const city = geoData.address.city || geoData.address.town || geoData.address.village || geoData.address.county || "";
                        const state = geoData.address.state || "";
                        locationName = `${city}, ${state}`.replace(/^, | ,/g, '').trim();
                    }
                } catch(e) {
                    console.error("Gagal reverse geocoding:", e);
                }

                locationNameEl.innerText = locationName;
                locationCoordsEl.innerText = `Koordinat: ${lat.toFixed(5)}, ${lon.toFixed(5)}`;
                
                fetchPrayerTimes(lat, lon);
            },
            (error) => {
                let msg = 'Gagal mendapatkan lokasi. ';
                if(error.code === error.PERMISSION_DENIED) msg += 'Anda menolak akses izin lokasi presisi.';
                showError(msg);
                btnLocation.style.display = 'inline-block';
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    });

    // Fungsi untuk menambah menit ke string jam HH:MM
    function addMinutes(timeStr, minsToAdd) {
        if(!timeStr) return timeStr;
        // Aladhan mengembalikan waktu dalam format "HH:MM (WIB)" atau "HH:MM"
        const cleanTime = timeStr.split(' ')[0]; // Ambil "HH:MM" saja
        const [hours, minutes] = cleanTime.split(':').map(Number);
        
        const dateObj = new Date();
        dateObj.setHours(hours, minutes, 0, 0);
        dateObj.setMinutes(dateObj.getMinutes() + minsToAdd);
        
        const newHours = String(dateObj.getHours()).padStart(2, '0');
        const newMins = String(dateObj.getMinutes()).padStart(2, '0');
        
        return `${newHours}:${newMins}`;
    }

    function fetchPrayerTimes(lat, lon) {
        const date = new Date();
        const formattedDate = date.getDate() + '-' + (date.getMonth() + 1) + '-' + date.getFullYear();
        const url = `https://api.aladhan.com/v1/timings/${formattedDate}?latitude=${lat}&longitude=${lon}&method=11`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.code === 200) {
                    renderPrayerTimes(data.data.timings);
                    locationWrapper.style.display = 'block';
                    disclaimerText.style.display = 'block';
                } else {
                    showError('Gagal mengambil data dari server Aladhan API.');
                    btnLocation.style.display = 'inline-block';
                }
            })
            .catch(err => {
                showError('Terjadi kesalahan jaringan.');
                btnLocation.style.display = 'inline-block';
            })
            .finally(() => {
                prayerLoading.style.display = 'none';
            });
    }

    function renderPrayerTimes(timings) {
        // Tambahkan +3 menit ke masing-masing waktu
        const offsetMins = 3;

        const prayers = [
            { name: 'Subuh', time: addMinutes(timings.Fajr, offsetMins), icon: 'fa-cloud-sun' },
            { name: 'Dzuhur', time: addMinutes(timings.Dhuhr, offsetMins), icon: 'fa-sun' },
            { name: 'Ashar', time: addMinutes(timings.Asr, offsetMins), icon: 'fa-cloud' },
            { name: 'Maghrib', time: addMinutes(timings.Maghrib, offsetMins), icon: 'fa-moon' },
            { name: 'Isya', time: addMinutes(timings.Isha, offsetMins), icon: 'fa-star-and-crescent' }
        ];

        let html = '';
        prayers.forEach(p => {
            html += `
                <div class="prayer-card glass">
                    <i class="fa-solid ${p.icon} prayer-icon"></i>
                    <div class="prayer-name">${p.name}</div>
                    <div class="prayer-time">${p.time}</div>
                </div>
            `;
        });

        prayerGrid.innerHTML = html;
        prayerGrid.style.display = 'grid';
    }

    function showError(msg) {
        prayerLoading.style.display = 'none';
        prayerError.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${msg}`;
        prayerError.style.display = 'block';
    }
    </script>
</body>
</html>
