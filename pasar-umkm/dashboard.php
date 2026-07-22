<?php
// 1. Paksa PHP menampilkan eror jika ada masalah
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Jalankan Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. PROTEKSI KEAMANAN: Jika belum login, tendang kembali ke halaman login
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: login.php");
    exit;
}

// 4. Hubungkan database untuk mengisi angka statistik dashboard
include __DIR__ . '/includes/config/koneksi.php';

// Ambil hitungan total data dari database secara dinamis
$total_produk   = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM tabel_produk"));
$total_umkm     = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM tabel_umkm"));
$total_kategori = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM tabel_kategori"));
$total_artikel  = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM tabel_artikel")); // Tambahan Baru
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengelola — MajuUMKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
        }
        .sidebar {
            background-color: #0f172a;
            min-height: 100vh;
            color: #94a3b8;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.06);
            color: #ffffff;
        }
        .main-content-wrapper {
            margin-left: 25%; /* Default fallback */
        }
        @media (min-width: 768px) {
            .main-content-wrapper {
                margin-left: 25%;
            }
        }
        @media (min-width: 992px) {
            .main-content-wrapper {
                margin-left: 16.666667%;
            }
        }
        .avatar-admin {
            width: 40px;
            height: 40px;
            background-color: #cbd5e1;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border-radius: 50%;
        }
        .fade-in-up {
            animation: fadeInUp 0.35s ease-out forwards;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <!-- SIDEBAR MENU -->
        <div class="col-md-3 col-lg-2 sidebar p-3 d-none d-md-block">
            <div class="d-flex align-items-center gap-2 px-2 py-3 mb-4 border-bottom border-secondary border-opacity-20">
                <i class="fa-solid fa-layer-group text-white fs-4"></i>
                <span class="fw-bold text-white fs-5 tracking-tight">Dashboard UMKM</span>
            </div>
            
            <ul class="nav flex-column" id="sidebar-menu">
                <li class="nav-item">
                    <a class="nav-link active" onclick="loadRingkasanUtama()">
                        <i class="fa-solid fa-chart-pie me-2 fa-sm"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-target="admin-umkm.php">
                        <i class="fa-solid fa-store me-2 fa-sm"></i> Data Mitra UMKM
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-target="admin-produk.php">
                        <i class="fa-solid fa-box me-2 fa-sm"></i> Manajemen Produk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-target="admin-kategori.php">
                        <i class="fa-solid fa-tags me-2 fa-sm"></i> Kategori Wilayah
                    </a>
                </li>
                <!-- REVISI BARU: Menu Kelola Artikel Sesuai Jalur AJAX -->
                <li class="nav-item">
                    <a class="nav-link" data-target="admin-artikel.php">
                        <i class="fa-solid fa-newspaper me-2 fa-sm"></i> Kelola Artikel
                    </a>
                </li>
                <li class="nav-item mt-4 border-top border-secondary border-opacity-10 pt-3">
                    <a class="nav-link text-danger bg-danger bg-opacity-10" href="login.php?aksi=logout">
                        <i class="fa-solid fa-power-off me-2 fa-sm"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>

        <!-- KONTEN TENGAH (DAPAT BERGANTI VIA AJAX) -->
        <div class="col-md-9 col-lg-10 main-content-wrapper px-md-4 py-4" id="ajax-content-container">
            <div class="fade-in-up">
                <header class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                    <div>
                        <h5 class="fw-bold m-0">Ringkasan Sistem</h5>
                        <p class="text-muted small m-0">Selamat datang kembali, pengelola portal.</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end d-none d-sm-block">
                            <span class="fw-semibold d-block small text-dark"><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?></span>
                            <span class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Administrator</span>
                        </div>
                        <div class="avatar-admin">
                            <?= strtoupper(substr($_SESSION['admin_nama'] ?? 'A', 0, 1)); ?>
                        </div>
                    </div>
                </header>

                <!-- REVISI GRID: col-lg-4 diubah ke col-lg-3 agar muat 4 baris statistik -->
                <div class="row g-4 mb-5">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card-stat p-4 d-flex align-items-center justify-content-between" style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
                            <div>
                                <span class="text-muted small fw-medium d-block mb-1 text-uppercase tracking-wider" style="font-size: 11px;">Total Mitra UMKM</span>
                                <h2 class="fw-bold m-0"><?= $total_umkm; ?></h2>
                            </div>
                            <div class="p-3 bg-light rounded-3 text-secondary">
                                <i class="fa-solid fa-store fa-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card-stat p-4 d-flex align-items-center justify-content-between" style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
                            <div>
                                <span class="text-muted small fw-medium d-block mb-1 text-uppercase tracking-wider" style="font-size: 11px;">Produk Terdaftar</span>
                                <h2 class="fw-bold m-0"><?= $total_produk; ?></h2>
                            </div>
                            <div class="p-3 bg-light rounded-3 text-secondary">
                                <i class="fa-solid fa-box fa-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card-stat p-4 d-flex align-items-center justify-content-between" style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
                            <div>
                                <span class="text-muted small fw-medium d-block mb-1 text-uppercase tracking-wider" style="font-size: 11px;">Kategori Sektor</span>
                                <h2 class="fw-bold m-0"><?= $total_kategori; ?></h2>
                            </div>
                            <div class="p-3 bg-light rounded-3 text-secondary">
                                <i class="fa-solid fa-tags fa-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- REVISI BARU: Kotak Counter Statistik Artikel -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card-stat p-4 d-flex align-items-center justify-content-between" style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
                            <div>
                                <span class="text-muted small fw-medium d-block mb-1 text-uppercase tracking-wider" style="font-size: 11px;">Artikel Rilis</span>
                                <h2 class="fw-bold m-0"><?= $total_artikel; ?></h2>
                            </div>
                            <div class="p-3 bg-light rounded-3 text-secondary">
                                <i class="fa-solid fa-newspaper fa-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 p-md-5 rounded-4 border bg-white mb-4 position-relative overflow-hidden shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-8 position-relative" style="z-index: 2;">
                            <h4 class="fw-bold text-dark mb-2">PANEL MANAJEMEN UNGGULAN UMKM DESA KEDUNGLO</h4>
                            <p class="text-muted small mb-4" style="max-width: 600px;">
                                Melalui panel ini, Anda memegang kendali penuh untuk menambahkan mitra usaha baru, menyetujui perubahan foto produk komoditas lokal, hingga memperbarui klasifikasi kategori pasar.
                            </p>
                            <div class="d-flex gap-2">
                                <a onclick="document.querySelector('[data-target=\'admin-produk.php\']').click();" class="btn btn-dark btn-sm rounded-3 px-3 py-2 fw-medium" style="cursor:pointer;">Kelola Produk</a>
                                <a href="../index.php" target="_blank" class="btn btn-outline-secondary btn-sm rounded-3 px-3 py-2">Lihat Live Website</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="ringkasan-template-storage" class="d-none"></div>

<script>
let htmlRingkasanBawaan = "";

document.addEventListener("DOMContentLoaded", function() {
    htmlRingkasanBawaan = document.getElementById('ajax-content-container').innerHTML;
    
    document.querySelectorAll('#sidebar-menu .nav-link[data-target]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            document.querySelectorAll('#sidebar-menu .nav-link').forEach(item => item.classList.remove('active'));
            this.classList.add('active');
            
            const fileTarget = this.getAttribute('data-target');
            muatKontenTengah(fileTarget);
        });
    });
});

