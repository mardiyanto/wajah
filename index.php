<?php
include 'koneksi.php'; // File koneksi database
// Ambil data dari database
$result = $conn->query("SELECT * FROM faces");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Rekam Wajah</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Data Rekam Wajah</h2>
        <a href="rekam.php" class="btn btn-primary mb-3">Tambah Rekam Wajah</a>
        <a href="datawajah.php" class="btn btn-primary mb-3">deteksi wajah</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nama</th>
                    <th>Gambar</th>
                    <th>Waktu Dibuat</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['username'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><img src="<?= $row['image_path'] ?>" width="100"></td>
                        <td><?= $row['created_at'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
