<?php
// 1. Jalankan Session & Proteksi Halaman Admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cek status login
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    echo "<div class='container mt-5'><div class='alert alert-danger border-0 rounded-4 small shadow-sm text-center'>Sesi habis, silakan login kembali.</div></div>";
    exit;
}

// 2. Hubungkan Koneksi Database
include __DIR__ . '/includes/config/koneksi.php'; 

// 3. Proses Validasi & Simpan Form Masuk (POST)
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul  = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
    $slug   = mysqli_real_escape_string($koneksi, trim($_POST['slug']));
    $konten = mysqli_real_escape_string($koneksi, trim($_POST['konten']));
    
    // Penanganan File Gambar/Cover
    $nama_gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $nama_gambar = 'art_' . time() . '_' . uniqid() . '.' . $file_ext;
            $target_dir  = __DIR__ . '/assets/img/artikel/';
            
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            move_uploaded_file($file_tmp, $target_dir . $nama_gambar);
        } else {
            $error_message = 'Format gambar tidak valid. Gunakan format JPG, PNG, atau WEBP.';
        }
    }

    if (empty($error_message)) {
        $query_tambah = "INSERT INTO tabel_artikel (judul, slug, isi, gambar) VALUES ('$judul', '$slug', '$konten', '$nama_gambar')";
        
        if (mysqli_query($koneksi, $query_tambah)) {
            // PERBAIKAN FIXED: Langsung redirect ke dashboard.php utama
            echo "<script>window.location.href = 'dashboard.php?page=artikel&status=sukses_tambah';</script>";
            exit;
        } else {
            $error_message = 'Gagal menyimpan artikel ke database: ' . mysqli_error($koneksi);
        }
    }
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #f8fafc;
        color: #0f172a;
    }
    
    .main-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Header Styling */
    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.5px;
    }

    /* Premium Card Design */
    .card-premium {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -2px rgba(0, 0, 0, 0.02);
        transition: box-shadow 0.3s ease;
    }
    
    .card-premium:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -4px rgba(0, 0, 0, 0.04);
    }

    /* Custom Form Controls */
    .form-label-premium {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control-premium {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 14px;
        color: #0f172a;
        background-color: #ffffff;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-control-premium::placeholder {
        color: #94a3b8;
    }

    .form-control-premium:focus {
        border-color: #0f172a;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.06);
        outline: none;
    }

    /* Fixed Input Group for Slug */
    .slug-addon {
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-right: none;
        border-radius: 10px 0 0 10px;
        color: #64748b;
        font-size: 13px;
        font-weight: 500;
        padding: 0 16px;
    }

    .form-control-slug {
        border-radius: 0 10px 10px 0 !important;
        background-color: #f8fafc;
        cursor: not-allowed;
    }

    /* Interactive Upload Zone */
    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 32px 16px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .upload-zone:hover {
        border-color: #0f172a;
        background: #f1f5f9;
    }

    .preview-image-holder {
        width: 100%;
        border-radius: 8px;
        overflow: hidden;
        display: none;
    }

    .preview-image-holder img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }

    /* Action Buttons */
    .btn-action-back {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        font-weight: 500;
        font-size: 14px;
        padding: 10px 20px;
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .btn-action-back:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    .btn-action-submit {
        background: #0f172a;
        color: #ffffff;
        font-weight: 500;
        font-size: 14px;
        padding: 12px 24px;
        border-radius: 10px;
        border: none;
        transition: all 0.2s ease;
        box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.15);
    }

    .btn-action-submit:hover {
        background: #1e293b;
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.2);
    }

    .btn-action-submit:active {
        transform: translateY(0);
    }
</style>

