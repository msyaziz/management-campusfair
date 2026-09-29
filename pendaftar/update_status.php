<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /pendaftar/index.php");
    exit;
}

$pendaftar_id       = (int) ($_POST['pendaftar_id'] ?? 0);
$status_diterima    = trim($_POST['status_diterima'] ?? 'pending');
$divisi_ditempatkan = trim($_POST['divisi_ditempatkan'] ?? '');
$decided_by         = $_SESSION['user_id'];

if ($pendaftar_id <= 0) {
    header("Location: /pendaftar/index.php");
    exit;
}

if ($status_diterima !== 'diterima') {
    $divisi_ditempatkan = null;
}

$stmt = $pdo->prepare("
    UPDATE seleksi
    SET status_diterima = :status_diterima,
        divisi_ditempatkan = :divisi_ditempatkan,
        decided_by = :decided_by,
        decided_at = NOW()
    WHERE pendaftar_id = :pendaftar_id
");

$stmt->execute([
    'status_diterima'    => $status_diterima,
    'divisi_ditempatkan' => $divisi_ditempatkan,
    'decided_by'         => $decided_by,
    'pendaftar_id'       => $pendaftar_id
]);

header("Location: /pendaftar/index.php?msg=updated");
exit;
