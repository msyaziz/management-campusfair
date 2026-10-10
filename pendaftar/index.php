<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
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

$search = trim($_GET['search'] ?? '');
$status_wawancara = $_GET['status_wawancara'] ?? '';
$status_diterima = $_GET['status_diterima'] ?? '';

// Pagination parameters
$limit = 15;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Where conditions
$where_clauses = ["1=1"];
$params = [];

if ($search !== '') {
    $where_clauses[] = "(p.nama_lengkap LIKE :search1 OR p.asal_kampus_jurusan LIKE :search2 OR p.no_whatsapp LIKE :search3)";
    $params['search1'] = "%$search%";
    $params['search2'] = "%$search%";
    $params['search3'] = "%$search%";
}

if ($status_wawancara !== '') {
    $where_clauses[] = "w.status_wawancara = :status_wawancara";
    $params['status_wawancara'] = $status_wawancara;
}

if ($status_diterima !== '') {
    $where_clauses[] = "s.status_diterima = :status_diterima";
    $params['status_diterima'] = $status_diterima;
}

$where_sql = implode(' AND ', $where_clauses);

// Count Total Rows for Pagination
$count_sql = "SELECT COUNT(p.id) 
              FROM pendaftar p
              LEFT JOIN wawancara w ON p.id = w.pendaftar_id
              LEFT JOIN seleksi s ON p.id = s.pendaftar_id
              WHERE $where_sql";
$stmtCount = $pdo->prepare($count_sql);
$stmtCount->execute($params);
$total_rows = (int) $stmtCount->fetchColumn();
$total_pages = max(1, ceil($total_rows / $limit));