function loadRingkasanUtama() {
    document.querySelectorAll('#sidebar-menu .nav-link').forEach(item => item.classList.remove('active'));
    document.querySelector('#sidebar-menu .nav-link:not([data-target])').classList.add('active');
    
    const container = document.getElementById('ajax-content-container');
    container.innerHTML = htmlRingkasanBawaan;
}

function muatKontenTengah(urlFile) {
    const container = document.getElementById('ajax-content-container');
    
    container.innerHTML = `
        <div class="d-flex flex-column align-items-center justify-content-center py-5" style="min-height: 50vh;">
            <div class="spinner-border text-dark mb-3" role="status" style="width: 2.5rem; height: 2.5rem; stroke-width: 3px;"></div>
            <span class="text-muted small font-monospace">Sinkronisasi Data...</span>
        </div>
    `;
    
    fetch(urlFile)
        .then(response => {
            if (!response.ok) throw new Error('Berkas gagal disinkronkan oleh sistem.');
            return response.text();
        })
        .then(htmlResult => {
            container.innerHTML = `<div class="fade-in-up">${htmlResult}</div>`;
        })
        .catch(error => {
            container.innerHTML = `
                <div class="alert alert-danger border-0 p-4 rounded-3 d-flex align-items-center gap-3 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation fa-2xl"></i>
                    <div>
                        <h6 class="fw-bold m-0">Gagal Memuat Konten</h6>
                        <span class="small opacity-75">Pastikan file <b>${urlFile}</b> sudah tersedia di direktori atau periksa koneksi lokal Anda.</span>
                    </div>
                </div>
            `;
        });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>