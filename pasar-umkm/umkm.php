<?php 
// 1. Tampilkan detail eror untuk debugging (Matikan jika website sudah online)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Hubungkan koneksi database (Jalur Absolut Benar)
include __DIR__ . '/includes/config/koneksi.php'; 

// Proteksi awal: Mencegah Fatal Error jika koneksi bermasalah
if (!isset($koneksi) || !$koneksi) {
    die("<div class='container py-5'><div class='alert alert-danger text-center'>Eror Sistem: Variabel \$koneksi tidak ditemukan. Periksa kembali file koneksi.php Anda.</div></div>");
}

// 3. Logika Menangkap & Mensanitasi Kata Kunci Pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, trim($_GET['search'])) : "";

// 4. Bangun Query Dinamis Mengambil Data Direktori UMKM
$query_umkm = "SELECT * FROM tabel_umkm WHERE 1=1";
if (!empty($search)) {
    // Mencari berdasarkan nama UMKM ataupun deskripsi usaha
    $query_umkm .= " AND (nama_umkm LIKE '%$search%' OR deskripsi LIKE '%$search%')";
}
$query_umkm .= " ORDER BY nama_umkm ASC"; // Diurutkan alfabetis agar presisi dan rapi

$tampil_umkm = mysqli_query($koneksi, $query_umkm);

// 5. Hubungkan header template setelah seluruh pemrosesan data selesai
include __DIR__ . '/includes/header.php'; 

// PENGAMAN: Jika query gagal, tampilkan pesan eror yang elegan dalam layout
if (!$tampil_umkm) {
    echo "<div class='container py-5'>
            <div class='alert alert-danger border-0 shadow-sm text-center p-4 rounded-4'>
                <i class='fa-solid fa-circle-exclamation fa-2x mb-3 text-danger'></i>
                <h5 class='fw-bold text-dark'>Gagal Memuat Data UMKM</h5>
                <p class='text-muted small mb-0'>Pesan Eror: <code>" . mysqli_error($koneksi) . "</code></p>
            </div>
          </div>";
    include __DIR__ . '/includes/footer.php';
    exit; 
}
?>

<style>
    .card-premium {
        border-color: #f1f5f9 !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .card-premium:hover {
        transform: translateY(-5px);
        border-color: #cbd5e1 !important;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
    }
    /* Style Komponen Tombol Registrasi Premium */
    .btn-premium-cta {
        background: #f8fafc;
        color: #0f172a;
        font-weight: 600;
        font-size: 14px;
        padding: 10px 24px;
        transition: all 0.3s ease;
        border: none;
    }
    .btn-premium-cta:hover {
        background: #e2e8f0;
        transform: translateY(-2px);
        color: #0f172a;
        box-shadow: 0 4px 12px rgba(255,255,255,0.15);
    }
</style>

<section class="py-5 text-white text-center" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
    <div class="container py-3">
        <h1 class="fw-bold mb-2">Direktori Mitra UMKM</h1>
        <p class="text-white-50 max-w-2xl mx-auto mb-4">Jelajahi dan dukung seluruh pelaku usaha lokal unggulan yang telah terkurasi dengan standar kualitas terbaik.</p>
        
        <a href="daftar-umkm.php" class="btn btn-premium-cta rounded-pill shadow-sm">
            <i class="fa-solid fa-store me-2 fa-sm"></i> Daftarkan UMKM Anda
        </a>
    </div>
</section>

<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form action="umkm.php" method="GET" class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0 shadow-none" placeholder="Cari nama UMKM atau produk..." value="<?= htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="btn btn-dark px-4 rounded-3">Cari</button>
                    <?php if (!empty($search)) : ?>
                        <a href="umkm.php" class="btn btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container py-3">
        
        <?php if (!empty($search)) : ?>
            <div class="mb-4">
                <p class="text-muted">Menampilkan hasil pencarian untuk: <strong class="text-dark">"<?= htmlspecialchars($search); ?>"</strong> (<?= mysqli_num_rows($tampil_umkm); ?> ditemukan)</p>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php 
            if (mysqli_num_rows($tampil_umkm) > 0) :
                while($umkm = mysqli_fetch_assoc($tampil_umkm)) : 
            ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card card-premium h-100 p-3 border text-center">
                    <div class="mx-auto mb-3 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <img src="assets/img/logo/<?= $umkm['logo']; ?>" onerror="this.src='https://placehold.co/80?text=UMKM'" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <span class="text-uppercase text-xs fw-bold tracking-wider text-muted mb-1" style="font-size: 11px;">Mitra Terverifikasi</span>
                    <h5 class="fw-bold mb-2 text-dark" style="font-size: 16px;"><?= htmlspecialchars($umkm['nama_umkm']); ?></h5>
                    <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 38px;">
                        <?= htmlspecialchars($umkm['deskripsi']); ?>
                    </p>
                    <div class="pt-2 border-top mt-auto">
                        <a href="detail-umkm.php?id=<?= $umkm['id']; ?>" class="btn btn-sm btn-outline-dark w-100 rounded-pill">Kunjungi Toko</a>
                    </div>
                </div>
            </div>
            <?php 
                endwhile;
            else : 
            ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="fa-solid fa-store-slash fa-3x mb-3 text-secondary"></i>
                    <h5 class="fw-bold text-dark">UMKM Tidak Ditemukan</h5>
                    <p class="small mb-4">Ingin toko Anda muncul di sini? Silakan daftarkan usaha Anda sekarang.</p>
                    <a href="daftar-umkm.php" class="btn btn-dark rounded-pill px-4 py-2 small fw-medium">
                        <i class="fa-solid fa-plus me-2 fa-xs"></i>Mulai Daftar Mitra
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>