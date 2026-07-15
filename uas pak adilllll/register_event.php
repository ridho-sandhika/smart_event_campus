<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$event_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND event_date >= CURDATE()");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Kegiatan tidak ditemukan atau sudah berlalu.");
}

// Cek Kuota
$reg_stmt = $conn->prepare("SELECT COUNT(*) as total FROM registrations WHERE event_id = ? AND status != 'rejected'");
$reg_stmt->bind_param("i", $event_id);
$reg_stmt->execute();
$registered = $reg_stmt->get_result()->fetch_assoc()['total'];

$is_full = ($event['max_participants'] > 0 && $registered >= $event['max_participants']);
$error = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$is_full) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $institution = trim($_POST['institution']);
    
    $payment_proof = NULL;
    
    if ($event['is_paid']) {
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            $filename = $_FILES['payment_proof']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed) && $_FILES['payment_proof']['size'] <= 2097152) {
                $new_filename = uniqid() . '_' . time() . '.' . $ext;
                $dest = 'uploads/' . $new_filename;
                if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $dest)) {
                    $payment_proof = $new_filename;
                } else {
                    $error = "Gagal mengupload bukti pembayaran.";
                }
            } else {
                $error = "Format file tidak valid atau ukuran lebih dari 2MB.";
            }
        } else {
            $error = "Bukti pembayaran wajib diupload untuk kegiatan berbayar.";
        }
    }
    
    if (empty($error)) {
        // Generate a unique Ticket ID
        $ticket_id = 'SEC-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        
        $ins = $conn->prepare("INSERT INTO registrations (event_id, ticket_id, full_name, email, phone, institution, payment_proof) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->bind_param("issssss", $event_id, $ticket_id, $full_name, $email, $phone, $institution, $payment_proof);
        if ($ins->execute()) {
            $success = true;
        } else {
            $error = "Gagal menyimpan pendaftaran: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran - <?= htmlspecialchars($event['title']) ?></title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .reg-container { max-width: 600px; margin: 2rem auto; padding: 2rem; }
        .event-summary { background: rgba(99,102,241,0.1); border: 1px solid var(--primary); padding: 1.5rem; border-radius: 1rem; margin-bottom: 2rem; }
        .event-summary h3 { color: #fff; margin-bottom: 0.5rem; font-size: 1.25rem; }
        .event-summary p { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.25rem; }
        .price-tag { display: inline-block; padding: 0.4rem 1rem; background: var(--primary); color: #fff; border-radius: 2rem; font-weight: bold; font-size: 1.1rem; margin-top: 1rem; }
        .bank-info { background: rgba(255,255,255,0.05); border: 1px dashed var(--secondary); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; }
        .bank-info p { margin-bottom: 0.2rem; color: #fff; }
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
        <div class="reg-container glass">
            
            <?php if($success): ?>
                <div style="text-align: center; padding: 2rem 0;">
                    <i class="fa-solid fa-circle-check" style="font-size: 4rem; color: #10b981; margin-bottom: 1rem;"></i>
                    <h2 style="color: #fff; margin-bottom: 1rem;">Pendaftaran Berhasil!</h2>
                    
                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px dashed #10b981; padding: 1.5rem; border-radius: 1rem; margin: 1.5rem 0;">
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">ID Tiket Anda (Simpan untuk layanan Customer Service):</p>
                        <h3 style="color: #10b981; font-size: 2rem; letter-spacing: 2px; margin: 0; user-select: all;"><?= htmlspecialchars($ticket_id ?? '') ?></h3>
                    </div>
                    
                    <p style="color: var(--text-muted); margin-bottom: 2rem;">Terima kasih telah mendaftar di kegiatan <strong><?= htmlspecialchars($event['title']) ?></strong>.<br>Admin akan segera memverifikasi data Anda.</p>
                    <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
                </div>
            <?php else: ?>

                <h2 style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem; margin-bottom: 1.5rem;">Form Pendaftaran</h2>
                
                <div class="event-summary">
                    <h3><?= htmlspecialchars($event['title']) ?></h3>
                    <p><i class="fa-regular fa-calendar"></i> <?= date("d M Y", strtotime($event['event_date'])) ?> | <i class="fa-regular fa-clock"></i> <?= date("H:i", strtotime($event['event_time'])) ?></p>
                    <p><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($event['location']) ?></p>
                    
                    <?php if($event['is_paid']): ?>
                        <div class="price-tag">Rp <?= number_format($event['price'], 0, ',', '.') ?></div>
                    <?php else: ?>
                        <div class="price-tag" style="background: #10b981;">GRATIS</div>
                    <?php endif; ?>
                    
                    <?php if($event['max_participants'] > 0): ?>
                        <p style="margin-top: 1rem; font-weight: bold; color: <?= $is_full ? '#ef4444' : '#10b981' ?>;">
                            Kuota: <?= $registered ?> / <?= $event['max_participants'] ?> Terisi
                        </p>
                    <?php endif; ?>
                </div>

                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if($is_full): ?>
                    <div class="alert alert-danger" style="text-align: center;">
                        <i class="fa-solid fa-ban" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        Mohon maaf, kuota peserta untuk kegiatan ini sudah penuh.
                    </div>
                <?php else: ?>
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>No. HP / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Asal Instansi / Kampus</label>
                            <input type="text" name="institution" class="form-control" required>
                        </div>

                        <?php if($event['is_paid']): ?>
                            <div class="bank-info">
                                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">Silakan transfer sebesar <strong>Rp <?= number_format($event['price'], 0, ',', '.') ?></strong> ke rekening berikut:</p>
                                <p style="font-size: 1.25rem; font-weight: bold;">Bank BCA - 1234567890</p>
                                <p>a.n. Panitia Smart Event Campus</p>
                            </div>
                            <div class="form-group">
                                <label>Upload Bukti Transfer (JPG/PNG/PDF, Max 2MB)</label>
                                <input type="file" name="payment_proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required style="padding: 0.6rem;">
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;"><i class="fa-solid fa-paper-plane"></i> Kirim Pendaftaran</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    <?php if($success): ?>
    Swal.fire({
        title: 'Berhasil Mendaftar!',
        html: 'Data Anda telah tersimpan dan menunggu verifikasi admin.<br>ID Tiket Anda: <strong><?= htmlspecialchars($ticket_id ?? '') ?></strong>.<br>Silakan pantau WhatsApp/Email Anda secara berkala.',
        icon: 'success',
        background: '#1e293b',
        color: '#fff',
        confirmButtonColor: '#10b981'
    });
    <?php endif; ?>
    
    <?php if(!empty($error)): ?>
    Swal.fire({
        title: 'Mohon Maaf',
        text: '<?= htmlspecialchars($error, ENT_QUOTES) ?>',
        icon: 'error',
        background: '#1e293b',
        color: '#fff',
        confirmButtonColor: '#ef4444'
    });
    <?php endif; ?>
    </script>
</body>
</html>
