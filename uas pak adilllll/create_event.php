<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = trim($_POST['event_date']);
    $event_time = trim($_POST['event_time']);
    $event_type = trim($_POST['event_type']);
    $location = trim($_POST['location']);
    $maps_url = trim($_POST['maps_url']);
    $is_paid = isset($_POST['is_paid']) ? 1 : 0;
    $price = !empty($_POST['price']) ? $_POST['price'] : 0;
    $max_participants = !empty($_POST['max_participants']) ? $_POST['max_participants'] : 0;
    
    $poster_name = NULL;
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['poster']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $upload_dir = 'uploads/posters/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $poster_name = time() . '_' . basename($filename);
            $target_file = $upload_dir . $poster_name;
            
            if (!move_uploaded_file($_FILES['poster']['tmp_name'], $target_file)) {
                $error = "Gagal mengunggah poster.";
            }
        } else {
            $error = "Format file poster tidak valid (Hanya JPG, PNG, GIF, WEBP).";
        }
    }
    
    $material_name = NULL;
    if (isset($_FILES['material']) && $_FILES['material']['error'] == 0) {
        $allowed_mat = ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'zip', 'rar'];
        $mat_filename = $_FILES['material']['name'];
        $mat_ext = strtolower(pathinfo($mat_filename, PATHINFO_EXTENSION));
        
        if (in_array($mat_ext, $allowed_mat)) {
            $mat_upload_dir = 'uploads/materials/';
            if (!is_dir($mat_upload_dir)) {
                mkdir($mat_upload_dir, 0777, true);
            }
            $material_name = time() . '_' . basename($mat_filename);
            $mat_target = $mat_upload_dir . $material_name;
            
            if (!move_uploaded_file($_FILES['material']['tmp_name'], $mat_target)) {
                $error = "Gagal mengunggah materi.";
            }
        } else {
            $error = "Format materi tidak valid (PDF, PPT, DOC, ZIP).";
        }
    }
    
    if (empty($error)) {
        if (empty($title) || empty($description) || empty($event_date) || empty($event_time) || empty($event_type) || empty($location)) {
            $error = "Semua field wajib diisi (Maps URL, Poster, Materi opsional)!";
        } else {
            $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, event_time, event_type, location, maps_url, is_paid, price, max_participants, poster, material_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssidiss", $title, $description, $event_date, $event_time, $event_type, $location, $maps_url, $is_paid, $price, $max_participants, $poster_name, $material_name);
            
            if ($stmt->execute()) {
                $_SESSION['success_msg'] = "Kegiatan berhasil ditambahkan!";
                header("Location: admin.php");
                exit;
            } else {
                $error = "Terjadi kesalahan: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kegiatan - Smart Event Campus</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container { max-width: 800px; margin: 0 auto; padding: 2.5rem; }
        textarea.form-control { resize: vertical; min-height: 120px; }
        option { background: var(--bg-main); color: var(--text-main); }
    </style>
</head>
<body>
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
    </nav>

    <div class="container">
        <a href="admin.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        <div class="form-container glass">
            <h2 style="margin-bottom: 2rem; color: #fff; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                <i class="fa-solid fa-calendar-plus" style="color: var(--primary); margin-right: 0.5rem;"></i> Tambah Kegiatan Baru
            </h2>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="title">Judul Kegiatan</label>
                        <input type="text" id="title" name="title" class="form-control" required placeholder="Contoh: Seminar AI 2026">
                    </div>
                    
                    <div class="form-group">
                        <label for="event_type">Jenis Kegiatan</label>
                        <select id="event_type" name="event_type" class="form-control" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Workshop">Workshop</option>
                            <option value="Lomba">Lomba</option>
                            <option value="Pelatihan">Pelatihan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Lokasi (Nama Tempat)</label>
                        <input type="text" id="location" name="location" class="form-control" required placeholder="Gedung Rektorat Lt.3">
                    </div>

                    <div class="form-group full-width">
                        <label for="maps_url"><i class="fa-solid fa-map-location-dot" style="color: var(--secondary);"></i> Link Google Maps (Opsional)</label>
                        <input type="url" id="maps_url" name="maps_url" class="form-control" placeholder="https://maps.google.com/...">
                    </div>

                    <div class="form-group full-width">
                        <label for="poster"><i class="fa-solid fa-image" style="color: var(--primary);"></i> Poster Event (Opsional)</label>
                        <input type="file" id="poster" name="poster" class="form-control" accept="image/jpeg, image/png, image/webp, image/gif">
                        <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">Format: JPG, PNG, WEBP, GIF. Ukuran menyesuaikan otomatis.</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="material"><i class="fa-solid fa-file-pdf" style="color: var(--primary);"></i> File Materi (Opsional)</label>
                        <input type="file" id="material" name="material" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar">
                        <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">Peserta terverifikasi dapat mengunduh materi ini. (PDF, DOC, PPT, ZIP).</small>
                    </div>

                    <div class="form-group full-width" style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 0.75rem; border: 1px solid var(--border-color);">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" id="is_paid" name="is_paid" value="1" onchange="togglePrice(this)"> 
                            <strong>Kegiatan Berbayar?</strong> (Centang jika ini event berbayar)
                        </label>
                        <div id="price_container" style="display: none; margin-top: 1rem; display: flex; gap: 1rem;">
                            <div style="flex: 1;">
                                <label for="price">Harga Tiket (Rp)</label>
                                <input type="number" id="price" name="price" class="form-control" placeholder="50000">
                            </div>
                            <div style="flex: 1;">
                                <label for="max_participants">Kuota Maksimal Peserta</label>
                                <input type="number" id="max_participants" name="max_participants" class="form-control" placeholder="100">
                            </div>
                        </div>
                    </div>

                    <script>
                        function togglePrice(cb) {
                            const container = document.getElementById('price_container');
                            if(cb.checked) {
                                container.style.display = 'flex';
                                document.getElementById('price').required = true;
                                document.getElementById('max_participants').required = true;
                            } else {
                                container.style.display = 'none';
                                document.getElementById('price').required = false;
                                document.getElementById('max_participants').required = false;
                            }
                        }
                        // Init
                        togglePrice(document.getElementById('is_paid'));
                    </script>

                    <div class="form-group">
                        <label for="event_date">Tanggal</label>
                        <input type="date" id="event_date" name="event_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_time">Waktu</label>
                        <input type="time" id="event_time" name="event_time" class="form-control" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" class="form-control" required placeholder="Tuliskan deskripsi lengkap mengenai kegiatan ini..."></textarea>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Kegiatan</button>
                    <a href="admin.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
