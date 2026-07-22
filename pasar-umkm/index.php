<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/includes/config/koneksi.php'; 

if (!isset($koneksi) || !$koneksi) {
    die("Eror Sistem: Variabel \$koneksi tidak ditemukan.");
}

// DETEKSI KOLOM OTOMATIS
$kolom_relasi = "umkm_id";
$kolom_gambar = "foto";

$cek_kolom_produk = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_produk");
if ($cek_kolom_produk) {
    $cols_produk = [];
    while ($k = mysqli_fetch_assoc($cek_kolom_produk)) {
        $cols_produk[] = strtolower($k['Field']);
    }
    if (in_array('id_umkm', $cols_produk)) $kolom_relasi = "id_umkm";
    if (in_array('gambar', $cols_produk)) $kolom_gambar = "gambar";
    elseif (in_array('image', $cols_produk)) $kolom_gambar = "image";
}

$kolom_wa = "whatsapp";
$cek_kolom_umkm = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_umkm");
if ($cek_kolom_umkm) {
    $cols_umkm = [];
    while ($k = mysqli_fetch_assoc($cek_kolom_umkm)) {
        $cols_umkm[] = strtolower($k['Field']);
    }
    if (in_array('no_wa', $cols_umkm)) $kolom_wa = "no_wa";
    elseif (in_array('no_hp', $cols_umkm)) $kolom_wa = "no_hp";
}

// QUERY UMKM
$query_umkm   = "SELECT * FROM tabel_umkm LIMIT 10";
$tampil_umkm  = mysqli_query($koneksi, $query_umkm);

// QUERY PRODUK
$query_produk  = "SELECT tabel_produk.*, tabel_umkm.nama_umkm, tabel_umkm.{$kolom_wa} AS no_wa_umkm 
                  FROM tabel_produk 
                  JOIN tabel_umkm ON tabel_produk.{$kolom_relasi} = tabel_umkm.id 
                  ORDER BY tabel_produk.id DESC LIMIT 4";
$tampil_produk = mysqli_query($koneksi, $query_produk);

include __DIR__ . '/includes/header.php'; 
?>

