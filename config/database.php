<?php
// Detail koneksi database
$host     = 'localhost';    // Biasanya 'localhost'
$dbname   = 'db_absensi';   // Sesuaikan dengan nama database yang kamu buat
$user     = 'root';         // User default XAMPP
$password = '';             // Password default XAMPP (kosong)

// Membuat koneksi menggunakan PDO (PHP Data Objects)
try {
    // Buat objek PDO baru
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    
    // Set mode error PDO ke exception agar lebih mudah debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // (Opsional) Baris ini bisa kamu hapus nanti, hanya untuk tes
    // echo "Koneksi ke database berhasil!";

} catch (PDOException $e) {
    // Jika koneksi gagal, hentikan skrip dan tampilkan pesan error
    die("Koneksi ke database gagal: " . $e->getMessage());
}
?>