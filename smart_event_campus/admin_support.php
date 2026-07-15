<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$active_ticket = isset($_GET['ticket']) ? $_GET['ticket'] : null;

// Handle Send Message (admin reply)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message']) && $active_ticket) {
    $msg = trim($_POST['message']);
    if (!empty($msg)) {
        $sender = 'admin';
        $stmt_uname = $conn->prepare("SELECT user_name FROM cs_messages WHERE cs_ticket_id = ? AND user_name IS NOT NULL LIMIT 1");
        $stmt_uname->bind_param("s", $active_ticket);
        $stmt_uname->execute();
        $res_uname = $stmt_uname->get_result();
        $uname = $res_uname->num_rows > 0 ? $res_uname->fetch_assoc()['user_name'] : 'Pengguna';
        $ins = $conn->prepare("INSERT INTO cs_messages (cs_ticket_id, user_name, sender_type, message) VALUES (?, ?, ?, ?)");
        $ins->bind_param("ssss", $active_ticket, $uname, $sender, $msg);
        $ins->execute();
        header("Location: admin_support.php?ticket=" . urlencode($active_ticket));
        exit;
    }
}

// List all conversations
$tickets_query = "
    SELECT cs_ticket_id,
           MAX(user_name) as user_name,
           (SELECT message FROM cs_messages m2 WHERE m2.cs_ticket_id = m1.cs_ticket_id ORDER BY created_at DESC LIMIT 1) as last_msg,
           MAX(created_at) as last_time
    FROM cs_messages m1
    GROUP BY cs_ticket_id
    ORDER BY last_time DESC
";
$tickets = $conn->query($tickets_query);

$messages = null;
$ticket_display_name = 'Pengguna';

