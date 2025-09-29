<?php
date_default_timezone_set('Asia/Jakarta');
require_once 'config/database.php';
require_once 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$notifikasi = '';
$notifikasi_type = '';

// Handle tambah pegawai
if (isset($_POST['tambah_pegawai'])) {
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $jabatan = $_POST['jabatan'];
    $qr_secret = hash('sha256', $nip . time());

    try {
        $sql = "INSERT INTO pegawai (nip, nama_pegawai, jabatan, qr_secret) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nip, $nama, $jabatan, $qr_secret]);
        $notifikasi = "Data pegawai berhasil ditambahkan!";
        $notifikasi_type = "success";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $notifikasi = "Error: NIP sudah terdaftar. Gunakan NIP lain.";
        } else {
            $notifikasi = "Error: " . $e->getMessage();
        }
        $notifikasi_type = "danger";
    }
}

// Handle edit pegawai
if (isset($_POST['edit_pegawai'])) {
    $id = $_POST['id'];
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $jabatan = $_POST['jabatan'];

    try {
        $sql = "UPDATE pegawai SET nip = ?, nama_pegawai = ?, jabatan = ? WHERE id_pegawai = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nip, $nama, $jabatan, $id]);
        $notifikasi = "Data pegawai berhasil diupdate!";
        $notifikasi_type = "success";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $notifikasi = "Error: NIP sudah terdaftar. Gunakan NIP lain.";
        } else {
            $notifikasi = "Error: " . $e->getMessage();
        }
        $notifikasi_type = "danger";
    }
}

// Handle hapus pegawai
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    try {
        $sql = "DELETE FROM pegawai WHERE id_pegawai = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $notifikasi = "Data pegawai berhasil dihapus!";
        $notifikasi_type = "success";
    } catch (PDOException $e) {
        $notifikasi = "Error: " . $e->getMessage();
        $notifikasi_type = "danger";
    }
}

// Pagination logic
$records_per_page = isset($_GET['records']) ? (int)$_GET['records'] : 5;
if ($records_per_page < 1) $records_per_page = 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $records_per_page;

// Get total number of records
$sql_total = "SELECT COUNT(*) as total FROM pegawai";
$stmt_total = $pdo->query($sql_total);
$total_records = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Ensure page doesn't exceed total pages
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $records_per_page;
}

