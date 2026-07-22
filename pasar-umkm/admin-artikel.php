<?php
// 1. Jalankan Session & Proteksi Halaman Admin (Disesuaikan untuk sistem AJAX)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cek apakah user sudah login sebagai admin/pengelola
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    echo "<div class='alert alert-danger border-0 rounded-3 small'>Sesi habis, silakan login kembali.</div>";
    exit;
}

// 2. Hubungkan Koneksi Database
include __DIR__ . '/includes/config/koneksi.php'; 

// 3. Ambil Data Artikel dari Database (Dibuat aman tanpa ORDER BY yang memicu eror kolom)
$query_artikel = "SELECT * FROM tabel_artikel";
$tampil_artikel = mysqli_query($koneksi, $query_artikel);
?>

<style>
    .card-custom {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }
    .table-premium th {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        padding: 14px;
        border-bottom: 2px solid #e2e8f0;
    }
    .table-premium td {
        padding: 14px;
        vertical-align: middle;
        font-size: 14px;
        border-bottom: 1px solid #f1f5f9;
    }
    .btn-action {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .img-preview-thumb {
        width: 60px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }
</style>

<div class="fade-in-up">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0 text-dark">Kelola Artikel & Berita</h4>
            <p class="text-muted small m-0">Tulis, perbarui, dan atur strategi konten edukasi website Anda.</p>
        </div>
        <a href="tambah-artikel.php" class="btn btn-dark rounded-3 px-3 py-2 small fw-medium">
            <i class="fa-solid fa-pen-to-square me-2 fa-sm"></i>Tulis Artikel Baru
        </a>
    </div>

    <?php if (isset($_GET['status'])) : ?>
        <?php if ($_GET['status'] === 'sukses_tambah') : ?>
            <div class="alert alert-success border-0 rounded-3 small py-2">🎉 Artikel baru berhasil diterbitkan!</div>
        <?php elseif ($_GET['status'] === 'sukses_hapus') : ?>
            <div class="alert alert-light border border-danger-subtle text-danger rounded-3 small py-2">🗑️ Artikel telah berhasil dihapus dari sistem.</div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card card-custom overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover table-premium m-0">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Cover</th>
                        <th width="45%">Judul Artikel</th>
                        <th width="20%">Tanggal Rilis</th>
                        <th width="20%" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($tampil_artikel) > 0) :
                        $no = 1;
                        while($artikel = mysqli_fetch_assoc($tampil_artikel)) : 
                    ?>
                    <tr>
                        <td class="text-muted"><?= $no++; ?></td>
                        <td>
                            <img src="assets/img/artikel/<?= $artikel['gambar']; ?>" onerror="this.src='https://placehold.co/120x80?text=No+Image'" class="img-preview-thumb">
                        </td>
                        <td>
                            <span class="fw-semibold text-dark d-block mb-1"><?= htmlspecialchars($artikel['judul']); ?></span>
                            <span class="text-muted d-block" style="font-size: 11px;">URL: <code><?= htmlspecialchars($artikel['slug']); ?></code></span>
                        </td>
                        <td class="text-muted small">
                            <i class="fa-regular fa-calendar me-1"></i> 
                            <?php 
                            // Proteksi Tanggal: Cek ketersediaan kolom secara dinamis agar tidak memicu eror PHP
                            if (isset($artikel['created_at'])) {
                                echo date('d M Y, H:i', strtotime($artikel['created_at'])) . ' WIB';
                            } elseif (isset($artikel['tanggal_rilis'])) {
                                echo date('d M Y, H:i', strtotime($artikel['tanggal_rilis'])) . ' WIB';
                            } else {
                                echo '<span class="text-muted small"><em>-</em></span>';
                            }
                            ?>
                        </td>
                        <td class="text-end">
                            <a href="edit-artikel.php?id=<?= $artikel['id']; ?>" class="btn btn-outline-secondary btn-action me-1" title="Ubah Konten">
                                <i class="fa-solid fa-pencil fa-xs"></i>
                            </a>
                            <a href="hapus-artikel.php?id=<?= $artikel['id']; ?>" class="btn btn-outline-danger btn-action" title="Hapus Konten" onclick="return confirm('Apakah Anda yakin ingin menghapus artikel premium ini? Tindakan ini permanen.');">
                                <i class="fa-solid fa-trash fa-xs"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else : 
                    ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="fa-solid fa-newspaper fa-2x mb-3 text-secondary d-block"></i>
                            <span class="fw-medium text-dark d-block mb-1">Belum Ada Artikel</span>
                            <p class="small text-muted mb-0">Klik tombol di atas untuk mulai memproduksi tulisan pertama Anda.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>