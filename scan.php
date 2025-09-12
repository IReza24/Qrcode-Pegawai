<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f4f4; }
        #qr-reader { 
            width: 500px;
            border: 2px solid #0d6efd;
            border-radius: 10px;
        }
        .container { max-width: 600px; }
        @media (max-width: 600px) {
            #qr-reader {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container d-flex flex-column align-items-center justify-content-center vh-100">
        <h1 class="mb-4 text-center">Arahkan QR Code Pegawai ke Kamera</h1>
        
        <div id="qr-reader"></div>
        
        <div id="scan-result" class="mt-4 p-3 rounded text-center" style="width: 100%;"></div>

        <a href="pegawai.php" class="btn btn-secondary mt-4">Kembali ke Manajemen Pegawai</a>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        // Fungsi ini akan dijalankan saat QR code berhasil di-scan
        function onScanSuccess(decodedText, decodedResult) {
            // Hentikan scanner setelah berhasil scan
            html5QrcodeScanner.clear();

            // Tampilkan loading spinner atau pesan
            document.getElementById('scan-result').innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memproses data...</p>
            `;

            // Kirim data hasil scan (decodedText) ke server menggunakan AJAX (Fetch API)
            fetch('proses_absen.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'qr_secret=' + encodeURIComponent(decodedText)
            })
            .then(response => response.json()) // Ubah response dari server menjadi format JSON
            .then(data => {
                // Tampilkan pesan dari server
                let resultDiv = document.getElementById('scan-result');
                if (data.status === 'success') {
                    resultDiv.className = 'mt-4 p-3 rounded text-center bg-success-subtle text-success-emphasis';
                } else {
                    resultDiv.className = 'mt-4 p-3 rounded text-center bg-danger-subtle text-danger-emphasis';
                }
                resultDiv.innerHTML = `<h4>${data.message}</h4>`;
                
                // Mulai ulang scanner setelah 3 detik
                setTimeout(() => {
                    location.reload(); 
                }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('scan-result').innerHTML = `<div class="alert alert-danger">Terjadi kesalahan saat mengirim data.</div>`;
            });
        }

        // Fungsi ini akan dijalankan jika terjadi error saat scanning
        function onScanFailure(error) {
            // Bisa diabaikan, karena akan terus mencoba scan
            // console.warn(`QR code scan error = ${error}`);
        }

        // Buat instance scanner baru
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", // ID dari div tempat kamera akan tampil
            { fps: 10, qrbox: { width: 250, height: 250 } }, // Konfigurasi scanner
            false // verbose
        );

        // Jalankan scanner
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>
</body>
</html>