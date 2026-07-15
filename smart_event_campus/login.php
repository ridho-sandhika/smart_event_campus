<?php
session_start();
require_once 'config.php';

// Jika sudah login, arahkan ke admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = "Username dan Password harus diisi!";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Password benar, buat session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                header("Location: admin.php");
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Smart Event Campus</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <a href="index.php" class="back-btn" style="margin-top: 1rem; margin-bottom: 0;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
        </a>

        <div class="auth-container glass" style="margin-top: 2rem;">
            <div class="auth-header">
                <i class="fa-solid fa-user-shield" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                <h2>Secure Login</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Administrator Access Only</p>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="username"><i class="fa-solid fa-user" style="color: var(--secondary); margin-right: 0.5rem;"></i> Username</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus placeholder="Masukkan username">
                </div>
            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock" style="color: var(--secondary); margin-right: 0.5rem;"></i> Password</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem; padding: 0.8rem;">
                <i class="fa-solid fa-right-to-bracket"></i> Login ke Dashboard
            </button>
        </form>
    </div>
</body>
</html>
