<?php
// Koneksi ke database
include 'koneksi.php'; // Pastikan koneksi database telah benar

// Ambil semua data dari tabel faces
$result = $conn->query("SELECT id, username, name, data_qrcode FROM faces ORDER BY id DESC");

$dataFaces = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dataFaces[] = $row;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

                // Cari data wajah dari database menggunakan decodedText
                const faceData = <?php echo json_encode($dataFaces); ?>;
                const scannedFace = faceData.find(face => face.data_qrcode === decodedText);

                if (scannedFace) {
                    // Tampilkan data wajah dalam daftar
                    const listItem = document.createElement("li");
                    listItem.className = "list-group-item";
                    listItem.textContent = `ID: ${scannedFace.id}, Username: ${scannedFace.username}, Name: ${scannedFace.name}, QR Code Data: ${scannedFace.data_qrcode}`;
                    scannedDataList.appendChild(listItem);

                    // Kirim data ke server untuk disimpan ke absensi
                    saveToAbsensi(scannedFace.id, scannedFace.username);
                }
            }
        }

        function onScanFailure(error) {
            console.warn(`Kode QR tidak terdeteksi: ${error}`);
        }

        function saveToAbsensi(id_wajah, username) {
            const formData = new FormData();
            formData.append("id_wajah", id_wajah);
            formData.append("username", username);

            fetch("proses_absen.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Notifikasi sukses menggunakan SweetAlert2 dengan timer untuk otomatis menghilang
                    Swal.fire({
                        icon: 'success',
                        title: 'Absen Berhasil!',
                        text: 'Data absensi berhasil disimpan.',
                        timer: 2000, // Notifikasi otomatis menghilang dalam 2 detik
                        showConfirmButton: false
                    });
                    console.log("Data berhasil disimpan:", data.message);
                } else {
                    // Notifikasi error menggunakan SweetAlert2 dengan timer untuk otomatis menghilang
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message, // Menampilkan pesan kesalahan, misalnya "Sudah absen hari ini."
                        timer: 2000, // Notifikasi otomatis menghilang dalam 2 detik
                        showConfirmButton: false
                    });
                    console.error("Gagal menyimpan data:", data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Terjadi kesalahan saat mengirim data.',
                    timer: 2000, // Notifikasi otomatis menghilang dalam 2 detik
                    showConfirmButton: false
                });
                console.error("Kesalahan saat mengirim data:", error);
            });
        }

        // Mulai pemindaian
        reader.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure).catch(err => {
            console.error("Kesalahan memulai kamera:", err);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
