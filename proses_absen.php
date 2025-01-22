<?php
include 'koneksi.php'; // Koneksi ke database

// Ambil data yang dikirimkan
$id_wajah = $_POST['id_wajah'];
$username = $_POST['username'];
$tanggal_absen = date('Y-m-d'); // Tanggal absen hari ini

// Cek apakah data absen sudah ada
$query = "SELECT * FROM absensi WHERE id_wajah = ? AND tanggal_absen = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $id_wajah, $tanggal_absen);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Data sudah ada, berarti sudah absen hari ini
    echo json_encode([
        'success' => false,
        'message' => 'Sudah absen hari ini.'
    ]);
} else {
    // Data belum ada, simpan ke absensi
    $waktu_absen = date('H:i:s'); // Waktu absen saat ini
    $insertQuery = "INSERT INTO absensi (id_wajah, username, waktu_absen, tanggal_absen) VALUES (?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param('ssss', $id_wajah, $username, $waktu_absen, $tanggal_absen);
    
    if ($insertStmt->execute()) {
        // Sukses disimpan
        echo json_encode([
            'success' => true,
            'message' => 'Absen berhasil!'
        ]);
    } else {
        // Gagal menyimpan data
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menyimpan data.'
        ]);
    }
}
?>
