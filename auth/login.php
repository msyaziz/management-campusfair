<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika user sengaja menguji/mengunjungi halaman login (GET request) saat ada sesi aktif,
// reset sesi agar tab ini dapat digunakan untuk login ke akun lain tanpa bentrok role.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['username']= $user['username'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['just_logged_in'] = true;

            header("Location: /index.php");
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    }
}

$page_title = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-grow-1 align-items-center justify-content-center">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="card card-custom p-4 p-sm-5 hover-shadow">
                    <div class="text-center mb-4">
                        <h2 class="fw-bolder mb-1 font-archivo"><span class="neo-highlight">LOGIN</span></h2>
                        <p class="text-muted fw-bold mt-2">Masukkan kredensial untuk masuk ke area admin Campus Fair 2027.</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert text-dark border-thick rounded-neo py-3 fw-bold mb-4 d-flex align-items-center" style="background-color: var(--accent-pink); border: 3px solid #000; box-shadow: 4px 4px 0px #000;" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i> 
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" autocomplete="off">
                        <div class="mb-4">
                            <label for="username" class="form-label small fw-bold text-secondary text-uppercase" style="letter-spacing: 0.5px;">Username</label>
                            <input type="text" class="form-control form-control-lg" id="username" name="username" placeholder="admin.campusfair" autocomplete="username" required>
                        </div>
                        <div class="mb-5">
                            <label for="password" class="form-label small fw-bold text-dark text-uppercase" style="letter-spacing: 0.5px;">Password</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                        </div>
                        <button type="submit" class="btn-login-submit w-100 text-center" style="background-color: var(--accent-mint);">
                            <span>MASUK</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
