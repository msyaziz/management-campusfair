<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = isset($_SESSION['user_id']) ? [
    'nama' => $_SESSION['nama'],
    'role' => $_SESSION['role']
] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>Campus Fair 2027</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<?php if ($user): ?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <div class="bg-warning text-dark p-2 rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-award-fill"></i>
            </div>
            Campus Fair '27
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-4 me-auto mb-2 mb-lg-0 gap-1">
                <?php if ($user['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-pill <?= (isset($active_nav) && $active_nav === 'pendaftar') ? 'active bg-dark text-white' : 'text-muted fw-semibold' ?>" href="/pendaftar/index.php">
                        Data Pendaftar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-pill <?= (isset($active_nav) && $active_nav === 'wawancara') ? 'active bg-dark text-white' : 'text-muted fw-semibold' ?>" href="/wawancara/index.php">
                        Wawancara
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-pill <?= (isset($active_nav) && $active_nav === 'staff') ? 'active bg-dark text-white' : 'text-muted fw-semibold' ?>" href="/staff/index.php">
                        Staff Final
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-pill <?= (isset($active_nav) && $active_nav === 'wawancara') ? 'active bg-dark text-white' : 'text-muted fw-semibold' ?>" href="/wawancara/index.php">
                        Penugasan Wawancara Saya
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block">
                    <div class="fw-bold text-dark fs-7"><?= htmlspecialchars($user['nama']) ?></div>
                    <div class="text-muted small text-capitalize" style="font-size: 0.75rem;"><?= htmlspecialchars($user['role']) ?></div>
                </div>
                <div class="d-flex align-items-center justify-content-center bg-light rounded-circle text-primary fw-bold" style="width: 40px; height: 40px;">
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                </div>
                <a href="/auth/logout.php" class="btn btn-light rounded-circle text-muted" title="Logout" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="py-4">
    <div class="container">
