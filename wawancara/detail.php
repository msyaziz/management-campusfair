<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$user = current_user();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT p.*, 
           w.id as wawancara_id, w.status_wawancara, w.komentar, w.komentar_updated_at, w.pewawancara_id
    FROM pendaftar p
    INNER JOIN wawancara w ON p.id = w.pendaftar_id
    WHERE p.id = :id
    LIMIT 1
");
$stmt->execute(['id' => $id]);
$pendaftar = $stmt->fetch();

if (!$pendaftar) {
    die("Data pendaftar tidak ditemukan.");
}

// Security Check: Interviewer can only access their assigned applicant
if ($user['role'] !== 'admin' && $pendaftar['pewawancara_id'] != $user['id']) {
    http_response_code(403);
    die("Akses Ditolak: Anda hanya berhak mengakses pendaftar yang di-assign ke Anda.");
}

$page_title = 'Wawancara: ' . htmlspecialchars($pendaftar['nama_lengkap']);
$active_nav = 'wawancara';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="/wawancara/index.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Wawancara</a>
                <h3 class="fw-bold mb-0 mt-1"><?= htmlspecialchars($pendaftar['nama_lengkap']) ?></h3>
                <div class="text-muted small"><?= htmlspecialchars($pendaftar['asal_kampus_jurusan']) ?> &bull; Lulus <?= htmlspecialchars($pendaftar['tahun_lulus']) ?></div>
            </div>
            <div>
                <?php if ($pendaftar['status_wawancara'] === 'selesai'): ?>
                    <span class="badge badge-yellow fs-6"><i class="bi bi-check-circle-fill me-1"></i>Selesai Wawancara</span>
                <?php else: ?>
                    <span class="badge badge-gray fs-6"><i class="bi bi-clock me-1"></i>Belum Wawancara</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
            <div class="alert alert-success py-2 small mb-4">
                <i class="bi bi-check-circle-fill me-1"></i> Komentar & status wawancara berhasil disimpan!
            </div>
        <?php endif; ?>

        <!-- Form Input Hasil Wawancara (Halaman ini tempat pewawancara bekerja) -->
        <div class="card card-custom p-4 mb-4 border-2 border-warning">
            <h5 class="fw-bold border-bottom pb-2 mb-3 text-dark">
                <i class="bi bi-pencil-square me-2 text-warning"></i>Input Hasil Wawancara
            </h5>
            <form action="/wawancara/simpan_komentar.php" method="POST">
                <input type="hidden" name="pendaftar_id" value="<?= $pendaftar['id'] ?>">

                <div class="mb-3">
                    <label for="komentar" class="form-label fw-semibold small">Catatan & Komentar Pewawancara</label>
                    <textarea name="komentar" id="komentar" class="form-control rounded-3" rows="5" placeholder="Tuliskan evaluasi, kelebihan, kekurangan, atau kesan hasil wawancara di sini..." required><?= htmlspecialchars($pendaftar['komentar'] ?? '') ?></textarea>
                    <div class="form-text small text-muted">Catatan ini akan menimpa catatan sebelumnya setiap kali disimpan.</div>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-2">
                    <div>
                        <label class="form-label fw-semibold small d-block mb-1">Status Wawancara</label>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="status_wawancara" id="status_belum" value="belum" <?= $pendaftar['status_wawancara'] === 'belum' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-secondary btn-sm rounded-start-pill px-3" for="status_belum">Tandai Belum Selesai</label>

                            <input type="radio" class="btn-check" name="status_wawancara" id="status_selesai" value="selesai" <?= $pendaftar['status_wawancara'] === 'selesai' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-warning text-dark fw-bold btn-sm rounded-end-pill px-3" for="status_selesai">Tandai Selesai Wawancara</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-pill-dark px-4">
                        <i class="bi bi-save me-1"></i> Simpan Catatan Wawancara
                    </button>
                </div>
            </form>
        </div>

        <!-- Read-Only Applicant Information -->
        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-person-lines-fill me-2 text-warning"></i>Data Jawaban Pendaftar (Read-Only)</h5>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <span class="text-muted small d-block">No. WhatsApp:</span>
                    <strong class="text-dark"><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($pendaftar['no_whatsapp']) ?></strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small d-block">Tautan Berkas:</span>
                    <a href="<?= htmlspecialchars($pendaftar['cv_link']) ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill me-1"><i class="bi bi-file-pdf"></i> Lihat CV</a>
                    <?php if ($pendaftar['portofolio_link']): ?>
                        <a href="<?= htmlspecialchars($pendaftar['portofolio_link']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="bi bi-briefcase"></i> Portofolio</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="fw-semibold text-muted small">Alasan Mendaftar:</label>
                <div class="p-3 bg-light rounded-3 text-dark mt-1"><?= nl2br(htmlspecialchars($pendaftar['alasan_mendaftar'])) ?></div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 h-100">
                        <h6 class="fw-bold text-primary">Pilihan 1: <?= htmlspecialchars($pendaftar['pilihan_1']) ?></h6>
                        <hr class="my-2">
                        <strong class="small text-muted">Alasan:</strong>
                        <p class="mb-2 small"><?= nl2br(htmlspecialchars($pendaftar['alasan_pilihan_1'])) ?></p>
                        <strong class="small text-muted">Pengalaman:</strong>
                        <p class="mb-0 small"><?= nl2br(htmlspecialchars($pendaftar['pengalaman_pilihan_1'])) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 h-100">
                        <h6 class="fw-bold text-secondary">Pilihan 2: <?= htmlspecialchars($pendaftar['pilihan_2']) ?></h6>
                        <hr class="my-2">
                        <strong class="small text-muted">Alasan:</strong>
                        <p class="mb-2 small"><?= nl2br(htmlspecialchars($pendaftar['alasan_pilihan_2'])) ?></p>
                        <strong class="small text-muted">Pengalaman:</strong>
                        <p class="mb-0 small"><?= nl2br(htmlspecialchars($pendaftar['pengalaman_pilihan_2'])) ?></p>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="fw-semibold text-muted small">Kesibukan Bulan Okt - Jan:</label>
                    <div class="p-3 bg-light rounded-3 text-dark mt-1"><?= nl2br(htmlspecialchars($pendaftar['kesibukan_okt_jan'])) ?></div>
                </div>
                <div class="col-md-4">
                    <label class="fw-semibold text-muted small">Skor Penilaian Mandiri:</label>
                    <div class="p-3 bg-light rounded-3 text-dark mt-1">
                        <div>Komitmen: <strong><?= $pendaftar['skor_komitmen'] ?>/10</strong></div>
                        <div>Prioritas: <strong><?= $pendaftar['skor_prioritas'] ?>/10</strong></div>
                        <div>Keyakinan: <strong><?= $pendaftar['skor_keyakinan'] ?>/10</strong></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
