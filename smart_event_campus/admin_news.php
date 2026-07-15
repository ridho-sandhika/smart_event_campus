<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_news'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image_name = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $upload_dir = 'uploads/news/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $image_name = time() . '_' . basename($filename);
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
        }
    }

    $stmt = $conn->prepare("INSERT INTO news (title, content, image) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $image_name);
    if($stmt->execute()){
        $_SESSION['swal_success'] = "Berita berhasil ditambahkan!";
    }
    header("Location: admin_news.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $_SESSION['swal_success'] = "Berita berhasil dihapus!";
    }
    header("Location: admin_news.php");
    exit;
}

$news = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Berita Kampus - Smart Event Campus</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .container { max-width: 900px; padding: 2rem; margin: 0 auto; }
        .news-card { display: flex; gap: 1rem; background: rgba(255,255,255,0.9); padding: 1rem; border-radius: 1rem; margin-bottom: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .news-card img { width: 150px; height: 100px; object-fit: cover; border-radius: 0.5rem; }
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
            Berita Kampus
        </a>
    </nav>
    <div class="container">
        <a href="admin.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        <div class="glass" style="padding: 2rem; border-radius: 1rem; margin-bottom: 2rem;">
            <h2>Tambah Berita</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="title" class="form-control" placeholder="Judul Berita" required style="margin-bottom: 1rem;">
                <textarea name="content" class="form-control" placeholder="Isi Berita..." required style="margin-bottom: 1rem; min-height: 100px;"></textarea>
                <input type="file" name="image" class="form-control" accept="image/*" style="margin-bottom: 1rem;">
                <button type="submit" name="add_news" class="btn btn-primary">Simpan Berita</button>
            </form>
        </div>
        
        <h2>Daftar Berita</h2>
        <?php while($row = $news->fetch_assoc()): ?>
            <div class="news-card">
                <?php if($row['image']): ?>
                    <img src="uploads/news/<?php echo $row['image']; ?>" alt="News">
                <?php else: ?>
                    <div style="width:150px; height:100px; background:#e2e8f0; border-radius:0.5rem; display:flex; align-items:center; justify-content:center; color:#94a3b8;"><i class="fa-regular fa-image"></i></div>
                <?php endif; ?>
                <div>
                    <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p style="color: #64748b; font-size: 0.9rem;"><?php echo substr(htmlspecialchars($row['content']), 0, 100); ?>...</p>
                    <div style="margin-top: 1rem;">
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn" style="background:#ef4444; color:#fff; font-size: 0.8rem; padding: 0.4rem 0.8rem;">Hapus</a>
                    </div>
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
