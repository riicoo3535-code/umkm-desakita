<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/includes/config/koneksi.php';

// Cek login
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: umkm.php");
    exit;
}

$id_umkm = isset($_GET['id_umkm']) ? (int)$_GET['id_umkm'] : 0;
$pesan_error = "";

$query_umkm = mysqli_query($koneksi, "SELECT * FROM tabel_umkm WHERE id = $id_umkm LIMIT 1");
$umkm = mysqli_fetch_assoc($query_umkm);

if (!$umkm) {
    die("Data UMKM tidak ditemukan.");
}

$query_kategori = mysqli_query($koneksi, "SELECT * FROM tabel_kategori ORDER BY id ASC");

// DETEKSI KOLOM OTOMATIS
$kolom_relasi = "umkm_id"; 
$kolom_gambar = "foto";
$has_kategori = false;

$cek_kolom_produk = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_produk");
if ($cek_kolom_produk) {
    $semua_kolom = [];
    while ($k = mysqli_fetch_assoc($cek_kolom_produk)) {
        $semua_kolom[] = strtolower($k['Field']);
    }

    if (in_array('id_umkm', $semua_kolom)) $kolom_relasi = "id_umkm";
    if (in_array('gambar', $semua_kolom)) $kolom_gambar = "gambar";
    elseif (in_array('image', $semua_kolom)) $kolom_gambar = "image";
    if (in_array('kategori_id', $semua_kolom)) $has_kategori = true;
}

// PROSES SIMPAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = mysqli_real_escape_string($koneksi, trim($_POST['nama_produk']));
    $harga       = (int) str_replace(['.', ','], '', $_POST['harga']);
    $deskripsi   = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi'] ?? ''));
    $kategori_id = isset($_POST['kategori_id']) ? (int)$_POST['kategori_id'] : 0;
    
    $nama_file_foto = "default-produk.png";

    if (empty($nama_produk)) {
        $pesan_error = "Nama produk wajib diisi.";
    } elseif ($has_kategori && $kategori_id <= 0) {
        $pesan_error = "Silakan pilih kategori produk terlebih dahulu.";
    }

    // Upload Gambar
    if (empty($pesan_error) && isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $file_name = $_FILES['foto']['name'];
        $file_tmp  = $_FILES['foto']['tmp_name'];
        $ekstensi  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'])) {
            $nama_file_foto = 'prod_' . time() . '_' . uniqid() . '.' . $ekstensi;
            $folder_tujuan = __DIR__ . '/assets/img/produk/';

            if (!is_dir($folder_tujuan)) {
                mkdir($folder_tujuan, 0755, true);
            }

            move_uploaded_file($file_tmp, $folder_tujuan . $nama_file_foto);
        } else {
            $pesan_error = "Format gambar harus JPG, JPEG, PNG, atau WEBP.";
        }
    }

    // Insert Database (Tanpa Stok)
    if (empty($pesan_error)) {
        if ($has_kategori) {
            $query_simpan = "INSERT INTO tabel_produk ({$kolom_relasi}, kategori_id, nama_produk, harga, deskripsi, {$kolom_gambar}) 
                             VALUES ($id_umkm, $kategori_id, '$nama_produk', $harga, '$deskripsi', '$nama_file_foto')";
        } else {
            $query_simpan = "INSERT INTO tabel_produk ({$kolom_relasi}, nama_produk, harga, deskripsi, {$kolom_gambar}) 
                             VALUES ($id_umkm, '$nama_produk', $harga, '$deskripsi', '$nama_file_foto')";
        }

        if (mysqli_query($koneksi, $query_simpan)) {
            header("Location: detail-umkm.php?id=" . $id_umkm);
            exit;
        } else {
            $pesan_error = "Gagal menyimpan produk: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk - <?= htmlspecialchars($umkm['nama_umkm']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light py-5">

<div class="container" style="max-width: 600px;">
    
    <div class="mb-3">
        <a href="detail-umkm.php?id=<?= $id_umkm; ?>" class="text-decoration-none text-secondary small">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Toko
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4">
        <h4 class="fw-bold mb-1">Tambah Produk Baru</h4>
        <p class="text-muted small mb-4">Toko: <strong><?= htmlspecialchars($umkm['nama_umkm']); ?></strong></p>

        <?php if (!empty($pesan_error)) : ?>
            <div class="alert alert-danger small rounded-3 mb-4">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?= $pesan_error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label class="form-label small fw-medium">Nama Produk</label>
                <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Rujak Lontong" required>
            </div>

            <?php if ($has_kategori) : ?>
                <div class="mb-3">
                    <label class="form-label small fw-medium">Kategori Produk</label>
                    <select name="kategori_id" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php 
                        if ($query_kategori && mysqli_num_rows($query_kategori) > 0) {
                            while ($kat = mysqli_fetch_assoc($query_kategori)) {
                                $nama_kategori = $kat['nama_kategori'] ?? $kat['nama'] ?? $kat['kategori'] ?? ('Kategori ' . $kat['id']);
                                echo '<option value="' . $kat['id'] . '">' . htmlspecialchars($nama_kategori) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label small fw-medium">Harga (Rp)</label>
                <input type="number" name="harga" class="form-control" placeholder="Contoh: 8000" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-medium">Deskripsi Produk (Opsional)</label>
                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan keunggulan produk..."></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-medium">Foto Produk</label>
                <input type="file" name="foto" class="form-control" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-medium">
                <i class="fa-solid fa-save me-1"></i> Simpan Produk
            </button>
        </form>
    </div>
</div>

</body>
</html>