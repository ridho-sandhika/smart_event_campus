<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_announcement'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $stmt = $conn->prepare("INSERT INTO announcements (title, content, is_active) VALUES (?, ?, 1)");
    $stmt->bind_param("ss", $title, $content);
    if($stmt->execute()){
        $_SESSION['swal_success'] = "Pengumuman berhasil ditambahkan!";
    }
    header("Location: admin_announcements.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $_SESSION['swal_success'] = "Pengumuman berhasil dihapus!";
    }
    header("Location: admin_announcements.php");
    exit;
}

if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $conn->prepare("UPDATE announcements SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $_SESSION['swal_success'] = "Status pengumuman diubah!";
    }
    header("Location: admin_announcements.php");
    exit;
}

$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengumuman - Smart Event Campus</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .container { max-width: 900px; padding: 2rem; margin: 0 auto; }
        .announcement-card { background: rgba(255,255,255,0.9); padding: 1.5rem; border-radius: 1rem; margin-bottom: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
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
            Pengumuman
        </a>
    </nav>
    <div class="container">
        <a href="admin.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        <div class="glass" style="padding: 2rem; border-radius: 1rem; margin-bottom: 2rem;">
            <h2>Tambah Pengumuman</h2>
            <form method="POST">
                <input type="text" name="title" class="form-control" placeholder="Judul Pengumuman" required style="margin-bottom: 1rem;">
                <textarea name="content" class="form-control" placeholder="Isi Pengumuman..." required style="margin-bottom: 1rem; min-height: 100px;"></textarea>
                <button type="submit" name="add_announcement" class="btn btn-primary">Simpan</button>
            </form>
        </div>
        
        <h2>Daftar Pengumuman</h2>
        <?php while($row = $announcements->fetch_assoc()): ?>
            <div class="announcement-card">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p style="color: #64748b; margin: 0.5rem 0;"><?php echo htmlspecialchars($row['content']); ?></p>
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    <a href="?toggle=<?php echo $row['id']; ?>" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.5rem;">
                        <?php echo $row['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
                    </a>
                    <a href="?delete=<?php echo $row['id']; ?>" class="btn" style="background:#ef4444; color:#fff; font-size: 0.8rem; padding: 0.5rem;">Hapus</a>
                    <?php if($row['is_active']): ?>
                        <span style="color:#10b981; margin-left:auto; font-weight:bold;">Aktif</span>
                    <?php else: ?>
                        <span style="color:#ef4444; margin-left:auto; font-weight:bold;">Nonaktif</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
        
        <div style="margin-top: 2rem; text-align: left;">
            <a href="admin.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </div>
    
    <?php if(isset($_SESSION['swal_success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= $_SESSION['swal_success'] ?>',
            confirmButtonColor: '#10b981'
        });
    </script>
    <?php unset($_SESSION['swal_success']); endif; ?>
</body>
</html>
