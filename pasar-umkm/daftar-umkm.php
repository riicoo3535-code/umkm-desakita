<?php
// 1. Jalankan Session & Tampilkan Eror jika ada kendala
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Hubungkan Koneksi Database
include __DIR__ . '/includes/config/koneksi.php'; 

if (!isset($koneksi) || !$koneksi) {
    die("Eror Sistem: Koneksi database gagal dimuat.");
}

$pesan_error = "";

// 3. PROSES PENDAFTARAN UMKM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nama_umkm = mysqli_real_escape_string($koneksi, trim($_POST['nama_umkm']));
    $no_wa     = mysqli_real_escape_string($koneksi, trim($_POST['no_wa'] ?? ''));
    $deskripsi = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi'] ?? ''));
    
    $nama_file_foto = "default-produk.png"; 

    // Validasi Kolom Wajib
    if (empty($nama_umkm)) {
        $pesan_error = "Nama UMKM wajib diisi.";
    } elseif (empty($no_wa)) {
        $pesan_error = "Nomor WhatsApp wajib diisi agar pembeli dapat menghubungi Anda.";
    }

    // FORMATTING OTOMATIS NOMOR WA (Mengubah 08/ +628 menjadi 628)
    if (empty($pesan_error)) {
        $no_wa = str_replace([' ', '-', '+'], '', $no_wa);
        if (str_starts_with($no_wa, '0')) {
            $no_wa = '62' . substr($no_wa, 1);
        }
    }

    // 4. LOGIKA UPLOAD FOTO / LOGO UMKM
    if (empty($pesan_error) && isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $file_name = $_FILES['logo']['name'];
        $file_tmp  = $_FILES['logo']['tmp_name'];
        
        $ekstensi = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ekstensi, $ekstensi_diizinkan)) {
            $nama_file_foto = 'umkm_' . time() . '_' . uniqid() . '.' . $ekstensi;
            
            // Folder tujuan
            $folder_umkm = __DIR__ . '/assets/img/umkm/';
            $folder_logo = __DIR__ . '/assets/img/logo/';

            if (!is_dir($folder_umkm)) mkdir($folder_umkm, 0755, true);
            if (!is_dir($folder_logo)) mkdir($folder_logo, 0755, true);

            // Simpan ke folder umkm dan salin ke folder logo agar aman di semua halaman
            if (move_uploaded_file($file_tmp, $folder_umkm . $nama_file_foto)) {
                copy($folder_umkm . $nama_file_foto, $folder_logo . $nama_file_foto);
            }
        } else {
            $pesan_error = "Format foto/logo harus berupa JPG, JPEG, PNG, atau WEBP.";
        }
    }

    // 5. JIKA TIDAK ADA EROR, MASUKKAN KE DATABASE
    if (empty($pesan_error)) {

        // Cek secara otomatis nama kolom gambar di tabel_umkm ('foto' atau 'logo')
        $kolom_gambar = "foto";
        $cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_umkm");
        while ($k = mysqli_fetch_assoc($cek_kolom)) {
            if ($k['Field'] === 'logo') {
                $kolom_gambar = "logo";
                break;
            }
        }

        // Query Insert Data UMKM
        $query_tambah = "INSERT INTO tabel_umkm (nama_umkm, no_wa, deskripsi, {$kolom_gambar}) 
                         VALUES ('$nama_umkm', '$no_wa', '$deskripsi', '$nama_file_foto')";
        
        $eksekusi = mysqli_query($koneksi, $query_tambah);

        if ($eksekusi) {
            // DIALIHKAN KE UMKM.PHP DENGAN PARAMETER SUKSES
            header("Location: umkm.php?notif=sukses");
            exit;
        } else {
            $pesan_error = "Gagal mendaftar ke database: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Mitra Baru — MajuUMKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(120% 140% at 15% 0%, #164E39 0%, var(--forest) 45%, var(--forest-deep) 100%);
            min-height: 100vh;
            position: relative;
        }
        body::before{
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 85% 15%, rgba(198,161,91,0.16) 0%, transparent 45%),
                radial-gradient(circle at 10% 90%, rgba(175,200,160,0.12) 0%, transparent 40%);
            pointer-events: none;
        }

        .card-register {
            border: 1px solid rgba(198, 161, 91, 0.25);
            background: rgba(251, 248, 242, 0.97);
            backdrop-filter: blur(16px);
            border-radius: 18px;
            position: relative;
            z-index: 1;
        }

        .card-register h3 {
            font-family: 'Fraunces', serif;
            color: var(--forest-deep) !important;
        }
        .card-register .text-white-50{
            color: var(--stone) !important;
        }

        .form-label.text-white-50{
            color: var(--forest) !important;
        }

        .form-control-premium {
            background: #FFFFFF;
            border: 1px solid var(--ivory-deep);
            color: var(--ink);
            padding: 12px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .form-control-premium::placeholder{
            color: #A6A99D;
        }
        .form-control-premium:focus {
            background: #FFFFFF;
            border-color: var(--gold);
            color: var(--ink);
            box-shadow: 0 0 0 3px rgba(198, 161, 91, 0.18);
        }
        .form-control-premium.text-white-50{
            color: var(--stone) !important;
        }

        .btn-premium-register {
            background: var(--forest);
            color: var(--ivory);
            border: none;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-premium-register:hover {
            background: var(--forest-deep);
            color: var(--ivory);
            box-shadow: 0 10px 22px -10px rgba(15,61,46,0.5);
            transform: translateY(-1px);
        }

        .btn-back-to-umkm {
            color: rgba(251, 248, 242, 0.75);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: 30px;
            background: rgba(251, 248, 242, 0.05);
            border: 1px solid rgba(198, 161, 91, 0.3);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-back-to-umkm:hover {
            color: var(--ivory);
            background: rgba(251, 248, 242, 0.1);
            border-color: var(--gold);
        }

        .alert-danger.bg-danger{
            background-color: rgba(198, 45, 45, 0.08) !important;
            color: #A32E2E !important;
        }

        .text-white-30{
            color: rgba(251, 248, 242, 0.5) !important;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3 py-5">

<div class="w-100" style="max-width: 500px;">
    
    <div class="text-center mb-4">
        <a href="umkm.php" class="btn-back-to-umkm">
            <i class="fa-solid fa-arrow-left fa-sm"></i> Kembali ke Direktori
        </a>
    </div>

    <div class="card card-register p-4 p-md-5 shadow-lg">
        
        <div class="text-center mb-4">
            <span class="text-uppercase tracking-widest text-white-50 d-block mb-1" style="font-size: 10px; letter-spacing: 3px;">Formulir Kemitraan</span>
            <h3 class="fw-bold text-white m-0" style="letter-spacing: -0.5px;">Gabung MajuUMKM</h3>
        </div>

        <?php if (!empty($pesan_error)) : ?>
            <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger small p-3 rounded-3 d-flex align-items-center gap-2 mb-4">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?= $pesan_error; ?></div>
            </div>
        <?php endif; ?>

        <form action="daftar-umkm.php" method="POST" enctype="multipart/form-data">
            
            <!-- 1. Nama UMKM -->
            <div class="mb-3">
                <label class="form-label text-white-50 small fw-medium">Nama Usaha / UMKM</label>
                <input type="text" name="nama_umkm" class="form-control form-control-premium" placeholder="Contoh: Keripik Singkong Renyah" required autocomplete="off">
            </div>

            <!-- 2. Nomor WhatsApp (DITAMBAHKAN AGAR WA BISA DIHUBUNGI) -->
            <div class="mb-3">
                <label class="form-label text-white-50 small fw-medium">Nomor WhatsApp Pemilik Usaha</label>
                <input type="tel" name="no_wa" class="form-control form-control-premium" placeholder="Contoh: 08123456789" required autocomplete="off">
            </div>
            
            <!-- 3. Deskripsi Usaha -->
            <div class="mb-3">
                <label class="form-label text-white-50 small fw-medium">Deskripsi Singkat Usaha</label>
                <textarea name="deskripsi" class="form-control form-control-premium" rows="3" placeholder="Jelaskan produk unggulan usaha Anda..."></textarea>
            </div>

            <!-- 4. Logo / Foto UMKM -->
            <div class="mb-4">
                <label class="form-label text-white-50 small fw-medium mb-1">Logo Toko / Banner Usaha <span class="text-white-30" style="font-size: 11px;">(Opsional)</span></label>
                <input type="file" name="logo" class="form-control form-control-premium text-white-50" style="font-size: 13px;" accept="image/*">
            </div>

            <button type="submit" class="btn btn-premium-register w-100 shadow-sm mt-2">
                Daftar Mitra UMKM <i class="fa-solid fa-rocket ms-1 fa-xs"></i>
            </button>
        </form>

    </div>
    
    <div class="text-center mt-4 text-white-30" style="font-size: 11px; opacity: 0.7;">
        &copy; 2026 MajuUMKM Direktori. Pemrosesan Data Terenkripsi Aman.
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>