// Fetch Data with Pagination
$sql = "SELECT p.*, 
               w.status_wawancara, w.pewawancara_id, u.nama as nama_pewawancara,
               s.status_diterima, s.divisi_ditempatkan
        FROM pendaftar p
        LEFT JOIN wawancara w ON p.id = w.pendaftar_id
        LEFT JOIN users u ON w.pewawancara_id = u.id
        LEFT JOIN seleksi s ON p.id = s.pendaftar_id
        WHERE $where_sql
        ORDER BY p.created_at DESC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue(":$key", $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$pendaftar_list = $stmt->fetchAll();

// Get Stats
$stmtStats = $pdo->query("
    SELECT 
        COUNT(p.id) as total,
        COALESCE(SUM(CASE WHEN w.status_wawancara = 'belum' THEN 1 ELSE 0 END), 0) as belum_wawancara,
        COALESCE(SUM(CASE WHEN w.status_wawancara = 'selesai' THEN 1 ELSE 0 END), 0) as sudah_wawancara,
        COALESCE(SUM(CASE WHEN s.status_diterima = 'diterima' THEN 1 ELSE 0 END), 0) as diterima
    FROM pendaftar p
    LEFT JOIN wawancara w ON p.id = w.pendaftar_id
    LEFT JOIN seleksi s ON p.id = s.pendaftar_id
");
$stats = $stmtStats->fetch();

$query_params = [
    'search' => $search,
    'status_wawancara' => $status_wawancara,
    'status_diterima' => $status_diterima,
    'page' => $page
];
$current_query_string = http_build_query($query_params);

$page_title = 'Data Pendaftar';
$active_nav = 'pendaftar';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1 font-archivo"><span class="neo-highlight">Data Pendaftar</span></h2>
        <p class="text-muted small mb-0">Kelola pendaftar calon Staff Campus Fair 2027</p>
    </div>
    <a href="/pendaftar/tambah.php" class="btn btn-primary-orange">
        <i class="bi bi-person-plus-fill me-2"></i>Tambah Pendaftar
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-custom p-3 hover-shadow" style="background-color: var(--accent-pink);">
            <div class="text-dark small fw-bold text-uppercase mb-1">Total Pendaftar</div>
            <div class="d-flex align-items-center justify-content-between">
                <h3 class="fw-bold mb-0 text-dark stat-number"><?= $stats['total'] ?></h3>
                <i class="bi bi-people-fill fs-2"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 hover-shadow" style="background-color: var(--accent-yellow);">
            <div class="text-dark small fw-bold text-uppercase mb-1">Belum Wawancara</div>
            <div class="d-flex align-items-center justify-content-between">
                <h3 class="fw-bold mb-0 text-dark stat-number"><?= $stats['belum_wawancara'] ?></h3>
                <i class="bi bi-clock-history fs-2"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 hover-shadow" style="background-color: var(--accent-blue);">
            <div class="text-dark small fw-bold text-uppercase mb-1">Sudah Wawancara</div>
            <div class="d-flex align-items-center justify-content-between">
                <h3 class="fw-bold mb-0 text-dark stat-number"><?= $stats['sudah_wawancara'] ?></h3>
                <i class="bi bi-chat-square-text-fill fs-2"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 hover-shadow" style="background-color: var(--accent-mint);">
            <div class="text-dark small fw-bold text-uppercase mb-1">Diterima</div>
            <div class="d-flex align-items-center justify-content-between">
                <h3 class="fw-bold mb-0 text-dark stat-number"><?= $stats['diterima'] ?></h3>
                <i class="bi bi-check-circle-fill fs-2"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card card-custom p-3 mb-4">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control rounded-3 fs-6" placeholder="Cari nama, kampus, WA..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="status_wawancara" class="form-select rounded-3 fs-6">
                <option value="">-- Status Wawancara --</option>
                <option value="belum" <?= $status_wawancara === 'belum' ? 'selected' : '' ?>>Belum</option>
                <option value="selesai" <?= $status_wawancara === 'selesai' ? 'selected' : '' ?>>Sudah</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status_diterima" class="form-select rounded-3 fs-6">
                <option value="">-- Status Diterima --</option>
                <option value="pending" <?= $status_diterima === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="diterima" <?= $status_diterima === 'diterima' ? 'selected' : '' ?>>Diterima</option>
                <option value="tidak_diterima" <?= $status_diterima === 'tidak_diterima' ? 'selected' : '' ?>>Tidak Diterima</option>
            </select>
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-dark py-2 px-3 w-100" title="Terapkan Filter">
                <i class="bi bi-funnel-fill"></i>
            </button>
            <?php if ($search || $status_wawancara || $status_diterima): ?>
                <a href="/pendaftar/index.php" class="btn btn-light rounded-3 px-3 py-2" title="Reset"><i class="bi bi-x-circle"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Table Section -->
<div class="card card-custom mb-4">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center align-middle" style="width: 45px; font-size: 0.8rem;">NO</th>
                    <th class="align-middle" style="font-size: 0.8rem;">NAMA PENDAFTAR</th>
                    <th class="text-center align-middle" style="width: 90px; font-size: 0.8rem;">TAHUN LULUS</th>
                    <th class="align-middle" style="width: 300px; font-size: 0.8rem;">PILIHAN 1 & 2</th>
                    <th class="text-center align-middle" style="width: 170px; font-size: 0.8rem;">STATUS WAWANCARA</th>
                    <th class="text-center align-middle" style="width: 145px; font-size: 0.8rem;">STATUS SELEKSI</th>
                    <th class="text-center align-middle" style="width: 160px; font-size: 0.8rem;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendaftar_list)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Belum ada data pendaftar yang cocok.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendaftar_list as $idx => $p): ?>
                        <tr>
                            <td class="fw-bold text-dark text-center align-middle" style="font-size: 0.85rem;"><?= $offset + $idx + 1 ?></td>
                            <td class="align-middle py-3">
                                <div class="fw-bold text-dark" style="font-size: 0.95rem;"><?= htmlspecialchars($p['nama_lengkap']) ?></div>
                                <div class="text-muted fw-semibold" style="font-size: 0.8rem;"><?= htmlspecialchars($p['asal_kampus_jurusan']) ?></div>
                                <div class="text-muted fw-semibold" style="font-size: 0.8rem;"><i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($p['no_whatsapp']) ?></div>
                            </td>
                            <td class="text-center align-middle">
                                <span class="btn btn-outline-dark btn-sm py-1 px-2 fw-bold text-dark rounded-3 pointer-events-none" style="font-size: 0.8rem; border-width: 2px; cursor: default; box-shadow: 2px 2px 0px #000;"><?= htmlspecialchars($p['tahun_lulus']) ?></span>
                            </td>
                            <td class="align-middle">
                                <div class="fw-bold text-dark" style="font-size: 0.85rem;">1. <?= htmlspecialchars($p['pilihan_1']) ?></div>
                                <div class="fw-semibold text-secondary" style="font-size: 0.8rem;">2. <?= htmlspecialchars($p['pilihan_2']) ?></div>
                            </td>
                            <td class="text-center align-middle">
                                <?php if ($p['status_wawancara'] === 'selesai'): ?>
                                    <span class="btn btn-outline-dark btn-sm py-1 px-3 fw-bold rounded-3" style="font-size: 0.75rem; background-color: var(--accent-yellow); box-shadow: 2px 2px 0px #000; cursor: default;">
                                        <i class="bi bi-check-circle-fill me-1"></i>SUDAH
                                    </span>
                                <?php else: ?>
                                    <span class="btn btn-outline-dark btn-sm py-1 px-2 fw-bold rounded-3" style="font-size: 0.75rem; background-color: #FFF; box-shadow: 2px 2px 0px #000; cursor: default;">
                                        <i class="bi bi-clock me-1"></i>BELUM
                                    </span>
                                <?php endif; ?>
                                <?php if ($p['nama_pewawancara']): ?>
                                    <div class="fst-italic text-muted mt-1" style="font-size: 0.75rem;"><i class="bi bi-person me-1"></i><?= htmlspecialchars($p['nama_pewawancara']) ?></div>
                                <?php else: ?>
                                    <div class="fst-italic text-muted mt-1" style="font-size: 0.75rem;">Unassigned</div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center align-middle" style="width: 145px;">
                                <form action="/pendaftar/update_status.php" method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="return_qs" value="<?= htmlspecialchars($current_query_string) ?>">
                                    <select name="status" class="form-select form-select-sm fw-bold rounded-3 py-2 px-3 mx-auto text-center" style="max-width: 110px; font-size: 0.85rem; border: 2.5px solid #000 !important; box-shadow: 3px 3px 0px #000 !important; font-family: 'Inter', sans-serif;" onchange="this.form.submit()">
                                        <option value="pending" <?= $p['status_diterima'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="diterima" <?= $p['status_diterima'] === 'diterima' ? 'selected' : '' ?>>Diterima</option>
                                        <option value="tidak_diterima" <?= $p['status_diterima'] === 'tidak_diterima' ? 'selected' : '' ?>>Ditolak</option>
                                    </select>
                                </form>
                            </td>
                            <td class="text-center align-middle">
                                <?php if ($p['status_diterima'] === 'diterima'): ?>
                                    <!-- Jika Diterima, tampilkan Form Pilihan Divisi (75% dropdown, sisanya button centang) -->
                                    <div class="d-flex flex-column align-items-center gap-1 mx-auto" style="width: 140px;">
                                        <a href="/pendaftar/detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark rounded py-1 px-2 w-100 fw-bold fs-7">
                                            DETAIL
                                        </a>
                                        <form action="/pendaftar/update_status.php" method="POST" class="d-flex gap-1 w-100 align-items-center">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="return_qs" value="<?= htmlspecialchars($current_query_string) ?>">
                                            <input type="hidden" name="status" value="diterima">
                                            <select name="divisi" class="form-select form-select-sm rounded-3 fs-7 py-1 px-1 text-truncate flex-grow-1" style="width: 75%; font-size: 0.75rem; border: 2px solid #000 !important; box-shadow: 2px 2px 0px #000 !important;" required>
                                                <option value="">-- Divisi --</option>
                                                <?php foreach ($divisi_list as $div): ?>
                                                    <option value="<?= $div ?>" <?= ($p['divisi_ditempatkan'] === $div) ? 'selected' : '' ?>>
                                                        <?= ($div === 'Divisi Publikasi, Dekorasi, dan Dokumentasi') ? 'Divisi PDD' : htmlspecialchars($div) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary-orange py-1 px-2 text-nowrap d-flex align-items-center justify-content-center" style="width: 25%;" title="Simpan Divisi"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <!-- Jika Pending / Ditolak, tampilkan Detail & Hapus -->
                                    <div class="d-flex flex-column align-items-center gap-1 mx-auto" style="width: 100px;">
                                        <a href="/pendaftar/detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark rounded py-1 px-2 w-100 fw-bold fs-7">
                                            DETAIL
                                        </a>
                                        <a href="/pendaftar/hapus.php?id=<?= $p['id'] ?>&return_qs=<?= urlencode($current_query_string) ?>" class="btn btn-sm btn-danger rounded py-1 px-2 text-white w-100 fw-bold fs-7" onclick="return confirm('Yakin ingin menghapus pendaftar ini?')">
                                            Hapus
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination Controls -->
<?php if ($total_pages > 1): ?>
    <nav class="d-flex justify-content-between align-items-center pt-2">
        <div class="small text-muted">
            Menampilkan <strong><?= count($pendaftar_list) ?></strong> dari <strong><?= $total_rows ?></strong> data (Halaman <?= $page ?> dari <?= $total_pages ?>)
        </div>
        <ul class="pagination pagination-sm mb-0 gap-1">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link rounded-3 border-0 text-dark fw-bold" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"><i class="bi bi-chevron-left"></i> Prev</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link rounded-3 border-0 <?= $i === $page ? 'bg-dark text-white fw-bold' : 'text-dark' ?>" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link rounded-3 border-0 text-dark fw-bold" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next <i class="bi bi-chevron-right"></i></a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

