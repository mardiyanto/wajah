<?php
include 'koneksi.php'; // File koneksi database
if ($conn->connect_error) die(json_encode(['success' => false, 'error' => 'Koneksi database gagal: ' . $conn->connect_error]));

$data = json_decode(file_get_contents('php://input'), true);

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

$username = $conn->real_escape_string($data['username']);
$name = $conn->real_escape_string($data['name']);
$imageData = $data['image'];
$descriptor = json_encode($data['descriptor']);

list($type, $imageData) = explode(';', $imageData);
list(, $imageData) = explode(',', $imageData);
$imageData = base64_decode($imageData);

$imagePath = 'uploads/' . $username . '_' . time() . '.png';
if (!file_put_contents($imagePath, $imageData)) {
    echo json_encode(['success' => false, 'error' => 'Gagal menyimpan gambar.']);
    exit;
}

$sql = "INSERT INTO faces (username, name, image_path, descriptor,data_qrcode,qr_code_path) VALUES ('$username', '$name', '$imagePath', '$descriptor', '$randomNumber', '$fileName')";
if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal menyimpan data ke database: ' . $conn->error]);
}

$conn->close();
?>
