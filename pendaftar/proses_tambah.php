<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /pendaftar/index.php");
    exit;
}

try {
    $pdo->beginTransaction();

    $nama_lengkap         = trim($_POST['nama_lengkap'] ?? '');
    $tahun_lulus          = trim($_POST['tahun_lulus'] ?? '');
    $asal_kampus_jurusan  = trim($_POST['asal_kampus_jurusan'] ?? '');
    $no_whatsapp          = trim($_POST['no_whatsapp'] ?? '');
    $alasan_mendaftar     = trim($_POST['alasan_mendaftar'] ?? '');
    $pilihan_1            = trim($_POST['pilihan_1'] ?? '');
    $alasan_pilihan_1     = trim($_POST['alasan_pilihan_1'] ?? '');
    $pengalaman_pilihan_1 = trim($_POST['pengalaman_pilihan_1'] ?? '');
    $pilihan_2            = trim($_POST['pilihan_2'] ?? '');
    $alasan_pilihan_2     = trim($_POST['alasan_pilihan_2'] ?? '');
    $pengalaman_pilihan_2 = trim($_POST['pengalaman_pilihan_2'] ?? '');
    $kesibukan_okt_jan    = trim($_POST['kesibukan_okt_jan'] ?? '');
    $skor_komitmen        = (int) ($_POST['skor_komitmen'] ?? 0);
    $skor_prioritas       = (int) ($_POST['skor_prioritas'] ?? 0);
    $skor_keyakinan       = (int) ($_POST['skor_keyakinan'] ?? 0);
    $cv_link              = trim($_POST['cv_link'] ?? '');
    $portofolio_link      = trim($_POST['portofolio_link'] ?? '');
    $input_by             = $_SESSION['user_id'];

    if (empty($nama_lengkap) || empty($pilihan_1) || empty($pilihan_2) || empty($cv_link)) {
        throw new Exception("Field wajib belum terisi dengan benar!");
    }
    
    $valid_divisi = [
        'Divisi Humas',
        'Divisi Acara',
        'Divisi Konsumsi',
        'Divisi Sponsorship',
        'Divisi Perlengkapan',
        'Divisi Keamanan',
        'Divisi Publikasi, Dekorasi, dan Dokumentasi'
    ];
    if (!in_array($pilihan_1, $valid_divisi) || !in_array($pilihan_2, $valid_divisi)) {
        throw new Exception("Pilihan divisi tidak valid. Silakan pilih ulang.");
    }

    $stmt = $pdo->prepare("
        INSERT INTO pendaftar (
            nama_lengkap, tahun_lulus, asal_kampus_jurusan, no_whatsapp, alasan_mendaftar,
            pilihan_1, alasan_pilihan_1, pengalaman_pilihan_1,
            pilihan_2, alasan_pilihan_2, pengalaman_pilihan_2,
            kesibukan_okt_jan, skor_komitmen, skor_prioritas, skor_keyakinan,
            cv_link, portofolio_link, input_by
        ) VALUES (
            :nama_lengkap, :tahun_lulus, :asal_kampus_jurusan, :no_whatsapp, :alasan_mendaftar,
            :pilihan_1, :alasan_pilihan_1, :pengalaman_pilihan_1,
            :pilihan_2, :alasan_pilihan_2, :pengalaman_pilihan_2,
            :kesibukan_okt_jan, :skor_komitmen, :skor_prioritas, :skor_keyakinan,
            :cv_link, :portofolio_link, :input_by
        )
    ");

    $stmt->execute([
        'nama_lengkap'         => $nama_lengkap,
        'tahun_lulus'          => $tahun_lulus,
        'asal_kampus_jurusan'  => $asal_kampus_jurusan,
        'no_whatsapp'          => $no_whatsapp,
        'alasan_mendaftar'     => $alasan_mendaftar,
        'pilihan_1'            => $pilihan_1,
        'alasan_pilihan_1'     => $alasan_pilihan_1,
        'pengalaman_pilihan_1' => $pengalaman_pilihan_1,
        'pilihan_2'            => $pilihan_2,
        'alasan_pilihan_2'     => $alasan_pilihan_2,
        'pengalaman_pilihan_2' => $pengalaman_pilihan_2,
        'kesibukan_okt_jan'    => $kesibukan_okt_jan,
        'skor_komitmen'        => $skor_komitmen,
        'skor_prioritas'       => $skor_prioritas,
        'skor_keyakinan'       => $skor_keyakinan,
        'cv_link'              => $cv_link,
        'portofolio_link'      => empty($portofolio_link) ? null : $portofolio_link,
        'input_by'             => $input_by
    ]);

    $pendaftar_id = $pdo->lastInsertId();

    // Auto Insert Wawancara row
    $stmtWawancara = $pdo->prepare("
        INSERT INTO wawancara (pendaftar_id, pewawancara_id, status_wawancara) 
        VALUES (:pendaftar_id, NULL, 'belum')
    ");
    $stmtWawancara->execute(['pendaftar_id' => $pendaftar_id]);

    // Auto Insert Seleksi row
    $stmtSeleksi = $pdo->prepare("
        INSERT INTO seleksi (pendaftar_id, status_diterima) 
        VALUES (:pendaftar_id, 'pending')
    ");
    $stmtSeleksi->execute(['pendaftar_id' => $pendaftar_id]);

    $pdo->commit();
    header("Location: /pendaftar/index.php?msg=success");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header("Location: /pendaftar/tambah.php?error=" . urlencode($e->getMessage()));
    exit;
}
