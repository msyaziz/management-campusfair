<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$divisi_list = [
    'Divisi Humas',
    'Divisi Acara',
    'Divisi Konsumsi',
    'Divisi Sponsorship',
    'Divisi Perlengkapan',
    'Divisi Keamanan',
    'Divisi Publikasi, Dekorasi, dan Dokumentasi'
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
        <h2 class="fw-bold mb-1 font-archivo"><span class="neo-highlight">Daftar Final Staff CF 2027</span></h2>
        <p class="text-muted small mb-0">Halaman ini otomatis terisi saat status pendaftar diubah menjadi "Diterima" dan divisi ditempatkan.</p>
    </div>
    <span class="badge badge-green fs-6"><i class="bi bi-people-fill me-1"></i>Total Staff Diterima: <?= count($raw_accepted) ?></span>
</div>

<!-- Tabs Divisi -->
<ul class="nav nav-pills mb-4" id="divisiTab" role="tablist">
    <?php foreach ($divisi_list as $idx => $div): 
        $slug = 'div-' . preg_replace('/[^a-z0-9]/', '', strtolower($div));
        $target_id = 'pane-' . $slug;
    ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $idx === 0 ? 'active' : '' ?>" 
                    id="tab-<?= $slug ?>" 
                    data-bs-toggle="pill" 
                    data-bs-target="#<?= $target_id ?>" 
                    type="button" role="tab" 
                    aria-controls="<?= $target_id ?>" 
                    aria-selected="<?= $idx === 0 ? 'true' : 'false' ?>">
                <?= htmlspecialchars($div) ?>
                <span class="badge bg-light text-dark ms-1"><?= count($staff_by_divisi[$div]) ?></span>
            </button>
        </li>
    <?php endforeach; ?>
</ul>

<!-- Tab Contents -->
<div class="tab-content" id="divisiTabContent">
    <?php foreach ($divisi_list as $idx => $div): 
        $slug = 'div-' . preg_replace('/[^a-z0-9]/', '', strtolower($div));
        $target_id = 'pane-' . $slug;
    ?>
        <div class="tab-pane fade <?= $idx === 0 ? 'show active' : '' ?>" id="<?= $target_id ?>" role="tabpanel" aria-labelledby="tab-<?= $slug ?>">
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-diagram-3-fill me-2 text-dark"></i><?= htmlspecialchars($div) ?>
                    </h5>
                    <button class="btn btn-outline-dark btn-sm rounded-3 fw-bold" onclick="alert('Kadiv <?= htmlspecialchars($div) ?>: [Belum ditentukan / Silakan atur di Pengaturan]')">
                        <i class="bi bi-person-badge-fill me-1"></i> Kadiv
                    </button>
                </div>
                
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
                                        <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($s['no_whatsapp']) ?>
                                    </div>

                                    <?php if ($s['komentar']): ?>
                                        <div class="p-2 bg-light rounded-3 small text-muted mb-3 border-start border-3 border-dark">
                                            <i class="bi bi-chat-quote me-1 text-dark"></i>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('#divisiTab button[data-bs-toggle="pill"]');
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            });
            
            // Add active class to clicked tab
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            
            // Hide all tab panes
            const tabPanes = document.querySelectorAll('#divisiTabContent .tab-pane');
            tabPanes.forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Show targeted tab pane
            const targetId = this.getAttribute('data-bs-target');
            const targetPane = document.querySelector(targetId);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