<!-- ============================================================
     PREMIUM DESIGN OVERRIDE — Ivory / Emerald / Gold
     Hanya mengubah tampilan (CSS & markup dekoratif).
     Tidak ada query, variabel, atau link yang diubah.
     ============================================================ -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root{
        --ivory: #FBF8F2;
        --ivory-deep: #F4EEE1;
        --forest: #0F3D2E;
        --forest-deep: #0A2B20;
        --gold: #C6A15B;
        --gold-soft: #E4CE9C;
        --sage: #AFC8A0;
        --ink: #1F2320;
        --stone: #6E7268;
        --shadow-lux: 0 24px 60px -20px rgba(15, 61, 46, 0.35);
    }

    body{
        background-color: var(--ivory);
        color: var(--ink);
        font-family: 'Inter', sans-serif;
    }

    h1, h2, h3, h4, h5, h6{
        font-family: 'Fraunces', serif;
        color: var(--forest-deep);
        letter-spacing: -0.01em;
    }

    /* ---------- HERO ---------- */
    .hero-section{
        background: radial-gradient(120% 140% at 15% 0%, #164E39 0%, var(--forest) 45%, var(--forest-deep) 100%);
        color: var(--ivory);
        padding: 6.5rem 0 5.5rem;
        position: relative;
        overflow: hidden;
        border-bottom: 1px solid rgba(198, 161, 91, 0.35);
    }
    .hero-section::before{
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            radial-gradient(circle at 85% 20%, rgba(198,161,91,0.18) 0%, transparent 45%),
            radial-gradient(circle at 10% 90%, rgba(175,200,160,0.12) 0%, transparent 40%);
        pointer-events: none;
    }
    .hero-section .container{ position: relative; z-index: 1; }
    .hero-section .badge{
        background: transparent !important;
        border: 1px solid var(--gold);
        color: var(--gold-soft) !important;
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        font-size: 12px;
    }
    .hero-section h1{
        color: var(--ivory);
        font-weight: 600;
        font-style: normal;
    }
    .hero-section .text-white-50{
        color: rgba(251, 248, 242, 0.72) !important;
        font-family: 'Inter', sans-serif;
    }
    .hero-section .btn-light{
        background: var(--gold);
        border: 1px solid var(--gold);
        color: var(--forest-deep) !important;
        font-weight: 600;
        transition: all .25s ease;
    }
    .hero-section .btn-light:hover{
        background: var(--gold-soft);
        border-color: var(--gold-soft);
        transform: translateY(-2px);
    }
    .hero-section .btn-outline-light{
        border: 1px solid rgba(251,248,242,0.5);
        color: var(--ivory);
        font-weight: 500;
        transition: all .25s ease;
    }
    .hero-section .btn-outline-light:hover{
        background: rgba(251,248,242,0.08);
        border-color: var(--ivory);
        transform: translateY(-2px);
    }
    .hero-section img{
        border: 6px solid rgba(251,248,242,0.08);
        box-shadow: var(--shadow-lux);
    }

    /* ---------- SECTION HEADERS ---------- */
    #daftar-umkm, section.bg-white{ background-color: var(--ivory) !important; }
    #daftar-umkm h2, #produk-unggulan h2, section.bg-white h2{
        font-weight: 600;
        position: relative;
        display: inline-block;
        padding-bottom: 14px;
    }
    #daftar-umkm .text-center h2::after,
    #produk-unggulan h2::after,
    section.bg-white .text-center h2::after{
        content: "";
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 56px;
        height: 2px;
        background: linear-gradient(90deg, var(--sage), var(--gold));
    }
    #produk-unggulan .d-flex h2::after{
        left: 0;
        transform: none;
    }
    .text-muted{ color: var(--stone) !important; font-family: 'Inter', sans-serif; }

    /* ---------- CARDS (UMKM + PRODUK) ---------- */
    .card-premium{
        background: #FFFFFF;
        border: 1px solid var(--ivory-deep) !important;
        border-radius: 18px;
        transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
    }
    .card-premium:hover{
        transform: translateY(-6px);
        box-shadow: 0 20px 40px -18px rgba(15, 61, 46, 0.25);
        border-color: var(--gold) !important;
    }
    #daftar-umkm .card-premium{
        text-align: center;
    }
    #daftar-umkm .rounded-circle.bg-light{
        background: var(--ivory-deep) !important;
        border: 2px solid var(--sage);
    }
    #daftar-umkm h5{ font-weight: 600; font-size: 16px !important; }
    #daftar-umkm .btn-primary{
        background: var(--forest);
        border: 1px solid var(--forest);
        font-weight: 500;
        letter-spacing: 0.01em;
        transition: all .25s ease;
    }
    #daftar-umkm .btn-primary:hover{
        background: var(--forest-deep);
        border-color: var(--gold);
        box-shadow: 0 8px 20px -8px rgba(15,61,46,0.5);
    }

    /* ---------- PRODUK SECTION ---------- */
    #produk-unggulan{
        background: linear-gradient(180deg, var(--ivory) 0%, var(--ivory-deep) 100%) !important;
        position: relative;
    }
    #produk-unggulan .btn-outline-dark{
        border: 1px solid var(--forest);
        color: var(--forest-deep);
        font-weight: 500;
        transition: all .25s ease;
    }
    #produk-unggulan .btn-outline-dark:hover{
        background: var(--forest);
        color: var(--ivory);
        border-color: var(--forest);
    }
    #produk-unggulan .card-premium{
        border-radius: 18px;
        overflow: hidden;
    }
    #produk-unggulan .card-img-top{
        filter: saturate(1.05);
    }
    #produk-unggulan h5.fw-bold{
        color: var(--forest-deep) !important;
        font-family: 'Fraunces', serif;
        font-weight: 600 !important;
    }
    #produk-unggulan .btn-success{
        background: var(--forest);
        border: 1px solid var(--forest);
        font-weight: 500;
        transition: all .25s ease;
    }
    #produk-unggulan .btn-success:hover{
        background: #0C3527;
        border-color: var(--gold);
        box-shadow: 0 10px 22px -10px rgba(15,61,46,0.55);
    }

    /* ---------- TESTIMONI ---------- */
    section.bg-white .bg-light{
        background: var(--ivory-deep) !important;
        border: 1px solid rgba(198,161,91,0.35) !important;
        border-radius: 16px;
        position: relative;
    }
    section.bg-white .bg-light p{
        font-family: 'Fraunces', serif;
        font-style: italic;
        color: var(--ink) !important;
        font-size: 1.05rem;
        line-height: 1.6;
    }
    section.bg-white .bg-light h6{
        color: var(--forest) !important;
        font-family: 'Inter', sans-serif;
        font-weight: 600;
    }
</style>

<!-- 1. HERO BANNER SECTION -->
<section class="hero-section text-center text-md-start">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <span class="badge bg-secondary mb-3 px-3 py-2 text-uppercase" style="letter-spacing: 2px;">Katalog Produk Lokal</span>
                <h1 class="display-4 fw-bold mb-3" style="line-height: 1.2;">Dukung Karya Lokal,<br>Bawa ke Pasar Global</h1>
                <p class="lead text-white-50 mb-4">Temukan produk unggulan dikurasi langsung dari 10 UMKM terbaik di daerah kita. Transaksi mudah, cepat, dan langsung via WhatsApp.</p>
                <a href="#produk-unggulan" class="btn btn-light btn-lg px-4 fs-6 fw-semibold shadow-sm me-2">Jelajahi Produk</a>
                <a href="#daftar-umkm" class="btn btn-outline-light btn-lg px-4 fs-6">Lihat UMKM</a>
            </div>
            <div class="col-md-6 text-center">
                <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=600" alt="Local Market" class="img-fluid rounded-4 shadow-lg" style="max-height: 400px; object-fit: cover; width: 100%;">
            </div>
        </div>
    </div>
</section>

