<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /wawancara/index.php");
    exit;
}

$user = current_user();
$pendaftar_id     = (int) ($_POST['pendaftar_id'] ?? 0);
$komentar         = trim($_POST['komentar'] ?? '');
$status_wawancara = trim($_POST['status_wawancara'] ?? 'belum');

if ($pendaftar_id <= 0) {
    header("Location: /wawancara/index.php");
    exit;
}

// Fetch row to verify ownership/permission
$stmt = $pdo->prepare("SELECT pewawancara_id FROM wawancara WHERE pendaftar_id = :pendaftar_id LIMIT 1");
$stmt->execute(['pendaftar_id' => $pendaftar_id]);
$row = $stmt->fetch();

if (!$row) {
    die("Pendaftar tidak ditemukan.");
}

if ($user['role'] !== 'admin' && $row['pewawancara_id'] != $user['id']) {
    http_response_code(403);
    die("Akses Ditolak: Anda tidak berhak mengubah komentar pendaftar ini.");
}

$stmtUpdate = $pdo->prepare("
    UPDATE wawancara
    SET komentar = :komentar,
        status_wawancara = :status_wawancara,
        komentar_updated_at = NOW()
    WHERE pendaftar_id = :pendaftar_id
");

$stmtUpdate->execute([
    'komentar'         => $komentar,
    'status_wawancara' => $status_wawancara,
    'pendaftar_id'     => $pendaftar_id
]);

header("Location: /wawancara/detail.php?id=" . $pendaftar_id . "&msg=saved");
exit;
