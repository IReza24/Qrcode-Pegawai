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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--primary);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: none;
            margin-bottom: 24px;
        }
        
        .card-header {
            background-color: var(--primary);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .btn-primary {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .table th {
            background-color: var(--primary);
            color: white;
            border: none;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .badge {
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-badge.bg-success {
            background-color: rgba(46, 204, 113, 0.15) !important;
            color: #27ae60;
        }
        
        .status-badge.bg-danger {
            background-color: rgba(231, 76, 60, 0.15) !important;
            color: #c0392b;
        }
        
        .status-badge.bg-secondary {
            background-color: rgba(149, 165, 166, 0.15) !important;
            color: #7f8c8d;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .section-title {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--secondary);
        }
        
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        .qr-code-img {
            transition: transform 0.3s;
        }
        
        .qr-code-img:hover {
            transform: scale(1.8);
            z-index: 10;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-users me-2"></i>Sistem Manajemen Pegawai
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-home me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-calendar-alt me-1"></i> Absensi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-cog me-1"></i> Pengaturan</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <!-- Notifikasi -->
        <?php if($notifikasi): ?>
        <div class="notification-toast">
            <div class="alert alert-<?php echo $notifikasi_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas <?php echo $notifikasi_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                <?php echo $notifikasi; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <?php endif; ?>

        <h2 class="section-title"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Manajemen Pegawai</h2>

        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-user-plus me-2"></i>Tambah Pegawai Baru</span>
                        <i class="fas fa-info-circle" data-bs-toggle="tooltip" title="Isi form untuk menambahkan pegawai baru"></i>
                    </div>
                    <div class="card-body">
                        <form action="pegawai.php" method="POST">
                            <div class="mb-3">
                                <label for="nip" class="form-label">NIP <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control" id="nip" name="nip" required placeholder="Masukkan NIP pegawai">
                                </div>
                                <div class="form-text">Nomor Induk Pegawai harus unik</div>
                            </div>
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Pegawai <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="nama" name="nama" required placeholder="Masukkan nama lengkap">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="jabatan" class="form-label">Jabatan</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                    <input type="text" class="form-control" id="jabatan" name="jabatan" placeholder="Masukkan jabatan">
                                </div>
                            </div>
                            <button type="submit" name="tambah_pegawai" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Simpan Data Pegawai
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-chart-bar me-2"></i>Statistik Pegawai</span>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border rounded p-3 bg-light">
                                    <h3 class="text-primary">
                                        <?php
                                        $sql_total = "SELECT COUNT(*) as total FROM pegawai";
                                        $stmt_total = $pdo->query($sql_total);
                                        $total_pegawai = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
                                        echo $total_pegawai;
                                        ?>
                                    </h3>
                                    <p class="mb-0 text-muted">Total Pegawai</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3 bg-light">
                                    <h3 class="text-success">
                                        <?php
                                        $sql_hadir = "SELECT COUNT(DISTINCT id_pegawai) as hadir FROM absensi WHERE tanggal = CURDATE()";
                                        $stmt_hadir = $pdo->query($sql_hadir);
                                        $hadir_hari_ini = $stmt_hadir->fetch(PDO::FETCH_ASSOC)['hadir'];
                                        echo $hadir_hari_ini;
                                        ?>
                                    </h3>
                                    <p class="mb-0 text-muted">Hadir Hari Ini</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3 bg-light">
                                    <h3 class="text-danger">
                                        <?php
                                        $tidak_hadir = $total_pegawai - $hadir_hari_ini;
                                        echo $tidak_hadir > 0 ? $tidak_hadir : 0;
                                        ?>
                                    </h3>
                                    <p class="mb-0 text-muted">Tidak Hadir</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <span><i class="fas fa-info-circle me-2"></i>Panduan Cepat</span>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            <h6><i class="fas fa-lightbulb me-2"></i>Tips Penggunaan:</h6>
                            <ul class="mb-0 ps-3">
                                <li>Gunakan form di samping untuk menambahkan pegawai baru</li>
                                <li>Setiap pegawai otomatis mendapatkan QR Code unik</li>
                                <li>Status kehadiran akan diperbarui secara otomatis</li>
                                <li>Arahkan kursor ke QR Code untuk melihat lebih detail</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i>Daftar Pegawai</span>
                <div class="d-flex">
                    <input type="text" class="form-control form-control-sm me-2" placeholder="Cari pegawai..." id="searchInput">
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0" id="pegawaiTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Status Hari Ini</th>
                                <th>QR Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Ambil tanggal hari ini sekali saja sebelum loop
                            $tanggal_hari_ini = date('Y-m-d');

                            // Query utama untuk mengambil semua data pegawai
                            $sql_select_pegawai = "SELECT * FROM pegawai ORDER BY nama_pegawai ASC";
                            $stmt_pegawai = $pdo->query($sql_select_pegawai);
                            
                            // Siapkan query untuk cek absensi (kita siapkan di luar loop biar lebih efisien)
                            $sql_cek_absensi = "SELECT waktu_masuk, waktu_pulang FROM absensi WHERE id_pegawai = ? AND tanggal = ?";
                            $stmt_cek = $pdo->prepare($sql_cek_absensi);

                            $nomor = 1;
                            // Looping untuk setiap pegawai
                            while($row_pegawai = $stmt_pegawai->fetch(PDO::FETCH_ASSOC)) {
                                
                                // --- LOGIKA PENGECEKAN STATUS HARI INI ---
                                $id_pegawai_saat_ini = $row_pegawai['id_pegawai'];
                                
                                // Jalankan query cek absensi untuk pegawai ini & hari ini
                                $stmt_cek->execute([$id_pegawai_saat_ini, $tanggal_hari_ini]);
                                $absensi_hari_ini = $stmt_cek->fetch(PDO::FETCH_ASSOC);

                                $status_text = '';
                                $status_badge_class = '';
                                $status_icon = '';

                                if ($absensi_hari_ini) {
                                    // Jika ada data absensi hari ini
                                    if ($absensi_hari_ini['waktu_pulang']) {
                                        // Jika waktu pulang SUDAH diisi
                                        $status_text = 'Sudah Pulang';
                                        $status_badge_class = 'status-badge bg-secondary';
                                        $status_icon = 'fa-home';
                                    } else {
                                        // Jika waktu pulang BELUM diisi (berarti baru masuk)
                                        $status_text = 'Sudah Masuk';
                                        $status_badge_class = 'status-badge bg-success';
                                        $status_icon = 'fa-building';
                                    }
                                } else {
                                    // Jika TIDAK ADA data absensi hari ini
                                    $status_text = 'Belum Hadir';
                                    $status_badge_class = 'status-badge bg-danger';
                                    $status_icon = 'fa-times-circle';
                                }
                                // --- AKHIR LOGIKA PENGECEKAN STATUS ---


                                // --- Generate QR Code (Kode ini masih sama seperti sebelumnya) ---
                                $qrCode = QrCode::create($row_pegawai['qr_secret']);
                                $writer = new PngWriter();
                                $result = $writer->write($qrCode);
                                $qrCodeBase64 = base64_encode($result->getString());
                                // --- Akhir Generate QR Code ---


                                // Mulai cetak baris tabel
                                echo "<tr>";
                                echo "<td class='fw-bold'>" . $nomor++ . "</td>";
                                echo "<td>" . htmlspecialchars($row_pegawai['nip']) . "</td>";
                                echo "<td class='fw-semibold'>" . htmlspecialchars($row_pegawai['nama_pegawai']) . "</td>";
                                echo "<td>" . htmlspecialchars($row_pegawai['jabatan']) . "</td>";
                                
                                // Cetak status hari ini pakai badge Bootstrap
                                echo '<td><span class="' . $status_badge_class . '"><i class="fas ' . $status_icon . ' me-1"></i> ' . $status_text . '</span></td>';

                                // Cetak QR Code
                                echo '<td><img src="data:image/png;base64,' . $qrCodeBase64 . '" width="80" class="qr-code-img"></td>';
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Menampilkan <?php echo isset($nomor) ? $nomor - 1 : 0; ?> pegawai
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download me-1"></i>Export Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Sistem Manajemen Pegawai</h5>
                    <p class="mb-0">Aplikasi untuk mengelola data dan kehadiran pegawai</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0">© 2023 Company Name. All rights reserved.</p>
                    <p class="mb-0">v1.0.0</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Aktifkan tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Fungsi pencarian tabel
        document.getElementById('searchInput').addEventListener('keyup', function() {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("pegawaiTable");
            tr = table.getElementsByTagName("tr");
            
            for (i = 1; i < tr.length; i++) {
                var found = false;
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length - 1; j++) { // Skip QR code column
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = found ? "" : "none";
            }
        });
        
        // Auto-hide notifikasi setelah 3 detik
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 3000);
    </script>
</body>
</html>