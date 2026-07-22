<?php
// A. Deteksi jika user datang membawa perintah logout dari sidebar dashboard
if (isset($_GET['aksi']) && $_GET['aksi'] === 'logout') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Kosongkan semua data login di session
    session_unset();
    // Hancurkan session dari server
    session_destroy();
    
    // Alihkan ke login.php yang bersih tanpa parameter agar siap login lagi
    header("Location: login.php");
    exit;
}

// 1. Paksa PHP menampilkan eror jika ada masalah (Kode Anda yang kemarin)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Aktifkan session secara aman
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Jika sudah login, langsung alihkan ke dashboard.php
if (isset($_SESSION['status_login']) && $_SESSION['status_login'] === true) {
    header("Location: dashboard.php");
    exit;
}

// 4. Hubungkan koneksi database (JALUR ABSOLUT AMAN)
include __DIR__ . '/includes/config/koneksi.php'; 

// Proteksi tambahan agar tidak Fatal Error di bawahnya jika database mati
if (!isset($koneksi) || !$koneksi) {
    die("Eror Sistem: Koneksi database gagal dimuat di halaman login.");
}

// Inisialisasi awal agar tidak memicu 'Undefined Variable' di dalam HTML bawah
$pesan_error = "";

// 5. PROSES BACKEND (Hanya berjalan jika form disubmit via method POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ambil data dan amankan dari SQL Injection / Spasi Kosong
    $username = isset($_POST['username']) ? mysqli_real_escape_string($koneksi, trim($_POST['username'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($username) && !empty($password)) {
        
        // Mencari data admin berdasarkan username
        $query_admin  = "SELECT * FROM tabel_admin WHERE username = '$username'";
        $kirim_query  = mysqli_query($koneksi, $query_admin);
        
        if (!$kirim_query) {
            die("Eror Query Database: " . mysqli_error($koneksi));
        }
        
        // Jika username ditemukan
        if (mysqli_num_rows($kirim_query) === 1) {
            $data_admin = mysqli_fetch_assoc($kirim_query);
            
            // Verifikasi enkripsi password (Mendukung MD5 sesuai bawaan Anda atau Plain Text)
            if (md5($password) === $data_admin['password'] || $password === $data_admin['password']) {
                
                // Daftarkan data ke dalam Session
                $_SESSION['status_login'] = true;
                $_SESSION['admin_id']     = $data_admin['id'];
                $_SESSION['admin_nama']   = $data_admin['nama_lengkap'];
                $_SESSION['admin_user']   = $data_admin['username'];
                
                // Sukses login, langsung lempar ke dashboard.php di folder yang sama
                header("Location: dashboard.php");
                exit;
            } else {
                $pesan_error = "Kata sandi yang Anda masukkan salah.";
            }
        } else {
            $pesan_error = "Username tidak terdaftar dalam sistem.";
        }
    } else {
        $pesan_error = "Username dan kata sandi wajib diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Autentikasi — MajuUMKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
        }
        .card-login {
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border-radius: 16px;
        }
        .form-control-premium {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            padding: 12px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .form-control-premium:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: rgba(255, 255, 255, 0.3);
            color: #fff;
            box-shadow: none;
        }
        .btn-premium-login {
            background: #f8fafc;
            color: #0f172a;
            border: none;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-premium-login:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }
        /* Style Tambahan untuk Tombol Beranda Premium */
        .btn-back-to-home {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-back-to-home:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .btn-back-to-home i {
            transition: transform 0.2s ease;
        }
        .btn-back-to-home:hover i {
            transform: translateX(-3px); /* Efek panah bergeser lembut ke kiri */
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">

<div class="w-100" style="max-width: 420px;">
    
    <div class="text-center mb-4">
        <a href="index.php" class="btn-back-to-home">
            <i class="fa-solid fa-arrow-left fa-sm"></i> Kembali ke Beranda
        </a>
    </div>

    <div class="card card-login p-4 p-md-5 shadow-lg">
        
        <div class="text-center mb-4">
            <span class="text-uppercase tracking-widest text-white-50 d-block mb-1" style="font-size: 10px; letter-spacing: 3px;">Control Panel</span>
            <h3 class="fw-bold text-white m-0" style="letter-spacing: -0.5px;">MajuUMKM</h3>
        </div>

        <?php if (!empty($pesan_error)) : ?>
            <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger small p-3 rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation shadow-none"></i>
                <div><?= $pesan_error; ?></div>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label class="form-label text-white-50 small fw-medium">Username Pengelola</label>
                <input type="text" name="username" class="form-control form-control-premium" placeholder="Masukkan username" required autocomplete="off" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="mb-4">
                <label class="form-label text-white-50 small fw-medium mb-1">Kata Sandi</label>
                <input type="password" name="password" class="form-control form-control-premium" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-premium-login w-100 shadow-sm mt-2">
                Masuk ke Dashboard <i class="fa-solid fa-right-to-bracket ms-1 fa-xs"></i>
            </button>
        </form>

    </div>
    
    <div class="text-center mt-4 text-white-30" style="font-size: 11px; opacity: 0.4;">
        &copy; 2026 MajuUMKM Direktori. All Rights Reserved.
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>