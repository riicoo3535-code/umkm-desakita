<?php 
// 1. Tampilkan detail eror untuk debugging (Matikan jika website sudah online)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Hubungkan koneksi database (Jalur Absolut Benar)
include __DIR__ . '/includes/config/koneksi.php'; 

// Proteksi awal: Pastikan sistem database siap
if (!isset($koneksi) || !$koneksi) {
    die("<div class='container py-5'><div class='alert alert-danger text-center'>Eror Sistem: Variabel \$koneksi tidak ditemukan.</div></div>");
}

// 3. Logika Pencarian Artikel
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, trim($_GET['search'])) : "";

// 4. Sistem Paginasi
$jumlah_per_halaman = 9; 
$halaman_aktif      = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($halaman_aktif < 1) { $halaman_aktif = 1; }
$data_awal = ($halaman_aktif > 1) ? ($halaman_aktif * $jumlah_per_halaman) - $jumlah_per_halaman : 0;

// 5. Hitung Total Data Artikel
$query_hitung = "SELECT COUNT(*) AS total FROM tabel_artikel WHERE 1=1";
if (!empty($search)) {
    $query_hitung .= " AND (judul LIKE '%$search%' OR isi LIKE '%$search%')";
}
$baca_hitung   = mysqli_query($koneksi, $query_hitung);
$hasil_hitung  = mysqli_fetch_assoc($baca_hitung);
$total_artikel = $hasil_hitung['total'];
$total_halaman = ceil($total_artikel / $jumlah_per_halaman);

// 6. Query Utama Mengambil Data Artikel
$query_artikel = "SELECT * FROM tabel_artikel WHERE 1=1";
if (!empty($search)) {
    $query_artikel .= " AND (judul LIKE '%$search%' OR isi LIKE '%$search%')";
}
$query_artikel .= " ORDER BY tanggal DESC LIMIT $data_awal, $jumlah_per_halaman";
$tampil_artikel = mysqli_query($koneksi, $query_artikel);



include __DIR__ . '/includes/header.php'; 


// PENGAMAN: Jika query gagal, tampilkan pesan peringatan
if (!$tampil_artikel) {
    echo "<div class='container py-5'>
            <div class='alert alert-danger border-0 shadow-sm text-center p-4 rounded-4'>
                <i class='fa-solid fa-circle-exclamation fa-2x mb-3 text-danger'></i>
                <h5 class='fw-bold text-dark'>Gagal Memuat Data Artikel</h5>
                <p class='text-muted small mb-0'>Pesan Eror: <code>" . mysqli_error($koneksi) . "</code></p>
            </div>
          </div>";
    
    include __DIR__ . '/includes/footer.php';
    exit; 
}
?>

<section class="py-5 text-white text-center" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
    <div class="container py-3">
        <h1 class="fw-bold mb-2">Artikel & Berita UMKM</h1>
        <p class="text-white-50 max-w-2xl mx-auto">Ikuti kisah inspiratif, perkembangan produk lokal, dan tips seputar dunia usaha mikro di sekitar kita.</p>
    </div>
</section>

<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form action="artikel.php" method="GET" class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0 shadow-none" placeholder="Cari artikel atau berita..." value="<?= htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="btn btn-dark px-4 rounded-3">Cari</button>
                    <?php if (!empty($search)) : ?>
                        <a href="artikel.php" class="btn btn-outline-secondary">Reset</a>
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
                <p class="text-muted">Menampilkan hasil pencarian artikel: <strong class="text-dark">"<?= htmlspecialchars($search); ?>"</strong> (<?= mysqli_num_rows($tampil_artikel); ?> ditemukan)</p>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php 
            if (mysqli_num_rows($tampil_artikel) > 0) :
                while($artikel = mysqli_fetch_assoc($tampil_artikel)) : 
                    // Format tanggal menjadi format Indonesia yang lebih elegan (contoh: 22 Jun 2026)
                    $tanggal_format = date('d M Y', strtotime($artikel['tanggal']));
                    
                    // Memotong isi artikel untuk ringkasan (snippet) maksimal 120 karakter
                    $ringkasan = strip_tags($artikel['isi']);
                    if (strlen($ringkasan) > 120) {
                        $ringkasan = substr($ringkasan, 0, 120) . '...';
                    }
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-premium h-100 overflow-hidden border shadow-sm d-flex flex-column">
                    <img src="assets/img/artikel/<?= $artikel['gambar']; ?>" onerror="this.src='https://placehold.co/600x400?text=Berita+UMKM'" class="card-img-top" alt="Gambar Artikel" style="height: 200px; object-fit: cover;">
                    
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="text-muted small mb-2">
                            <i class="fa-regular fa-calendar me-1"></i> <?= $tanggal_format; ?>
                        </div>
                        
                        <h5 class="fw-bold text-dark mb-2" style="line-height: 1.4; font-size: 18px;">
                            <a href="detail-artikel.php?id=<?= $artikel['id']; ?>" class="text-decoration-none text-dark hover-text-secondary">
                                <?= htmlspecialchars($artikel['judul']); ?>
                            </a>
                        </h5>
                        
                        <p class="text-muted small mb-4">
                            <?= htmlspecialchars($ringkasan); ?>
                        </p>
                        
                        <a href="detail-artikel.php?id=<?= $artikel['id']; ?>" class="text-dark fw-semibold small text-decoration-none mt-auto">
                            Baca Selengkapnya <i class="fa-solid fa-arrow-right fa-xs ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php 
                endwhile;
            else : 
            ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="fa-regular fa-newspaper fa-3x mb-3 text-secondary"></i>
                    <h5 class="fw-bold text-dark">Belum Ada Artikel</h5>
                    <p class="small">Kabar terbaru dan artikel promosi akan segera diterbitkan.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>