<!-- 2. DAFTAR UMKM SECTION -->
<section id="daftar-umkm" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">10 UMKM Mitra Kami</h2>
            <p class="text-muted">Profil singkat para pelaku usaha lokal yang mengutamakan kualitas.</p>
        </div>
        
        <div class="row g-4">
            <?php if (mysqli_num_rows($tampil_umkm) > 0) : ?>
                <?php while($umkm = mysqli_fetch_assoc($tampil_umkm)) : ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card card-premium h-100 p-3 border text-center">
                        <div class="mx-auto mb-3 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                            <img src="assets/img/logo/<?= $umkm['logo']; ?>" onerror="this.src='https://placehold.co/70?text=UMKM'" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <span class="text-uppercase text-xs fw-bold tracking-wider text-muted mb-1" style="font-size: 11px;">Mitra Lokal</span>
                        <h5 class="fw-bold mb-2 text-dark" style="font-size: 16px;"><?= htmlspecialchars($umkm['nama_umkm']); ?></h5>
                        <p class="text-muted small text-truncate mb-3"><?= htmlspecialchars($umkm['deskripsi']); ?></p>
                        
                        <a href="detail-umkm.php?id=<?= $umkm['id']; ?>" class="btn btn-primary btn-sm rounded-3">Kunjungi Toko</a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="col-12 text-center text-muted py-4">
                    <p>Belum ada data UMKM yang didaftarkan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 3. PRODUK UNGGULAN SECTION -->
<section id="produk-unggulan" class="py-5" style="background-color: #f1f5f9;">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="fw-bold m-0">Produk Unggulan</h2>
                <p class="text-muted m-0">Produk terlaris pilihan kurator.</p>
            </div>
            <a href="produk.php" class="btn btn-outline-dark rounded-pill px-4">Lihat Semua</a>
        </div>

        <div class="row g-4">
            <?php if (mysqli_num_rows($tampil_produk) > 0) : ?>
                <?php while($produk = mysqli_fetch_assoc($tampil_produk)) : 
                    $img_file = !empty($produk[$kolom_gambar]) ? $produk[$kolom_gambar] : 'default-produk.png';

                    // Format Nomor WhatsApp
                    $raw_wa = $produk['no_wa_umkm'] ?? '';
                    $no_wa = preg_replace('/[^0-9]/', '', $raw_wa);
                    if (str_starts_with($no_wa, '0')) {
                        $no_wa = '62' . substr($no_wa, 1);
                    }

                    // Format Pesan WhatsApp
                    $pesan_wa = "Halo " . $produk['nama_umkm'] . ", saya tertarik untuk membeli produk berikut:\n\n" .
                                 "📌 *Nama Produk:* " . $produk['nama_produk'] . "\n" .
                                 "💰 *Harga:* Rp " . number_format($produk['harga'], 0, ',', '.') . "\n\n" .
                                 "Apakah produk ini tersedia?";
                    $link_wa = "https://wa.me/" . $no_wa . "?text=" . urlencode($pesan_wa);
                ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-premium h-100 overflow-hidden border-0 shadow-sm d-flex flex-column">
                        <!-- GAMBAR PRODUK -->
                        <img src="assets/img/produk/<?= $img_file; ?>" onerror="this.src='https://placehold.co/400x220?text=Produk'" class="card-img-top" alt="Produk" style="height: 200px; object-fit: cover;">
                        
                        <!-- DETAIL PRODUK & HARGA -->
                        <div class="card-body p-3 d-flex flex-column flex-grow-1">
                            <span class="text-muted small"><?= htmlspecialchars($produk['nama_umkm']); ?></span>
                            <h6 class="fw-bold my-1 text-dark text-truncate"><?= htmlspecialchars($produk['nama_produk']); ?></h6>
                            
                            <h5 class="text-dark fw-bold my-2" style="font-size: 16px;">
                                Rp <?= number_format($produk['harga'], 0, ',', '.'); ?>
                            </h5>

                            <!-- TOMBOL BELI VIA WA -->
                            <a href="<?= $link_wa; ?>" target="_blank" class="btn btn-success w-100 mt-auto rounded-3 fw-medium">
                                <i class="fa-brands fa-whatsapp me-1"></i> Beli via WA
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="col-12 text-center text-muted py-4">
                    <p>Belum ada produk unggulan yang diunggah.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 4. TESTIMONI SECTION -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Ulasan Pengunjung</h2>
            <p class="text-muted">Apa kata mereka tentang produk lokal kami.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-5">
                <div class="p-4 border rounded-3 bg-light">
                    <p class="fst-italic text-secondary">"Kualitas batik tulisnya luar biasa rapi, harganya sangat rasional untuk kualitas premium seperti ini. Komunikasi ke penjual via WhatsApp juga sangat cepat merespon."</p>
                    <h6 class="fw-bold m-0 mt-3">— Aris S.</h6>
                </div>
            </div>
            <div class="col-md-5">
                <div class="p-4 border rounded-3 bg-light">
                    <p class="fst-italic text-secondary">"Sangat terbantu dengan website direktori seperti ini. Bisa langsung borong nastar dan kopi premium untuk hampers kantor tanpa ribet lewat marketplace besar."</p>
                    <h6 class="fw-bold m-0 mt-3">— Rina M.</h6>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>