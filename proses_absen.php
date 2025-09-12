<?php
// Set header untuk memberitahu browser bahwa response-nya adalah JSON
header('Content-Type: application/json');

// Panggil file koneksi database
require_once 'config/database.php';

// Fungsi untuk mengirim response JSON dan menghentikan skrip
function send_response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

// Cek apakah ada data qr_secret yang dikirim
if (!isset($_POST['qr_secret'])) {
    send_response('error', 'Data QR Code tidak ditemukan.');
}

$qr_secret = $_POST['qr_secret'];

try {
    // 1. Cari pegawai berdasarkan qr_secret
    $stmt = $pdo->prepare("SELECT id_pegawai, nama_pegawai FROM pegawai WHERE qr_secret = ?");
    $stmt->execute([$qr_secret]);
    $pegawai = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pegawai) {
        send_response('error', 'QR Code tidak valid atau pegawai tidak terdaftar.');
    }

    $id_pegawai = $pegawai['id_pegawai'];
    $nama_pegawai = $pegawai['nama_pegawai'];
    $tanggal_hari_ini = date('Y-m-d');

    // 2. Cek apakah pegawai sudah absen masuk hari ini
    $stmt = $pdo->prepare("SELECT * FROM absensi WHERE id_pegawai = ? AND tanggal = ?");
    $stmt->execute([$id_pegawai, $tanggal_hari_ini]);
    $absensi_hari_ini = $stmt->fetch(PDO::FETCH_ASSOC);

    $waktu_sekarang = date('Y-m-d H:i:s');

    if (!$absensi_hari_ini) {
        // BELUM ABSEN MASUK: Lakukan absensi masuk
        $stmt_insert = $pdo->prepare(
            "INSERT INTO absensi (id_pegawai, tanggal, waktu_masuk, status) VALUES (?, ?, ?, ?)"
        );
        $stmt_insert->execute([$id_pegawai, $tanggal_hari_ini, $waktu_sekarang, 'Hadir']);
        send_response('success', "Selamat Datang, $nama_pegawai! Absen masuk berhasil dicatat.");
    
    } else if (empty($absensi_hari_ini['waktu_pulang'])) {
        // SUDAH ABSEN MASUK TAPI BELUM PULANG: Lakukan absensi pulang
        $stmt_update = $pdo->prepare(
            "UPDATE absensi SET waktu_pulang = ? WHERE id_absensi = ?"
        );
        $stmt_update->execute([$waktu_sekarang, $absensi_hari_ini['id_absensi']]);
        send_response('success', "Terima Kasih, $nama_pegawai! Absen pulang berhasil dicatat.");

    } else {
        // SUDAH ABSEN MASUK DAN PULANG
        send_response('error', "Anda sudah melakukan absensi masuk dan pulang hari ini.");
    }

} catch (PDOException $e) {
    // Tangani error database
    send_response('error', 'Terjadi masalah pada database: ' . $e->getMessage());
}
?>