if ($active_ticket) {
    $stmt = $conn->prepare("SELECT user_name FROM cs_messages WHERE cs_ticket_id = ? AND user_name IS NOT NULL LIMIT 1");
    $stmt->bind_param("s", $active_ticket);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $ticket_display_name = $res->fetch_assoc()['user_name'];
    }
    $msg_stmt = $conn->prepare("SELECT * FROM cs_messages WHERE cs_ticket_id = ? ORDER BY created_at ASC");
    $msg_stmt->bind_param("s", $active_ticket);
    $msg_stmt->execute();
    $messages = $msg_stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola CS - Smart Event Campus</title>
    <link rel="stylesheet" href="style.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .logo-img { width: 40px !important; height: 40px !important; flex-shrink: 0; border-radius: 8px; }
        .navbar-brand { font-size: 1.2rem !important; gap: 0.6rem; }

        .cs-admin-wrap {
            max-width: 1400px;
            margin: 1.5rem auto;
            padding: 0 1rem;
        }
        .cs-admin-wrap h2 {
            color: #fff;
            font-size: 1.6rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        /* Grid layout */
        .cs-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 1.2rem;
            height: calc(100vh - 180px);
            min-height: 500px;
        }
        /* Left panel */
        .cs-sidebar {
            background: rgba(30,41,59,0.85);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .cs-sidebar-title {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            background: rgba(0,0,0,0.2);
            font-weight: 700;
            color: #e2e8f0;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .cs-ticket-item {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: block;
            text-decoration: none;
            color: #e2e8f0;
            transition: background 0.2s;
        }
        .cs-ticket-item:hover, .cs-ticket-item.active {
            background: rgba(99,102,241,0.18);
            border-left: 3px solid #10b981;
        }
        .cs-ticket-item .t-name { font-weight: 700; color: #fff; font-size: 0.95rem; margin-bottom: 0.2rem; }
        .cs-ticket-item .t-id { font-size: 0.75rem; color: #10b981; margin-bottom: 0.25rem; }
        .cs-ticket-item .t-preview { font-size: 0.82rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cs-ticket-item .t-time { font-size: 0.72rem; color: #64748b; float: right; }

        /* Right panel */
        .cs-chat-panel {
            background: rgba(30,41,59,0.85);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .cs-chat-head {
            padding: 1rem 1.5rem;
            background: rgba(15,23,42,0.9);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .cs-chat-head h3 { color: #fff; font-size: 1rem; font-weight: 700; }
        .cs-chat-head span { color: #94a3b8; font-size: 0.8rem; }
        .cs-msgs {
            flex: 1;
            overflow-y: auto;
            padding: 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }
        .msg-bbl {
            max-width: 78%;
            padding: 0.75rem 1rem;
            border-radius: 0.9rem;
            word-break: break-word;
            font-size: 0.92rem;
            line-height: 1.5;
        }
        .msg-bbl-admin-sent {
            align-self: flex-end;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border-bottom-right-radius: 0.2rem;
        }
        .msg-bbl-user-sent {
            align-self: flex-start;
            background: rgba(255,255,255,0.08);
            color: #e2e8f0;
            border: 1px solid rgba(255,255,255,0.07);
            border-bottom-left-radius: 0.2rem;
        }
        .msg-t { display: block; font-size: 0.7rem; opacity: 0.6; margin-top: 0.3rem; }
        .cs-reply-bar {
            padding: 0.9rem 1.2rem;
            background: rgba(15,23,42,0.9);
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            gap: 0.6rem;
            align-items: center;
        }
        .cs-reply-bar input {
            flex: 1;
            padding: 0.75rem 1.1rem;
            background: rgba(30,41,59,0.9);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 2rem;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-size: 0.9rem;
            outline: none;
        }
        .cs-reply-bar input:focus { border-color: #10b981; }
        .cs-reply-bar input::placeholder { color: #64748b; }
        .cs-reply-btn {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #f59e0b);
            border: none; color: #fff;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
            transition: transform 0.2s;
        }
        .cs-reply-btn:hover { transform: scale(1.1); }
        .cs-empty {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            height: 100%; color: #64748b; text-align: center;
        }
        .cs-empty i { font-size: 3.5rem; margin-bottom: 1rem; color: rgba(255,255,255,0.08); }
        @media (max-width: 768px) {
            .cs-grid { grid-template-columns: 1fr; height: auto; }
            .cs-sidebar { max-height: 260px; }
            .cs-chat-panel { min-height: 420px; }
        }
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

    <div class="cs-admin-wrap">
        <a href="admin.php" class="back-btn" style="margin-bottom: 1rem;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        <h2><i class="fa-solid fa-headset" style="color:#10b981;"></i> Customer Service</h2>

        <div class="cs-grid">
            <!-- Sidebar -->
            <div class="cs-sidebar">
                <div class="cs-sidebar-title">
                    <i class="fa-solid fa-comments" style="color:#10b981;"></i> Daftar Percakapan
                </div>
                <?php
                if ($tickets && $tickets->num_rows > 0) {
                    while($t = $tickets->fetch_assoc()) {
                        $is_active = ($active_ticket == $t['cs_ticket_id']) ? 'active' : '';
                        $dname = htmlspecialchars($t['user_name'] ?: 'Pengguna');
                        $time = date("d M, H:i", strtotime($t['last_time']));
                        $preview = htmlspecialchars(mb_strimwidth($t['last_msg'], 0, 50, '...'));
                        $url = urlencode($t['cs_ticket_id']);
                        echo "<a href='admin_support.php?ticket={$url}' class='cs-ticket-item {$is_active}'>";
                        echo "<span class='t-time'>{$time}</span>";
                        echo "<div class='t-name'>{$dname}</div>";
                        echo "<div class='t-id'>" . htmlspecialchars($t['cs_ticket_id']) . "</div>";
                        echo "<div class='t-preview'>{$preview}</div>";
                        echo "</a>";
                    }
                } else {
                    echo "<div style='padding:2rem;text-align:center;color:#64748b;'><i class='fa-solid fa-inbox' style='font-size:2rem;margin-bottom:0.75rem;display:block;'></i>Belum ada obrolan masuk.</div>";
                }
                ?>
            </div>

            <!-- Chat Panel -->
            <div class="cs-chat-panel">
                <?php if ($active_ticket): ?>
                    <div class="cs-chat-head">
                        <h3><i class="fa-solid fa-user" style="color:#10b981;"></i> <?= htmlspecialchars($ticket_display_name) ?></h3>
                        <span>ID Obrolan: <?= htmlspecialchars($active_ticket) ?></span>
                    </div>

                    <div class="cs-msgs" id="chatMessages">
                        <div class="msg-bbl msg-bbl-user-sent">
                            Halo <?= htmlspecialchars($ticket_display_name) ?>! 👋 Ada yang bisa kami bantu?
                            <span class="msg-t">Sistem Admin</span>
                        </div>
                        <?php
                        if ($messages && $messages->num_rows > 0) {
                            while($msg = $messages->fetch_assoc()) {
                                $is_admin = ($msg['sender_type'] == 'admin');
                                $cls = $is_admin ? 'msg-bbl-admin-sent' : 'msg-bbl-user-sent';
                                $sndr = $is_admin ? 'Anda (Admin)' : htmlspecialchars($ticket_display_name);
                                $t = date("d M, H:i", strtotime($msg['created_at']));
                                echo '<div class="msg-bbl ' . $cls . '">';
                                echo nl2br(htmlspecialchars($msg['message']));
                                echo '<span class="msg-t">' . $sndr . ' • ' . $t . '</span>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>

                    <form method="POST" action="" class="cs-reply-bar">
                        <input type="text" name="message" placeholder="Ketik balasan..." required autocomplete="off" autofocus>
                        <button type="submit" class="cs-reply-btn"><i class="fa-solid fa-paper-plane"></i></button>
                    </form>
                <?php else: ?>
                    <div class="cs-empty">
                        <i class="fa-solid fa-comments"></i>
                        <p>Pilih percakapan dari daftar di kiri untuk membalas pesan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const cm = document.getElementById('chatMessages');
        if (cm) cm.scrollTop = cm.scrollHeight;
    </script>
</body>
</html>
