<?php
// 1. Jalankan Session & Proteksi Login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. Hubungkan Database
include __DIR__ . '/includes/config/koneksi.php'; 

if (!isset($koneksi) || !$koneksi) {
    die("Eror Sistem: Koneksi database gagal dimuat.");
}

// 3. AMBIL SEMUA DATA KATEGORI & HITUNG JUMLAH PRODUK DI DALAMNYA
// Menggunakan INNER JOIN agar hanya menampilkan kategori wilayah yang benar-benar sedang digunakan oleh produk saja
$query_tampil = mysqli_query($koneksi, "
    SELECT k.*, COUNT(p.id) AS total_produk 
    FROM tabel_kategori k 
    INNER JOIN tabel_produk p ON k.id = p.kategori_id 
    GROUP BY k.id 
    ORDER BY k.nama_kategori ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kategori Wilayah — MajuUMKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
        .card-custom { border: 1px solid #e2e8f0; border-radius: 16px; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .table > :not(caption) > * > * { padding: 14px 16px; border-bottom-color: #f1f5f9; }
        .badge-count { background-color: #f1f5f9; color: #475569; font-weight: 600; font-size: 12px; }
    </style>
</head>
<body class="py-5">

<div class="container" style="max-width: 750px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-medium" style="border-radius: 8px;">
            <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Dashboard
        </a>
        <span class="text-muted small font-monospace">Data Master Wilayah</span>
    </div>

    <div class="card card-custom p-4">
        <div class="mb-4">
            <h4 class="fw-bold text-dark m-0">Kategori Wilayah Aktif</h4>
            <p class="text-muted small m-0">Menampilkan daftar wilayah/sektor yang sedang digunakan oleh komoditas produk.</p>
        </div>

        <div class="table-responsive">
            <table class="table align-middle m-0">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th style="width: 80px;" class="text-center">No</th>
                        <th>Nama Kategori Wilayah</th>
                        <th style="width: 200px;" class="text-center">Total Distribusi Produk</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (mysqli_num_rows($query_tampil) > 0) : ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($query_tampil)) : ?>
                            <tr>
                                <td class="text-center fw-medium text-secondary"><?= $no++; ?></td>
                                <td class="fw-semibold text-dark">
                                    <i class="fa-solid fa-location-dot text-secondary opacity-50 me-2 fa-sm"></i>
                                    <?= htmlspecialchars($row['nama_kategori']); ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-count rounded-pill px-3 py-2">
                                        <?= $row['total_produk']; ?> Komoditas
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-map-location-dot fa-3x opacity-25 d-block mb-3"></i>
                                Belum ada kategori wilayah yang aktif digunakan pada produk.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>