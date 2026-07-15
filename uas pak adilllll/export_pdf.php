<?php
require_once 'config.php';

$sql = "SELECT * FROM events ORDER BY event_date ASC";
$result = $conn->query($sql);
$events = [];
while ($row = $result->fetch_assoc()) { $events[] = $row; }

$total = count($events);
$upcoming = array_filter($events, fn($e) => $e['event_date'] >= date('Y-m-d'));
$total_upcoming = count($upcoming);

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
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
  table { width: 100%; border-collapse: collapse; font-size: 13px; }
  thead tr { background: #10b981; color: #fff; }
  th { padding: 12px 14px; text-align: left; font-weight: 600; }
  td { padding: 11px 14px; border-bottom: 1px solid #e2e8f0; }
  tr:nth-child(even) td { background: #f8fafc; }
  .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #d1fae5; color: #10b981; }
  .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 20px; }
  @media print { body { padding: 20px; } }
</style>
</head>
<body>
<div class="header">
  <h1>🎓 Smart Event Campus</h1>
  <p>Laporan Daftar Kegiatan Kampus &mdash; Dicetak pada: <?= date('d F Y, H:i') ?> WIB</p>
</div>

<div class="stats">
  <div class="stat-box"><h2><?= $total ?></h2><p>Total Kegiatan</p></div>
  <div class="stat-box"><h2><?= $total_upcoming ?></h2><p>Akan Datang</p></div>
  <div class="stat-box" style="border-top-color: #f59e0b;"><h2 style="color:#f59e0b;"><?= date('Y') ?></h2><p>Tahun Aktif</p></div>
</div>

<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Judul Kegiatan</th>
      <th>Jenis</th>
      <th>Tanggal</th>
      <th>Waktu</th>
      <th>Lokasi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($events as $i => $e): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><strong><?= htmlspecialchars($e['title']) ?></strong><br><span style="font-size:11px; color:#64748b;"><?= mb_strimwidth(htmlspecialchars($e['description']), 0, 80, '...') ?></span></td>
      <td><span class="badge"><?= htmlspecialchars($e['event_type']) ?></span></td>
      <td><?= date("d M Y", strtotime($e['event_date'])) ?></td>
      <td><?= date("H:i", strtotime($e['event_time'])) ?> WIB</td>
      <td><?= htmlspecialchars($e['location']) ?></td>
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
