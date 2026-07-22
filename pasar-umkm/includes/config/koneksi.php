<?php
// 1. Pengaturan Konfigurasi Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_pasar_umkm";

// 2. Membuat Koneksi ke Server MySQL
$koneksi = mysqli_connect($host, $user, $pass, $db);

// 3. Validasi Status Koneksi (Jika gagal, hentikan sistem dan tampilkan eror)
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// 4. Proteksi Karakter Teks (Memastikan data teks terbaca dengan sempurna)
mysqli_set_charset($koneksi, "utf8mb4");
?>