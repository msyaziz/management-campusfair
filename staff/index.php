<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$divisi_list = [
    'Humas',
    'Acara',
    'Konsumsi',
    'Sponsorship',
    'Perlengkapan',
    'Keamanan',
    'Publikasi, Dekorasi, dan Dokumentasi'
];

// Fetch accepted applicants
$stmt = $pdo->query("
    SELECT p.id, p.nama_lengkap, p.asal_kampus_jurusan, p.no_whatsapp, p.cv_link, p.portofolio_link,
           s.divisi_ditempatkan,
           w.status_wawancara, w.komentar, u.nama as nama_pewawancara
    FROM pendaftar p
    INNER JOIN seleksi s ON p.id = s.pendaftar_id
    LEFT JOIN wawancara w ON p.id = w.pendaftar_id
    LEFT JOIN users u ON w.pewawancara_id = u.id
    WHERE s.status_diterima = 'diterima'
    ORDER BY p.nama_lengkap ASC
");

$raw_accepted = $stmt->fetchAll();

// Group by division
$staff_by_divisi = [];
foreach ($divisi_list as $div) {
    $staff_by_divisi[$div] = [];
}
foreach ($raw_accepted as $staff) {
    if (isset($staff_by_divisi[$staff['divisi_ditempatkan']])) {
        $staff_by_divisi[$staff['divisi_ditempatkan']][] = $staff;
    }
}

$page_title = 'Staff CF 2027 Final';
$active_nav = 'staff';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Daftar Final Staff CF 2027</h2>
        <p class="text-muted small mb-0">Halaman ini otomatis terisi saat status pendaftar diubah menjadi "Diterima" dan divisi ditempatkan.</p>
    </div>
    <span class="badge badge-green fs-6"><i class="bi bi-people-fill me-1"></i>Total Staff Diterima: <?= count($raw_accepted) ?></span>
</div>

<!-- Tabs Divisi -->
<ul class="nav nav-pills mb-4" id="divisiTab" role="tablist">
    <?php foreach ($divisi_list as $idx => $div): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $idx === 0 ? 'active' : '' ?>" 
                    id="tab-<?= $idx ?>" 
                    data-bs-toggle="tab" 
                    data-bs-target="#pane-<?= $idx ?>" 
                    type="button" role="tab">
                <?= htmlspecialchars($div) ?>
                <span class="badge bg-light text-dark ms-1"><?= count($staff_by_divisi[$div]) ?></span>
            </button>
        </li>
    <?php endforeach; ?>
</ul>

<!-- Tab Contents -->
<div class="tab-content" id="divisiTabContent">
    <?php foreach ($divisi_list as $idx => $div): ?>
        <div class="tab-pane fade <?= $idx === 0 ? 'show active' : '' ?>" id="pane-<?= $idx ?>" role="tabpanel">
            <div class="card card-custom p-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="bi bi-diagram-3-fill me-2 text-warning"></i>Divisi <?= htmlspecialchars($div) ?>
                </h5>
                
                <?php if (empty($staff_by_divisi[$div])): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                        Belum ada staff yang ditempatkan di divisi ini.
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($staff_by_divisi[$div] as $s): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card border rounded-4 p-3 h-100 position-relative hover-shadow">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($s['nama_lengkap']) ?></h6>
                                        <span class="badge badge-green small">Staff Final</span>
                                    </div>
                                    <p class="text-muted small mb-2"><?= htmlspecialchars($s['asal_kampus_jurusan']) ?></p>
                                    
                                    <div class="small text-secondary mb-3">
                                        <i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($s['no_whatsapp']) ?>
                                    </div>

                                    <?php if ($s['komentar']): ?>
                                        <div class="p-2 bg-light rounded-3 small text-muted mb-3 border-start border-3 border-warning">
                                            <i class="bi bi-chat-quote me-1 text-warning"></i>
                                            "<?= htmlspecialchars(mb_strimwidth($s['komentar'], 0, 90, '...')) ?>"
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-auto d-flex justify-content-between align-items-center pt-2 border-top">
                                        <a href="/pendaftar/detail.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                            Detail Lengkap
                                        </a>
                                        <a href="<?= htmlspecialchars($s['cv_link']) ?>" target="_blank" class="text-muted small text-decoration-none" title="Lihat CV">
                                            <i class="bi bi-file-earmark-pdf fs-5"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
