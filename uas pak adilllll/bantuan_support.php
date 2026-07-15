<?php
session_start();
require_once 'config.php';

$error = "";

// Handle New Chat / Name Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_name']) && !isset($_POST['message'])) {
    $name = trim($_POST['user_name']);
    if (empty($name)) {
        $error = "Nama harus diisi!";
    } else {
        $new_cs_id = 'CS-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        $_SESSION['cs_ticket_id'] = $new_cs_id;
        $_SESSION['cs_user_name'] = $name;
        header("Location: bantuan_support.php");
        exit;
    }
}

// Handle Existing Chat Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['existing_cs_id']) && !isset($_POST['message'])) {
    $existing_id = trim($_POST['existing_cs_id']);
    $stmt = $conn->prepare("SELECT user_name FROM cs_messages WHERE cs_ticket_id = ? LIMIT 1");
    $stmt->bind_param("s", $existing_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['cs_ticket_id'] = $existing_id;
        $_SESSION['cs_user_name'] = $row['user_name'] ? $row['user_name'] : 'Pengguna';
        header("Location: bantuan_support.php");
        exit;
    } else {
        $error = "ID Obrolan tidak ditemukan!";
    }
}

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['cs_ticket_id']);
    unset($_SESSION['cs_user_name']);
    header("Location: bantuan_support.php");
    exit;
}

// Handle Send Message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message']) && isset($_SESSION['cs_ticket_id'])) {
    $msg = trim($_POST['message']);
    if (!empty($msg)) {
        $t_id = $_SESSION['cs_ticket_id'];
        $sender = 'user';
        $uname = $_SESSION['cs_user_name'];
        $ins = $conn->prepare("INSERT INTO cs_messages (cs_ticket_id, user_name, sender_type, message) VALUES (?, ?, ?, ?)");
        $ins->bind_param("ssss", $t_id, $uname, $sender, $msg);
        $ins->execute();
        header("Location: bantuan_support.php");
        exit;
    }
}

