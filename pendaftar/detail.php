<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT p.*, 
           w.status_wawancara, w.komentar, w.komentar_updated_at, w.pewawancara_id, u.nama as nama_pewawancara,
           s.status_diterima, s.divisi_ditempatkan, s.decided_at, u2.nama as nama_decided_by
    FROM pendaftar p
    LEFT JOIN wawancara w ON p.id = w.pendaftar_id
    LEFT JOIN users u ON w.pewawancara_id = u.id
    LEFT JOIN seleksi s ON p.id = s.pendaftar_id
    LEFT JOIN users u2 ON s.decided_by = u2.id
    WHERE p.id = :id
    LIMIT 1
");
$stmt->execute(['id' => $id]);
$pendaftar = $stmt->fetch();

if (!$pendaftar) {
    die("Pendaftar tidak ditemukan.");
}

$page_title = 'Detail Pendaftar: ' . htmlspecialchars($pendaftar['nama_lengkap']);
$active_nav = 'pendaftar';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="javascript:history.back()" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                <h3 class="fw-bold mb-0 mt-1"><?= htmlspecialchars($pendaftar['nama_lengkap']) ?></h3>
                <div class="text-muted small"><?= htmlspecialchars($pendaftar['asal_kampus_jurusan']) ?> &bull; Lulus <?= htmlspecialchars($pendaftar['tahun_lulus']) ?></div>
            </div>
            <div class="text-end">
                <?php if ($pendaftar['status_diterima'] === 'diterima'): ?>
                    <span class="badge badge-green fs-6 mb-1">Diterima: <?= htmlspecialchars($pendaftar['divisi_ditempatkan']) ?></span>
                <?php elseif ($pendaftar['status_diterima'] === 'tidak_diterima'): ?>
                    <span class="badge badge-coral fs-6 mb-1">Tidak Diterima</span>
                <?php else: ?>
                    <span class="badge badge-purple fs-6 mb-1">Status: Pending</span>
                <?php endif; ?>
                <div class="small text-muted"><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($pendaftar['no_whatsapp']) ?></div>
            </div>
        </div>

        <!-- Section 1: Ringkasan Nilai & Link Berkas -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-custom p-3 text-center">
                    <span class="text-muted small">Skor Komitmen</span>
                    <h2 class="fw-bold text-dark my-1"><?= $pendaftar['skor_komitmen'] ?> <span class="fs-6 text-muted">/10</span></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom p-3 text-center">
                    <span class="text-muted small">Skor Prioritas</span>
                    <h2 class="fw-bold text-dark my-1"><?= $pendaftar['skor_prioritas'] ?> <span class="fs-6 text-muted">/10</span></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom p-3 text-center">
                    <span class="text-muted small">Skor Keyakinan</span>
                    <h2 class="fw-bold text-dark my-1"><?= $pendaftar['skor_keyakinan'] ?> <span class="fs-6 text-muted">/10</span></h2>
                </div>
            </div>
        </div>

        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-folder2-open me-2 text-warning"></i>Tautan Berkas Pendaftar</h5>
            <div class="d-flex gap-3">
                <a href="<?= htmlspecialchars($pendaftar['cv_link']) ?>" target="_blank" class="btn btn-pill-dark btn-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Buka Link CV
                </a>
                <?php if ($pendaftar['portofolio_link']): ?>
                    <a href="<?= htmlspecialchars($pendaftar['portofolio_link']) ?>" target="_blank" class="btn btn-pill-outline btn-sm">
                        <i class="bi bi-briefcase me-1"></i> Buka Portofolio
                    </a>
                <?php else: ?>
                    <span class="text-muted small align-self-center"><em>Portofolio tidak dilampirkan</em></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section 2: Detail Jawaban Form -->
        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-card-text me-2 text-warning"></i>Detail Jawaban & Motivasi</h5>
            
            <div class="mb-4">
                <label class="fw-semibold text-muted small">Alasan Mendaftar Staff CF 2027:</label>
                <div class="p-3 bg-light rounded-3 text-dark mt-1"><?= nl2br(htmlspecialchars($pendaftar['alasan_mendaftar'])) ?></div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 h-100">
                        <h6 class="fw-bold text-primary">Pilihan 1: <?= htmlspecialchars($pendaftar['pilihan_1']) ?></h6>
                        <hr class="my-2">
                        <div class="mb-2">
                            <strong class="small text-muted">Alasan:</strong>
                            <p class="mb-2 small"><?= nl2br(htmlspecialchars($pendaftar['alasan_pilihan_1'])) ?></p>
                        </div>
                        <div>
                            <strong class="small text-muted">Pengalaman:</strong>
                            <p class="mb-0 small"><?= nl2br(htmlspecialchars($pendaftar['pengalaman_pilihan_1'])) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 h-100">
                        <h6 class="fw-bold text-secondary">Pilihan 2: <?= htmlspecialchars($pendaftar['pilihan_2']) ?></h6>
                        <hr class="my-2">
                        <div class="mb-2">
                            <strong class="small text-muted">Alasan:</strong>
                            <p class="mb-2 small"><?= nl2br(htmlspecialchars($pendaftar['alasan_pilihan_2'])) ?></p>
                        </div>
                        <div>
                            <strong class="small text-muted">Pengalaman:</strong>
                            <p class="mb-0 small"><?= nl2br(htmlspecialchars($pendaftar['pengalaman_pilihan_2'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="fw-semibold text-muted small">Kesibukan Bulan Okt - Jan:</label>
                <div class="p-3 bg-light rounded-3 text-dark mt-1"><?= nl2br(htmlspecialchars($pendaftar['kesibukan_okt_jan'])) ?></div>
            </div>
        </div>

        <!-- Section 3: Catatan Hasil Wawancara -->
        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-chat-left-quote me-2 text-warning"></i>Hasil Wawancara</h5>
            <div class="mb-3">
                <span class="fw-semibold text-muted small">Pewawancara:</span>
                <span class="ms-2 fw-bold text-dark"><?= $pendaftar['nama_pewawancara'] ? htmlspecialchars($pendaftar['nama_pewawancara']) : 'Belum di-assign' ?></span>
            </div>
            <div class="mb-3">
                <span class="fw-semibold text-muted small">Status Wawancara:</span>
                <?php if ($pendaftar['status_wawancara'] === 'selesai'): ?>
                    <span class="badge badge-yellow ms-2">Sudah Wawancara</span>
                <?php else: ?>
                    <span class="badge badge-gray ms-2">Belum Wawancara</span>
                <?php endif; ?>
            </div>
            <div>
                <label class="fw-semibold text-muted small">Komentar Pewawancara:</label>
                <div class="p-3 bg-light border-start border-4 border-warning rounded-3 mt-1">
                    <?php if ($pendaftar['komentar']): ?>
                        <?= nl2br(htmlspecialchars($pendaftar['komentar'])) ?>
                        <div class="text-muted small mt-2 fs-7">Diperbarui: <?= htmlspecialchars($pendaftar['komentar_updated_at']) ?></div>
                    <?php else: ?>
                        <em class="text-muted">Belum ada komentar wawancara.</em>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
