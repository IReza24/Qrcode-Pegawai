<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code Absensi - Sistem Presensi Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --light-bg: #f8f9fa;
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 20px 0;
        }
        
        .app-container {
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }
        
        .app-card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .app-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            text-align: center;
            border-bottom: none;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .app-logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .app-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .scanner-container {
            position: relative;
            margin: 20px auto;
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        #qr-reader {
            width: 100%;
            height: 300px;
            background-color: #000;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10;
        }
        
        .scan-line {
            position: absolute;
            height: 2px;
            width: 100%;
            background: linear-gradient(90deg, transparent, var(--success-color), transparent);
            top: 30%;
            animation: scan 2s linear infinite;
        }
        
        @keyframes scan {
            0% { top: 30%; }
            50% { top: 70%; }
            100% { top: 30%; }
        }
        
        .scanner-corner {
            position: absolute;
            width: 20px;
            height: 20px;
            border-color: var(--success-color);
            border-width: 0;
        }
        
        .corner-tl {
            top: 0;
            left: 0;
            border-top-width: 3px;
            border-left-width: 3px;
            border-top-left-radius: 12px;
        }
        
        .corner-tr {
            top: 0;
            right: 0;
            border-top-width: 3px;
            border-right-width: 3px;
            border-top-right-radius: 12px;
        }
        
        .corner-bl {
            bottom: 0;
            left: 0;
            border-bottom-width: 3px;
            border-left-width: 3px;
            border-bottom-left-radius: 12px;
        }
        
        .corner-br {
            bottom: 0;
            right: 0;
            border-bottom-width: 3px;
            border-right-width: 3px;
            border-bottom-right-radius: 12px;
        }
        
        #scan-result {
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            width: 100%;
            margin: 20px 0;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .status-success {
            background-color: rgba(76, 201, 240, 0.1);
            border: 1px solid rgba(76, 201, 240, 0.3);
            color: #198754;
        }
        
        .status-error {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }
        
        .status-waiting {
            background-color: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            color: #ffc107;
        }
        
        .btn-back {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }
        
        .instruction-box {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid var(--primary-color);
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #6c757d;
            font-size: 14px;
        }
        
        .theme-switcher {
            position: absolute;
            top: 20px;
            right: 20px;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .theme-switcher:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .upload-section {
            text-align: center;
            margin: 20px 0;
            display: none;
        }
        
        .btn-upload {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }
        
        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
            color: white;
        }
        
        #file-name {
            font-size: 14px;
            margin-top: 10px;
            color: #6c757d;
        }
        
        .preview-container {
            margin-top: 20px;
            text-align: center;
            display: none;
        }
        
        #image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .mode-switcher {
            display: flex;
            justify-content: center;
            margin: 15px 0;
            gap: 10px;
        }
        
        .mode-btn {
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid var(--primary-color);
            background: white;
            color: var(--primary-color);
        }
        
        .mode-btn.active {
            background: var(--primary-color);
            color: white;
        }
        
        .mode-btn i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="app-card card">
            <div class="card-header position-relative">
                <div class="theme-switcher" id="themeSwitcher">
                    <i class="fas fa-moon"></i>
                </div>
                <div class="app-logo"><i class="fas fa-qrcode me-2"></i>SISTEM ABSENSI QR</div>
                <div class="app-subtitle">Scan kode QR untuk melakukan presensi</div>
            </div>
            
            <div class="card-body">
                <div class="instruction-box">
                    <h6><i class="fas fa-info-circle me-2"></i>Petunjuk Penggunaan</h6>
                    <p class="mb-0 small">Gunakan kamera untuk scan QR code langsung, atau upload gambar QR code dari perangkat Anda. Pastikan QR code terlihat jelas dan dalam pencahayaan yang cukup.</p>
                </div>
                
                <div class="mode-switcher">
                    <button class="mode-btn active" id="camera-mode-btn">
                        <i class="fas fa-camera"></i>Mode Kamera
                    </button>
                    <button class="mode-btn" id="upload-mode-btn">
                        <i class="fas fa-upload"></i>Mode Upload
                    </button>
                </div>
                
                <div id="camera-section">
                    <div class="scanner-container">
                        <div id="qr-reader"></div>
                        <div class="scanner-overlay">
                            <div class="scanner-corner corner-tl"></div>
                            <div class="scanner-corner corner-tr"></div>
                            <div class="scanner-corner corner-bl"></div>
                            <div class="scanner-corner corner-br"></div>
                            <div class="scan-line"></div>
                        </div>
                    </div>
                </div>
                
                <div class="upload-section" id="upload-section">
                    <input type="file" id="qr-input" accept="image/*" capture="environment" style="display: none;">
                    <button class="btn-upload" id="upload-trigger">
                        <i class="fas fa-upload me-2"></i>Pilih Gambar QR Code
                    </button>
                    <div id="file-name"></div>
                    
                    <div class="preview-container" id="preview-container">
                        <img id="image-preview" src="#" alt="Preview QR Code">
                        <div class="mt-3">
                            <button class="btn btn-success me-2" id="process-btn">
                                <i class="fas fa-cog me-2"></i>Proses QR Code
                            </button>
                            <button class="btn btn-outline-secondary" id="cancel-btn">
                                <i class="fas fa-times me-2"></i>Batal
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="scan-result" class="status-waiting">
                    <i class="fas fa-camera fa-2x mb-2"></i>
                    <p class="text-center mb-0">Menunggu pemindaian QR Code<br><small>Pastikan QR code berada dalam frame kamera</small></p>
                </div>
                
                <div class="text-center">
                    <a href="pegawai.php" class="btn btn-back">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Manajemen
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; 2023 Sistem Presensi Digital. All rights reserved.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        // Theme switcher functionality
        const themeSwitcher = document.getElementById('themeSwitcher');
        const htmlElement = document.documentElement;
        
        themeSwitcher.addEventListener('click', () => {
            if (htmlElement.getAttribute('data-bs-theme') === 'light') {
                htmlElement.setAttribute('data-bs-theme', 'dark');
                themeSwitcher.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                htmlElement.setAttribute('data-bs-theme', 'light');
                themeSwitcher.innerHTML = '<i class="fas fa-moon"></i>';
            }
        });
        
        // Mode switcher functionality
        const cameraModeBtn = document.getElementById('camera-mode-btn');
        const uploadModeBtn = document.getElementById('upload-mode-btn');
        const cameraSection = document.getElementById('camera-section');
        const uploadSection = document.getElementById('upload-section');
        let html5QrcodeScanner;
        
        // Initialize scanner
        function initScanner() {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader",
                { fps: 10, qrbox: { width: 250, height: 250 } },
                false
            );
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }
        
        // Stop scanner
        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().then(() => {
                    console.log("QR Scanner stopped.");
                }).catch((error) => {
                    console.error("Failed to stop QR Scanner.", error);
                });
            }
        }
        
        // Switch to camera mode
        function switchToCameraMode() {
            cameraModeBtn.classList.add('active');
            uploadModeBtn.classList.remove('active');
            cameraSection.style.display = 'block';
            uploadSection.style.display = 'none';
            initScanner();
        }
        
        // Switch to upload mode
        function switchToUploadMode() {
            cameraModeBtn.classList.remove('active');
            uploadModeBtn.classList.add('active');
            cameraSection.style.display = 'none';
            uploadSection.style.display = 'block';
            stopScanner();
        }
        
        // Event listeners for mode switching
        cameraModeBtn.addEventListener('click', switchToCameraMode);
        uploadModeBtn.addEventListener('click', switchToUploadMode);
        
        // Upload QR Code functionality
        const qrInput = document.getElementById('qr-input');
        const uploadTrigger = document.getElementById('upload-trigger');
        const fileName = document.getElementById('file-name');
        const previewContainer = document.getElementById('preview-container');
        const imagePreview = document.getElementById('image-preview');
        const processBtn = document.getElementById('process-btn');
        const cancelBtn = document.getElementById('cancel-btn');
        
        uploadTrigger.addEventListener('click', () => {
            qrInput.click();
        });
        
        qrInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                fileName.textContent = file.name;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
        
        processBtn.addEventListener('click', () => {
            if (qrInput.files && qrInput.files[0]) {
                processQRCode(qrInput.files[0]);
            }
        });
        
        cancelBtn.addEventListener('click', () => {
            qrInput.value = '';
            fileName.textContent = '';
            previewContainer.style.display = 'none';
        });
        
        function processQRCode(file) {
            // Tampilkan status loading
            document.getElementById('scan-result').className = 'status-waiting';
            document.getElementById('scan-result').innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Memproses QR Code dari gambar...</p>
            `;
            
            // Gunakan FileReader untuk membaca file
            const reader = new FileReader();
            reader.onload = function(e) {
                // Decode QR code dari gambar
                Html5Qrcode.getScanDataFromImage(e.target.result)
                    .then(decodedResult => {
                        if (decodedResult && decodedResult.length > 0) {
                            // QR code berhasil dibaca
                            const decodedText = decodedResult[0].decodedText;
                            sendToServer(decodedText);
                        } else {
                            // QR code tidak terdeteksi
                            document.getElementById('scan-result').className = 'status-error';
                            document.getElementById('scan-result').innerHTML = `
                                <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                <h4>QR Code Tidak Terdeteksi</h4>
                                <p class="mb-0 small">Pastikan gambar jelas dan merupakan QR code yang valid</p>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error decoding QR code:', error);
                        document.getElementById('scan-result').className = 'status-error';
                        document.getElementById('scan-result').innerHTML = `
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <h4>Gagal Memproses Gambar</h4>
                            <p class="mb-0 small">Terjadi kesalahan saat memproses gambar</p>
                        `;
                    });
            };
            reader.readAsDataURL(file);
        }
        
        // Fungsi untuk mengirim data ke server
        function sendToServer(decodedText) {
            // Kirim data hasil scan (decodedText) ke server menggunakan AJAX (Fetch API)
            fetch('proses_absen.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'qr_secret=' + encodeURIComponent(decodedText)
            })
            .then(response => response.json())
            .then(data => {
                // Tampilkan pesan dari server
                let resultDiv = document.getElementById('scan-result');
                if (data.status === 'success') {
                    resultDiv.className = 'status-success';
                    resultDiv.innerHTML = `
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h4>${data.message}</h4>
                        <p class="mb-0 small">Selamat, presensi Anda berhasil tercatat</p>
                    `;
                } else {
                    resultDiv.className = 'status-error';
                    resultDiv.innerHTML = `
                        <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                        <h4>${data.message}</h4>
                        <p class="mb-0 small">Silakan coba lagi atau hubungi administrator</p>
                    `;
                }
                
                // Reset form upload
                qrInput.value = '';
                fileName.textContent = '';
                previewContainer.style.display = 'none';
                
                // Kembali ke mode kamera setelah 3 detik
                setTimeout(() => {
                    switchToCameraMode();
                }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('scan-result').className = 'status-error';
                document.getElementById('scan-result').innerHTML = `
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h4>Terjadi Kesalahan</h4>
                    <p class="mb-0 small">Silakan coba lagi atau hubungi administrator</p>
                `;
            });
        }
        
        // Fungsi ini akan dijalankan saat QR code berhasil di-scan
        function onScanSuccess(decodedText, decodedResult) {
            // Hentikan scanner setelah berhasil scan
            html5QrcodeScanner.clear();
            sendToServer(decodedText);
        }

        // Fungsi ini akan dijalankan jika terjadi error saat scanning
        function onScanFailure(error) {
            // Bisa diabaikan, karena akan terus mencoba scan
        }

        // Initialize scanner on page load
        initScanner();
    </script>
</body>
</html>