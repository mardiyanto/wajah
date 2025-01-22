<?php
// Koneksi ke database
include 'koneksi.php'; // File koneksi database

// Ambil semua data dari tabel qrcodes
$result = $conn->query("SELECT * FROM faces ORDER BY id DESC");
$dataQR = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dataQR[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time QR Code Scanner with Sound</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #reader {
            width: 100%;
            max-width: 500px;
            margin: auto;
        }
        .scanned-list {
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Real-Time QR Code Scanner with Sound</h1>
        <div id="reader" class="mt-4"></div>
        <h3 class="text-center mt-4">Hasil Pemindaian:</h3>
        <div class="scanned-list mt-3">
            <ul id="scannedDataList" class="list-group"></ul>
        </div>
    </div>

    <!-- Pustaka HTML5 QR Code -->
    <script src="html5/minified/html5-qrcode.min.js"></script>

    <script>
        const reader = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: 250 };
        const scannedDataList = document.getElementById("scannedDataList");
        let scannedData = new Set(); // Menghindari duplikasi data

        // Memuat file suara
        const beepSound = new Audio("beep.mp3"); // Pastikan Anda memiliki file beep.mp3 di folder yang sesuai

        function onScanSuccess(decodedText, decodedResult) {
            // Tambahkan data yang baru dipindai ke dalam daftar, jika belum ada
            if (!scannedData.has(decodedText)) {
                scannedData.add(decodedText);

                // Mainkan bunyi
                beepSound.play();

                // Tampilkan data dalam daftar
                const listItem = document.createElement("li");
                listItem.className = "list-group-item";
                listItem.textContent = decodedText;
                scannedDataList.appendChild(listItem);
            }
        }

        function onScanFailure(error) {
            // Kesalahan saat mendeteksi kode (opsional untuk log)
            console.warn(`Kode QR tidak terdeteksi: ${error}`);
        }

        // Mulai pemindaian
        reader.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure).catch(err => {
            console.error("Kesalahan memulai kamera:", err);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
