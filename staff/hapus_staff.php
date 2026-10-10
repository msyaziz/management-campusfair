<?php
/**
 * hapus_staff.php
 * Menerima AJAX request untuk "menghapus" staff dari daftar final.
 * Data TIDAK dihapus permanen — hanya mereset status_diterima ke 'pending'
 * dan mengosongkan divisi_ditempatkan serta jabatan.
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

// Validate
if ($pendaftar_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID pendaftar tidak valid']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE seleksi 
        SET status_diterima = 'pending', 
            divisi_ditempatkan = NULL,
            jabatan = 'Anggota'
        WHERE pendaftar_id = :pid 
          AND status_diterima = 'diterima'
    ");
    $stmt->execute([':pid' => $pendaftar_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Staff berhasil dikeluarkan dari daftar final']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan atau status sudah bukan diterima']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
