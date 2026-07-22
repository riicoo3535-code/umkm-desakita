<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/includes/config/koneksi.php';

// 1. AMBIL ID UMKM DARI URL (contoh: detail-umkm.php?id=5)
$id_umkm = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query_umkm = mysqli_query($koneksi, "SELECT * FROM tabel_umkm WHERE id = $id_umkm LIMIT 1");
$umkm = mysqli_fetch_assoc($query_umkm);

// Jika toko tidak ditemukan
if (!$umkm) {
    echo "<script>alert('Toko UMKM tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

// 2. CEK STATUS LOGIN
$is_owner = (isset($_SESSION['status_login']) && $_SESSION['status_login'] === true);

// 3. DETEKSI NAMA KOLOM FOTO & WHATSAPP SECARA OTOMATIS
$kolom_logo = 'foto';
$kolom_wa   = 'no_wa';

$cek_kolom_umkm = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_umkm");
if ($cek_kolom_umkm) {
    $cols_umkm = [];
    while ($k = mysqli_fetch_assoc($cek_kolom_umkm)) {
        $cols_umkm[] = strtolower($k['Field']);
    }
    // Cek fleksibel nama kolom profil/logo
    if (in_array('logo', $cols_umkm)) $kolom_logo = 'logo';
    elseif (in_array('gambar', $cols_umkm)) $kolom_logo = 'gambar';
    elseif (in_array('foto_profil', $cols_umkm)) $kolom_logo = 'foto_profil';

    // Cek fleksibel nama kolom WhatsApp
    if (in_array('whatsapp', $cols_umkm)) $kolom_wa = 'whatsapp';
    elseif (in_array('no_hp', $cols_umkm)) $kolom_wa = 'no_hp';
}

// 3. FORMAT NOMOR WHATSAPP (OTOMATIS CARI KOLOM YANG ADA ISINYA)
$raw_wa = !empty($umkm['no_wa']) ? $umkm['no_wa'] : 
         (!empty($umkm['whatsapp']) ? $umkm['whatsapp'] : 
         (!empty($umkm['no_hp']) ? $umkm['no_hp'] : 
         ($umkm['telepon'] ?? '')));

$no_wa = preg_replace('/[^0-9]/', '', $raw_wa);
if (str_starts_with($no_wa, '0')) {
    $no_wa = '62' . substr($no_wa, 1);
}
// 5. DETEKSI NAMA KOLOM RELASI PRODUK (id_umkm atau umkm_id)
$kolom_relasi = "id_umkm"; 
$cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_produk");
if ($cek_kolom) {
    while ($k = mysqli_fetch_assoc($cek_kolom)) {
        if ($k['Field'] === 'umkm_id') {
            $kolom_relasi = "umkm_id";
            break;
        }
    }
}

// 6. AMBIL DAFTAR PRODUK MILIK TOKO INI
$query_produk = mysqli_query($koneksi, "SELECT * FROM tabel_produk WHERE {$kolom_relasi} = $id_umkm ORDER BY id DESC");

// 7. PENANGANAN FOTO PROFIL TOKO (MULTI-FOLDER CHECK & SVG FALLBACK)
$nama_foto = trim($umkm[$kolom_logo] ?? '');
$foto_toko = '';

if (!empty($nama_foto)) {
    if (file_exists(__DIR__ . '/assets/img/logo/' . $nama_foto)) {
        $foto_toko = 'assets/img/logo/' . $nama_foto;
    } elseif (file_exists(__DIR__ . '/assets/img/umkm/' . $nama_foto)) {
        $foto_toko = 'assets/img/umkm/' . $nama_foto;
    } elseif (file_exists(__DIR__ . '/assets/img/produk/' . $nama_foto)) {
        $foto_toko = 'assets/img/produk/' . $nama_foto;
    } elseif (file_exists(__DIR__ . '/assets/img/' . $nama_foto)) {
        $foto_toko = 'assets/img/' . $nama_foto;
    }
}

// SVG Fallback Lokal (Ikon Toko Abu-abu Netral - Dijamin tidak akan pecah)
$svg_fallback = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='130' height='130' viewBox='0 0 24 24'><rect width='100%' height='100%' fill='%23e9ecef'/><path fill='%236c757d' d='M19 6h-2c0-2.21-1.79-4-4-4S9 3.79 9 6H7c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H7V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h2v12z'/></svg>";

if (empty($foto_toko)) {
    $foto_toko = $svg_fallback;
}
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/includes/config/koneksi.php';

// 1. AMBIL ID UMKM DARI URL (contoh: detail-umkm.php?id=5)
$id_umkm = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query_umkm = mysqli_query($koneksi, "SELECT * FROM tabel_umkm WHERE id = $id_umkm LIMIT 1");
$umkm = mysqli_fetch_assoc($query_umkm);

// Jika toko tidak ditemukan
if (!$umkm) {
    echo "<script>alert('Toko UMKM tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

// 2. CEK STATUS LOGIN (Apakah yang buka ini Admin/Pemilik UMKM atau Pembeli)
$is_owner = (isset($_SESSION['status_login']) && $_SESSION['status_login'] === true);

// 3. DETEKSI NAMA KOLOM FOTO & WHATSAPP SECARA OTOMATIS
$kolom_logo = 'foto';
$kolom_wa   = 'no_wa';

$cek_kolom_umkm = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_umkm");
if ($cek_kolom_umkm) {
    $cols_umkm = [];
    while ($k = mysqli_fetch_assoc($cek_kolom_umkm)) {
        $cols_umkm[] = strtolower($k['Field']);
    }
    if (in_array('logo', $cols_umkm)) $kolom_logo = 'logo';
    elseif (in_array('gambar', $cols_umkm)) $kolom_logo = 'gambar';
    elseif (in_array('foto_profil', $cols_umkm)) $kolom_logo = 'foto_profil';

    if (in_array('whatsapp', $cols_umkm)) $kolom_wa = 'whatsapp';
    elseif (in_array('no_hp', $cols_umkm)) $kolom_wa = 'no_hp';
}

// 4. FORMAT NOMOR WHATSAPP UNTUK PEMESANAN
$raw_wa = $umkm[$kolom_wa] ?? '';
$no_wa  = preg_replace('/[^0-9]/', '', $raw_wa);
if (str_starts_with($no_wa, '0')) {
    $no_wa = '62' . substr($no_wa, 1);
}

// 5. DETEKSI NAMA KOLOM RELASI DENGAN AMAN (id_umkm atau umkm_id)
$kolom_relasi = "id_umkm"; 
$cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_produk");
if ($cek_kolom) {
    while ($k = mysqli_fetch_assoc($cek_kolom)) {
        if ($k['Field'] === 'umkm_id') {
            $kolom_relasi = "umkm_id";
            break;
        }
    }
}

// 6. AMBIL DAFTAR PRODUK MILIK TOKO INI
$query_produk = mysqli_query($koneksi, "SELECT * FROM tabel_produk WHERE {$kolom_relasi} = $id_umkm ORDER BY id DESC");

// 7. PENANGANAN FOTO PROFIL TOKO (MULTI-FOLDER CHECK & SVG FALLBACK)
$nama_foto = trim($umkm[$kolom_logo] ?? '');
$foto_toko = '';

if (!empty($nama_foto)) {
    if (file_exists(__DIR__ . '/assets/img/logo/' . $nama_foto)) {
        $foto_toko = 'assets/img/logo/' . $nama_foto;
    } elseif (file_exists(__DIR__ . '/assets/img/umkm/' . $nama_foto)) {
        $foto_toko = 'assets/img/umkm/' . $nama_foto;
    } elseif (file_exists(__DIR__ . '/assets/img/produk/' . $nama_foto)) {
        $foto_toko = 'assets/img/produk/' . $nama_foto;
    } elseif (file_exists(__DIR__ . '/assets/img/' . $nama_foto)) {
        $foto_toko = 'assets/img/' . $nama_foto;
    }
}

// SVG Fallback Lokal (Ikon Toko - Dijamin tidak akan pecah jika gambar belum diisi)
$svg_fallback = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='110' height='110' viewBox='0 0 24 24'><rect width='100%' height='100%' fill='%23e9ecef'/><path fill='%236c757d' d='M19 6h-2c0-2.21-1.79-4-4-4S9 3.79 9 6H7c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H7V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h2v12z'/></svg>";

if (empty($foto_toko)) {
    $foto_toko = $svg_fallback;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($umkm['nama_umkm']); ?> | Pasar UMKM</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container py-5">
    
    <!-- HEADER TOKO UMKM -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 p-4">
        <div class="row align-items-center">
            <div class="col-md-2 text-center text-md-start mb-3 mb-md-0">
                <!-- Tambahan atribut onerror agar jika link file bermasalah, otomatis muncul ikon toko -->
                <img src="<?= $foto_toko; ?>" 
                     alt="<?= htmlspecialchars($umkm['nama_umkm']); ?>" 
                     class="rounded-circle img-thumbnail" 
                     style="width: 110px; height: 110px; object-fit: cover;"
                     onerror="this.onerror=null; this.src='<?= $svg_fallback; ?>';">
            </div>
           <div class="col-md-7 text-center text-md-start">
    <h2 class="fw-bold mb-1"><?= htmlspecialchars($umkm['nama_umkm']); ?></h2>
    <p class="text-muted mb-2"><?= htmlspecialchars($umkm['deskripsi'] ?? 'Mitra Resmi Pasar UMKM'); ?></p>
    
    <!-- TAMPILKAN BADGE JIKA NOMOR WA TERSEDIA -->
    <?php if (!empty($no_wa)) : ?>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
            <i class="fa-brands fa-whatsapp me-1"></i> +<?= $no_wa; ?>
        </span>
    <?php endif; ?>
</div>
            
            <!-- HANYA MUNCUL JIKA USER LOGIN (PEMILIK / ADMIN) -->
            <?php if ($is_owner) : ?>
                <div class="col-md-3 text-center text-md-end mt-3 mt-md-0">
                    <a href="tambah-produk.php?id_umkm=<?= $id_umkm; ?>" class="btn btn-primary rounded-3 w-100 py-2 fw-medium">
                        <i class="fa-solid fa-plus me-1"></i> Tambah Produk
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- KATALOG PRODUK TOKO -->
    <h4 class="fw-bold mb-4"><i class="fa-solid fa-store me-2 text-primary"></i>Katalog Produk Toko</h4>

    <div class="row g-4">
        <?php if (mysqli_num_rows($query_produk) > 0) : ?>
            <?php while ($p = mysqli_fetch_assoc($query_produk)) : ?>
                <?php
                    // Gambar produk
                    $foto_produk = 'assets/img/produk/' . (!empty($p['foto']) ? $p['foto'] : 'default-produk.png');
                    
                    // Format Pesan WA Otomatis untuk Pembeli
                    $teks_wa = "Halo " . $umkm['nama_umkm'] . ", saya mau pesan produk ini:\n\n" .
                               "📌 *Nama Produk:* " . $p['nama_produk'] . "\n" .
                               "💰 *Harga:* Rp " . number_format($p['harga'], 0, ',', '.') . "\n\n" .
                               "Apakah produk ini tersedia?";
                    $link_pesan_wa = "https://wa.me/" . $no_wa . "?text=" . urlencode($teks_wa);
                ?>

                <div class="col-md-3 col-sm-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <img src="<?= $foto_produk; ?>" 
                             class="card-img-top" 
                             style="height: 180px; object-fit: cover;"
                             onerror="this.src='https://placehold.co/400x220?text=Produk';">
                        
                        <div class="card-body p-3 d-flex flex-column">
                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($p['nama_produk']); ?></h6>
                            
                            <p class="text-primary fw-bold mb-3">
                                <?= ($p['harga'] > 0) ? 'Rp ' . number_format($p['harga'], 0, ',', '.') : 'Hubungi Penjual'; ?>
                            </p>

                            <!-- TOMBOL UNTUK PEMBELI (KONSUMEN) -->
                            <a href="<?= $link_pesan_wa; ?>" target="_blank" class="btn btn-success mt-auto rounded-3 w-100 fw-medium">
                                <i class="fa-brands fa-whatsapp me-1"></i> Pesan via WA
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-box-open fa-3x text-muted mb-3 d-block"></i>
                <p class="text-muted mb-0">Belum ada produk yang ditambahkan di toko ini.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>