<?php
// 1. Jalankan Session & Proteksi Login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// 2. Hubungkan Database
include __DIR__ . '/includes/config/koneksi.php'; 

if (!isset($koneksi) || !$koneksi) {
    die("Eror Sistem: Koneksi database gagal dimuat.");
}

// 3. DETEKSI OTOMATIS KOLOM RELASI UMKM
$kolom_relasi = "id_umkm"; 
$cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_produk");
while ($k = mysqli_fetch_assoc($cek_kolom)) {
    if ($k['Field'] === 'umkm_id') {
        $kolom_relasi = "umkm_id";
        break;
    }
}

// 4. LOGIKA KETIKA TOMBOL SIMPAN DIKLIK
$notif = "";
if (isset($_POST['btn_simpan'])) {
    $nama_produk     = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $nama_umkm       = trim(mysqli_real_escape_string($koneksi, $_POST['nama_umkm']));
    $no_wa           = trim(mysqli_real_escape_string($koneksi, $_POST['no_wa'])); 
    $nama_kategori   = trim(mysqli_real_escape_string($koneksi, $_POST['kategori_wilayah']));
    $harga           = (int)$_POST['harga'];
    $stok            = (int)$_POST['stok'];
    
    $id_umkm = 0;
    $kategori_id = 0;

    // Validasi Isian Kosong
    if (empty($nama_produk)) {
        $notif = "produk_kosong";
    } elseif (empty($nama_umkm)) {
        $notif = "umkm_kosong";
    } elseif (empty($no_wa)) {
        $notif = "wa_kosong";
    } elseif (empty($nama_kategori)) {
        $notif = "kategori_kosong";
    }

    // FORMATTING OTOMATIS NOMOR WA (Mengubah 08/ +628 menjadi 628)
    if ($notif === "") {
        $no_wa = str_replace([' ', '-', '+'], '', $no_wa); // Hilangkan spasi, strip, dan tanda plus
        if (str_starts_with($no_wa, '0')) {
            $no_wa = '62' . substr($no_wa, 1);
        }
    }

    // HANDLER OTOMATISASI NAMA UMKM KE ID
    if ($notif === "") {
        $cek_umkm = mysqli_query($koneksi, "SELECT id FROM tabel_umkm WHERE LOWER(nama_umkm) = LOWER('$nama_umkm') LIMIT 1");
        if (mysqli_num_rows($cek_umkm) > 0) {
            $row_umkm = mysqli_fetch_assoc($cek_umkm);
            $id_umkm = (int)$row_umkm['id'];
            
            // Update nomor WA jika UMKM sudah ada agar tetap sinkron yang terbaru
            mysqli_query($koneksi, "UPDATE tabel_umkm SET no_wa = '$no_wa' WHERE id = $id_umkm");
        } else {
            // Jika nama UMKM belum terdaftar, buat baru + masukkan nomor WA-nya sekaligus
            $insert_umkm = mysqli_query($koneksi, "INSERT INTO tabel_umkm (nama_umkm, no_wa) VALUES ('$nama_umkm', '$no_wa')");
            if ($insert_umkm) {
                $id_umkm = (int)mysqli_insert_id($koneksi);
            } else {
                $notif = "gagal_umkm_otomatis";
            }
        }
    }

    // BAGIAN YANG DIKEMBALIKAN: HANDLER OTOMATISASI KATEGORI TEKS KE ID
    if ($notif === "") {
        $cek_kat = mysqli_query($koneksi, "SELECT id FROM tabel_kategori WHERE LOWER(nama_kategori) = LOWER('$nama_kategori') LIMIT 1");
        if (mysqli_num_rows($cek_kat) > 0) {
            $row_kat = mysqli_fetch_assoc($cek_kat);
            $kategori_id = (int)$row_kat['id'];
        } else {
            // Jika kategori belum ada di database, buat baru otomatis
            $insert_kat = mysqli_query($koneksi, "INSERT INTO tabel_kategori (nama_kategori) VALUES ('$nama_kategori')");
            if ($insert_kat) {
                $kategori_id = (int)mysqli_insert_id($koneksi);
            } else {
                $notif = "gagal_kategori_otomatis";
            }
        }
    }

    // Foto default jika user tidak mengunggah gambar baru
    $nama_foto_final = "default-produk.png";

    // Validasi & Proses Upload Foto Baru (Jika ada yang diunggah)
    if ($notif === "" && isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $nama_file = $_FILES['foto']['name'];
        $ukuran    = $_FILES['foto']['size'];
        $tmp_name  = $_FILES['foto']['tmp_name'];
        
        $ekstensi_diperbolehkan = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi_file = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        if (in_array($ekstensi_file, $ekstensi_diperbolehkan)) {
            if ($ukuran <= 2000000) { 
                $dir_target = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'produk';
                if (!file_exists($dir_target)) {
                    mkdir($dir_target, 0777, true);
                }

                $nama_foto_final = "prod-" . time() . "." . $ekstensi_file;
                $target_path = $dir_target . DIRECTORY_SEPARATOR . $nama_foto_final;
                
                move_uploaded_file($tmp_name, $target_path);
            } else {
                $notif = "ukuran_terlalu_besar";
            }
        } else {
            $notif = "ekstensi_salah";
        }
    }

    // Jalankan eksekusi query insert data produk baru
    if ($notif === "") {
        $query_tambah = "INSERT INTO tabel_produk (nama_produk, {$kolom_relasi}, kategori_id, harga, stok, foto) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_tambah = mysqli_prepare($koneksi, $query_tambah);
        if ($stmt_tambah) {
            mysqli_stmt_bind_param($stmt_tambah, "siiiis", $nama_produk, $id_umkm, $kategori_id, $harga, $stok, $nama_foto_final);
            if (mysqli_stmt_execute($stmt_tambah)) {
                echo "<script>window.location.href = 'dashboard.php?page=produk&status=sukses_tambah';</script>";
                exit;
            } else {
                $notif = "gagal_tambah";
            }
            mysqli_stmt_close($stmt_tambah);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Produk — MajuUMKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
        .card-custom { border: 1px solid #e2e8f0; border-radius: 16px; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .form-control { border-radius: 8px; padding: 10px 14px; border: 1px solid #cbd5e1; font-size: 14px; }
        .form-control:focus { border-color: #0f172a; box-shadow: none; }
        .preview-box { width: 100px; height: 100px; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f8fafc; }
    </style>
</head>
<body class="py-5">

<div class="container" style="max-width: 700px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="dashboard.php?page=produk" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-medium" style="border-radius: 8px;">
            <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Produk
        </a>
        <span class="text-muted small font-monospace">Formulir Produk Baru</span>
    </div>

    <?php if ($notif === 'umkm_kosong') : ?>
        <div class="alert alert-warning border-0 shadow-sm p-3 rounded-3 mb-4 small d-flex align-items-center gap-2">
            <i class="fa-solid fa-store fa-lg"></i> Gagal: Nama Pemilik UMKM tidak boleh kosong.
        </div>
    <?php elseif ($notif === 'wa_kosong') : ?>
        <div class="alert alert-warning border-0 shadow-sm p-3 rounded-3 mb-4 small d-flex align-items-center gap-2">
            <i class="fa-brands fa-whatsapp fa-lg"></i> Gagal: Nomor WhatsApp Pemilik wajib diisi.
        </div>
    <?php elseif ($notif === 'kategori_kosong') : ?>
        <div class="alert alert-warning border-0 shadow-sm p-3 rounded-3 mb-4 small d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation fa-lg"></i> Gagal: Isian Kategori Wilayah tidak boleh kosong.
        </div>
    <?php elseif ($notif !== '') : ?>
        <div class="alert alert-danger border-0 shadow-sm p-3 rounded-3 mb-4 small d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation fa-lg"></i> 
            <span>
                <?php 
                    if($notif === 'ukuran_terlalu_besar') echo "Gagal: Ukuran file gambar melebihi batas maksimal 2MB.";
                    elseif($notif === 'ekstensi_salah') echo "Gagal: Format gambar tidak valid. Gunakan ekstensi JPG, PNG, atau WEBP.";
                    else echo "Gagal memproses data atau menyimpan ke sistem.";
                ?>
            </span>
        </div>
    <?php endif; ?>

    <div class="card card-custom p-4 p-md-5">
        <div class="mb-4">
            <h4 class="fw-bold text-dark m-0">Tambah Informasi Produk</h4>
            <p class="text-muted small m-0">Masukkan detail produk komoditas baru beserta cakupan wilayah sektornya.</p>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Nama Produk Komoditas</label>
                <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Kopi Bubuk Arabika" required autocomplete="off">
            </div>

            <div class="row">
                <div class="col-sm-6 mb-3">
                    <label class="form-label small fw-semibold text-secondary">Nama UMKM Pemilik</label>
                    <input type="text" name="nama_umkm" class="form-control" placeholder="Contoh: UD Sumber Sari" required autocomplete="off">
                </div>

                <div class="col-sm-6 mb-3">
                    <label class="form-label small fw-semibold text-secondary">No. WhatsApp Pemilik</label>
                    <input type="text" name="no_wa" class="form-control" placeholder="Contoh: 08123456789" required autocomplete="off">
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 mb-3">
                    <label class="form-label small fw-semibold text-secondary">Kategori Wilayah / Sektor</label>
                    <input type="text" name="kategori_wilayah" class="form-control" placeholder="Contoh: Situbondo, Panji" required autocomplete="off">
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 mb-3">
                    <label class="form-label small fw-semibold text-secondary">Harga Satuan (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted small">Rp</span>
                        <input type="number" name="harga" class="form-control border-start-0" placeholder="0" required min="0">
                    </div>
                </div>
                
                <div class="col-sm-6 mb-3">
                    <label class="form-label small fw-semibold text-secondary">Stok Produk</label>
                    <input type="number" name="stok" class="form-control" placeholder="0" required min="0">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Foto Produk Ilustrasi</label>
                <div class="d-flex align-items-center gap-3">
                    <div class="preview-box">
                        <img id="img-preview" src="assets/img/produk/default-produk.png" alt="Preview" class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="flex-grow-1">
                        <input type="file" name="foto" id="input-foto" class="form-control" accept="image/*">
                        <span class="text-muted d-block mt-1" style="font-size: 11px;">Maksimal file gambar berukuran 2MB.</span>
                    </div>
                </div>
            </div>

            <hr class="text-secondary opacity-10 my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="dashboard.php?page=produk" class="btn btn-light px-4 py-2 small" style="border-radius: 8px; font-size: 14px;">Batal</a>
                <button type="submit" name="btn_simpan" class="btn btn-dark px-4 py-2 fw-medium text-white" style="border-radius: 8px; font-size: 14px;">
                    <i class="fa-solid fa-circle-check me-2 fa-sm"></i> Tambah Produk
                </button>
            </div>

        </form>
    </div>

</div>

<script>
const inputFoto = document.getElementById('input-foto');
const imgPreview = document.getElementById('img-preview');

inputFoto.onchange = evt => {
    const [file] = inputFoto.files;
    if (file) {
        imgPreview.src = URL.createObjectURL(file);
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>