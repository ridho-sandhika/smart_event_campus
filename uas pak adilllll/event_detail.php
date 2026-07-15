<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit;
}

$event = $result->fetch_assoc();
$date = date("l, d F Y", strtotime($event['event_date']));
$time = date("H:i", strtotime($event['event_time']));

$cal_start = date("Ymd\THis\Z", strtotime($event['event_date'] . ' ' . $event['event_time']));
$cal_end   = date("Ymd\THis\Z", strtotime($event['event_date'] . ' ' . $event['event_time']) + 7200);
$cal_url   = "https://calendar.google.com/calendar/render?action=TEMPLATE&text=" . urlencode($event['title']) . "&dates={$cal_start}/{$cal_end}&details=" . urlencode($event['description']) . "&location=" . urlencode($event['location']);

$page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$share_text = "🚀 *JANGAN LEWATKAN KESEMPATAN INI!*\n\n";
$share_text .= "Segera tingkatkan wawasan dan kembangkan potensimu di acara luar biasa kami:\n\n";
$share_text .= "✨ *{$event['title']}*\n";
$share_text .= "📅 *Tanggal:* $date\n";
$share_text .= "⏰ *Waktu:* $time WIB\n";
$share_text .= "📍 *Lokasi:* {$event['location']}\n\n";
if ($event['is_paid']) {
    $share_text .= "🎟️ *Harga Tiket:* Rp " . number_format($event['price'], 0, ',', '.') . "\n\n";
} else {
    $share_text .= "🎟️ *GRATIS!* (Kuota Terbatas - Segera Daftar!)\n\n";
}
$share_text .= "Ayo bergabung dan perluas koneksimu! 👇\n";
$share_text .= $page_url;

$wa_share_url = "https://api.whatsapp.com/send?text=" . urlencode($share_text);

// Cek Tiket Logic
$ticket_data = null;
if(isset($_POST['check_ticket']) && !empty($_POST['ticket_id'])) {
    $tid = trim($_POST['ticket_id']);
    $t_stmt = $conn->prepare("SELECT * FROM registrations WHERE ticket_id = ? AND event_id = ?");
    $t_stmt->bind_param("si", $tid, $id);
    $t_stmt->execute();
    $t_res = $t_stmt->get_result();
    if($t_res->num_rows > 0) {
        $ticket_data = $t_res->fetch_assoc();
    } else {
        $ticket_error = "Tiket tidak ditemukan untuk acara ini.";
    }
}

// Review Submit Logic
if(isset($_POST['submit_review'])) {
    $r_name = trim($_POST['reviewer_name']);
    $r_rating = intval($_POST['rating']);
    $r_comment = trim($_POST['comment']);
    if($r_rating >= 1 && $r_rating <= 5 && !empty($r_name)) {
        $r_stmt = $conn->prepare("INSERT INTO event_reviews (event_id, reviewer_name, rating, comment) VALUES (?, ?, ?, ?)");
        $r_stmt->bind_param("isis", $id, $r_name, $r_rating, $r_comment);
        if($r_stmt->execute()){
            $review_success = "Terima kasih atas ulasan Anda!";
        }
    }
}

