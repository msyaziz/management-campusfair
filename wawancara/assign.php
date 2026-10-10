<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /wawancara/index.php");
    exit;
}

$pendaftar_id   = (int) ($_POST['pendaftar_id'] ?? 0);
$pewawancara_id = !empty($_POST['pewawancara_id']) ? (int) $_POST['pewawancara_id'] : null;

if ($pendaftar_id <= 0) {
    header("Location: /wawancara/index.php");
    exit;
}

$stmt = $pdo->prepare("
    UPDATE wawancara
    SET pewawancara_id = :pewawancara_id
    WHERE pendaftar_id = :pendaftar_id
");

$stmt->execute([
    'pewawancara_id' => $pewawancara_id,
    'pendaftar_id'   => $pendaftar_id
]);

$return_qs = $_POST['return_qs'] ?? '';

if (!empty($return_qs)) {
    header("Location: /wawancara/index.php?msg=assigned&" . $return_qs);
} else {
    $page = (int) ($_POST['page'] ?? 1);
    header("Location: /wawancara/index.php?msg=assigned&page=" . $page);
}
exit;