$is_logged_in = isset($_SESSION['cs_ticket_id']);
if ($is_logged_in) {
    $msg_stmt = $conn->prepare("SELECT * FROM cs_messages WHERE cs_ticket_id = ? ORDER BY created_at ASC");
    $msg_stmt->bind_param("s", $_SESSION['cs_ticket_id']);
    $msg_stmt->execute();
    $messages = $msg_stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Service - Smart Event Campus</title>
    <link rel="stylesheet" href="style.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Force logo size */
        .logo-img { width: 40px !important; height: 40px !important; flex-shrink: 0; border-radius: 8px; }
        .navbar-brand { font-size: 1.2rem !important; gap: 0.6rem; }

        /* Chat page layout */
        .cs-page-wrap {
            max-width: 760px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        /* Auth card */
        .cs-auth-card {
            background: rgba(30,41,59,0.85);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1.2rem;
            padding: 2.5rem;
            backdrop-filter: blur(12px);
        }
        .cs-auth-card h2 {
            font-size: 1.6rem;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .cs-auth-card p {
            color: #94a3b8;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .cs-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: #64748b;
            font-size: 0.85rem;
        }
        .cs-divider::before, .cs-divider::after {
            content: '';
            flex: 1;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        /* Chat window */
        .cs-chat-card {
            background: rgba(30,41,59,0.85);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1.2rem;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 160px);
            min-height: 450px;
        }
        .cs-chat-header {
            padding: 1.2rem 1.5rem;
            background: rgba(15,23,42,0.9);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .cs-ticket-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(16,185,129,0.15);
            border: 1px solid rgba(16,185,129,0.4);
            color: #10b981;
            border-radius: 2rem;
            padding: 0.3rem 0.8rem;
            font-size: 0.82rem;
            font-weight: 700;
            margin-top: 0.3rem;
            word-break: break-all;
        }
        .cs-messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .msg-bubble {
            max-width: 80%;
            padding: 0.8rem 1.1rem;
            border-radius: 1rem;
            word-break: break-word;
            line-height: 1.5;
            font-size: 0.95rem;
        }
        .msg-user {
            align-self: flex-end;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border-bottom-right-radius: 0.2rem;
        }
        .msg-admin {
            align-self: flex-start;
            background: rgba(255,255,255,0.08);
            color: #e2e8f0;
            border-bottom-left-radius: 0.2rem;
            border: 1px solid rgba(255,255,255,0.06);
        }
        .msg-time {
            display: block;
            font-size: 0.72rem;
            opacity: 0.6;
            margin-top: 0.35rem;
        }
        .cs-input-bar {
            padding: 1rem 1.5rem;
            background: rgba(15,23,42,0.9);
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }
        .cs-input-bar input {
            flex: 1;
            padding: 0.8rem 1.2rem;
            background: rgba(30,41,59,0.9);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 2rem;
            color: #fff;
            font-size: 0.95rem;
            font-family: 'Outfit', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        .cs-input-bar input:focus { border-color: #10b981; }
        .cs-input-bar input::placeholder { color: #64748b; }
        .cs-send-btn {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #f59e0b);
            border: none;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .cs-send-btn:hover { transform: scale(1.1); box-shadow: 0 4px 15px rgba(16,185,129,0.5); }
        @media (max-width: 600px) {
            .cs-chat-card { height: calc(100vh - 120px); }
            .cs-auth-card { padding: 1.5rem; }
            .msg-bubble { max-width: 92%; }
        }
    </style>
</head>
<body>
    <div class="nav-overlay" id="navOverlay" onclick="closeNav()"></div>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <svg class="logo-img" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="100" height="100" rx="20" fill="url(#csGrad1)"/>
                <path d="M50 25L20 40L50 55L80 40L50 25Z" fill="white"/>
                <path d="M20 55V70L50 85L80 70V55L50 70L20 55Z" fill="white" fill-opacity="0.8"/>
                <defs>
                    <linearGradient id="csGrad1" x1="0" y1="0" x2="100" y2="100" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#10b981"/>
                        <stop offset="1" stop-color="#f59e0b"/>
                    </linearGradient>
                </defs>
            </svg>
            Smart Event Campus
        </a>
    </nav>

    <div class="cs-page-wrap">
        <a href="index.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
        </a>
        <?php if (!$is_logged_in): ?>
            <!-- Form Mulai Chat -->
            <div class="cs-auth-card">
                <h2><i class="fa-solid fa-headset" style="color:#10b981;"></i> Pusat Bantuan CS</h2>
                <p>Masukkan nama Anda untuk memulai obrolan baru dengan tim Customer Service kami.</p>

                <?php if(!empty($error)): ?>
                    <div style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.4);border-radius:0.6rem;padding:0.8rem 1rem;margin-bottom:1rem;color:#fca5a5;font-size:0.9rem;">
                        <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label style="color:#94a3b8;font-size:0.9rem;margin-bottom:0.4rem;display:block;">Nama Lengkap</label>
                        <input type="text" name="user_name" class="form-control" placeholder="Contoh: Budi Santoso" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1rem;padding:0.9rem;">
                        <i class="fa-solid fa-comments"></i> Mulai Obrolan Baru
                    </button>
                </form>

                <div class="cs-divider">sudah punya ID obrolan?</div>

                <form method="POST" action="">
                    <div style="display:flex;gap:0.6rem;">
                        <input type="text" name="existing_cs_id" class="form-control" placeholder="Masukkan ID (CS-XXXXXXXX)" required style="flex:1;">
                        <button type="submit" class="btn btn-outline" style="white-space:nowrap;padding:0.7rem 1rem;">
                            <i class="fa-solid fa-arrow-right"></i> Lanjut
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Jendela Chat -->
            <div class="cs-chat-card">
                <div class="cs-chat-header">
                    <div>
                        <div style="color:#fff;font-weight:700;font-size:1rem;margin-bottom:0.2rem;">
                            <i class="fa-solid fa-headset" style="color:#10b981;"></i>
                            CS Bantuan — <?= htmlspecialchars($_SESSION['cs_user_name']) ?>
                        </div>
                        <div class="cs-ticket-badge">
                            <i class="fa-solid fa-key"></i> <?= htmlspecialchars($_SESSION['cs_ticket_id']) ?>
                        </div>
                        <div style="color:#64748b;font-size:0.78rem;margin-top:0.3rem;">Simpan ID ini jika ingin melanjutkan obrolan nanti.</div>
                    </div>
                    <a href="bantuan_support.php?action=logout" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.45rem 0.9rem;border:1px solid rgba(255,255,255,0.2);border-radius:0.5rem;color:#94a3b8;text-decoration:none;font-size:0.85rem;white-space:nowrap;transition:all 0.2s;" onmouseover="this.style.borderColor='#ef4444';this.style.color='#ef4444';" onmouseout="this.style.borderColor='rgba(255,255,255,0.2)';this.style.color='#94a3b8';">
                        <i class="fa-solid fa-right-from-bracket"></i> Akhiri
                    </a>
                </div>

                <div class="cs-messages-area" id="chatMessages">
                    <div class="msg-bubble msg-admin">
                        Halo <strong><?= htmlspecialchars($_SESSION['cs_user_name']) ?></strong>! 👋 Ada yang bisa kami bantu hari ini?
                        <span class="msg-time">Sistem Admin</span>
                    </div>
                    <?php
                    if ($messages && $messages->num_rows > 0) {
                        while($msg = $messages->fetch_assoc()) {
                            $is_user = ($msg['sender_type'] == 'user');
                            $class = $is_user ? 'msg-user' : 'msg-admin';
                            $sender = $is_user ? 'Anda' : 'Admin';
                            $time = date("d M, H:i", strtotime($msg['created_at']));
                            echo '<div class="msg-bubble ' . $class . '">';
                            echo nl2br(htmlspecialchars($msg['message']));
                            echo '<span class="msg-time">' . $sender . ' • ' . $time . '</span>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>

                <form method="POST" action="" class="cs-input-bar">
                    <input type="text" name="message" placeholder="Ketik pesan Anda..." required autocomplete="off" autofocus>
                    <button type="submit" class="cs-send-btn"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
    </script>
</body>
</html>