// Get records for the current page
$sql_select_pegawai = "SELECT * FROM pegawai ORDER BY nama_pegawai ASC LIMIT :offset, :records_per_page";
$stmt_pegawai = $pdo->prepare($sql_select_pegawai);
$stmt_pegawai->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_pegawai->bindValue(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt_pegawai->execute();

// Get today's attendance stats
$sql_hadir = "SELECT COUNT(DISTINCT id_pegawai) as hadir FROM absensi WHERE tanggal = CURDATE()";
$stmt_hadir = $pdo->query($sql_hadir);
$hadir_hari_ini = $stmt_hadir->fetch(PDO::FETCH_ASSOC)['hadir'];

// Get data for edit form if requested
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql_edit = "SELECT * FROM pegawai WHERE id_pegawai = ?";
    $stmt_edit = $pdo->prepare($sql_edit);
    $stmt_edit->execute([$id]);
    $edit_data = $stmt_edit->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pegawai</title>
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
            --accent: #e74c3c;
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
            
            --border-radius-sm: 0.25rem;
            --border-radius: 0.375rem;
            --border-radius-lg: 0.5rem;
            --border-radius-xl: 0.75rem;
            
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            --shadow: 0 3px 6px rgba(0,0,0,0.15), 0 2px 4px rgba(0,0,0,0.12);
            --shadow-lg: 0 10px 20px rgba(0,0,0,0.15), 0 3px 6px rgba(0,0,0,0.10);
            --shadow-xl: 0 15px 25px rgba(0,0,0,0.15), 0 5px 10px rgba(0,0,0,0.05);
            
            --font-family-sans: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            --font-family-monospace: 'Fira Code', 'Consolas', monospace;
            
            --transition-base: all 0.2s ease-in-out;
            --transition-slow: all 0.35s ease-in-out;
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-800);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Improved Header & Navigation */
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            box-shadow: var(--shadow);
            padding: 0.8rem 1rem;
            position: sticky;
            top: 0;
            z-index: 1030;
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
        
        /* Improved Cards */
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
            transform: translateY(-2px);
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
        
        /* Improved Buttons */
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
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--secondary), var(--secondary-dark));
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, var(--secondary-dark), var(--secondary));
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .btn-info {
            background: linear-gradient(to right, var(--info), #2c81c7);
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
        
        /* Improved Forms */
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
        
        .input-group-text {
            background-color: var(--gray-100);
            border: 1px solid var(--gray-300);
            color: var(--gray-600);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--gray-700);
        }
        
        /* Improved Tables */
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
            transition: var(--transition-base);
        }
        
        .table-hover tbody tr {
            transition: var(--transition-base);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .table tbody tr {
            animation: fadeIn 0.5s ease-out;
            animation-fill-mode: both;
        }
        
        /* Improved Status badges */
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
        
        /* Improved QR Code */
        .qr-code-img {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: var(--border-radius);
            cursor: pointer;
            border: 1px solid var(--gray-200);
        }
        
        .qr-code-img:hover {
            transform: scale(1.8);
            z-index: 100;
            box-shadow: var(--shadow-lg);
        }
        
        /* Improved Statistics */
        .stat-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: var(--border-radius-lg);
            background: white;
            box-shadow: var(--shadow);
            transition: var(--transition-base);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }
        
        .stat-card p {
            color: var(--gray-600);
            margin-bottom: 0;
            font-weight: 500;
        }
        
        .stat-card .icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            opacity: 0.1;
            font-size: 3rem;
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
        
        /* Notification */
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            animation: slideInRight 0.5s, fadeOut 0.5s 2.5s forwards;
            max-width: 350px;
        }
        
        .alert {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
                visibility: hidden;
            }
        }
        
        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }
        
        /* Pagination */
        .pagination {
            margin-bottom: 0;
        }
        
        .page-link {
            border-radius: var(--border-radius) !important;
            margin: 0 3px;
            border: 1px solid var(--gray-300);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
        }
        
        .page-link:hover {
            background-color: var(--gray-100);
            color: var(--primary);
        }
        
        .page-item.active .page-link {
            background: linear-gradient(to right, var(--secondary), var(--secondary-dark));
            border-color: var(--secondary);
        }
        
        /* Search box */
        .search-box {
            position: relative;
            max-width: 300px;
        }
        
        .search-box .form-control {
            padding-left: 40px;
            border-radius: var(--border-radius-lg);
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
            z-index: 5;
        }
        
        /* Loading state */
        .loading {
            position: relative;
            pointer-events: none;
        }
        
        .loading:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--border-radius);
        }
        
        /* Skeleton loading */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: var(--border-radius);
            min-height: 1rem;
        }
        
        @keyframes loading {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }
        
        /* Focus states for accessibility */
        .btn:focus, .form-control:focus, .page-link:focus, .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
            outline: none;
        }
        
        /* Custom utilities */
        .hover-lift {
            transition: var(--transition-base);
        }
        
        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }
        
        .text-gradient {
            background: linear-gradient(to right, var(--secondary), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Animation for table rows */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .table tbody tr {
            animation: fadeIn 0.5s ease-out;
            animation-fill-mode: both;
        }
        
        .table tbody tr:nth-child(1) { animation-delay: 0.1s; }
        .table tbody tr:nth-child(2) { animation-delay: 0.2s; }
        .table tbody tr:nth-child(3) { animation-delay: 0.3s; }
        .table tbody tr:nth-child(4) { animation-delay: 0.4s; }
        .table tbody tr:nth-child(5) { animation-delay: 0.5s; }
        
        /* Records per page selector */
        .records-per-page {
            max-width: 100px;
        }
        
        /* Dark mode styles */
        body.dark-mode {
            background-color: var(--gray-900);
            color: var(--gray-200);
        }
        
        body.dark-mode .card {
            background-color: var(--gray-800);
            color: var(--gray-200);
        }
        
        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background-color: var(--gray-700);
            border-color: var(--gray-600);
            color: var(--gray-200);
        }
        
        body.dark-mode .form-control:focus,
        body.dark-mode .form-select:focus {
            background-color: var(--gray-700);
            color: var(--gray-200);
        }
        
        body.dark-mode .input-group-text {
            background-color: var(--gray-700);
            border-color: var(--gray-600);
            color: var(--gray-400);
        }
        
        body.dark-mode .table {
            color: var(--gray-200);
        }
        
        body.dark-mode .table td {
            border-color: var(--gray-700);
        }
        
        body.dark-mode .stat-card {
            background-color: var(--gray-800);
            color: var(--gray-200);
        }
        
        body.dark-mode .stat-card p {
            color: var(--gray-400);
        }
        
        body.dark-mode .text-muted {
            color: var(--gray-500) !important;
        }
        
        /* Mobile menu */
        @media (max-width: 992px) {
            .navbar-nav {
                padding: 1rem 0;
            }
            
            .nav-link {
                padding: 0.5rem 0 !important;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .stat-card h3 {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
            
            .navbar-collapse {
                background: var(--primary);
                padding: 1rem;
                border-radius: var(--border-radius);
                margin-top: 1rem;
                box-shadow: var(--shadow);
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .table th, .table td {
                padding: 0.75rem 0.5rem;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .stat-card h3 {
                font-size: 1.75rem;
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
            
            /* Mobile table adjustments */
            .table-responsive table {
                min-width: 600px;
            }
            
            /* Stack statistics on mobile */
            .stat-column {
                margin-bottom: 1rem;
            }
            
            .search-box {
                width: 100%;
                max-width: none;
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand span {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 1.1rem;
            }
            
            .stat-card h3 {
                font-size: 1.5rem;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .d-flex.gap-2 {
                flex-direction: column;
            }
            
            .table th, .table td {
                padding: 0.5rem 0.25rem;
                font-size: 0.8rem;
            }
            
            .status-badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
            
            .qr-code-img {
                width: 40px;
            }
            
            footer .row > div {
                text-align: center !important;
                margin-bottom: 1rem;
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
                        <a class="nav-link active" href="pegawai.php" aria-current="page">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="rekap_absensi.php">
                            <i class="fas fa-calendar-alt me-1"></i> Rekap Absensi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="scan.php">
                            <i class="fas fa-camera me-1"></i> Halaman Scan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-user me-1"></i> Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="theme-toggle">
                            <i class="fas fa-moon me-1"></i> Mode Gelap
                        </a>
                    </li>
                </ul>
            </div>
</div>
    </nav>

    <div class="container mb-5">
        <?php if($notifikasi): ?>
        <div class="notification-toast">
            <div class="alert alert-<?php echo $notifikasi_type; ?> alert-dismissible fade show shadow" role="alert">
                <i class="fas <?php echo $notifikasi_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                <?php echo $notifikasi; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <?php endif; ?>

        <h2 class="section-title">
            <i class="fas fa-tachometer-alt me-2 text-gradient"></i>Dashboard Manajemen Pegawai
        </h2>

        <div class="d-flex gap-2 mb-4 flex-wrap">
            <a href="scan.php" class="btn btn-primary hover-lift">
                <i class="fas fa-camera me-2"></i> Buka Halaman Scan
            </a>
            <a href="rekap_absensi.php" class="btn btn-info text-white hover-lift">
                <i class="fas fa-file-alt me-2"></i> Lihat Rekap Laporan
            </a>
            <button class="btn btn-outline-secondary hover-lift" id="exportBtn">
                <i class="fas fa-download me-2"></i> Export Data
            </button>
            <button class="btn btn-outline-primary hover-lift" id="refreshBtn">
                <i class="fas fa-sync-alt me-2"></i> Refresh
            </button>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas <?php echo $edit_data ? 'fa-edit' : 'fa-user-plus'; ?> me-2"></i>
                            <?php echo $edit_data ? 'Edit Pegawai' : 'Tambah Pegawai Baru'; ?>
                        </span>
                        <i class="fas fa-info-circle" data-bs-toggle="tooltip" title="Isi form untuk menambahkan pegawai baru"></i>
                    </div>
                    <div class="card-body">
                        <form action="pegawai.php" method="POST" id="pegawaiForm">
                            <?php if ($edit_data): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_data['id_pegawai']; ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="nip" class="form-label">NIP <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control" id="nip" name="nip" required 
                                           placeholder="Masukkan NIP pegawai" aria-describedby="nipHelp"
                                           value="<?php echo $edit_data ? $edit_data['nip'] : ''; ?>">
                                </div>
                                <div id="nipHelp" class="form-text">Nomor Induk Pegawai harus unik</div>
                            </div>
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Pegawai <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="nama" name="nama" required 
                                           placeholder="Masukkan nama lengkap"
                                           value="<?php echo $edit_data ? $edit_data['nama_pegawai'] : ''; ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="jabatan" class="form-label">Jabatan</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                    <input type="text" class="form-control" id="jabatan" name="jabatan" 
                                           placeholder="Masukkan jabatan"
                                           value="<?php echo $edit_data ? $edit_data['jabatan'] : ''; ?>">
                                </div>
                            </div>
                            <button type="submit" name="<?php echo $edit_data ? 'edit_pegawai' : 'tambah_pegawai'; ?>" 
                                    class="btn btn-primary w-100 py-2 hover-lift" id="submitBtn">
                                <i class="fas fa-save me-2"></i>
                                <?php echo $edit_data ? 'Update Data Pegawai' : 'Simpan Data Pegawai'; ?>
                            </button>
                            
                            <?php if ($edit_data): ?>
                                <a href="pegawai.php" class="btn btn-secondary w-100 py-2 mt-2 hover-lift">
                                    <i class="fas fa-times me-2"></i> Batal
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div class="row">
                    <div class="col-md-4 stat-column mb-3">
                        <div class="stat-card">
                            <i class="fas fa-users icon"></i>
                            <h3 class="text-primary">
                                <?php echo $total_records; ?>
                            </h3>
                            <p class="mb-0 text-muted">Total Pegawai</p>
                        </div>
                    </div>
                    <div class="col-md-4 stat-column mb-3">
                        <div class="stat-card">
                            <i class="fas fa-user-check icon"></i>
                            <h3 class="text-success">
                                <?php echo $hadir_hari_ini; ?>
                            </h3>
                            <p class="mb-0 text-muted">Hadir Hari Ini</p>
                        </div>
                    </div>
                    <div class="col-md-4 stat-column mb-3">
                        <div class="stat-card">
                            <i class="fas fa-user-times icon"></i>
                            <h3 class="text-danger">
                                <?php
                                $tidak_hadir = $total_records - $hadir_hari_ini;
                                echo $tidak_hadir > 0 ? $tidak_hadir : 0;
                                ?>
                            </h3>
                            <p class="mb-0 text-muted">Tidak Hadir</p>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <span><i class="fas fa-info-circle me-2"></i>Panduan Cepat</span>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-0 border-0">
                            <h6><i class="fas fa-lightbulb me-2"></i>Tips Penggunaan:</h6>
                            <ul class="mb-0 ps-3">
                                <li>Gunakan form di samping untuk menambahkan pegawai baru</li>
                                <li>Setiap pegawai otomatis mendapatkan QR Code unik</li>
                                <li>Status kehadiran akan diperbarui secara otomatis</li>
                                <li>Arahkan kursor ke QR Code untuk melihat lebih detail</li>
                                <li>Klik tombol edit atau hapus untuk mengelola data pegawai</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <span><i class="fas fa-list me-2"></i>Daftar Pegawai</span>
                <div class="d-flex align-items-center mt-2 mt-md-0">
                    <div class="search-box me-2">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control form-control-sm" placeholder="Cari pegawai..." id="searchInput" aria-label="Cari pegawai">
                    </div>
                    
                    <div class="d-flex align-items-center me-2">
                        <span class="me-2 d-none d-md-block">Tampilkan</span>
                        <select class="form-select form-select-sm records-per-page" id="recordsPerPage" aria-label="Jumlah data per halaman">
                            <option value="5" <?php echo $records_per_page == 5 ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo $records_per_page == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo $records_per_page == 20 ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?php echo $records_per_page == 50 ? 'selected' : ''; ?>>50</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="pegawaiTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th>QR Code</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tanggal_hari_ini = date('Y-m-d');
                            $sql_cek_absensi = "SELECT waktu_masuk, waktu_pulang, status FROM absensi WHERE id_pegawai = ? AND tanggal = ?";
                            $stmt_cek = $pdo->prepare($sql_cek_absensi);

                            $nomor = $offset + 1;
                            while($row_pegawai = $stmt_pegawai->fetch(PDO::FETCH_ASSOC)) {
                                
                                $stmt_cek->execute([$row_pegawai['id_pegawai'], $tanggal_hari_ini]);
                                $absensi_hari_ini = $stmt_cek->fetch(PDO::FETCH_ASSOC);

                                $tampil_jam_masuk = '-';
                                $tampil_jam_pulang = '-';
                                $tampil_status = 'Belum Hadir';
                                $status_badge_class = 'status-badge bg-danger-light';
                                $status_icon = 'fa-times-circle';

                                if ($absensi_hari_ini) {
                                    $tampil_jam_masuk = date('H:i', strtotime($absensi_hari_ini['waktu_masuk']));
                                    
                                    $tampil_status = htmlspecialchars($absensi_hari_ini['status']);
                                    if ($tampil_status == 'Hadir') {
                                        $status_badge_class = 'status-badge bg-success-light';
                                        $status_icon = 'fa-check-circle';
                                    } else if ($tampil_status == 'Terlambat') {
                                        $status_badge_class = 'status-badge bg-warning-light';
                                        $status_icon = 'fa-clock';
                                    }

                                    if (!empty($absensi_hari_ini['waktu_pulang'])) {
                                        $tampil_jam_pulang = date('H:i', strtotime($absensi_hari_ini['waktu_pulang']));
                                        $tampil_status = 'Pulang';
                                        $status_badge_class = 'status-badge bg-secondary-light';
                                        $status_icon = 'fa-home';
                                    }
                                }

                                $qrCode = QrCode::create($row_pegawai['qr_secret']);
                                $writer = new PngWriter();
                                $result = $writer->write($qrCode);
                                $qrCodeBase64 = base64_encode($result->getString());

                                echo "<tr>";
                                echo "<td>" . $nomor++ . "</td>";
                                echo "<td>" . htmlspecialchars($row_pegawai['nip']) . "</td>";
                                echo "<td>" . htmlspecialchars($row_pegawai['nama_pegawai']) . "</td>";
                                echo "<td>" . htmlspecialchars($row_pegawai['jabatan']) . "</td>";
                                echo "<td>" . $tampil_jam_masuk . "</td>";
                                echo "<td>" . $tampil_jam_pulang . "</td>";
                                echo '<td><span class="' . $status_badge_class . '"><i class="fas ' . $status_icon . ' me-1"></i>' . $tampil_status . '</span></td>';
                                echo '<td><img src="data:image/png;base64,' . $qrCodeBase64 . '" width="50" class="qr-code-img" data-bs-toggle="tooltip" title="Klik untuk memperbesar" alt="QR Code untuk ' . htmlspecialchars($row_pegawai['nama_pegawai']) . '"></td>';
                                echo '<td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?edit=' . $row_pegawai['id_pegawai'] . '" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="?hapus=' . $row_pegawai['id_pegawai'] . '" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Hapus" onclick="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>';
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted">
                    Menampilkan <?php echo ($offset + 1) . ' sampai ' . min($offset + $records_per_page, $total_records) . ' dari ' . $total_records; ?> pegawai
                </div>
                <div class="mt-2 mt-md-0">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&records=<?php echo $records_per_page; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php
                            // Show page numbers
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $start_page + 4);
                            
                            if ($end_page - $start_page < 4) {
                                $start_page = max(1, $end_page - 4);
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&records=<?php echo $records_per_page; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&records=<?php echo $records_per_page; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
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

    <!-- Modal for QR Code -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">QR Code Pegawai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalQrImage" src="" alt="QR Code" class="img-fluid">
                    <h6 id="modalEmployeeName" class="mt-3"></h6>
                    <p id="modalEmployeeNip" class="text-muted mb-0"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="downloadQr">Download</button>
                </div>
            </div>
        </div>
    </div>

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
                for (j = 0; j < td.length - 1; j++) {
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
        var notificationToast = document.querySelector('.notification-toast');
        if (notificationToast) {
            setTimeout(function() {
                notificationToast.style.display = 'none';
            }, 3000);
        }
        
        // Records per page selector
        document.getElementById('recordsPerPage').addEventListener('change', function() {
            var recordsPerPage = this.value;
            window.location.href = '?page=1&records=' + recordsPerPage;
        });
        
        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            this.disabled = true;
            window.location.reload();
        });
        
        // Export button
        document.getElementById('exportBtn').addEventListener('click', function() {
            // In a real application, this would export data to CSV/Excel
            alert('Fitur export data akan segera tersedia!');
        });
        
        // Form submission loading state
        document.getElementById('pegawaiForm').addEventListener('submit', function() {
            var submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            submitBtn.disabled = true;
        });
        
        // QR Code modal
        var qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
        var modalQrImage = document.getElementById('modalQrImage');
        var modalEmployeeName = document.getElementById('modalEmployeeName');
        var modalEmployeeNip = document.getElementById('modalEmployeeNip');
        
        document.querySelectorAll('.qr-code-img').forEach(function(img) {
            img.addEventListener('click', function() {
                var row = this.closest('tr');
                var name = row.cells[2].textContent;
                var nip = row.cells[1].textContent;
                
                modalQrImage.src = this.src;
                modalEmployeeName.textContent = name;
                modalEmployeeNip.textContent = 'NIP: ' + nip;
                
                qrModal.show();
            });
        });
        
        // Download QR Code
        document.getElementById('downloadQr').addEventListener('click', function() {
            var link = document.createElement('a');
            link.download = 'qrcode-' + modalEmployeeNip.textContent.replace('NIP: ', '') + '.png';
            link.href = modalQrImage.src;
            link.click();
        });
        
        // Theme toggle functionality
        document.getElementById('theme-toggle').addEventListener('click', function() {
            const body = document.body;
            const isDarkMode = body.classList.contains('dark-mode');
            const icon = this.querySelector('i');
            
            if (isDarkMode) {
                body.classList.remove('dark-mode');
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                this.querySelector('span').textContent = ' Mode Gelap';
                localStorage.setItem('theme', 'light');
            } else {
                body.classList.add('dark-mode');
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                this.querySelector('span').textContent = ' Mode Terang';
                localStorage.setItem('theme', 'dark');
            }
        });
        
        // Check for saved theme preference
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const themeToggle = document.getElementById('theme-toggle');
            const icon = themeToggle.querySelector('i');
            
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                themeToggle.querySelector('span').textContent = ' Mode Terang';
            }
        });
        
        // Responsive table adjustment
        function adjustTableForMobile() {
            if (window.innerWidth < 768) {
                document.querySelectorAll('.table th, .table td').forEach(function(cell) {
                    cell.style.padding = '0.5rem 0.25rem';
                });
                
                document.querySelectorAll('.status-badge').forEach(function(badge) {
                    badge.style.fontSize = '0.75rem';
                    badge.style.padding = '0.25rem 0.5rem';
                });
            }
        }
        
        // Initial adjustment
        adjustTableForMobile();
        
        // Adjust on window resize
        window.addEventListener('resize', adjustTableForMobile);
    </script>
</body>
</html>