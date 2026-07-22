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

// 3. Ambil ID Artikel yang akan diedit
$id_artikel = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_artikel <= 0) {
    echo "<script>window.location.href = 'dashboard.php?page=artikel';</script>";
    exit;
}

// Fetch data artikel lama
$query_ambil = "SELECT * FROM tabel_artikel WHERE id = $id_artikel";
$hasil_ambil = mysqli_query($koneksi, $query_ambil);
$artikel     = mysqli_fetch_assoc($hasil_ambil);

if (!$artikel) {
    echo "<script>alert('Artikel tidak ditemukan!'); window.location.href = 'dashboard.php?page=artikel';</script>";
    exit;
}

// 4. Proses Update Data (POST)
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul  = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
    $slug   = mysqli_real_escape_string($koneksi, trim($_POST['slug']));
    $konten = mysqli_real_escape_string($koneksi, trim($_POST['konten']));
    
    $nama_gambar = $artikel['gambar']; // Default pakai gambar lama
    
    // Jika user mengunggah gambar baru
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            // Hapus gambar lama dari server jika ada
            $target_dir  = __DIR__ . '/assets/img/artikel/';
            if (!empty($artikel['gambar']) && file_exists($target_dir . $artikel['gambar'])) {
                unlink($target_dir . $artikel['gambar']);
            }
            
            // Generate nama gambar baru
            $nama_gambar = 'art_' . time() . '_' . uniqid() . '.' . $file_ext;
            move_uploaded_file($file_tmp, $target_dir . $nama_gambar);
        } else {
            $error_message = 'Format gambar tidak valid. Gunakan format JPG, PNG, atau WEBP.';
        }
    }

    if (empty($error_message)) {
        $query_update = "UPDATE tabel_artikel SET judul = '$judul', slug = '$slug', isi = '$konten', gambar = '$nama_gambar' WHERE id = $id_artikel";
        
        if (mysqli_query($koneksi, $query_update)) {
            // Redirect kembali ke list artikel dashboard
            echo "<script>window.location.href = 'dashboard.php?page=artikel&status=sukses_edit';</script>";
            exit;
        } else {
            $error_message = 'Gagal memperbarui artikel: ' . mysqli_error($koneksi);
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
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #0f172a; }
    .main-container { max-width: 1200px; margin: 0 auto; }
    .page-title { font-size: 24px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px; }
    .card-premium { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); }
    .form-label-premium { font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-control-premium { border: 1px solid #cbd5e1; border-radius: 10px; padding: 12px 16px; font-size: 14px; color: #0f172a; }
    .form-control-premium:focus { border-color: #0f172a; box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.06); outline: none; }
    .slug-addon { background-color: #f1f5f9; border: 1px solid #cbd5e1; border-right: none; border-radius: 10px 0 0 10px; color: #64748b; font-size: 13px; }
    .form-control-slug { border-radius: 0 10px 10px 0 !important; background-color: #f8fafc; cursor: not-allowed; }
    .upload-zone { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 24px 16px; text-align: center; background: #f8fafc; cursor: pointer; }
    .preview-image-holder { width: 100%; border-radius: 8px; overflow: hidden; }
    .preview-image-holder img { width: 100%; height: 180px; object-fit: cover; }
    .btn-action-back { border: 1px solid #e2e8f0; background: #ffffff; color: #475569; font-weight: 500; padding: 10px 20px; border-radius: 10px; }
    .btn-action-submit { background: #0f172a; color: #ffffff; font-weight: 500; padding: 12px 24px; border-radius: 10px; border: none; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.15); }
    .btn-action-submit:hover { background: #1e293b; transform: translateY(-1px); }
</style>

<div class="container py-5 main-container">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-5">
        <div>
            <h1 class="page-title m-0">Perbarui Artikel</h1>
            <p class="text-muted small m-0 mt-1">Lakukan perubahan isi konten atau perbarui visual cover artikel Anda.</p>
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
                        <input type="text" class="form-control form-control-premium w-100" id="judul" name="judul" value="<?= htmlspecialchars($artikel['judul']); ?>" required autocomplete="off">
                    </div>
                    
                    <div class="mb-4">
                        <label for="slug" class="form-label-premium">Struktur Permalink / URL</label>
                        <div class="input-group">
                            <span class="input-group-text slug-addon">/artikel/</span>
                            <input type="text" class="form-control form-control-premium form-control-slug" id="slug" name="slug" value="<?= htmlspecialchars($artikel['slug']); ?>" required readonly>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="konten" class="form-label-premium">Konten Lengkap</label>
                        <textarea class="form-control form-control-premium" id="konten" name="konten" rows="12" required style="resize: vertical; min-height: 200px;"><?= htmlspecialchars($artikel['isi']); ?></textarea>
                    </div>
                    
                </div>
            </div>

            <div class="col-lg-4">
                <div class="d-flex flex-column gap-4">
                    
                    <div class="card card-premium p-4">
                        <label class="form-label-premium mb-3">Gambar Sampul</label>
                        
                        <div class="upload-zone mb-3" onclick="document.getElementById('gambar').click();">
                            <!-- Placeholder sembunyi jika sudah ada gambar bawaan database -->
                            <div id="upload-placeholder" style="display: <?= !empty($artikel['gambar']) ? 'none' : 'block'; ?>;">
                                <div class="icon-wrap mb-2 text-muted"><i class="fa-regular fa-images fa-2x"></i></div>
                                <p class="m-0 text-dark fw-medium small">Ganti File Gambar</p>
                            </div>
                            
                            <!-- Menampilkan pratinjau file gambar bawaan database -->
                            <div id="image-preview-holder" class="preview-image-holder" style="display: <?= !empty($artikel['gambar']) ? 'block' : 'none'; ?>;">
                                <img id="image-view" src="assets/img/artikel/<?= $artikel['gambar']; ?>" onerror="this.src='https://placehold.co/400x250?text=No+Image'" alt="Preview Cover">
                            </div>
                        </div>

                        <input type="file" class="d-none" id="gambar" name="gambar" accept="image/*">
                        
                        <div class="text-muted" style="font-size: 11px; line-height: 1.5;">
                            <i class="fa-solid fa-circle-info me-1"></i> Biarkan kosong jika tidak ingin mengubah gambar utama saat ini.
                        </div>
                    </div>

                    <div class="card card-premium p-4">
                        <label class="form-label-premium mb-2">Simpan Perubahan</label>
                        <p class="text-muted small mb-4" style="line-height: 1.4;">Perubahan akan langsung diperbarui ke sistem beranda website publik saat ini juga.</p>
                        
                        <button type="submit" class="btn btn-action-submit w-100 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-floppy-disk me-2 fa-sm"></i> Simpan Perubahan
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </form>
</div>

<script>
    // Auto-generate Slug Baru jika Judul diedit kembali
    document.getElementById('judul').addEventListener('input', function() {
        let title = this.value;
        let slug = title.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
        document.getElementById('slug').value = slug;
    });

    // Handle Live Preview pergantian gambar baru
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
        }
    });
</script>