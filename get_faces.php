<?php
header('Content-Type: application/json');
include 'koneksi.php'; // File koneksi database

$query = "SELECT id, username,name, image_path FROM faces"; // Sesuaikan dengan nama tabel Anda
$result = $conn->query($query);

$faces = [];
while ($row = $result->fetch_assoc()) {
    $faces[] = $row;
}

echo json_encode($faces);
?>
