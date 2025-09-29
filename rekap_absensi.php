<?php
// Panggil file koneksi
require_once 'config/database.php';
// Set timezone WAJIB
date_default_timezone_set('Asia/Jakarta');

// Tentukan rentang tanggal default (7 hari terakhir sampai hari ini)
$start_date = date('Y-m-d', strtotime('-7 days'));
$end_date = date('Y-m-d');

// Cek apakah user mengirimkan form (memilih tanggal)
if (isset($_POST['filter_tanggal'])) {
    if (!empty($_POST['start_date'])) {
        $start_date = $_POST['start_date'];
    }
    if (!empty($_POST['end_date'])) {
        $end_date = $_POST['end_date'];
    }
}

// -----------------------------------------------------------------
// SQL QUERY UTAMA (JOIN 2 TABEL DENGAN FILTER TANGGAL)
// -----------------------------------------------------------------
$sql = "SELECT 
            a.tanggal, 
            a.waktu_masuk, 
            a.waktu_pulang, 
            a.status, 
            p.nama_pegawai, 
            p.nip
        FROM 
            absensi a
        JOIN 
            pegawai p ON a.id_pegawai = p.id_pegawai
        WHERE 
            a.tanggal BETWEEN ? AND ?
        ORDER BY 
            a.tanggal DESC, a.waktu_masuk DESC, p.nama_pegawai ASC";

$stmt = $pdo->prepare($sql);
// Eksekusi query dengan rentang tanggal yang sudah kita tentukan
$stmt->execute([$start_date, $end_date]);
$data_absensi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik
$total_absensi = count($data_absensi);
$hadir_count = 0;
$terlambat_count = 0;
$pulang_count = 0;

