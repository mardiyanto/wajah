<?php
// Koneksi ke database
include 'koneksi.php'; // File koneksi database

// Periksa apakah tombol generate ditekan
if (isset($_POST['generate'])) {
    require_once 'phpqrcode/qrlib.php'; // Include library PHP QR Code

    // Generate angka acak
    $randomNumber = mt_rand(100000, 999999); // Angka 6 digit acak

    // Direktori untuk menyimpan QR Code
    $qrDir = "qrcodes/";
    if (!is_dir($qrDir)) {
        mkdir($qrDir, 0777, true);
    }

    // Nama file untuk QR Code
    $fileName = $qrDir . uniqid() . ".png";

    // Generate QR Code dan simpan ke file
    QRcode::png($randomNumber, $fileName, QR_ECLEVEL_L, 10);

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO qrcodes (data, qr_code_path) VALUES (?, ?)");
    $stmt->bind_param("ss", $randomNumber, $fileName);
    $stmt->execute();

    echo "<div class='alert alert-success text-center'>QR Code berhasil dibuat dengan angka acak: <strong>$randomNumber</strong></div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">QR Code Generator (Angka Acak)</h1>
        <!-- <form action="" method="POST" class="mt-4 text-center">
            <button type="submit" name="generate" class="btn btn-primary">Generate QR Code dengan Angka Acak</button>
        </form> -->

        <?php
        // Tampilkan data QR Code yang telah disimpan
        $result = $conn->query("SELECT * FROM faces ORDER BY id DESC");
        if ($result->num_rows > 0) {
            echo "<div class='mt-5'>";
            echo "<h2 class='text-center'>Daftar QR Code</h2>";
            echo "<table class='table table-bordered'>";
            echo "<thead><tr><th>No</th><th>Data</th><th>QR Code</th></tr></thead><tbody>";
            $no = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$no}</td>";
                echo "<td>{$row['data_qrcode']}</td>";
                echo "<td><img src='{$row['qr_code_path']}' alt='QR Code' width='100'></td>";
                echo "</tr>";
                $no++;
            }
            echo "</tbody></table></div>";
        }
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
