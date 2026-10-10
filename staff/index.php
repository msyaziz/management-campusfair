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

// Fetch accepted applicants — ORDER BY: Kadiv first, then Anggota, then by name
$stmt = $pdo->query("
    SELECT p.id, p.nama_lengkap, p.asal_kampus_jurusan, p.no_whatsapp, p.tahun_lulus,
           p.cv_link, p.portofolio_link,
           s.divisi_ditempatkan, s.jabatan,
           w.status_wawancara, w.komentar, u.nama as nama_pewawancara
    FROM pendaftar p
    INNER JOIN seleksi s ON p.id = s.pendaftar_id
    LEFT JOIN wawancara w ON p.id = w.pendaftar_id
    LEFT JOIN users u ON w.pewawancara_id = u.id
    WHERE s.status_diterima = 'diterima'
    ORDER BY FIELD(s.jabatan, 'Kadiv', 'Anggota'), p.nama_lengkap ASC
");

$raw_accepted = $stmt->fetchAll();

// Group by division — termasuk yang belum ditempatkan (divisi NULL)
$staff_by_divisi = [];
$staff_belum_ditempatkan = [];
foreach ($divisi_list as $div) {
    $staff_by_divisi[$div] = [];
}
foreach ($raw_accepted as $staff) {
    if (empty($staff['divisi_ditempatkan'])) {
        $staff_belum_ditempatkan[] = $staff;
    } elseif (isset($staff_by_divisi[$staff['divisi_ditempatkan']])) {
        $staff_by_divisi[$staff['divisi_ditempatkan']][] = $staff;
    }
}

// Gabungkan tab list: Belum Ditempatkan di depan jika ada isinya
$all_tabs = [];
if (!empty($staff_belum_ditempatkan)) {
    $all_tabs['Belum Ditempatkan'] = $staff_belum_ditempatkan;
}
foreach ($divisi_list as $div) {
    $all_tabs[$div] = $staff_by_divisi[$div];
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
    <?php foreach ($all_tabs as $idx => $members): 
        $div = $idx; // key = nama divisi / "Belum Ditempatkan"
        $slug = 'div-' . preg_replace('/[^a-z0-9]/', '', strtolower($div));
        $target_id = 'pane-' . $slug;
        $is_first = (array_key_first($all_tabs) === $idx);
    ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $is_first ? 'active' : '' ?> <?= ($div === 'Belum Ditempatkan' && !empty($members)) ? 'text-danger' : '' ?>" 
                    id="tab-<?= $slug ?>" 
                    data-bs-toggle="pill" 
                    data-bs-target="#<?= $target_id ?>" 
                    type="button" role="tab" 
                    aria-controls="<?= $target_id ?>" 
                    aria-selected="<?= $is_first ? 'true' : 'false' ?>">
                <?php if ($div === 'Belum Ditempatkan'): ?>
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?php endif; ?>
                <?= htmlspecialchars($div) ?>
                <span class="badge bg-light text-dark ms-1"><?= count($members) ?></span>
            </button>
        </li>
    <?php endforeach; ?>
</ul>

<!-- Tab Contents -->
<div class="tab-content" id="divisiTabContent">
    <?php foreach ($all_tabs as $idx => $members): 
        $div = $idx;
        $slug = 'div-' . preg_replace('/[^a-z0-9]/', '', strtolower($div));
        $target_id = 'pane-' . $slug;
        $is_first = (array_key_first($all_tabs) === $idx);
    ?>
        <div class="tab-pane fade <?= $is_first ? 'show active' : '' ?>" id="<?= $target_id ?>" role="tabpanel" aria-labelledby="tab-<?= $slug ?>">
            <div class="card card-custom mb-4">
                <div class="d-flex justify-content-between align-items-center p-3 pb-0 mb-3">
                    <h5 class="fw-bold mb-0">
                        <?php if ($div === 'Belum Ditempatkan'): ?>
                            <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
                        <?php else: ?>
                            <i class="bi bi-diagram-3-fill me-2 text-dark"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($div) ?>
                    </h5>
                    <span class="badge text-dark fw-bold" style="background-color: var(--accent-blue); border: 2px solid #000; box-shadow: 2px 2px 0px #000;"><?= count($members) ?> orang</span>
                </div>

                <?php if ($div === 'Belum Ditempatkan'): ?>
                    <div class="p-3 pt-2">
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Staff ini sudah berstatus "Diterima" tetapi belum ditempatkan ke divisi. Silakan atur divisi di halaman <a href="/pendaftar/index.php" class="fw-bold">Pendaftar</a>.</small>
                    </div>
                <?php endif; ?>

                <?php if (empty($members)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                        Belum ada staff yang ditempatkan di divisi ini.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 staff-table" data-divisi="<?= htmlspecialchars($slug) ?>" style="border-color: #000 !important;">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width: 45px; font-size: 0.8rem; background-color: var(--accent-yellow); color: #000;">NO</th>
                                    <th class="align-middle" style="font-size: 0.8rem; background-color: var(--accent-yellow); color: #000;">NAMA PENDAFTAR</th>
                                    <th class="text-center align-middle" style="width: 110px; font-size: 0.8rem; background-color: var(--accent-yellow); color: #000;">TAHUN LULUS</th>
                                    <th class="text-center align-middle" style="width: 150px; font-size: 0.8rem; background-color: var(--accent-yellow); color: #000;">KETERANGAN</th>
                                    <th class="text-center align-middle" style="width: 200px; font-size: 0.8rem; background-color: var(--accent-yellow); color: #000;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $no => $s): ?>
                                    <tr data-id="<?= $s['id'] ?>" data-jabatan="<?= htmlspecialchars($s['jabatan']) ?>">
                                        <td class="fw-bold text-dark text-center align-middle row-number" style="font-size: 0.85rem;"><?= $no + 1 ?></td>
                                        <td class="align-middle py-3">
                                            <div class="fw-bold text-dark" style="font-size: 0.95rem;"><?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                            <div class="text-muted fw-semibold" style="font-size: 0.8rem;"><?= htmlspecialchars($s['asal_kampus_jurusan']) ?></div>
                                            <div class="text-muted fw-semibold" style="font-size: 0.8rem;"><i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($s['no_whatsapp']) ?></div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="btn btn-outline-dark btn-sm py-1 px-2 fw-bold text-dark rounded-3 pointer-events-none" style="font-size: 0.8rem; border-width: 2px; cursor: default; box-shadow: 2px 2px 0px #000;"><?= htmlspecialchars($s['tahun_lulus']) ?></span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <select class="form-select form-select-sm fw-bold rounded-3 jabatan-select mx-auto"
                                                    data-id="<?= $s['id'] ?>"
                                                    style="max-width: 130px; font-size: 0.85rem; border: 2.5px solid #000 !important; box-shadow: 3px 3px 0px #000 !important;">
                                                <option value="Anggota" <?= $s['jabatan'] === 'Anggota' ? 'selected' : '' ?>>Anggota</option>
                                                <option value="Kadiv" <?= $s['jabatan'] === 'Kadiv' ? 'selected' : '' ?>>Kadiv</option>
                                            </select>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="/pendaftar/detail.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-dark rounded py-1 px-3 fw-bold" style="font-size: 0.8rem;">
                                                    DETAIL
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger rounded py-1 px-3 fw-bold btn-hapus-staff"
                                                        data-id="<?= $s['id'] ?>"
                                                        data-nama="<?= htmlspecialchars($s['nama_lengkap']) ?>"
                                                        style="font-size: 0.8rem;">
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- jQuery CDN for AJAX & animations -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

<script>
$(document).ready(function() {

    // =====================================================
    // Tab switching
    // =====================================================
    const tabButtons = document.querySelectorAll('#divisiTab button[data-bs-toggle="pill"]');
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            const tabPanes = document.querySelectorAll('#divisiTabContent .tab-pane');
            tabPanes.forEach(pane => pane.classList.remove('show', 'active'));
            const targetId = this.getAttribute('data-bs-target');
            const targetPane = document.querySelector(targetId);
            if (targetPane) targetPane.classList.add('show', 'active');
        });
    });

    // =====================================================
    // Helper: Re-number rows in a table
    // =====================================================
    function renumberRows($table) {
        $table.find('tbody tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
        });
    }

    // =====================================================
    // FITUR 1: Auto-Sort saat dropdown KETERANGAN diubah
    // Kadiv naik ke atas dengan animasi, lalu renumber.
    // =====================================================
    $(document).on('change', '.jabatan-select', function() {
        var $select = $(this);
        var pendaftarId = $select.data('id');
        var jabatanBaru = $select.val();
        var $row = $select.closest('tr');
        var $table = $select.closest('table');
        var $tbody = $table.find('tbody');

        // Update data attribute
        $row.attr('data-jabatan', jabatanBaru);

        // AJAX update ke server
        $.ajax({
            url: '/staff/update_jabatan.php',
            method: 'POST',
            data: {
                pendaftar_id: pendaftarId,
                jabatan: jabatanBaru
            },
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    alert('Gagal: ' + response.message);
                    return;
                }

                // Animate: fade out row, move it, fade in
                $row.css('transition', 'all 0.4s ease');
                $row.fadeOut(300, function() {
                    $row.detach();

                    if (jabatanBaru === 'Kadiv') {
                        var $firstAnggota = $tbody.find('tr[data-jabatan="Anggota"]').first();
                        if ($firstAnggota.length) {
                            $row.insertBefore($firstAnggota);
                        } else {
                            $tbody.append($row);
                        }
                    } else {
                        var $lastKadiv = $tbody.find('tr[data-jabatan="Kadiv"]').last();
                        if ($lastKadiv.length) {
                            $row.insertAfter($lastKadiv);
                        } else {
                            $tbody.prepend($row);
                        }
                    }

                    $row.fadeIn(400, function() {
                        renumberRows($table);
                    });

                    // Flash highlight effect
                    $row.css('background-color', '#d4edda');
                    setTimeout(function() {
                        $row.css('transition', 'background-color 0.8s ease');
                        $row.css('background-color', '');
                    }, 800);
                });
            },
            error: function(xhr) {
                alert('Terjadi kesalahan saat menyimpan jabatan.');
                console.error(xhr.responseText);
            }
        });
    });

    // =====================================================
    // FITUR 2: Tombol Hapus tanpa refresh (AJAX)
    // =====================================================
    $(document).on('click', '.btn-hapus-staff', function() {
        var $btn = $(this);
        var pendaftarId = $btn.data('id');
        var namaStaff = $btn.data('nama');
        var $row = $btn.closest('tr');
        var $table = $btn.closest('table');

        if (!confirm('Yakin ingin mengeluarkan "' + namaStaff + '" dari daftar Staff Final?\n\n(Data tidak akan dihapus permanen, status akan dikembalikan ke Pending)')) {
            return;
        }

        $.ajax({
            url: '/staff/hapus_staff.php',
            method: 'POST',
            data: { pendaftar_id: pendaftarId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $row.css('background-color', '#f8d7da');
                    $row.fadeOut(500, function() {
                        $row.remove();
                        renumberRows($table);

                        var $tabPane = $table.closest('.tab-pane');
                        var tabId = $tabPane.attr('id');
                        var remainingRows = $table.find('tbody tr').length;

                        $tabPane.find('.badge.bg-dark').text(remainingRows + ' orang');

                        var $tabBtn = $('button[data-bs-target="#' + tabId + '"]');
                        $tabBtn.find('.badge').text(remainingRows);

                        var totalBadge = document.querySelector('.badge.badge-green');
                        if (totalBadge) {
                            var currentTotal = parseInt(totalBadge.textContent.match(/\d+/));
                            totalBadge.innerHTML = '<i class="bi bi-people-fill me-1"></i>Total Staff Diterima: ' + (currentTotal - 1);
                        }

                        if (remainingRows === 0) {
                            $table.closest('.table-responsive').replaceWith(
                                '<div class="text-center py-5 text-muted">' +
                                '<i class="bi bi-people fs-1 d-block mb-2"></i>' +
                                'Belum ada staff yang ditempatkan di divisi ini.' +
                                '</div>'
                            );
                        }
                    });
                } else {
                    alert('Gagal: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Terjadi kesalahan saat menghapus staff.');
                console.error(xhr.responseText);
            }
        });
    });

});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