foreach ($data_absensi as $row) {
    if ($row['status'] == 'Hadir') $hadir_count++;
    if ($row['status'] == 'Terlambat') $terlambat_count++;
    if ($row['waktu_pulang']) $pulang_count++;
}
// -----------------------------------------------------------------

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Laporan Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --primary-light: #3c5575;
            --primary-dark: #1a2530;
            --secondary: #3498db;
            --secondary-light: #5dade2;
            --secondary-dark: #2980b9;
            --success: #27ae60;
            --success-light: #2ecc71;
            --warning: #f39c12;
            --warning-light: #f1c40f;
            --danger: #e74c3c;
            --danger-light: #e74c3c;
            --info: #3498db;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #ced4da;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --gray-700: #495057;
            --gray-800: #343a40;
            --gray-900: #212529;
            
            --border-radius: 0.375rem;
            --border-radius-lg: 0.5rem;
            
            --shadow: 0 3px 6px rgba(0,0,0,0.15), 0 2px 4px rgba(0,0,0,0.12);
            --shadow-lg: 0 10px 20px rgba(0,0,0,0.15), 0 3px 6px rgba(0,0,0,0.10);
            
            --transition-base: all 0.2s ease-in-out;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-800);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Header */
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            box-shadow: var(--shadow);
            padding: 0.8rem 1rem;
        }
        
        .navbar-brand {
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white !important;
        }
        
        .navbar-brand i {
            font-size: 1.5rem;
        }
        
        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: var(--border-radius);
            transition: var(--transition-base);
            color: rgba(255, 255, 255, 0.85) !important;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white !important;
        }
        
        /* Main Container */
        .container {
            max-width: 1400px;
            margin-top: 2rem;
            flex: 1;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            transition: var(--transition-base);
            margin-bottom: 1.5rem;
            overflow: hidden;
            background-color: white;
        }
        
        .card:hover {
            box-shadow: var(--shadow-lg);
        }
        
        .card-header {
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Buttons */
        .btn {
            border-radius: var(--border-radius);
            font-weight: 500;
            padding: 0.5rem 1.5rem;
            transition: var(--transition-base);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--secondary), var(--secondary-dark));
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, var(--secondary-dark), var(--secondary));
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .btn-outline-primary {
            border: 1px solid var(--secondary);
            color: var(--secondary);
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: var(--secondary);
            color: white;
        }
        
        /* Forms */
        .form-control, .form-select {
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            transition: var(--transition-base);
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-light);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.15);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--gray-700);
        }
        
        /* Tables */
        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin-bottom: 0;
        }
        
        .table th {
            background: linear-gradient(to bottom, var(--primary), var(--primary-light));
            color: white;
            font-weight: 600;
            padding: 1rem;
            border: none;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .table-hover tbody tr {
            transition: var(--transition-base);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        /* Status badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            gap: 0.25rem;
        }
        
        .bg-success-light {
            background-color: rgba(39, 174, 96, 0.15) !important;
            color: var(--success);
        }
        
        .bg-danger-light {
            background-color: rgba(231, 76, 60, 0.15) !important;
            color: var(--danger);
        }
        
        .bg-warning-light {
            background-color: rgba(243, 156, 18, 0.15) !important;
            color: var(--warning);
        }
        
        .bg-secondary-light {
            background-color: rgba(149, 165, 166, 0.15) !important;
            color: var(--gray-600);
        }
        
        /* Section title */
        .section-title {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(to right, var(--secondary), var(--primary));
            border-radius: 2px;
        }
        
        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }
        
        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        /* Filter form */
        .filter-form {
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        /* Summary stats */
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 1.25rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition-base);
        }
        
        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-item h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-item p {
            color: var(--gray-600);
            margin-bottom: 0;
            font-size: 0.9rem;
        }
        
        .stat-item .icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            opacity: 0.8;
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray-600);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .table th, .table td {
                padding: 0.75rem 0.5rem;
            }
            
            .card-header {
                padding: 0.75rem 1rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
            }
            
            .section-title {
                font-size: 1.25rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand span {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 1.1rem;
            }
            
            .table th, .table td {
                padding: 0.5rem 0.25rem;
                font-size: 0.8rem;
            }
            
            .status-badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
            
            .filter-form .row {
                flex-direction: column;
            }
            
            .filter-form .col-md-2 {
                width: 100%;
                margin-top: 1rem;
            }
            
            .summary-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-users"></i>
                <span>Sistem Manajemen Pegawai</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="pegawai.php">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="rekap_absensi.php" aria-current="page">
                            <i class="fas fa-calendar-alt me-1"></i> Rekap Absensi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="scan.php">
                            <i class="fas fa-camera me-1"></i> Halaman Scan
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <h2 class="section-title">
            <i class="fas fa-file-alt me-2"></i>Rekap Laporan Absensi
        </h2>

        <div class="action-buttons">
            <a href="scan.php" class="btn btn-primary">
                <i class="fas fa-camera me-2"></i> Halaman Scan
            </a>
            <a href="pegawai.php" class="btn btn-outline-primary">
                <i class="fas fa-users me-2"></i> Kembali ke Dashboard
            </a>
            <button class="btn btn-outline-secondary" id="exportBtn">
                <i class="fas fa-download me-2"></i> Export Data
            </button>
        </div>

        <div class="summary-stats">
            <div class="stat-item">
                <div class="icon text-primary">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="text-primary"><?php echo $total_absensi; ?></h3>
                <p>Total Absensi</p>
            </div>
            <div class="stat-item">
                <div class="icon text-success">
                    <i class="fas fa-user-check"></i>
                </div>
                <h3 class="text-success"><?php echo $hadir_count; ?></h3>
                <p>Hadir</p>
            </div>
            <div class="stat-item">
                <div class="icon text-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="text-warning"><?php echo $terlambat_count; ?></h3>
                <p>Terlambat</p>
            </div>
            <div class="stat-item">
                <div class="icon text-info">
                    <i class="fas fa-home"></i>
                </div>
                <h3 class="text-info"><?php echo $pulang_count; ?></h3>
                <p>Sudah Pulang</p>
            </div>
        </div>

        <div class="filter-form">
            <form action="rekap_absensi.php" method="POST">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="start_date" class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label for="end_date" class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="filter_tanggal" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-list-alt me-2"></i>
                Data Absensi (<?php echo date('d M Y', strtotime($start_date)); ?> s/d <?php echo date('d M Y', strtotime($end_date)); ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>NIP</th>
                                <th>Nama Pegawai</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($data_absensi) > 0): ?>
                                <?php foreach ($data_absensi as $row): ?>
                                    <?php
                                    $jam_masuk = $row['waktu_masuk'] ? date('H:i', strtotime($row['waktu_masuk'])) : '-';
                                    $jam_pulang = $row['waktu_pulang'] ? date('H:i', strtotime($row['waktu_pulang'])) : '-';
                                    $status = htmlspecialchars($row['status']);
                                    $badge_class = '';
                                    $status_icon = '';

                                    if ($jam_pulang != '-') {
                                        $status = 'Pulang';
                                        $badge_class = 'bg-secondary-light';
                                        $status_icon = 'fa-home';
                                    } elseif ($status == 'Hadir') {
                                        $badge_class = 'bg-success-light';
                                        $status_icon = 'fa-check-circle';
                                    } elseif ($status == 'Terlambat') {
                                        $badge_class = 'bg-warning-light';
                                        $status_icon = 'fa-clock';
                                    } else {
                                        $badge_class = 'bg-danger-light';
                                        $status_icon = 'fa-times-circle';
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['nip']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_pegawai']); ?></td>
                                        <td><?php echo $jam_masuk; ?></td>
                                        <td><?php echo $jam_pulang; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $badge_class; ?>">
                                                <i class="fas <?php echo $status_icon; ?> me-1"></i>
                                                <?php echo $status; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <h5>Tidak ada data absensi</h5>
                                        <p>Tidak ada data absensi untuk rentang tanggal yang dipilih.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Menampilkan <?php echo count($data_absensi); ?> catatan
                </div>
                <button class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                    <i class="fas fa-sync-alt me-1"></i> Segarkan
                </button>
            </div>
        </div>
    </div>
    
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-2">Sistem Manajemen Pegawai</h5>
                    <p class="mb-0">Aplikasi untuk mengelola data dan kehadiran pegawai</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">© 2023 Company Name. All rights reserved.</p>
                    <p class="mb-0">v1.2.0</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            window.location.reload();
        });
        
        // Export button
        document.getElementById('exportBtn').addEventListener('click', function() {
            // In a real application, this would export data to CSV/Excel
            alert('Fitur export data akan segera tersedia!');
        });
        
        // Set default dates if not set
        document.addEventListener('DOMContentLoaded', function() {
            if (!document.getElementById('end_date').value) {
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('end_date').value = today;
            }
            
            if (!document.getElementById('start_date').value) {
                const weekAgo = new Date();
                weekAgo.setDate(weekAgo.getDate() - 7);
                document.getElementById('start_date').value = weekAgo.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>