<div class="container py-5 main-container">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-5">
        <div>
            <h1 class="page-title m-0">Tulis Artikel Baru</h1>
            <p class="text-muted small m-0 mt-1">Kelola narasi ide, update bisnis, dan informasi edukasi UMKM Anda.</p>
        </div>
        <a href="dashboard.php?page=artikel" class="btn btn-action-back d-inline-flex align-items-center">
            <i class="fa-solid fa-arrow-left me-2 fa-sm"></i> Kembali
        </a>
    </div>

    <?php if (!empty($error_message)) : ?>
        <div class="alert alert-danger border-0 rounded-4 small py-3 px-4 shadow-sm mb-4 d-flex align-items-center">
            <i class="fa-solid fa-circle-exclamation me-3 fa-lg"></i>
            <div><?= $error_message; ?></div>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="row g-4">
            
            <div class="col-lg-8">
                <div class="card card-premium p-4 p-md-5">
                    
                    <div class="mb-4">
                        <label for="judul" class="form-label-premium">Judul Artikel</label>
                        <input type="text" class="form-control form-control-premium w-100" id="judul" name="judul" placeholder="Tuliskan judul artikel yang memikat..." required autocomplete="off">
                    </div>
                    
                    <div class="mb-4">
                        <label for="slug" class="form-label-premium">Struktur Permalink / URL</label>
                        <div class="input-group">
                            <span class="input-group-text slug-addon">/artikel/</span>
                            <input type="text" class="form-control form-control-premium form-control-slug" id="slug" name="slug" placeholder="slug-terbuat-otomatis" required readonly>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="konten" class="form-label-premium">Konten Lengkap</label>
                        <textarea class="form-control form-control-premium" id="konten" name="konten" rows="12" placeholder="Mulai ketikkan isi konten tulisan Anda di sini..." required style="resize: vertical; min-height: 200px;"></textarea>
                    </div>
                    
                </div>
            </div>

            <div class="col-lg-4">
                <div class="d-flex flex-column gap-4">
                    
                    <div class="card card-premium p-4">
                        <label class="form-label-premium mb-3">Gambar Sampul</label>
                        
                        <div class="upload-zone mb-3" onclick="document.getElementById('gambar').click();">
                            <div id="upload-placeholder">
                                <div class="icon-wrap mb-2 text-muted">
                                    <i class="fa-regular fa-images fa-2x"></i>
                                </div>
                                <p class="m-0 text-dark fw-medium small">Pilih File Gambar</p>
                                <p class="m-0 text-muted extra-small" style="font-size: 11px;">Klik untuk telusuri penyimpanan</p>
                            </div>
                            <div id="image-preview-holder" class="preview-image-holder">
                                <img id="image-view" alt="Preview Cover">
                            </div>
                        </div>

                        <input type="file" class="d-none" id="gambar" name="gambar" accept="image/*" required>
                        
                        <div class="text-muted" style="font-size: 11px; line-height: 1.5;">
                            <i class="fa-solid fa-circle-info me-1"></i> Gunakan aspek rasio ideal 3:2 (contoh: 1200x800px) maks. file 2MB berformat JPG, PNG, atau WEBP.
                        </div>
                    </div>

                    <div class="card card-premium p-4">
                        <label class="form-label-premium mb-2">Aksi Penerbitan</label>
                        <p class="text-muted small mb-4" style="line-height: 1.4;">Pastikan data artikel dan file cover sudah sesuai sebelum diterbitkan ke publik.</p>
                        
                        <button type="submit" class="btn btn-action-submit w-100 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-paper-plane me-2 fa-sm"></i> Terbitkan Artikel
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </form>
</div>

<script>
    // 1. Auto-generate Slug yang Bersih & SEO-Friendly
    document.getElementById('judul').addEventListener('input', function() {
        let title = this.value;
        let slug = title.toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '') // Hapus karakter spesial
                        .replace(/\s+/g, '-')         // Ganti spasi dengan tanda strip
                        .replace(/-+/g, '-');         // Atasi strip ganda berkelanjutan
        document.getElementById('slug').value = slug;
    });

    // 2. Premium Image Live Preview Handler
    document.getElementById('gambar').addEventListener('change', function() {
        const file = this.files[0];
        const placeholder = document.getElementById('upload-placeholder');
        const previewHolder = document.getElementById('image-preview-holder');
        const imageView = document.getElementById('image-view');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imageView.setAttribute('src', e.target.result);
                placeholder.style.display = 'none';
                previewHolder.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            placeholder.style.display = 'block';
            previewHolder.style.display = 'none';
            imageView.setAttribute('src', '');
        }
    });
</script>