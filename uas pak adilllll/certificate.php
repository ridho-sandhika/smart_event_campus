<?php
require_once 'config.php';

if (!isset($_GET['ticket_id'])) {
    die("Tiket tidak valid.");
}

$ticket_id = $_GET['ticket_id'];
$stmt = $conn->prepare("SELECT r.*, e.title as event_title, e.event_date FROM registrations r JOIN events e ON r.event_id = e.id WHERE r.ticket_id = ? AND r.status = 'verified' AND r.attendance_status = 'present'");
$stmt->bind_param("s", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Sertifikat tidak tersedia. Pastikan status pendaftaran Anda Terverifikasi dan Anda telah menghadiri acara (Tandai Hadir).");
}

$data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat - <?php echo htmlspecialchars($data['full_name']); ?></title>
    <style>
        body, html {
            margin: 0; padding: 0;
            font-family: 'Georgia', serif;
            background: #e2e8f0;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh;
        }
        .cert-container {
            width: 800px;
            height: 600px;
            background: #fff;
            padding: 40px;
            box-sizing: border-box;
            border: 20px solid #4f46e5;
            position: relative;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .cert-container::before {
            content: '';
            position: absolute;
            top: 10px; left: 10px; right: 10px; bottom: 10px;
            border: 2px solid #cbd5e1;
            z-index: 1;
        }
        .cert-content {
            position: relative;
            z-index: 2;
            padding-top: 50px;
        }
        h1 {
            font-size: 50px;
            color: #1e293b;
            margin-bottom: 10px;
        }
        h3 {
            font-size: 24px;
            color: #64748b;
            font-weight: normal;
        }
        .name {
            font-size: 48px;
            color: #4f46e5;
            font-weight: bold;
            font-style: italic;
            margin: 40px 0;
            text-transform: capitalize;
        }
        .event {
            font-size: 28px;
            color: #0f172a;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .date {
            font-size: 18px;
            color: #475569;
        }
        .signature {
            margin-top: 60px;
            display: flex;
            justify-content: flex-end;
            padding-right: 50px;
        }
        .signature div {
            text-align: center;
            border-top: 2px solid #000;
            padding-top: 10px;
            width: 200px;
            font-family: 'Arial', sans-serif;
            font-weight: bold;
        }
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #4f46e5;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        @media print {
            .print-btn { display: none; }
            body, html { background: #fff; }
            .cert-container { box-shadow: none; border-color: #4f46e5 !important; }
        }
    </style>
</head>
<body>

    <div class="cert-container">
        <div class="cert-content">
            <h1>SERTIFIKAT PENGHARGAAN</h1>
            <h3>Diberikan Kepada:</h3>
            
            <div class="name"><?php echo htmlspecialchars($data['full_name']); ?></div>
            
            <h3>Atas partisipasinya sebagai Peserta dalam acara:</h3>
            <div class="event"><?php echo htmlspecialchars($data['event_title']); ?></div>
            
            <div class="date">Diselenggarakan pada tanggal <?php echo date("d F Y", strtotime($data['event_date'])); ?></div>
            
            <div class="signature">
                <div>
                    Panitia Penyelenggara
                </div>
            </div>
        </div>
    </div>

    <button class="print-btn" onclick="window.print()">Cetak PDF</button>

</body>
</html>
