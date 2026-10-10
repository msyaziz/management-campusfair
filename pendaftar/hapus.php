<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    // Delete from seleksi, wawancara, and pendaftar
    $stmt1 = $pdo->prepare("DELETE FROM seleksi WHERE pendaftar_id = ?");
    $stmt1->execute([$id]);

    $stmt2 = $pdo->prepare("DELETE FROM wawancara WHERE pendaftar_id = ?");
    $stmt2->execute([$id]);

    $stmt3 = $pdo->prepare("DELETE FROM pendaftar WHERE id = ?");
    $stmt3->execute([$id]);
}

$return_qs = $_GET['return_qs'] ?? '';

if (!empty($return_qs)) {
    header("Location: /pendaftar/index.php?msg=deleted&" . $return_qs);
} else {
    header("Location: /pendaftar/index.php?msg=deleted");
}
exit;
