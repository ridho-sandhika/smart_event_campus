<?php
session_start();
require_once 'config.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Ambil semua data event
$sql = "SELECT * FROM events ORDER BY event_date DESC, event_time DESC";
$result = $conn->query($sql);

// === ANALYTICS DATA ===
$total_events = $conn->query("SELECT COUNT(*) as total FROM events")->fetch_assoc()['total'];
$upcoming_events = $conn->query("SELECT COUNT(*) as total FROM events WHERE event_date >= CURDATE()")->fetch_assoc()['total'];
$this_month = $conn->query("SELECT COUNT(*) as total FROM events WHERE MONTH(event_date) = MONTH(CURDATE()) AND YEAR(event_date) = YEAR(CURDATE())")->fetch_assoc()['total'];

$total_registrations = $conn->query("SELECT COUNT(*) as total FROM registrations")->fetch_assoc()['total'];
$pending_registrations = $conn->query("SELECT COUNT(*) as total FROM registrations WHERE status = 'pending'")->fetch_assoc()['total'];

$total_revenue = $conn->query("SELECT SUM(e.price) as total FROM registrations r JOIN events e ON r.event_id = e.id WHERE r.status = 'verified' AND e.is_paid = 1")->fetch_assoc()['total'];
$total_present = $conn->query("SELECT COUNT(*) as total FROM registrations WHERE attendance_status = 'present'")->fetch_assoc()['total'];
$total_reviews = $conn->query("SELECT COUNT(*) as total FROM event_reviews")->fetch_assoc()['total'];

