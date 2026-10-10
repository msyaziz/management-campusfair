<?php
/**
 * update_jabatan.php
 * Menerima AJAX request untuk mengupdate jabatan (Kadiv/Anggota) pada tabel seleksi.
 */
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$pendaftar_id = isset($_POST['pendaftar_id']) ? (int) $_POST['pendaftar_id'] : 0;
$jabatan = isset($_POST['jabatan']) ? trim($_POST['jabatan']) : '';

// Validate
if ($pendaftar_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID pendaftar tidak valid']);
    exit;
}

if (!in_array($jabatan, ['Anggota', 'Kadiv'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Jabatan tidak valid. Pilihan: Anggota atau Kadiv']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE seleksi SET jabatan = :jabatan WHERE pendaftar_id = :pid AND status_diterima = 'diterima'");
    $stmt->execute([
        ':jabatan' => $jabatan,
        ':pid' => $pendaftar_id,
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Jabatan berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan atau tidak berubah']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
