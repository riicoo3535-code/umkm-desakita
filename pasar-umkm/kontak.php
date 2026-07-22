<?php 
// 1. Hubungkan koneksi database & header template (JALUR SUDAH DISESUAIKAN)
include __DIR__ . '/includes/config/koneksi.php'; 
include __DIR__ . '/includes/header.php'; 

// ... sisa kode di bawahnya tetap sama ... 

// 2. Inisialisasi Status Notifikasi
$pesan_sukses = false;
$pesan_error  = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Proteksi awal: Pastikan variabel koneksi database tersedia
    if (!isset($koneksi) || !$koneksi) {
        die("Eror Sistem: Variabel \$koneksi tidak ditemukan. Pastikan file koneksi.php sudah di-include.");
    }

    // 1. Ambil data dengan trim & isset (Mencegah eror 'Undefined index' di PHP 8)
    $nama    = isset($_POST['nama']) ? mysqli_real_escape_string($koneksi, trim($_POST['nama'])) : "";
    $email   = isset($_POST['email']) ? mysqli_real_escape_string($koneksi, trim($_POST['email'])) : "";
    $subjek  = isset($_POST['subjek']) ? mysqli_real_escape_string($koneksi, trim($_POST['subjek'])) : "";
    $pesan   = isset($_POST['pesan']) ? mysqli_real_escape_string($koneksi, trim($_POST['pesan'])) : "";

    // 2. Logika Validasi Input
    if (empty($nama) || empty($email) || empty($subjek) || empty($pesan)) {
        $pesan_error = "Semua kolom wajib diisi, tidak boleh ada yang kosong.";
    } 
    // 3. Validasi Keaslian Format Email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = "Format alamat email yang Anda masukkan tidak valid.";
    } 
    // 4. Jika semua validasi lolos, lakukan proses simpan ke database
    else {
        // Menyimpan data pesan ke tabel_pesan
        $query_simpan = "INSERT INTO tabel_pesan (nama, email, subjek, pesan, tanggal) 
                         VALUES ('$nama', '$email', '$subjek', '$pesan', NOW())";
                         
        $kirim_simpan = mysqli_query($koneksi, $query_simpan);

        if ($kirim_simpan) {
            $pesan_sukses = true;
        } else {
            $pesan_error = "Gagal menyimpan ke database: " . mysqli_error($koneksi);
        }
    }
}
?>

<section class="py-5 text-white text-center" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
    <div class="container py-3">
        <h1 class="fw-bold mb-2">Hubungi Kami</h1>
        <p class="text-white-50 max-w-2xl mx-auto">Punya pertanyaan seputar kemitraan UMKM atau butuh bantuan teknis? Tim kami siap melayani Anda.</p>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="row g-5">
            
            <div class="col-lg-5">
                <h3 class="fw-bold text-dark mb-4">Informasi Saluran Resmi</h3>
                <p class="text-muted mb-4">Silakan hubungi kami melalui salah satu saluran di bawah ini atau kunjungi kantor operasional kami pada jam kerja (Senin - Jumat, 08.00 - 16.00 WIB).</p>
                
                <div class="d-flex align-items-start p-3 border rounded-3 mb-3 bg-light">
                    <div class="text-success me-3 mt-1">
                        <i class="fa-brands fa-whatsapp fa-xl"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">WhatsApp Layanan Pelanggan</h6>
                        <p class="text-muted small mb-2">Respon cepat untuk panduan pendaftaran mitra baru.</p>
                        <a href="https://wa.me/6281234567890" target="_blank" class="text-decoration-none text-success fw-semibold small">Hubungi via WA &rarr;</a>
                    </div>
                </div>

                <div class="d-flex align-items-start p-3 border rounded-3 mb-3 bg-light">
                    <div class="text-primary me-3 mt-1">
                        <i class="fa-regular fa-envelope fa-xl"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">E-mail Korespondensi</h6>
                        <p class="text-muted small mb-1">Untuk keperluan proposal kerja sama formal dan administrasi.</p>
                        <span class="text-dark fw-semibold small">support@majuumkm.local</span>
                    </div>
                </div>

                <div class="d-flex align-items-start p-3 border rounded-3 bg-light">
                    <div class="text-danger me-3 mt-1">
                        <i class="fa-solid fa-location-dot fa-xl"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Kantor Pusat Layanan</h6>
                        <p class="text-muted small mb-0">Jl. Raya Kampus No. 10, Kompleks Pusat Bisnis Kreatif, Indonesia.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="p-4 p-md-5 border rounded-4 bg-light shadow-sm">
                    <h3 class="fw-bold text-dark mb-2">Kirim Pesan Langsung</h3>
                    <p class="text-muted small mb-4">Gunakan formulir di bawah ini untuk mengirimkan saran, kritik, atau pertanyaan spesifik kepada tim pengelola website.</p>
                    
                    <?php if ($pesan_sukses) : ?>
                        <div class="alert alert-dark border-0 rounded-3 p-3 mb-4 d-flex align-items-center gap-3" role="alert">
                            <i class="fa-solid fa-circle-check text-success fa-lg"></i>
                            <div>
                                <strong class="d-block text-white">Pesan Anda Berhasil Terkirim!</strong>
                                <span class="text-white-50 small">Terima kasih, tim kurator kami akan segera meninjau pesan Anda.</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($pesan_error)) : ?>
                        <div class="alert alert-danger bg-danger bg-opacity-10 border-0 rounded-3 p-3 mb-4 d-flex align-items-center gap-3" role="alert">
                            <i class="fa-solid fa-circle-exclamation text-danger fa-lg"></i>
                            <div>
                                <strong class="d-block text-dark">Gagal Mengirim Pesan</strong>
                                <span class="text-muted small"><?= $pesan_error; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="kontak.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control shadow-none py-2 px-3 bg-white" placeholder="Masukkan nama Anda" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Alamat E-mail</label>
                                <input type="email" name="email" class="form-control shadow-none py-2 px-3 bg-white" placeholder="nama@email.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-secondary">Subjek Pertanyaan</label>
                                <input type="text" name="subjek" class="form-control shadow-none py-2 px-3 bg-white" placeholder="Contoh: Pendaftaran Mitra Baru" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-secondary">Isi Pesan Detail</label>
                                <textarea name="pesan" rows="5" class="form-control shadow-none py-2 px-3 bg-white" placeholder="Tuliskan pesan atau pertanyaan Anda secara rinci di sini..." required></textarea>
                            </div>
                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-dark w-100 py-2.5 fw-semibold rounded-3">
                                    <i class="fa-regular fa-paper-plane me-2"></i> Kirim Pesan Sekarang
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>