<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$divisi_list = [
    'Humas',
    'Acara',
    'Konsumsi',
    'Sponsorship',
    'Perlengkapan',
    'Keamanan',
    'Publikasi, Dekorasi, dan Dokumentasi'
];

$page_title = 'Tambah Pendaftar Baru';
$active_nav = 'pendaftar';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center py-3">
    <div class="col-lg-10 col-xl-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="/pendaftar/index.php" class="text-decoration-none text-muted small fw-semibold"><i class="bi bi-arrow-left me-1"></i> Kembali ke Data Pendaftar</a>
                <h2 class="fw-bold mb-0 mt-2" style="letter-spacing: -0.5px;">Form Pendaftar Baru</h2>
            </div>
            <span class="badge badge-gray text-uppercase shadow-sm">Manual Input</span>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert bg-danger-subtle text-danger border-0 rounded-4 py-3 small mb-4 d-flex align-items-center">
                <i class="bi bi-exclamation-circle-fill fs-5 me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <form action="/pendaftar/proses_tambah.php" method="POST">
            <!-- Section 1: Data Diri -->
            <div class="card card-custom p-4 p-md-5 mb-4 hover-shadow">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-person-badge fs-4"></i>
                    </div>
                    <h4 class="fw-bold mb-0">Identitas Diri</h4>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase" style="letter-spacing: 0.5px;">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control bg-light border-0" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase" style="letter-spacing: 0.5px;">Tahun Lulus <span class="text-danger">*</span></label>
                        <input type="text" name="tahun_lulus" class="form-control bg-light border-0" placeholder="Contoh: 2025" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase" style="letter-spacing: 0.5px;">Asal Kampus / Jurusan <span class="text-danger">*</span></label>
                        <input type="text" name="asal_kampus_jurusan" class="form-control bg-light border-0" placeholder="Contoh: UGM - Teknik Informatika" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase" style="letter-spacing: 0.5px;">No. WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" name="no_whatsapp" class="form-control bg-light border-0" placeholder="081234567890" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary text-uppercase" style="letter-spacing: 0.5px;">Alasan Mendaftar <span class="text-danger">*</span></label>
                        <textarea name="alasan_mendaftar" class="form-control bg-light border-0" rows="3" required placeholder="Tuliskan motivasi pendaftar..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Pilihan Divisi 1 & 2 -->
            <div class="card card-custom p-4 p-md-5 mb-4 hover-shadow">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-diagram-3 fs-4"></i>
                    </div>
                    <h4 class="fw-bold mb-0">Pilihan Divisi</h4>
                </div>
                
                <!-- Pilihan 1 -->
                <div class="p-4 rounded-4 mb-4" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                    <h6 class="fw-bold text-primary mb-3 d-flex align-items-center"><span class="badge bg-primary rounded-circle me-2 p-2">1</span>Pilihan Divisi Utama</h6>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Divisi <span class="text-danger">*</span></label>
                            <select name="pilihan_1" class="form-select border-0" style="box-shadow: 0 1px 2px rgba(0,0,0,0.05);" required>
                                <option value="">-- Pilih Divisi --</option>
                                <?php foreach ($divisi_list as $div): ?>
                                    <option value="<?= $div ?>"><?= $div ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Alasan Memilih Divisi Ini <span class="text-danger">*</span></label>
                            <textarea name="alasan_pilihan_1" class="form-control border-0" style="box-shadow: 0 1px 2px rgba(0,0,0,0.05);" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Pengalaman Terkait <span class="text-danger">*</span></label>
                            <textarea name="pengalaman_pilihan_1" class="form-control border-0" style="box-shadow: 0 1px 2px rgba(0,0,0,0.05);" rows="3" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pilihan 2 -->
                <div class="p-4 rounded-4" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                    <h6 class="fw-bold text-secondary mb-3 d-flex align-items-center"><span class="badge bg-secondary rounded-circle me-2 p-2">2</span>Pilihan Divisi Alternatif</h6>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Divisi <span class="text-danger">*</span></label>
                            <select name="pilihan_2" class="form-select border-0" style="box-shadow: 0 1px 2px rgba(0,0,0,0.05);" required>
                                <option value="">-- Pilih Divisi --</option>
                                <?php foreach ($divisi_list as $div): ?>
                                    <option value="<?= $div ?>"><?= $div ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Alasan Memilih Divisi Ini <span class="text-danger">*</span></label>
                            <textarea name="alasan_pilihan_2" class="form-control border-0" style="box-shadow: 0 1px 2px rgba(0,0,0,0.05);" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Pengalaman Terkait <span class="text-danger">*</span></label>
                            <textarea name="pengalaman_pilihan_2" class="form-control border-0" style="box-shadow: 0 1px 2px rgba(0,0,0,0.05);" rows="3" required></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Komitmen & Berkas -->
            <div class="card card-custom p-4 p-md-5 mb-5 hover-shadow">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-star-fill fs-4"></i>
                    </div>
                    <h4 class="fw-bold mb-0">Komitmen & Berkas</h4>
                </div>
                
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Kesibukan Okt - Jan <span class="text-danger">*</span></label>
                        <textarea name="kesibukan_okt_jan" class="form-control bg-light border-0" rows="2" placeholder="Kuliah, magang, organisasi lain, dsb." required></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Skor Komitmen (1-10) <span class="text-danger">*</span></label>
                        <input type="number" name="skor_komitmen" class="form-control form-control-lg bg-light border-0 text-center fw-bold text-dark" min="1" max="10" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Skor Prioritas (1-10) <span class="text-danger">*</span></label>
                        <input type="number" name="skor_prioritas" class="form-control form-control-lg bg-light border-0 text-center fw-bold text-dark" min="1" max="10" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Skor Keyakinan (1-10) <span class="text-danger">*</span></label>
                        <input type="number" name="skor_keyakinan" class="form-control form-control-lg bg-light border-0 text-center fw-bold text-dark" min="1" max="10" required>
                    </div>

                    <div class="col-md-6 mt-4 pt-3 border-top">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Link CV / Resume <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-link-45deg"></i></span>
                            <input type="url" name="cv_link" class="form-control bg-light border-0" placeholder="https://drive.google.com/..." required>
                        </div>
                    </div>
                    <div class="col-md-6 mt-4 pt-3 border-top">
                        <label class="form-label small fw-bold text-secondary text-uppercase">Link Portofolio (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-briefcase"></i></span>
                            <input type="url" name="portofolio_link" class="form-control bg-light border-0" placeholder="https://dribbble.com/...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 sticky-bottom bg-white p-3 border-top rounded-top-4" style="box-shadow: 0 -4px 12px rgba(0,0,0,0.05); margin-left: -12px; margin-right: -12px;">
                <a href="/pendaftar/index.php" class="btn btn-pill-outline py-2 px-4">Batal</a>
                <button type="submit" class="btn btn-pill-dark py-2 px-5 d-flex align-items-center gap-2">
                    <i class="bi bi-save"></i> <span>Simpan Data Pendaftar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Memperbaiki z-index navbar saat ada sticky-bottom footer form */
.navbar-custom {
    z-index: 1040;
}
.sticky-bottom {
    z-index: 1030;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
