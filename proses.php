<?php
include 'koneksi.php'; // File koneksi database
if ($conn->connect_error) die(json_encode(['success' => false, 'error' => 'Koneksi database gagal: ' . $conn->connect_error]));

$data = json_decode(file_get_contents('php://input'), true);

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

$sql = "INSERT INTO faces (username, name, image_path, descriptor) VALUES ('$username', '$name', '$imagePath', '$descriptor')";
if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal menyimpan data ke database: ' . $conn->error]);
}

$conn->close();
?>
