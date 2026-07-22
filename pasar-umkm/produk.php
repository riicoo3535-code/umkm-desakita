<?php 
// 1. Tampilkan detail eror untuk debugging (Matikan jika website sudah online)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Hubungkan koneksi database (JALUR ABSOLUT AMAN)
include __DIR__ . '/includes/config/koneksi.php'; 

// Proteksi awal: Mencegah Fatal Error jika koneksi database terputus
if (!isset($koneksi) || !$koneksi) {
    die("<div class='container py-5'><div class='alert alert-danger text-center'>Eror Sistem: Variabel \$koneksi tidak ditemukan. Periksa kembali file koneksi.php Anda.</div></div>");
}

// 3. Ambil data parameter pencarian & filter dari URL (Method GET)
$search          = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, trim($_GET['search'])) : "";
$filter_kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, trim($_GET['kategori'])) : "";

// 4. Query Ambil Data Kategori (Mengaktifkan Looping Dropdown Select Anda)
$query_kat  = "SELECT * FROM tabel_kategori ORDER BY nama_kategori ASC";
$tampil_kat = mysqli_query($koneksi, $query_kat);

// 5. Query Utama Produk Menggunakan INNER JOIN (Menghubungkan 3 Tabel Sekaligus)
// Langkah ini wajib agar data kategori, nama UMKM, dan nomor WhatsApp bisa terbaca dengan mulus
$query_produk = "SELECT tabel_produk.*, tabel_kategori.nama_kategori, tabel_umkm.nama_umkm, tabel_umkm.whatsapp 
                 FROM tabel_produk 
                 INNER JOIN tabel_kategori ON tabel_produk.kategori_id = tabel_kategori.id
                 INNER JOIN tabel_umkm ON tabel_produk.umkm_id = tabel_umkm.id
                 WHERE 1=1";

// Jika user mengetik kata kunci pencarian
if (!empty($search)) {
    $query_produk .= " AND tabel_produk.nama_produk LIKE '%$search%'";
}

// Jika user memilih kategori tertentu
if (!empty($filter_kategori)) {
    $query_produk .= " AND tabel_produk.kategori_id = '$filter_kategori'";
}

$query_produk .= " ORDER BY tabel_produk.id DESC";
$tampil_produk = mysqli_query($koneksi, $query_produk);

// 6. Hubungkan header template (JALUR ABSOLUT AMAN)
include __DIR__ . '/includes/header.php'; 
?>

<style>
    .card-premium {
        border-color: #f1f5f9 !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01) !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .card-premium:hover {
        transform: translateY(-6px);
        border-color: #cbd5e1 !important;
        box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.06), 0 8px 10px -6px rgba(15, 23, 42, 0.04) !important;
    }
    .btn-premium {
        background-color: #0f172a;
        color: #ffffff;
        border: 1px solid #0f172a;
        transition: all 0.2s ease;
    }
    .btn-premium:hover {
        background-color: #1e293b;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    }
</style>

<section class="py-5 text-white text-center" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
    <div class="container py-3">
        <h1 class="fw-bold mb-2">Katalog Produk Unggulan</h1>
        <p class="text-white-50 max-w-2xl mx-auto">Temukan produk lokal berkualitas terbaik, pesan instan tanpa perantara langsung ke pemilik usaha.</p>
    </div>
</section>

<section class="py-4 bg-light border-bottom">
    <div class="container">
        <form action="produk.php" method="GET" class="row g-3 justify-content-center">
            
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted border-secondary-subtle">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0 shadow-none border-secondary-subtle" placeholder="Cari nama produk yang Anda inginkan..." value="<?= htmlspecialchars($search); ?>">
                </div>
            </div>
            
            <div class="col-md-3">
                <select name="kategori" class="form-select shadow-none border-secondary-subtle">
                    <option value="">Semua Kategori</option>
                    <?php if ($tampil_kat) : ?>
                        <?php while($kat = mysqli_fetch_assoc($tampil_kat)) : ?>
                            <option value="<?= $kat['id']; ?>" <?= ($filter_kategori == $kat['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($kat['nama_kategori']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100 rounded-3">Filter</button>
                <?php if (!empty($search) || !empty($filter_kategori)) : ?>
                    <a href="produk.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container py-3">
        
        <?php if (!empty($search) || !empty($filter_kategori)) : ?>
            <div class="mb-4">
                <p class="text-muted mb-0">Menampilkan hasil penyaringan produk (<strong><?= mysqli_num_rows($tampil_produk); ?></strong> item ditemukan)</p>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php 
            if (mysqli_num_rows($tampil_produk) > 0) :
                while($produk = mysqli_fetch_assoc($tampil_produk)) : 

                    // Normalisasi nomor HP WhatsApp
                    $no_wa = $produk['whatsapp'];
                    if (substr($no_wa, 0, 1) === '0') {
                        $no_wa = '62' . substr($no_wa, 1);
                    }
                    
                    // Template teks pesan otomatis saat klik beli
                    $text_pesanan = "Halo " . $produk['nama_umkm'] . ", saya tertarik dengan produk *" . $produk['nama_produk'] . "* seharga *Rp " . number_format($produk['harga'], 0, ',', '.') . "*. Apakah masih ada stok?";
                    $link_whatsapp = "https://wa.me/" . preg_replace('/[^0-9]/', '', $no_wa) . "?text=" . urlencode($text_pesanan);
            ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card card-premium h-100 overflow-hidden border rounded-3 position-relative">
                    
                    <span class="badge bg-dark position-absolute m-3 px-2 py-1.5 fs-xs fw-medium text-uppercase" style="top:0; left:0; font-size:10px; letter-spacing: 0.5px; z-index: 10;">
                        <?= htmlspecialchars($produk['nama_kategori']); ?>
                    </span>
                    
                    <img src="assets/img/produk/<?= $produk['foto']; ?>" onerror="this.src='https://placehold.co/400x250?text=Produk+UMKM'" class="card-img-top" alt="Produk" style="height: 200px; object-fit: cover;">
                    
                    <div class="card-body p-3 d-flex flex-column">
                        <span class="text-muted small d-block mb-1" style="font-size: 12px;">
                            <i class="fa-solid fa-store fa-xs me-1"></i> <?= htmlspecialchars($produk['nama_umkm']); ?>
                        </span>
                        
                        <h6 class="fw-bold text-dark mb-2 text-truncate-2" style="min-height: 40px; line-height: 1.3; font-size: 15px;">
                            <?= htmlspecialchars($produk['nama_produk']); ?>
                        </h6>
                        
                        <h5 class="text-success fw-bold mb-3" style="font-size: 16px;">Rp <?= number_format($produk['harga'], 0, ',', '.'); ?></h5>
                        
                        <a href="<?= $link_whatsapp; ?>" target="_blank" class="btn btn-premium btn-sm w-100 mt-auto py-2 rounded-pill">
                            <i class="fa-brands fa-whatsapp me-2"></i>Pesan via WA
                        </a>
                    </div>
                </div>
            </div>
            <?php 
                endwhile;
            else : 
            ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="fa-solid fa-box-open fa-3x mb-3 text-secondary"></i>
                    <h5 class="fw-bold text-dark">Produk Tidak Ditemukan</h5>
                    <p class="small">Produk yang Anda cari belum tersedia atau kata kunci kurang pas.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>