// Data per jenis untuk chart
$chart_data_query = $conn->query("SELECT event_type, COUNT(*) as total FROM events GROUP BY event_type");
$chart_labels = []; $chart_values = []; $chart_colors = ['#10b981','#f59e0b','#06b6d4','#10b981'];
while ($row_c = $chart_data_query->fetch_assoc()) {
    $chart_labels[] = $row_c['event_type'];
    $chart_values[] = $row_c['total'];
}
$chart_labels_json = json_encode($chart_labels);
$chart_values_json = json_encode($chart_values);
$chart_colors_json = json_encode(array_slice($chart_colors, 0, count($chart_labels)));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Smart Event Campus</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { padding: 1.5rem; border-radius: 1rem; text-align: center; }
        .stat-icon { font-size: 2rem; margin-bottom: 0.75rem; }
        .stat-value { font-size: 2.5rem; font-weight: 800; color: #fff; line-height: 1; }
        .stat-label { font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .analytics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; }
        .chart-card { padding: 2rem; border-radius: 1rem; }
        .chart-card h3 { margin-bottom: 1.5rem; font-size: 1.1rem; color: #fff; }
        .qr-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); z-index: 999; align-items: center; justify-content: center; }
        .qr-modal.active { display: flex; }
        .qr-box { background: rgba(15,23,42,0.95); border: 1px solid rgba(255,255,255,0.1); border-radius: 1.5rem; padding: 2.5rem; text-align: center; max-width: 380px; width: 90%; animation: fadeIn 0.3s ease; }
        .qr-box h3 { color: #fff; margin-bottom: 0.5rem; font-size: 1.2rem; }
        .qr-box p { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem; }
        .qr-box img { width: 200px; height: 200px; border-radius: 0.75rem; border: 2px solid var(--primary); }
        .qr-close { margin-top: 1.5rem; }
        @media(max-width: 768px) { .analytics-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="nav-overlay" id="navOverlay" onclick="closeNav()"></div>
    <nav class="navbar">
        <a href="admin.php" class="navbar-brand">
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
            Admin Panel
        </a>
        <button class="menu-toggle" onclick="toggleNav()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="nav-links" id="navLinks">
            <a href="index.php" class="btn btn-outline" target="_blank"><i class="fa-solid fa-globe"></i> Web</a>
            <a href="admin_news.php" class="btn btn-outline" style="border-color: #f59e0b; color: #f59e0b;"><i class="fa-regular fa-newspaper"></i> Berita</a>
            <a href="admin_announcements.php" class="btn btn-outline" style="border-color: #8b5cf6; color: #8b5cf6;"><i class="fa-solid fa-bullhorn"></i> Pengumuman</a>
            <a href="admin_support.php" class="btn btn-outline" style="border-color: #06b6d4; color: #06b6d4;"><i class="fa-solid fa-headset"></i> CS Chat</a>
            <a href="admin_registrations.php" class="btn btn-primary" style="background: var(--secondary); border-color: var(--secondary);"><i class="fa-solid fa-users-gear"></i> Pendaftar</a>
            <a href="export_pdf.php" class="btn btn-outline" style="border-color: #10b981; color: #10b981;"><i class="fa-solid fa-file-pdf"></i> Export</a>
            <a href="logout.php" class="btn btn-primary" style="background: #ef4444; border-color: #ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card glass">
                <div class="stat-icon" style="color: var(--primary);"><i class="fa-solid fa-calendar-days"></i></div>
                <div class="stat-value"><?= $total_events ?></div>
                <div class="stat-label">Total Kegiatan</div>
            </div>
            <div class="stat-card glass">
                <div class="stat-icon" style="color: #06b6d4;"><i class="fa-solid fa-rocket"></i></div>
                <div class="stat-value"><?= $upcoming_events ?></div>
                <div class="stat-label">Akan Datang</div>
            </div>
            <div class="stat-card glass">
                <div class="stat-icon" style="color: #10b981;"><i class="fa-solid fa-users"></i></div>
                <div class="stat-value"><?= $total_registrations ?></div>
                <div class="stat-label">Total Pendaftar</div>
            </div>
            <div class="stat-card glass">
                <div class="stat-icon" style="color: #f59e0b;"><i class="fa-solid fa-user-clock"></i></div>
                <div class="stat-value"><?= $pending_registrations ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
            
            <div class="stat-card glass" style="border:1px solid #10b981;">
                <div class="stat-icon" style="color: #10b981;"><i class="fa-solid fa-sack-dollar"></i></div>
                <div class="stat-value" style="font-size: 1.5rem;">Rp <?= number_format((float)$total_revenue, 0, ',', '.') ?></div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
            <div class="stat-card glass" style="border:1px solid #8b5cf6;">
                <div class="stat-icon" style="color: #8b5cf6;"><i class="fa-solid fa-user-check"></i></div>
                <div class="stat-value"><?= $total_present ?></div>
                <div class="stat-label">Peserta Hadir</div>
            </div>
            <div class="stat-card glass" style="border:1px solid #f43f5e;">
                <div class="stat-icon" style="color: #f43f5e;"><i class="fa-solid fa-star"></i></div>
                <div class="stat-value"><?= $total_reviews ?></div>
                <div class="stat-label">Total Ulasan</div>
            </div>
        </div>

        <!-- ANALYTICS CHART -->
        <?php if (count($chart_labels) > 0): ?>
        <div class="analytics-grid">
            <div class="chart-card glass">
                <h3><i class="fa-solid fa-chart-pie" style="color: var(--primary); margin-right: 0.5rem;"></i> Distribusi Jenis Kegiatan</h3>
                <canvas id="donutChart" style="max-height: 250px;"></canvas>
            </div>
            <div class="chart-card glass" style="display: flex; flex-direction: column; justify-content: center;">
                <h3><i class="fa-solid fa-chart-bar" style="color: var(--secondary); margin-right: 0.5rem;"></i> Statistik Kegiatan</h3>
                <canvas id="barChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
        <?php endif; ?>

        <div class="admin-header">
            <h2>Kelola Kegiatan Kampus</h2>
            <div style="display: flex; gap: 0.5rem;">
                <button form="bulkDeleteEventForm" type="submit" class="btn btn-danger" id="btnHapusTerpilih" disabled><i class="fa-solid fa-trash-can"></i> Hapus Terpilih</button>
                <a href="create_event.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tambah Kegiatan</a>
            </div>
        </div>

        <div class="table-container glass" style="animation: fadeIn 0.5s ease-out forwards;">
            <form id="bulkDeleteEventForm" action="bulk_delete.php" method="POST">
                <input type="hidden" name="type" value="events">
                <table style="min-width: 800px;">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;"><input type="checkbox" id="checkAll" class="checkbox-3d"></th>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result->data_seek(0); // Reset pointer
                        if ($result && $result->num_rows > 0) {
                            $no = 1;
                            $delay = 1;
                            while($row = $result->fetch_assoc()) {
                                $event_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
                                $qr_text = urlencode("Smart Event Campus: " . $row['title'] . " | " . date("d M Y", strtotime($row['event_date'])) . " " . date("H:i", strtotime($row['event_time'])) . " | " . $row['location']);
                                $qr_img = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . $qr_text;
                                echo '<tr class="stagger-' . ($delay > 5 ? 5 : $delay++) . '">';
                                echo '<td style="text-align: center;"><input type="checkbox" name="ids[]" value="'.$row['id'].'" class="checkbox-3d chk-item"></td>';
                                echo '<td>' . $no++ . '</td>';
                                echo '<td><strong>' . htmlspecialchars($row['title']) . '</strong></td>';
                                echo '<td><span class="event-badge" style="position: static; padding: 0.2rem 0.6rem; font-size: 0.75rem; margin: 0;">' . htmlspecialchars($row['event_type']) . '</span></td>';
                                echo '<td><i class="fa-regular fa-calendar" style="color: var(--text-muted); margin-right: 5px;"></i>' . date("d M Y", strtotime($row['event_date'])) . '</td>';
                                echo '<td><i class="fa-regular fa-clock" style="color: var(--text-muted); margin-right: 5px;"></i>' . date("H:i", strtotime($row['event_time'])) . '</td>';
                                echo '<td>' . htmlspecialchars($row['location']) . '</td>';
                                
                                $status = $row['is_paid'] ? '<span style="color:#10b981; font-weight:bold;">Berbayar</span>' : '<span style="color:#64748b;">Gratis</span>';
                                echo '<td>' . $status . '</td>';

                                echo '<td class="action-buttons">';
                                echo '<a href="edit_event.php?id=' . $row['id'] . '" class="btn btn-outline action-btn" style="padding: 0.4rem 0.6rem;" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>';
                                echo '<button type="button" onclick="showQR(\'' . htmlspecialchars($row['title'], ENT_QUOTES) . '\', \'' . $qr_img . '\')" class="btn action-btn" style="padding: 0.4rem 0.6rem; background: rgba(6,182,212,0.15); border: 1px solid #06b6d4; color: #06b6d4;" title="QR Code"><i class="fa-solid fa-qrcode"></i></button>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="9" style="text-align: center; padding: 3rem;"><i class="fa-regular fa-folder-open" style="font-size: 2rem; color: var(--text-muted); display: block; margin-bottom: 1rem;"></i> Belum ada data kegiatan.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div class="qr-modal" id="qrModal" onclick="closeQR(event)">
        <div class="qr-box">
            <h3 id="qrTitle">QR Code Kegiatan</h3>
            <p>Scan QR Code di bawah untuk informasi kegiatan</p>
            <img id="qrImage" src="" alt="QR Code">
            <div class="qr-close">
                <button type="button" onclick="document.getElementById('qrModal').classList.remove('active')" class="btn btn-outline" style="width: 100%;">
                    <i class="fa-solid fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
    function toggleNav() {
        document.getElementById('navLinks').classList.toggle('active');
        document.getElementById('navOverlay').classList.toggle('active');
    }
    function closeNav() {
        document.getElementById('navLinks').classList.remove('active');
        document.getElementById('navOverlay').classList.remove('active');
    }

    // Check All Logic for Bulk Delete
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.chk-item');
    const btnHapus = document.getElementById('btnHapusTerpilih');

    function updateDeleteBtn() {
        const checkedCount = document.querySelectorAll('.chk-item:checked').length;
        btnHapus.disabled = checkedCount === 0;
        if(checkedCount > 0) {
            btnHapus.innerHTML = `<i class="fa-solid fa-trash-can"></i> Hapus Terpilih (${checkedCount})`;
        } else {
            btnHapus.innerHTML = `<i class="fa-solid fa-trash-can"></i> Hapus Terpilih`;
        }
    }

    if(checkAll) {
        checkAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateDeleteBtn();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const allChecked = document.querySelectorAll('.chk-item:checked').length === checkboxes.length;
            checkAll.checked = allChecked;
            updateDeleteBtn();
        });
    });

    // SweetAlert Bulk Delete Confirm
    document.getElementById('bulkDeleteEventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data kegiatan beserta semua pendaftarnya akan ikut terhapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal',
            background: '#1e293b',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        })
    });

    // QR Code Modal
    function showQR(title, imgUrl) {
        document.getElementById('qrTitle').textContent = title;
        document.getElementById('qrImage').src = imgUrl;
        document.getElementById('qrModal').classList.add('active');
    }
    function closeQR(event) {
        if (event.target === document.getElementById('qrModal')) {
            document.getElementById('qrModal').classList.remove('active');
        }
    }

    // SweetAlert handling session messages
    <?php if(isset($_SESSION['swal_success'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= $_SESSION['swal_success'] ?>',
        background: '#1e293b',
        color: '#fff',
        confirmButtonColor: '#10b981'
    });
    <?php unset($_SESSION['swal_success']); endif; ?>
    
    <?php if(isset($_SESSION['swal_error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '<?= $_SESSION['swal_error'] ?>',
        background: '#1e293b',
        color: '#fff',
        confirmButtonColor: '#ef4444'
    });
    <?php unset($_SESSION['swal_error']); endif; ?>

    <?php if (count($chart_labels) > 0): ?>
    // Chart.js Analytics
    const labels = <?= $chart_labels_json ?>;
    const values = <?= $chart_values_json ?>;
    const colors = <?= $chart_colors_json ?>;

    // Donut Chart
    new Chart(document.getElementById('donutChart'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: 'rgba(0,0,0,0.3)' }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#94a3b8', padding: 16, font: { family: 'Outfit', size: 13 } } }
            }
        }
    });

    // Bar Chart
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Kegiatan',
                data: values,
                backgroundColor: colors,
                borderRadius: 8, borderSkipped: false
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: '#94a3b8', font: { family: 'Outfit' } }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { ticks: { color: '#94a3b8', font: { family: 'Outfit' }, stepSize: 1 }, grid: { color: 'rgba(255,255,255,0.05)' } }
            }
        }
    });
    <?php endif; ?>
    </script>
</body>
</html>
