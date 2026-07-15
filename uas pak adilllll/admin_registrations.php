<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['action']) && isset($_GET['id']) && in_array($_GET['action'], ['verified', 'rejected'])) {
    $status = $_GET['action'];
    $id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE registrations SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    if($stmt->execute()) {
        $_SESSION['swal_success'] = "Status pendaftaran berhasil diubah menjadi " . ucfirst($status) . "!";
    }
    header("Location: admin_registrations.php");
    exit;
}

if (isset($_GET['attendance']) && isset($_GET['id']) && in_array($_GET['attendance'], ['present', 'absent'])) {
    $attendance = $_GET['attendance'];
    $id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE registrations SET attendance_status = ? WHERE id = ?");
    $stmt->bind_param("si", $attendance, $id);
    if($stmt->execute()) {
        $_SESSION['swal_success'] = "Status kehadiran berhasil diubah!";
    }
    header("Location: admin_registrations.php");
    exit;
}

$sql = "SELECT r.*, e.title as event_title, e.is_paid, e.price FROM registrations r JOIN events e ON r.event_id = e.id ORDER BY r.registered_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pendaftaran - Smart Event Campus</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            Kelola Pendaftar
        </a>
    </nav>
    <div class="container">
        <a href="admin.php" class="back-btn" style="margin-top: 1.5rem; margin-bottom: 0;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>

        <div class="admin-header" style="margin-top: 2rem;">
            <h2>Data Pendaftar Kegiatan</h2>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button form="bulkDeleteForm" type="submit" class="btn btn-danger" id="btnHapusTerpilih" disabled><i class="fa-solid fa-trash-can"></i> Hapus Terpilih</button>
                <a href="export_registrations_pdf.php" class="btn btn-primary" style="background: #10b981; border-color: #10b981;"><i class="fa-solid fa-file-pdf"></i> Cetak Laporan PDF</a>
            </div>
        </div>

        <div class="table-container glass" style="animation: fadeIn 0.5s ease-out forwards;">
            <form id="bulkDeleteForm" action="bulk_delete.php" method="POST">
                <input type="hidden" name="type" value="registrations">
                <table style="min-width: 800px;">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;"><input type="checkbox" id="checkAll" class="checkbox-3d"></th>
                            <th>Tgl Daftar</th>
                            <th>Peserta</th>
                            <th>Kontak</th>
                            <th>Instansi</th>
                            <th>Kegiatan</th>
                            <th>Status Daftar</th>
                            <th>Kehadiran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            $delay = 1;
                            while($row = $result->fetch_assoc()) {
                                // Format Nomor WhatsApp (hilangkan 0 di depan, ganti 62)
                                $wa_number = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $row['phone']));
                                
                                // Teks WhatsApp Professional
                                $status_indo = ($row['status'] == 'verified') ? 'TERVERIFIKASI ✅' : (($row['status'] == 'pending') ? 'PENDING ⏳' : 'DITOLAK ❌');
                                $is_paid_text = $row['is_paid'] ? "Berbayar (Rp " . number_format($row['price'],0,',','.') . ")" : "Gratis";
                                
                                $wa_message = "Halo *{$row['full_name']}*,\n\n"
                                    . "Salam hangat dari Panitia *Smart Event Campus* 🎓.\n\n"
                                    . "Berikut adalah detail informasi pendaftaran Anda:\n"
                                    . "──────────────────────\n"
                                    . "🎫 *Kegiatan:* {$row['event_title']}\n"
                                    . "🏫 *Instansi:* {$row['institution']}\n"
                                    . "📅 *Tgl Daftar:* " . date("d M Y H:i", strtotime($row['registered_at'])) . "\n"
                                    . "💰 *Jenis Tiket:* {$is_paid_text}\n"
                                    . "📌 *STATUS:* *{$status_indo}*\n"
                                    . "──────────────────────\n\n";
                                
                                if($row['status'] == 'pending') {
                                    $wa_message .= "Data pendaftaran Anda telah kami terima dan saat ini sedang dalam proses *Review/Pengecekan*. Mohon menunggu informasi verifikasi selanjutnya dari kami. Terima kasih!";
                                } else if ($row['status'] == 'verified') {
                                    $wa_message .= "Selamat! Pendaftaran Anda telah kami *Verifikasi*. Sampai jumpa di acara nanti! 🎉";
                                } else {
                                    $wa_message .= "Mohon maaf, pendaftaran Anda kami *Tolak* karena terdapat kendala pada data atau bukti pembayaran. Silakan periksa kembali atau hubungi kami jika ada pertanyaan.";
                                }
                                
                                $wa_text = urlencode($wa_message);
                                $wa_link = "https://wa.me/" . $wa_number . "?text=" . $wa_text;

                                echo '<tr class="stagger-' . ($delay > 5 ? 5 : $delay++) . '">';
                                echo '<td style="text-align: center;"><input type="checkbox" name="ids[]" value="'.$row['id'].'" class="checkbox-3d chk-item"></td>';
                                echo '<td>' . date("d/m/Y H:i", strtotime($row['registered_at'])) . '</td>';
                                echo '<td><strong>' . htmlspecialchars($row['full_name']) . '</strong></td>';
                                echo '<td>';
                                echo htmlspecialchars($row['email']) . '<br>';
                                echo '<a href="'.$wa_link.'" target="_blank" style="color: #25D366; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 0.25rem;"><i class="fa-brands fa-whatsapp"></i> ' . htmlspecialchars($row['phone']) . '</a>';
                                echo '</td>';
                                echo '<td>' . htmlspecialchars($row['institution']) . '</td>';
                                
                                $event_info = htmlspecialchars($row['event_title']);
                                if($row['is_paid']) {
                                    $event_info .= '<br><span style="color:#10b981; font-size: 0.8rem; font-weight: bold;">Rp ' . number_format($row['price'],0,',','.') . '</span>';
                                }
                                echo '<td>' . $event_info . '</td>';
                                
                                if($row['status'] == 'pending') $badge = '<span class="event-badge" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; margin:0;">Pending</span>';
                                else if($row['status'] == 'verified') $badge = '<span class="event-badge" style="background: rgba(16, 185, 129, 0.2); color: #10b981; margin:0;">Terverifikasi</span>';
                                else $badge = '<span class="event-badge" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; margin:0;">Ditolak</span>';
                                echo '<td>' . $badge . '</td>';
                                
                                $att_badge = ($row['attendance_status'] == 'present') 
                                    ? '<span class="event-badge" style="background: rgba(16, 185, 129, 0.2); color: #10b981; margin:0;"><i class="fa-solid fa-user-check"></i> Hadir</span>' 
                                    : '<span class="event-badge" style="background: rgba(255, 255, 255, 0.1); color: var(--text-muted); margin:0;">-</span>';
                                echo '<td>' . $att_badge . '</td>';
                                
                                echo '<td class="action-buttons">';
                                if($row['is_paid'] && !empty($row['payment_proof'])) {
                                    echo '<a href="uploads/' . htmlspecialchars($row['payment_proof']) . '" target="_blank" class="btn action-btn" style="background: rgba(16,185,129,0.12); border: 1px solid #10b981; color: #10b981;" title="Lihat Bukti Transfer"><i class="fa-solid fa-receipt"></i></a>';
                                }
                                if($row['status'] == 'pending') {
                                    echo '<a href="?action=verified&id=' . $row['id'] . '" class="btn action-btn" style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981;" title="Verifikasi"><i class="fa-solid fa-check"></i></a>';
                                    echo '<a href="?action=rejected&id=' . $row['id'] . '" class="btn action-btn" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #ef4444;" title="Tolak"><i class="fa-solid fa-xmark"></i></a>';
                                }
                                if($row['status'] == 'verified') {
                                    if($row['attendance_status'] == 'absent') {
                                        echo '<a href="?attendance=present&id=' . $row['id'] . '" class="btn action-btn" style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981;" title="Tandai Hadir"><i class="fa-solid fa-user-check"></i></a>';
                                    } else {
                                        echo '<a href="?attendance=absent&id=' . $row['id'] . '" class="btn action-btn" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #ef4444;" title="Batalkan Kehadiran"><i class="fa-solid fa-user-xmark"></i></a>';
                                    }
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="8" style="text-align: center; padding: 3rem;"><i class="fa-regular fa-folder-open" style="font-size: 2rem; color: var(--text-muted); display: block; margin-bottom: 1rem;"></i> Belum ada data pendaftar.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </form>
            
            <div style="margin-top: 2rem; text-align: left;">
                <a href="admin.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
            </div>
        </div>
    </div>

    <script>
        // Check All Logic
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

        // SweetAlert Confirm for Form Submission
        document.getElementById('bulkDeleteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data pendaftar yang dipilih akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#1e293b',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            })
        });

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
    </script>
</body>
</html>
