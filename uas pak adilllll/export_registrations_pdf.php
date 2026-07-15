<?php
session_start();
require_once 'config.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT r.*, e.title as event_title, e.is_paid, e.price FROM registrations r JOIN events e ON r.event_id = e.id ORDER BY r.registered_at DESC";
$result = $conn->query($sql);
$regs = [];
while ($row = $result->fetch_assoc()) { $regs[] = $row; }

$total = count($regs);
$verified = count(array_filter($regs, fn($r) => $r['status'] == 'verified'));

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Laporan Pendaftar Kegiatan</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #1e293b; padding: 40px; }
  .header { text-align: center; border-bottom: 3px solid #10b981; padding-bottom: 24px; margin-bottom: 32px; }
  .header h1 { font-size: 28px; color: #10b981; font-weight: 800; }
  .header p { font-size: 13px; color: #64748b; margin-top: 6px; }
  .stats { display: flex; gap: 20px; margin-bottom: 32px; }
  .stat-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-align: center; border-top: 4px solid #10b981; }
  .stat-box h2 { font-size: 32px; font-weight: 800; color: #10b981; }
  .stat-box p { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; }
  table { width: 100%; border-collapse: collapse; font-size: 12px; }
  thead tr { background: #10b981; color: #fff; }
  th { padding: 12px 14px; text-align: left; font-weight: 600; border: 1px solid #10b981; }
  td { padding: 11px 14px; border: 1px solid #e2e8f0; }
  tr:nth-child(even) td { background: #f8fafc; }
  .badge-verified { color: #10b981; font-weight: bold; }
  .badge-pending { color: #f59e0b; font-weight: bold; }
  .badge-rejected { color: #ef4444; font-weight: bold; }
  .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 20px; }
  @media print { body { padding: 20px; } }
</style>
</head>
<body>
<div class="header">
  <h1>📋 Smart Event Campus</h1>
  <p>Laporan Data Pendaftar Kegiatan &mdash; Dicetak pada: <?= date('d F Y, H:i') ?> WIB</p>
</div>

<div class="stats">
  <div class="stat-box"><h2><?= $total ?></h2><p>Total Pendaftar</p></div>
  <div class="stat-box"><h2><?= $verified ?></h2><p>Terverifikasi</p></div>
  <div class="stat-box" style="border-top-color: #f59e0b;"><h2 style="color:#f59e0b;"><?= $total - $verified ?></h2><p>Pending / Ditolak</p></div>
</div>

<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Waktu Daftar</th>
      <th>Nama Lengkap</th>
      <th>Instansi</th>
      <th>Kegiatan</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($regs as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= date("d/m/Y H:i", strtotime($r['registered_at'])) ?></td>
      <td><strong><?= htmlspecialchars($r['full_name']) ?></strong><br><span style="font-size:10px; color:#64748b;"><?= htmlspecialchars($r['email']) ?> | <?= htmlspecialchars($r['phone']) ?></span></td>
      <td><?= htmlspecialchars($r['institution']) ?></td>
      <td><?= htmlspecialchars($r['event_title']) ?><br><span style="font-size:10px; color:#10b981;"><?= $r['is_paid'] ? 'Rp '.number_format($r['price'],0,',','.') : 'Gratis' ?></span></td>
      <td>
        <?php
        if($r['status'] == 'verified') echo '<span class="badge-verified">Terverifikasi</span>';
        elseif($r['status'] == 'pending') echo '<span class="badge-pending">Pending</span>';
        else echo '<span class="badge-rejected">Ditolak</span>';
        ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<div class="footer">
  &copy; <?= date('Y') ?> Smart Event Campus &mdash; Sistem Manajemen Kegiatan Kampus
</div>

<script>window.print();</script>
</body>
</html>
