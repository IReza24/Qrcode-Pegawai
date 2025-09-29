<?php
// Set header untuk memberitahu browser bahwa response-nya adalah JSON
header('Content-Type: application/json');

// Panggil file koneksi database
require_once 'config/database.php';

// PENTING: SET ZONA WAKTU LOKAL (WIB)
date_default_timezone_set('Asia/Jakarta');

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

// -----------------------------------------------------------------
// AMBIL DATA WAKTU & TENTUKAN BATASAN JAM (VERSI BARU)
// -----------------------------------------------------------------
$tanggal_hari_ini    = date('Y-m-d');
$waktu_sekarang_full  = date('Y-m-d H:i:s');
$waktu_sekarang_h_i   = date('H:i'); // <-- DIGANTI: Cek jam dan menit, misal "08:31"

// Batas jam 9 pagi (lewat dari 08:30 dihitung terlambat)
$batas_waktu_masuk    = '08:30';  // <-- DIGANTI
// Batas jam 4 sore (sebelum 18:30 tidak bisa pulang)
$batas_waktu_pulang   = '18:30';  // <-- DIGANTI
// -----------------------------------------------------------------


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

    // 2. Cek apakah pegawai sudah absen masuk hari ini
    $stmt = $pdo->prepare("SELECT * FROM absensi WHERE id_pegawai = ? AND tanggal = ?");
    $stmt->execute([$id_pegawai, $tanggal_hari_ini]);
    $absensi_hari_ini = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$absensi_hari_ini) {
        // ----------------------------------------------------------
        // --- LOGIKA ABSEN MASUK (JADWAL BARU) ---
        // ----------------------------------------------------------
        $status_kehadiran = '';
        $pesan_sukses = '';

        // Cek apakah sudah lewat batas jam masuk
        // "08:31" > "08:30" = true (Terlambat)
        // "08:30" > "08:30" = false (Hadir)
        if ($waktu_sekarang_h_i > $batas_waktu_masuk) { // <-- DIGANTI
            // Jika lewat dari 08:30, statusnya "Terlambat"
            $status_kehadiran = 'Terlambat';
            $pesan_sukses = "Halo, $nama_pegawai! Anda terlambat, namun absen masuk berhasil dicatat.";
        } else {
            // Jika 08:30 atau sebelumnya, statusnya "Hadir"
            $status_kehadiran = 'Hadir';
            $pesan_sukses = "Selamat Datang, $nama_pegawai! Absen masuk berhasil dicatat.";
        }
        
        // Masukkan data ke database
        $stmt_insert = $pdo->prepare(
            "INSERT INTO absensi (id_pegawai, tanggal, waktu_masuk, status) VALUES (?, ?, ?, ?)"
        );
        $stmt_insert->execute([$id_pegawai, $tanggal_hari_ini, $waktu_sekarang_full, $status_kehadiran]);
        
        send_response('success', $pesan_sukses);
    
    } else if (empty($absensi_hari_ini['waktu_pulang'])) {
        // ----------------------------------------------------------
        // --- LOGIKA ABSEN PULANG (JADWAL BARU) ---
        // ----------------------------------------------------------
        
        // Cek apakah sudah waktunya pulang (18:30 atau lebih)
        // "18:29" < "18:30" = true (Ditolak)
        // "18:30" < "18:30" = false (Diterima)
        if ($waktu_sekarang_h_i < $batas_waktu_pulang) { // <-- DIGANTI
            // Jika jam masih di bawah 18:30, tolak.
            send_response('error', "Maaf, $nama_pegawai. Belum waktunya absen pulang. Absen pulang dimulai jam $batas_waktu_pulang.");
        } else {
            // Jika jam 18:30 atau lebih, catat absen pulang
            $stmt_update = $pdo->prepare(
                "UPDATE absensi SET waktu_pulang = ? WHERE id_absensi = ?"
            );
            $stmt_update->execute([$waktu_sekarang_full, $absensi_hari_ini['id_absensi']]);
            send_response('success', "Terima Kasih, $nama_pegawai! Absen pulang berhasil dicatat.");
        }

    } else {
        // SUDAH ABSEN MASUK DAN PULANG
        send_response('error', "Anda sudah melakukan absensi masuk dan pulang hari ini.");
    }

} catch (PDOException $e) {
    // Tangani error database
    send_response('error', 'Terjadi masalah pada database: ' . $e->getMessage());
}
?>