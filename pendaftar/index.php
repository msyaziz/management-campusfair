<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

$search = trim($_GET['search'] ?? '');
$status_wawancara = $_GET['status_wawancara'] ?? '';
$status_diterima = $_GET['status_diterima'] ?? '';

// Build Query
$sql = "SELECT p.*, 
               w.status_wawancara, w.pewawancara_id, u.nama as nama_pewawancara,
               s.status_diterima, s.divisi_ditempatkan
        FROM pendaftar p
        LEFT JOIN wawancara w ON p.id = w.pendaftar_id
        LEFT JOIN users u ON w.pewawancara_id = u.id
        LEFT JOIN seleksi s ON p.id = s.pendaftar_id
        WHERE 1=1";

$params = [];

if ($search !== '') {
    $sql .= " AND (p.nama_lengkap LIKE :search OR p.asal_kampus_jurusan LIKE :search OR p.no_whatsapp LIKE :search)";
    $params['search'] = "%$search%";
}

if ($status_wawancara !== '') {
    $sql .= " AND w.status_wawancara = :status_wawancara";
    $params['status_wawancara'] = $status_wawancara;
}

if ($status_diterima !== '') {
    $sql .= " AND s.status_diterima = :status_diterima";
    $params['status_diterima'] = $status_diterima;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pendaftar_list = $stmt->fetchAll();

$page_title = 'Data Pendaftar';
$active_nav = 'pendaftar';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Data Pendaftar</h2>
        <p class="text-muted small mb-0">Kelola pendaftar calon Staff Campus Fair 2027</p>
    </div>
    <a href="/pendaftar/tambah.php" class="btn btn-pill-dark">
        <i class="bi bi-person-plus-fill me-2"></i>Tambah Pendaftar
    </a>
</div>

<!-- Filter Section -->
<div class="card card-custom p-3 mb-4">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control rounded-3 fs-6" placeholder="Cari nama, kampus, WA..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="status_wawancara" class="form-select rounded-3 fs-6">
                <option value="">-- All Status Wawancara --</option>
                <option value="belum" <?= $status_wawancara === 'belum' ? 'selected' : '' ?>>Belum Wawancara</option>
                <option value="selesai" <?= $status_wawancara === 'selesai' ? 'selected' : '' ?>>Sudah Wawancara</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status_diterima" class="form-select rounded-3 fs-6">
                <option value="">-- All Status Diterima --</option>
                <option value="pending" <?= $status_diterima === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="diterima" <?= $status_diterima === 'diterima' ? 'selected' : '' ?>>Diterima</option>
                <option value="tidak_diterima" <?= $status_diterima === 'tidak_diterima' ? 'selected' : '' ?>>Tidak Diterima</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-pill-outline w-100 py-2">Filter</button>
            <?php if ($search || $status_wawancara || $status_diterima): ?>
                <a href="/pendaftar/index.php" class="btn btn-light rounded-pill px-3 py-2" title="Reset"><i class="bi bi-x-circle"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Table Section -->
<div class="card card-custom overflow-hidden">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Pendaftar</th>
                    <th>Tahun Lulus</th>
                    <th>Pilihan 1 & 2</th>
                    <th>Status Wawancara</th>
                    <th>Status Seleksi</th>
                    <th class="text-end">Aksi</th>
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
                            <td class="fw-semibold text-muted small"><?= $idx + 1 ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($p['nama_lengkap']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($p['asal_kampus_jurusan']) ?></div>
                                <div class="text-muted small"><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($p['no_whatsapp']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['tahun_lulus']) ?></span>
                            </td>
                            <td>
                                <div class="small fw-semibold text-primary">1. <?= htmlspecialchars($p['pilihan_1']) ?></div>
                                <div class="small text-secondary">2. <?= htmlspecialchars($p['pilihan_2']) ?></div>
                            </td>
                            <td>
                                <?php if ($p['status_wawancara'] === 'selesai'): ?>
                                    <span class="badge badge-yellow">
                                        <i class="bi bi-check-circle-fill me-1"></i>Sudah Wawancara
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-gray">
                                        <i class="bi bi-clock me-1"></i>Belum Wawancara
                                    </span>
                                <?php endif; ?>
                                <?php if ($p['nama_pewawancara']): ?>
                                    <div class="small text-muted mt-1"><i class="bi bi-person me-1"></i><?= htmlspecialchars($p['nama_pewawancara']) ?></div>
                                <?php else: ?>
                                    <div class="small text-danger mt-1"><em>Unassigned</em></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['status_diterima'] === 'diterima'): ?>
                                    <span class="badge badge-green">
                                        <i class="bi bi-check-lg me-1"></i>Diterima
                                    </span>
                                    <div class="small fw-bold text-success mt-1"><?= htmlspecialchars($p['divisi_ditempatkan']) ?></div>
                                <?php elseif ($p['status_diterima'] === 'tidak_diterima'): ?>
                                    <span class="badge badge-coral">
                                        <i class="bi bi-x-lg me-1"></i>Tidak Diterima
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-purple">
                                        <i class="bi bi-hourglass-split me-1"></i>Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="/pendaftar/detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3 me-1">
                                        Detail
                                    </a>
                                    <button type="button" class="btn btn-sm btn-dark rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalSeleksi<?= $p['id'] ?>">
                                        Set Status
                                    </button>
                                </div>

                                <!-- Modal Set Status Seleksi -->
                                <div class="modal fade text-start" id="modalSeleksi<?= $p['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4 shadow">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold">Keputusan Seleksi</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="/pendaftar/update_status.php" method="POST">
                                                <div class="modal-body py-3">
                                                    <input type="hidden" name="pendaftar_id" value="<?= $p['id'] ?>">
                                                    <p class="mb-3">Tentukan status hasil seleksi untuk <strong><?= htmlspecialchars($p['nama_lengkap']) ?></strong>:</p>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Status Penerimaan</label>
                                                        <select name="status_diterima" class="form-select rounded-3 status-select" required onchange="toggleDivisiSelect(this, <?= $p['id'] ?>)">
                                                            <option value="pending" <?= $p['status_diterima'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                            <option value="diterima" <?= $p['status_diterima'] === 'diterima' ? 'selected' : '' ?>>Diterima</option>
                                                            <option value="tidak_diterima" <?= $p['status_diterima'] === 'tidak_diterima' ? 'selected' : '' ?>>Tidak Diterima</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3 divisi-container-<?= $p['id'] ?>" style="display: <?= $p['status_diterima'] === 'diterima' ? 'block' : 'none' ?>;">
                                                        <label class="form-label fw-semibold small">Divisi Ditempatkan <span class="text-danger">*</span></label>
                                                        <select name="divisi_ditempatkan" class="form-select rounded-3">
                                                            <option value="">-- Pilih Divisi --</option>
                                                            <?php
                                                            $divisi_list = [
                                                                'Humas', 'Acara', 'Konsumsi', 'Sponsorship', 
                                                                'Perlengkapan', 'Keamanan', 'Publikasi, Dekorasi, dan Dokumentasi'
                                                            ];
                                                            foreach ($divisi_list as $div) {
                                                                $selected = ($p['divisi_ditempatkan'] === $div) ? 'selected' : '';
                                                                echo "<option value=\"$div\" $selected>$div</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-pill-outline btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-pill-dark btn-sm">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleDivisiSelect(selectElem, pendaftarId) {
    const container = document.querySelector('.divisi-container-' + pendaftarId);
    if (selectElem.value === 'diterima') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