// Fetch Reviews
$rev_stmt = $conn->prepare("SELECT * FROM event_reviews WHERE event_id = ? ORDER BY created_at DESC");
$rev_stmt->bind_param("i", $id);
$rev_stmt->execute();
$reviews = $rev_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> - Smart Event Campus</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($event['description'], 0, 160)); ?>">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
    function toggleNav() {
        document.getElementById('navLinks').classList.toggle('active');
        document.getElementById('navOverlay').classList.toggle('active');
    }
    function closeNav() {
        document.getElementById('navLinks').classList.remove('active');
        document.getElementById('navOverlay').classList.remove('active');
    }
    </script>
    <style>
        .detail-hero {
            position: relative;
            background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(236,72,153,0.15));
            border-radius: 1.5rem;
            overflow: hidden;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }
        .detail-hero-poster {
            width: 100%;
            max-height: 480px;
            object-fit: cover;
            display: block;
        }
        .detail-hero-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(to top, rgba(15,23,42,0.95) 0%, transparent 100%);
            padding: 3rem 2.5rem 2rem;
        }
        .detail-hero-noposter {
            padding: 3.5rem 2.5rem 2rem;
            background: radial-gradient(ellipse at top left, rgba(99,102,241,0.2), transparent 70%),
                        radial-gradient(ellipse at bottom right, rgba(236,72,153,0.2), transparent 70%);
        }
        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 2rem;
            align-items: start;
        }
        .detail-main {}
        .detail-sidebar {}

        .detail-badge-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }
        .detail-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }
        .detail-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .detail-meta-card {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1rem;
            padding: 1.2rem 1.4rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: background 0.3s;
        }
        .detail-meta-card:hover {
            background: rgba(255,255,255,0.1);
        }
        .detail-meta-icon {
            width: 42px;
            height: 42px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .icon-purple { background: rgba(99,102,241,0.2); color: var(--primary); }
        .icon-pink   { background: rgba(236,72,153,0.2); color: var(--secondary); }
        .icon-cyan   { background: rgba(6,182,212,0.2);  color: var(--accent); }
        .icon-green  { background: rgba(16,185,129,0.2); color: #10b981; }

        .detail-meta-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.25rem;
        }
        .detail-meta-value {
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
        }

        .detail-desc-section {
            margin-top: 2rem;
        }
        .detail-desc-section h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .detail-desc-section h2 i { color: var(--primary); }
        .detail-desc-body {
            font-size: 1.05rem;
            line-height: 1.9;
            color: #cbd5e1;
            white-space: pre-wrap;
            background: rgba(255,255,255,0.03);
            border-left: 3px solid var(--primary);
            padding: 1.5rem;
            border-radius: 0 1rem 1rem 0;
        }

        /* Sidebar */
        .sidebar-card {
            border-radius: 1.25rem;
            overflow: hidden;
            position: sticky;
            top: 90px;
        }
        .sidebar-price-block {
            padding: 1.75rem;
            text-align: center;
            background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(236,72,153,0.15));
            border-bottom: 1px solid var(--border-color);
        }
        .sidebar-price-label { font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem; }
        .sidebar-price-value { font-size: 2.5rem; font-weight: 800; color: #10b981; }
        .sidebar-cta {
            padding: 1.5rem;
        }
        .sidebar-cta .btn {
            width: 100%;
            justify-content: center;
            font-size: 1.1rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
        }
        .sidebar-share-label {
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 1rem 0 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .sidebar-share-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .sidebar-share-buttons .btn {
            flex: 1;
            padding: 0.65rem 0.5rem;
            font-size: 0.85rem;
            margin-bottom: 0;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .detail-layout {
                grid-template-columns: 1fr;
            }
            .sidebar-card {
                position: static;
            }
            .detail-title { font-size: 1.9rem; }
        }
        @media (max-width: 600px) {
            .detail-hero-overlay, .detail-hero-noposter { padding: 2rem 1.25rem 1.5rem; }
            .detail-title { font-size: 1.5rem; }
            .detail-meta-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 400px) {
            .detail-meta-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="nav-overlay" id="navOverlay" onclick="closeNav()"></div>
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
        <button class="menu-toggle" onclick="toggleNav()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="nav-links" id="navLinks">
            <a href="register_event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary"><i class="fa-solid fa-ticket"></i> Daftar</a>
        </div>
    </nav>

    <div class="container">
        <a href="index.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Semua Event
        </a>

    <!-- Hero Poster -->
    <div class="detail-hero glass">
        <?php if (!empty($event['poster'])): ?>
            <img src="uploads/posters/<?php echo htmlspecialchars($event['poster']); ?>"
                 alt="Poster <?php echo htmlspecialchars($event['title']); ?>"
                 class="detail-hero-poster">
            <div class="detail-hero-overlay">
        <?php else: ?>
            <div class="detail-hero-noposter">
        <?php endif; ?>
                <div class="detail-badge-row">
                    <span class="event-badge" style="font-size: 0.9rem;"><?php echo htmlspecialchars($event['event_type']); ?></span>
                    <?php if($event['is_paid']): ?>
                        <span style="background: rgba(16,185,129,0.15); color:#10b981; font-weight:700; padding: 0.3rem 0.9rem; border-radius: 2rem; border: 1px solid rgba(16,185,129,0.3); font-size: 0.95rem;">
                            Rp <?php echo number_format($event['price'],0,',','.'); ?>
                        </span>
                    <?php else: ?>
                        <span style="background: rgba(16,185,129,0.15); color:#10b981; font-weight:700; padding: 0.3rem 0.9rem; border-radius: 2rem; border: 1px solid rgba(16,185,129,0.3); font-size: 0.95rem;">
                            GRATIS
                        </span>
                    <?php endif; ?>
                </div>
                <h1 class="detail-title"><?php echo htmlspecialchars($event['title']); ?></h1>
            </div>
    </div>

    <!-- Two-Column Layout -->
    <div class="detail-layout">

        <!-- Main Content -->
        <div class="detail-main">
            <!-- Meta Grid -->
            <div class="detail-meta-grid">
                <div class="detail-meta-card">
                    <div class="detail-meta-icon icon-purple"><i class="fa-regular fa-calendar-check"></i></div>
                    <div>
                        <div class="detail-meta-label">Tanggal</div>
                        <div class="detail-meta-value"><?php echo $date; ?></div>
                    </div>
                </div>
                <div class="detail-meta-card">
                    <div class="detail-meta-icon icon-cyan"><i class="fa-regular fa-clock"></i></div>
                    <div>
                        <div class="detail-meta-label">Waktu Mulai</div>
                        <div class="detail-meta-value"><?php echo $time; ?> WIB</div>
                    </div>
                </div>
                <div class="detail-meta-card">
                    <div class="detail-meta-icon icon-pink"><i class="fa-solid fa-map-location-dot"></i></div>
                    <div>
                        <div class="detail-meta-label">Lokasi</div>
                        <div class="detail-meta-value"><?php echo htmlspecialchars($event['location']); ?></div>
                    </div>
                </div>
                <?php if($event['max_participants'] > 0): ?>
                <div class="detail-meta-card">
                    <div class="detail-meta-icon icon-green"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <div class="detail-meta-label">Kuota Peserta</div>
                        <div class="detail-meta-value"><?php echo $event['max_participants']; ?> Orang</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="detail-desc-section glass" style="padding: 2rem; border-radius: 1.25rem;">
                <h2><i class="fa-solid fa-align-left"></i> Tentang Acara Ini</h2>
                <div class="detail-desc-body"><?php echo htmlspecialchars($event['description']); ?></div>
            </div>

            <!-- Google Maps -->
            <?php if (!empty($event['maps_url'])): ?>
            <div class="glass" style="margin-top: 1.5rem; border-radius: 1.25rem; overflow: hidden;">
                <a href="<?php echo htmlspecialchars($event['maps_url']); ?>" target="_blank"
                   style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem 1.5rem; text-decoration: none; color: var(--text-main); transition: background 0.3s;"
                   onmouseover="this.style.background='rgba(255,255,255,0.05)'"
                   onmouseout="this.style.background=''">
                    <div class="detail-meta-icon icon-pink" style="width: 48px; height: 48px; font-size: 1.4rem; flex-shrink: 0;">
                        <i class="fa-brands fa-google"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 1rem; color: #fff;">Lihat di Google Maps</div>
                        <div style="font-size: 0.9rem; color: var(--text-muted);">Petunjuk arah menuju lokasi acara</div>
                    </div>
                    <i class="fa-solid fa-arrow-up-right-from-square" style="margin-left: auto; color: var(--text-muted);"></i>
                </a>
            </div>
            <?php endif; ?>

            <!-- Calendar -->
            <div class="glass" style="margin-top: 1rem; border-radius: 1.25rem; overflow: hidden;">
                <a href="<?php echo $cal_url; ?>" target="_blank"
                   style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem 1.5rem; text-decoration: none; color: var(--text-main); transition: background 0.3s;"
                   onmouseover="this.style.background='rgba(255,255,255,0.05)'"
                   onmouseout="this.style.background=''">
                    <div class="detail-meta-icon icon-cyan" style="width: 48px; height: 48px; font-size: 1.4rem; flex-shrink: 0;">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 1rem; color: #fff;">Tambah ke Google Calendar</div>
                        <div style="font-size: 0.9rem; color: var(--text-muted);">Ingatkan dirimu agar tidak terlewat</div>
                    </div>
                    <i class="fa-solid fa-arrow-up-right-from-square" style="margin-left: auto; color: var(--text-muted);"></i>
                </a>
            </div>
            <!-- Reviews Section -->
            <div class="detail-desc-section glass" style="padding: 2rem; border-radius: 1.25rem; margin-top: 2rem;">
                <h2><i class="fa-solid fa-star"></i> Ulasan & Penilaian Peserta</h2>
                <?php if(isset($review_success)): ?>
                    <div class="alert alert-success" style="background:#d1fae5; color:#065f46; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;"><?php echo htmlspecialchars($review_success); ?></div>
                <?php endif; ?>
                
                <div style="max-height: 400px; overflow-y: auto; padding-right: 1rem;">
                    <?php if($reviews->num_rows > 0): while($rev = $reviews->fetch_assoc()): ?>
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); padding: 1.25rem; border-radius: 1rem; margin-bottom: 1rem;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;">
                                <strong style="color:#fff;"><?php echo htmlspecialchars($rev['reviewer_name']); ?></strong>
                                <span style="color:#f59e0b;">
                                    <?php for($i=0; $i<$rev['rating']; $i++) echo '<i class="fa-solid fa-star"></i>'; ?>
                                </span>
                            </div>
                            <div style="color:#cbd5e1; font-size:0.95rem;"><?php echo htmlspecialchars($rev['comment']); ?></div>
                            <div style="color:var(--text-muted); font-size:0.8rem; margin-top:0.5rem; text-align:right;">
                                <?php echo date("d/m/Y H:i", strtotime($rev['created_at'])); ?>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div style="color:var(--text-muted); font-style:italic;">Belum ada ulasan untuk acara ini.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="detail-sidebar">
            <div class="sidebar-card glass">
                <div class="sidebar-price-block">
                    <div class="sidebar-price-label">Harga Pendaftaran</div>
                    <?php if($event['is_paid']): ?>
                        <div class="sidebar-price-value">Rp <?php echo number_format($event['price'],0,',','.'); ?></div>
                        <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.4rem;">/ per peserta</div>
                    <?php else: ?>
                        <div class="sidebar-price-value">GRATIS</div>
                        <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.4rem;">Terbuka untuk umum</div>
                    <?php endif; ?>
                </div>

                <div class="sidebar-cta">
                    <a href="register_event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">
                        <i class="fa-solid fa-ticket"></i> Daftar Sekarang
                    </a>

                    <?php if (!empty($event['poster'])): ?>
                    <a href="uploads/posters/<?php echo htmlspecialchars($event['poster']); ?>" download class="btn btn-outline" style="width: 100%; justify-content: center; border-color: #10b981; color: #10b981;">
                        <i class="fa-solid fa-download"></i> Unduh Poster
                    </a>
                    <?php endif; ?>

                    <div class="sidebar-share-label">Bagikan Acara</div>
                    <div class="sidebar-share-buttons">
                        <a href="<?php echo $wa_share_url; ?>" target="_blank" class="btn" style="background: #25D366; color: white; border: none; box-shadow: 0 4px 15px rgba(37,211,102,0.3); flex: 1; justify-content: center;">
                            <i class="fa-brands fa-whatsapp"></i> WhatsApp
                        </a>
                        <button onclick="copyLink()" class="btn btn-outline" id="copyBtn" style="flex: 1; justify-content: center;">
                            <i class="fa-solid fa-link"></i> Salin
                        </button>
                    </div>
                </div>

                <!-- Akses Peserta (Cek Tiket) -->
                <div style="border-top: 1px solid var(--border-color); padding: 1.5rem;">
                    <h3 style="font-size:1.1rem; margin-bottom:1rem; color:#fff;">Sudah Daftar? Cek Tiket</h3>
                    <?php if(isset($ticket_error)): ?>
                        <div style="color:#ef4444; font-size:0.9rem; margin-bottom:1rem;"><?php echo $ticket_error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="text" name="ticket_id" class="form-control" placeholder="Masukkan ID Tiket (TKT-...)" required style="margin-bottom:0.75rem; width:100%;">
                        <button type="submit" name="check_ticket" class="btn btn-primary" style="width:100%; justify-content:center; padding:0.75rem;">Cek Tiket</button>
                    </form>

                    <?php if($ticket_data): ?>
                        <div style="margin-top:1.5rem; padding:1rem; background:rgba(255,255,255,0.05); border-radius:0.75rem; border:1px solid var(--border-color);">
                            <div style="font-weight:bold; color:#fff; margin-bottom:0.5rem; text-align:center;">Status: <?php echo strtoupper($ticket_data['status']); ?></div>
                            
                            <?php if($ticket_data['status'] == 'verified'): ?>
                                <?php if(!empty($event['material_file'])): ?>
                                    <a href="uploads/materials/<?php echo htmlspecialchars($event['material_file']); ?>" download class="btn" style="background:var(--primary); color:white; width:100%; justify-content:center; margin-bottom:0.5rem; font-size:0.9rem;">
                                        <i class="fa-solid fa-file-pdf"></i> Unduh Materi
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($ticket_data['attendance_status'] == 'present'): ?>
                                    <a href="certificate.php?ticket_id=<?php echo urlencode($ticket_data['ticket_id']); ?>" target="_blank" class="btn" style="background:#10b981; color:white; width:100%; justify-content:center; font-size:0.9rem; margin-bottom:1rem;">
                                        <i class="fa-solid fa-award"></i> Unduh Sertifikat
                                    </a>
                                    
                                    <div style="border-top:1px solid rgba(255,255,255,0.1); margin-top:1rem; padding-top:1rem;">
                                        <div style="font-size:0.9rem; font-weight:bold; color:#fff; margin-bottom:0.5rem; text-align:center;">Beri Ulasan</div>
                                        <form method="POST" action="">
                                            <input type="hidden" name="reviewer_name" value="<?php echo htmlspecialchars($ticket_data['full_name']); ?>">
                                            <select name="rating" class="form-control" style="margin-bottom:0.5rem; width:100%; padding:0.5rem; font-size:0.9rem;" required>
                                                <option value="5">⭐⭐⭐⭐⭐ Sangat Baik</option>
                                                <option value="4">⭐⭐⭐⭐ Baik</option>
                                                <option value="3">⭐⭐⭐ Cukup</option>
                                                <option value="2">⭐⭐ Kurang</option>
                                                <option value="1">⭐ Sangat Kurang</option>
                                            </select>
                                            <textarea name="comment" class="form-control" placeholder="Tulis komentar..." style="margin-bottom:0.5rem; width:100%; padding:0.5rem; font-size:0.9rem; resize:vertical; min-height:60px;"></textarea>
                                            <button type="submit" name="submit_review" class="btn btn-outline" style="width:100%; justify-content:center; padding:0.5rem; font-size:0.9rem;">Kirim Ulasan</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info snippets -->
                <div style="border-top: 1px solid var(--border-color); padding: 1.25rem 1.5rem;">
                    <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.6rem;">
                        <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i>
                        Pendaftaran aman & terjamin
                    </div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.6rem;">
                        <i class="fa-solid fa-envelope" style="color: var(--primary);"></i>
                        Konfirmasi dikirim ke email
                    </div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-headset" style="color: var(--primary);"></i>
                        Butuh bantuan? <a href="bantuan_support.php" style="color: var(--primary); margin-left: 0.25rem;">Hubungi CS</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Smart Event Campus — All rights reserved</p>
</footer>

<a href="bantuan_support.php" title="Customer Service" style="position:fixed;bottom:2rem;right:2rem;background:linear-gradient(135deg,#10b981,#f59e0b);color:#fff;width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;box-shadow:0 6px 25px rgba(16,185,129,0.6);z-index:9999;text-decoration:none;animation:csFloat 2s ease-in-out infinite;">
    <i class="fa-solid fa-headset"></i>
</a>
<style>
@keyframes csFloat {
    0%,100%{transform:translateY(0);box-shadow:0 6px 25px rgba(16,185,129,0.6);}
    50%{transform:translateY(-8px);box-shadow:0 14px 30px rgba(99,102,241,0.8);}
}
</style>

<script>
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const btn = document.getElementById('copyBtn');
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Tersalin!';
        btn.style.borderColor = '#10b981';
        btn.style.color = '#10b981';
        setTimeout(() => {
            btn.innerHTML = original;
            btn.style.borderColor = '';
            btn.style.color = '';
        }, 2000);
    });
}
</script>
</body>
</html>
