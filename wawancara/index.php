<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$user = current_user();
$is_admin = ($user['role'] === 'admin');

if ($is_admin) {
    // Admin View: Get list of interviewers for dropdown assignment
    $stmtInterviewer = $pdo->query("SELECT id, nama, email FROM users WHERE role = 'pewawancara' ORDER BY nama ASC");
    $interviewers = $stmtInterviewer->fetchAll();

    // Progress statistics per interviewer
    $stmtStats = $pdo->query("
        SELECT u.id, u.nama,
               COUNT(w.id) as total_assigned,
               SUM(CASE WHEN w.status_wawancara = 'selesai' THEN 1 ELSE 0 END) as total_selesai
        FROM users u
        LEFT JOIN wawancara w ON u.id = w.pewawancara_id
        WHERE u.role = 'pewawancara'
        GROUP BY u.id, u.nama
    ");
    $interviewer_stats = $stmtStats->fetchAll();

    // All applicants with interview details
    $stmtApplicants = $pdo->query("
        SELECT p.id, p.nama_lengkap, p.asal_kampus_jurusan, p.no_whatsapp, p.pilihan_1, p.pilihan_2,
               w.id as wawancara_id, w.pewawancara_id, w.status_wawancara, w.komentar, u.nama as nama_pewawancara
        FROM pendaftar p
        LEFT JOIN wawancara w ON p.id = w.pendaftar_id
        LEFT JOIN users u ON w.pewawancara_id = u.id
        ORDER BY p.created_at DESC
    ");
    $applicants = $stmtApplicants->fetchAll();

} else {
    // Interviewer View: Only get assigned applicants
    $stmtApplicants = $pdo->prepare("
        SELECT p.id, p.nama_lengkap, p.asal_kampus_jurusan, p.no_whatsapp, p.pilihan_1, p.pilihan_2, p.skor_komitmen, p.skor_prioritas, p.skor_keyakinan,
               w.id as wawancara_id, w.pewawancara_id, w.status_wawancara, w.komentar, w.komentar_updated_at
        FROM pendaftar p
        INNER JOIN wawancara w ON p.id = w.pendaftar_id
        WHERE w.pewawancara_id = :pewawancara_id
        ORDER BY w.status_wawancara ASC, p.created_at DESC
    ");
    $stmtApplicants->execute(['pewawancara_id' => $user['id']]);
    $applicants = $stmtApplicants->fetchAll();
}

$page_title = 'Wawancara';
$active_nav = 'wawancara';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Fitur Wawancara</h2>
        <p class="text-muted small mb-0">
            <?= $is_admin ? 'Kelola alokasi penugasan pewawancara dan pantau progres wawancara' : 'Daftar pendaftar yang di-assign ke Anda' ?>
        </p>
    </div>
</div>

<?php if ($is_admin): ?>
    <!-- Admin Statistics Row -->
    <div class="row g-3 mb-4">
        <?php foreach ($interviewer_stats as $stat): ?>
            <div class="col-md-3">
                <div class="card card-custom p-3">
                    <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($stat['nama']) ?></div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="small text-muted">Progres:</span>
                        <span class="fw-bold text-dark"><?= $stat['total_selesai'] ?> / <?= $stat['total_assigned'] ?> selesai</span>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <?php 
                        $pct = $stat['total_assigned'] > 0 ? round(($stat['total_selesai'] / $stat['total_assigned']) * 100) : 0;
                        ?>
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pct ?>%;"></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Admin Assignment Table -->
    <div class="card card-custom overflow-hidden">
        <div class="table-responsive">
            <table class="table table-custom align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Pendaftar</th>
                        <th>Pilihan 1 & 2</th>
                        <th>Pewawancara saat ini</th>
                        <th>Status Wawancara</th>
                        <th>Assign / Ubah Pewawancara</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applicants as $idx => $a): ?>
                        <tr>
                            <td class="fw-semibold text-muted small"><?= $idx + 1 ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($a['nama_lengkap']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($a['asal_kampus_jurusan']) ?></div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-primary">1. <?= htmlspecialchars($a['pilihan_1']) ?></div>
                                <div class="small text-secondary">2. <?= htmlspecialchars($a['pilihan_2']) ?></div>
                            </td>
                            <td>
                                <?php if ($a['nama_pewawancara']): ?>
                                    <span class="badge badge-purple"><?= htmlspecialchars($a['nama_pewawancara']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-gray text-danger">Belum Di-assign</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($a['status_wawancara'] === 'selesai'): ?>
                                    <span class="badge badge-yellow"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                                <?php else: ?>
                                    <span class="badge badge-gray"><i class="bi bi-clock me-1"></i>Belum</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="/wawancara/assign.php" method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="pendaftar_id" value="<?= $a['id'] ?>">
                                    <select name="pewawancara_id" class="form-select form-select-sm rounded-3">
                                        <option value="">-- Pilih Pewawancara --</option>
                                        <?php foreach ($interviewers as $itv): ?>
                                            <option value="<?= $itv['id'] ?>" <?= ($a['pewawancara_id'] == $itv['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($itv['nama']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-pill-dark btn-sm px-3 text-nowrap">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php else: ?>
    <!-- Interviewer Personal Task List -->
    <div class="card card-custom overflow-hidden">
        <div class="table-responsive">
            <table class="table table-custom align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Pendaftar</th>
                        <th>Pilihan 1 & 2</th>
                        <th>Nilai Komitmen / Keyakinan</th>
                        <th>Status Wawancara</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applicants)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada pendaftar yang di-assign ke Anda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applicants as $idx => $a): ?>
                            <tr>
                                <td class="fw-semibold text-muted small"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($a['nama_lengkap']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($a['asal_kampus_jurusan']) ?></div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-primary">1. <?= htmlspecialchars($a['pilihan_1']) ?></div>
                                    <div class="small text-secondary">2. <?= htmlspecialchars($a['pilihan_2']) ?></div>
                                </td>
                                <td>
                                    <span class="small badge bg-light text-dark border">
                                        Kom: <?= $a['skor_komitmen'] ?> | Prio: <?= $a['skor_prioritas'] ?> | Yakin: <?= $a['skor_keyakinan'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($a['status_wawancara'] === 'selesai'): ?>
                                        <span class="badge badge-yellow"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                                    <?php else: ?>
                                        <span class="badge badge-gray"><i class="bi bi-clock me-1"></i>Belum Wawancara</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="/wawancara/detail.php?id=<?= $a['id'] ?>" class="btn btn-pill-dark btn-sm">
                                        <i class="bi bi-chat-left-text me-1"></i> Input Komentar & Status
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
