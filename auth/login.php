<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: /index.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            header("Location: /index.php");
            exit;
        } else {
            $error = 'Email atau password salah!';
        }
    }
}

$page_title = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex align-items-center" style="min-height: 80vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="card card-custom p-4 p-sm-5 hover-shadow" style="border-radius: 32px;">
                    <div class="text-center mb-5">
                        <div class="d-inline-block bg-warning text-dark p-3 rounded-circle mb-3" style="width: 56px; height: 56px; display: flex !important; align-items: center; justify-content: center; box-shadow: 0 10px 15px -3px rgba(253,224,71,0.4);">
                            <i class="bi bi-award-fill fs-3"></i>
                        </div>
                        <h2 class="fw-bolder mb-1" style="letter-spacing: -0.5px;">Welcome Back</h2>
                        <p class="text-muted small">Campus Fair 2027 Internal System</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert bg-danger-subtle text-danger border-0 rounded-4 py-3 small mb-4 d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i> 
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="email" class="form-label small fw-bold text-secondary text-uppercase" style="letter-spacing: 0.5px;">Email Address</label>
                            <input type="email" class="form-control form-control-lg bg-light border-0" id="email" name="email" placeholder="admin@campusfair.id" required>
                        </div>
                        <div class="mb-5">
                            <label for="password" class="form-label small fw-bold text-secondary text-uppercase" style="letter-spacing: 0.5px;">Password</label>
                            <input type="password" class="form-control form-control-lg bg-light border-0" id="password" name="password" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-pill-dark w-100 py-3 fs-6 d-flex align-items-center justify-content-center gap-2">
                            <span>Sign In</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>

                    <div class="mt-5 text-center">
                        <p class="text-muted small mb-0">Hanya untuk panitia internal yang terdaftar.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
