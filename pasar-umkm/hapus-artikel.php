<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi Admin
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    exit("Akses ditolak.");
}

include __DIR__ . '/includes/config/koneksi.php';

$id_artikel = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_artikel > 0) {
    // 1. Ambil nama file gambar terlebih dahulu untuk dihapus dari folder penyimpanan
    $query_gambar = "SELECT gambar FROM tabel_artikel WHERE id = $id_artikel";
    $hasil_gambar = mysqli_query($koneksi, $query_gambar);
    $data_artikel = mysqli_fetch_assoc($hasil_gambar);
    
    if ($data_artikel) {
        $nama_file = $data_artikel['gambar'];
        $path_file = __DIR__ . '/assets/img/artikel/' . $nama_file;
        
        // Hapus file fisik jika filenya memang ada di server
        if (!empty($nama_file) && file_exists($path_file)) {
            unlink($path_file);
        }
    }
    
    // 2. Hapus data row dari tabel database
    $query_hapus = "DELETE FROM tabel_artikel WHERE id = $id_artikel";
    if (mysqli_query($koneksi, $query_hapus)) {
        // PERBAIKAN: Jika sukses hapus, tetap di sidebar menu artikel
        echo "<script>window.location.href = 'dashboard.php?page=artikel&status=sukses_hapus';</script>";
        exit;
    } else {
        // PERBAIKAN: Jika query gagal, tetap kunci di menu artikel
        echo "<script>alert('Gagal menghapus data dari database.'); window.location.href = 'dashboard.php?page=artikel';</script>";
        exit;
    }
} else {
    // PERBAIKAN: Jika ID tidak valid/kosong, pastikan tidak dilempar ke dashboard utama, tetap di menu artikel
    echo "<script>window.location.href = 'dashboard.php?page=artikel';</script>";
    exit;
}
?>