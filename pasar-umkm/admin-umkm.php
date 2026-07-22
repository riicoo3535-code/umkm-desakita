<?php
// 1. Jalankan Session & Proteksi Admin (Contoh sederhana)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Anda bisa menambahkan pengecekan session admin di sini jika ada, misal:
// if(!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Hubungkan Koneksi Database
// Sesuaikan arah path include ini dengan struktur folder admin Anda
include __DIR__ . '/includes/config/koneksi.php'; 

if (!isset($koneksi) || !$koneksi) {
    die("Eror Sistem: Koneksi database gagal dimuat.");
}

// 3. LOGIKA HAPUS MITRA (MENGGUNAKAN PREPARED STATEMENT)
$notif = "";
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id_hapus = (int)$_GET['id'];
    
    // Ambil nama file logo lama agar bisa dihapus dari server (mencegah sampah file)
    $query_foto = "SELECT logo FROM tabel_umkm WHERE id = ?";
    $stmt_foto = mysqli_prepare($koneksi, $query_foto);
    if ($stmt_foto) {
        mysqli_stmt_bind_param($stmt_foto, "i", $id_hapus);
        mysqli_stmt_execute($stmt_foto);
        $result_foto = mysqli_stmt_get_result($stmt_foto);
        if ($row_foto = mysqli_fetch_assoc($result_foto)) {
            if ($row_foto['logo'] !== 'default-logo.png') {
                $path_logo = __DIR__ . '/assets/img/logo/' . $row_foto['logo'];
                if (file_exists($path_logo)) {
                    unlink($path_logo);
                }
            }
        }
        mysqli_stmt_close($stmt_foto);
    }

    // Jalankan query hapus data
    $query_hapus = "DELETE FROM tabel_umkm WHERE id = ?";
    $stmt_hapus = mysqli_prepare($koneksi, $query_hapus);
    if ($stmt_hapus) {
        mysqli_stmt_bind_param($stmt_hapus, "i", $id_hapus);
        if (mysqli_stmt_execute($stmt_hapus)) {
            $notif = "sukses_hapus";
        } else {
            $notif = "gagal_hapus";
        }
        mysqli_stmt_close($stmt_hapus);
    }
}

// 4. AMBIL DATA MITRA UMKM
$query_tampil = "SELECT * FROM tabel_umkm ORDER BY id DESC";
$eksekusi_tampil = mysqli_query($koneksi, $query_tampil);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mitra UMKM — Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
        }
        .sidebar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .card-custom {
            border: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(12px);
            border-radius: 12px;
        }
        .table-premium {
            color: #e2e8f0;
            vertical-align: middle;
        }
        .table-premium th {
            background: rgba(15, 23, 42, 0.6) !important;
            color: rgba(255, 255, 255, 0.6) !important;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .table-premium td {
            background: transparent !important;
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
            color: #e2e8f0;
        }
        .logo-container {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn-action {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-action-edit {
            background: rgba(255, 255, 255, 0.05);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }
        .btn-action-edit:hover {
            background: #38bdf8;
            color: #0f172a;
        }
        .btn-action-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .btn-action-delete:hover {
            background: #ef4444;
            color: #fff;
        }
        .badge-active {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.2);
            font-size: 11px;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-5">
        <div>
            <span class="text-uppercase tracking-widest text-white-50 d-block mb-1" style="font-size: 11px; letter-spacing: 2px;">Panel Administrasi</span>
            <h2 class="sidebar-brand text-white m-0">MajuUMKM Hub</h2>
        </div>
        <div>
            <a href="daftar-umkm.php" class="btn btn-light fw-medium px-4 py-2" style="border-radius: 8px; font-size: 14px;">
                <i class="fa-solid fa-plus me-2 fa-sm"></i> Tambah Mitra Baru
            </a>
        </div>
    </div>

    <?php if ($notif === 'sukses_hapus') : ?>
        <div class="alert alert-success bg-success bg-opacity-10 border-0 text-success small p-3 rounded-3 mb-4 d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> Data mitra dan file logo berhasil dihapus secara permanen.
        </div>
    <?php elseif ($notif === 'gagal_hapus') : ?>
        <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger small p-3 rounded-3 mb-4 d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i> Terjadi kesalahan sistem saat mencoba menghapus data.
        </div>
    <?php endif; ?>

    <div class="card card-custom shadow-lg overflow-hidden">
        <div class="px-4 py-3 border-bottom border-light border-opacity-10 d-flex justify-content-between align-items-center bg-black bg-opacity-10">
            <h5 class="m-0 fw-semibold text-white-50" style="font-size: 14px;">Daftar Direktori Mitra Terdaftar</h5>
            <span class="badge bg-white bg-opacity-10 text-white rounded-pill font-monospace" style="font-size: 11px;">
                <?= mysqli_num_rows($eksekusi_tampil); ?> Total
            </span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-premium m-0">
                <thead>
                    <tr>
                        <th style="width: 80px;" class="text-center">Logo</th>
                        <th>Nama Usaha</th>
                        <th>Deskripsi Bisnis</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 100px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($eksekusi_tampil) > 0) : ?>
                        <?php while ($mitra = mysqli_fetch_assoc($eksekusi_tampil)) : ?>
                            <tr>
                                <td class="text-center">
                                    <img src="assets/img/logo/<?= htmlspecialchars($mitra['logo']); ?>" 
                                         alt="Logo <?= htmlspecialchars($mitra['nama_umkm']); ?>" 
                                         class="logo-container"
                                         onerror="this.src='assets/img/logo/default-logo.png';">
                                </td>
                                <td>
                                    <div class="fw-semibold text-white"><?= htmlspecialchars($mitra['nama_umkm']); ?></div>
                                    <div class="text-white-30 small" style="font-size: 11px;">ID: #<?= $mitra['id']; ?></div>
                                </td>
                                <td>
                                    <div class="text-white-50 text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($mitra['deskripsi']); ?>">
                                        <?= htmlspecialchars($mitra['deskripsi']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-active">Terverifikasi</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="edit-umkm.php?id=<?= $mitra['id']; ?>" class="btn-action btn-action-edit" title="Edit Data">
                                            <i class="fa-solid fa-pen fa-xs"></i>
                                        </a>
                                        <a href="admin-umkm.php?aksi=hapus&id=<?= $mitra['id']; ?>" 
                                           class="btn-action btn-action-delete" 
                                           title="Hapus Data"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus mitra <?= htmlspecialchars($mitra['nama_umkm'], ENT_QUOTES); ?> secara permanen? Tindakan ini tidak dapat dibatalkan.');">
                                            <i class="fa-solid fa-trash fa-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-white-50">
                                <i class="fa-solid fa-folder-open d-block mb-3 text-white-30 fa-2xl"></i>
                                Belum ada data mitra UMKM yang terdaftar di sistem.
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