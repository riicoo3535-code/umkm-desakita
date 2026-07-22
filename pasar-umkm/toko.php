<?php
// Hubungkan Database
include __DIR__ . '/includes/config/koneksi.php'; 

if (!isset($koneksi) || !$koneksi) {
    die("Eror Sistem: Koneksi database gagal dimuat.");
}

// 1. Ambil ID UMKM dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php"); // lempar ke halaman utama jika tidak ada ID
    exit;
}

$id_umkm = (int)$_GET['id'];

// 2. Deteksi otomatis nama kolom relasi (id_umkm atau umkm_id)
$kolom_relasi = "id_umkm"; 
$cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_produk");
while ($k = mysqli_fetch_assoc($cek_kolom)) {
    if ($k['Field'] === 'umkm_id') {
        $kolom_relasi = "umkm_id";
        break;
    }
}

// 3. Ambil Data Profil UMKM
$query_umkm = mysqli_prepare($koneksi, "SELECT * FROM tabel_umkm WHERE id = ?");
mysqli_stmt_bind_param($query_umkm, "i", $id_umkm);
mysqli_stmt_execute($query_umkm);
$data_umkm = mysqli_fetch_assoc(mysqli_stmt_get_result($query_umkm));

if (!$data_umkm) {
    die("Toko UMKM tidak ditemukan di sistem.");
}

// 4. Ambil Semua Produk yang Dimiliki UMKM Ini
$query_produk = mysqli_prepare($koneksi, "SELECT p.*, k.nama_kategori 
                                         FROM tabel_produk p
                                         LEFT JOIN tabel_kategori k ON p.kategori_id = k.id
                                         WHERE p.{$kolom_relasi} = ? 
                                         ORDER BY p.id DESC");
mysqli_stmt_bind_param($query_produk, "i", $id_umkm);
mysqli_stmt_execute($query_produk);
$tampil_produk = mysqli_stmt_get_result($query_produk);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data_umkm['nama_umkm']); ?> — MajuUMKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
        .store-header { background: #ffffff; border-bottom: 1px solid #e2e8f0; }
        .product-card { border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; overflow: hidden; transition: transform 0.2s; }
        .product-card:hover { transform: translateY(-4px); }
        .img-container { height: 180px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    </style>
</head>
<body>

<div class="store-header py-5 mb-5">
    <div class="container">
        <div class="d-flex align-items-center gap-4">
            <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 28px; font-weight: 700;">
                <?= strtoupper(substr($data_umkm['nama_umkm'], 0, 1)); ?>
            </div>
            <div>
                <h2 class="fw-bold m-0"><?= htmlspecialchars($data_umkm['nama_umkm']); ?></h2>
                <p class="text-muted small m-0"><i class="fa-solid fa-store me-1"></i> Mitra Resmi Platform MajuUMKM</p>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <h4 class="fw-bold mb-4">Semua Produk / Menu Toko</h4>
    
    <div class="row g-4">
        <?php if (mysqli_num_rows($tampil_produk) > 0) : ?>
            <?php while ($produk = mysqli_fetch_assoc($tampil_produk)) : ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card h-100 shadow-sm">
                        <div class="img-container">
                            <img src="assets/img/produk/<?= $produk['foto']; ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($produk['nama_produk']); ?>">
                        </div>
                        <div class="p-3">
                            <span class="badge bg-light text-secondary mb-2 border text-capitalize" style="font-size: 10px;">
                                <?= htmlspecialchars($produk['nama_kategori'] ?? 'Umum'); ?>
                            </span>
                            <h6 class="fw-bold text-dark text-truncate mb-1"><?= htmlspecialchars($produk['nama_produk']); ?></h6>
                            <p class="text-dark fw-semibold small mb-2">Rp <?= number_format($produk['harga'], 0, ',', '.'); ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <span class="text-muted small" style="font-size: 11px;">Stok: <?= $produk['stok']; ?></span>
                                <button class="btn btn-dark btn-sm px-3" style="font-size: 11px; border-radius: 6px;">Beli</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-box-open fa-3x text-muted mb-3"></i>
                <p class="text-muted">Toko ini belum mengunggah produk atau menu apa pun.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>