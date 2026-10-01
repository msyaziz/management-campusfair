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
<div class="wrapper">
    <!-- Sidebar -->
    <aside class="sidebar-custom shadow-sm">
        <div class="sidebar-brand d-flex align-items-center justify-content-center gap-2">
            <span>CAMPUS FAIR '27</span>
        </div>
        
        <nav class="sidebar-nav">
            <?php if ($user['role'] === 'admin'): ?>
            <a href="/pendaftar/index.php" class="nav-link <?= (isset($active_nav) && $active_nav === 'pendaftar') ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Pendaftar
            </a>
            <a href="/wawancara/index.php" class="nav-link <?= (isset($active_nav) && $active_nav === 'wawancara') ? 'active' : '' ?>">
                <i class="bi bi-mic"></i> Wawancara
            </a>
            <a href="/staff/index.php" class="nav-link <?= (isset($active_nav) && $active_nav === 'staff') ? 'active' : '' ?>">
                <i class="bi bi-diagram-3"></i> Staff Final
            </a>
            <?php else: ?>
            <a href="/wawancara/index.php" class="nav-link <?= (isset($active_nav) && $active_nav === 'wawancara') ? 'active' : '' ?>">
                <i class="bi bi-mic"></i> Penugasan Wawancara
            </a>
            <?php endif; ?>
        </nav>
        
        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center bg-light rounded-circle text-primary fw-bold" style="width: 40px; height: 40px; flex-shrink: 0;">
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.85rem;"><?= htmlspecialchars($user['nama']) ?></div>
                    <div class="text-muted small text-capitalize" style="font-size: 0.75rem;"><?= htmlspecialchars($user['role']) ?></div>
                </div>
                <a href="/auth/logout.php" class="text-muted p-2 hover-shadow rounded bg-light" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </aside>

    <main class="main-content d-flex flex-column">
        <div class="d-md-none mb-3 d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-dark" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>
            <span class="fw-bold font-archivo">CAMPUS FAIR '27</span>
        </div>
        <div class="container-fluid flex-grow-1">
<?php else: ?>
<main class="py-4 flex-grow-1 d-flex flex-column" style="background: linear-gradient(180deg, #DCEBFD 0%, #F5FAFF 100%);">
    <div class="container flex-grow-1">
<?php endif; ?>
