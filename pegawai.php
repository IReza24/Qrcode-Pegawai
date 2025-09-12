<?php
// Panggil file koneksi database
require_once 'config/database.php';
// Panggil file autoload dari Composer
require_once 'vendor/autoload.php';

// Gunakan library QR Code
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Variabel untuk notifikasi
$notifikasi = '';
$notifikasi_type = '';

// Logika untuk TAMBAH data pegawai
// Cek apakah tombol 'tambah_pegawai' ditekan
if (isset($_POST['tambah_pegawai'])) {
    // Ambil data dari form
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $jabatan = $_POST['jabatan'];
    
    // Buat kode rahasia unik untuk QR Code
    // Gabungan NIP dan timestamp untuk memastikan keunikan
    $qr_secret = hash('sha256', $nip . time());

    try {
        // Siapkan query SQL untuk insert data
        $sql = "INSERT INTO pegawai (nip, nama_pegawai, jabatan, qr_secret) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        // Eksekusi query dengan data yang sudah disiapkan
        $stmt->execute([$nip, $nama, $jabatan, $qr_secret]);
        
        // Siapkan notifikasi sukses
        $notifikasi = "Data pegawai berhasil ditambahkan!";
        $notifikasi_type = "success";

    } catch (PDOException $e) {
        // Jika terjadi error, siapkan notifikasi gagal
        // Cek jika errornya karena NIP duplikat
        if ($e->getCode() == 23000) {
            $notifikasi = "Error: NIP sudah terdaftar. Gunakan NIP lain.";
        } else {
            $notifikasi = "Error: " . $e->getMessage();
        }
        $notifikasi_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pegawai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Manajemen Data Pegawai</h1>

        <div class="card">
            <div class="card-header">
                Tambah Pegawai Baru
            </div>
            <div class="card-body">
                <form action="pegawai.php" method="POST">
                    <div class="mb-3">
                        <label for="nip" class="form-label">NIP</label>
                        <input type="text" class="form-control" id="nip" name="nip" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Pegawai</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="jabatan" class="form-label">Jabatan</label>
                        <input type="text" class="form-control" id="jabatan" name="jabatan">
                    </div>
                    <button type="submit" name="tambah_pegawai" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>

        <hr>

        <h2 class="mt-5">Daftar Pegawai</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>QR Code</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query untuk mengambil semua data pegawai
                    $sql_select = "SELECT * FROM pegawai ORDER BY nama_pegawai ASC";
                    $stmt_select = $pdo->query($sql_select);
                    
                    $nomor = 1;
                    // Looping untuk menampilkan setiap baris data
                    while($row = $stmt_select->fetch(PDO::FETCH_ASSOC)) {
                        // Membuat objek QR code dari qr_secret
                        $qrCode = QrCode::create($row['qr_secret']);
                        $writer = new PngWriter();
                        $result = $writer->write($qrCode);
                        
                        // Mengubah gambar QR code menjadi base64 untuk ditampilkan di HTML
                        $qrCodeBase64 = base64_encode($result->getString());

                        echo "<tr>";
                        echo "<td>" . $nomor++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['nama_pegawai']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nip']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['jabatan']) . "</td>";
                        echo '<td><img src="data:image/png;base64,' . $qrCodeBase64 . '" width="80"></td>';
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>