<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

$pendaftar_id       = (int) ($_REQUEST['id'] ?? $_REQUEST['pendaftar_id'] ?? 0);
$status_diterima    = trim($_REQUEST['status'] ?? $_REQUEST['status_diterima'] ?? 'pending');
$divisi_ditempatkan = trim($_REQUEST['divisi'] ?? $_REQUEST['divisi_ditempatkan'] ?? '');
$decided_by         = $_SESSION['user_id'];

if ($pendaftar_id <= 0) {
    header("Location: /pendaftar/index.php");
    exit;
}

if ($status_diterima !== 'diterima' || empty($divisi_ditempatkan)) {
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

$return_qs = $_REQUEST['return_qs'] ?? '';

if (!empty($return_qs)) {
    header("Location: /pendaftar/index.php?msg=updated&" . $return_qs);
} else {
    $page = (int) ($_REQUEST['page'] ?? 1);
    header("Location: /pendaftar/index.php?msg=updated&page=" . $page);
}
exit;
