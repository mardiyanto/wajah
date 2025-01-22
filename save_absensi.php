<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Koneksi ke database
    include 'koneksi.php'; // File koneksi database

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Koneksi ke database gagal: ' . $conn->connect_error]);
        exit;
    }

    // Ambil data dari request
    $id_wajah = isset($_POST['id_wajah']) ? intval($_POST['id_wajah']) : null;
    $username = isset($_POST['username']) ? $conn->real_escape_string($_POST['username']) : null;

    if (!$id_wajah || !$username) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
        exit;
    }

    $waktu_absen = date('H:i:s');
    $tanggal_absen = date('Y-m-d');

    // Validasi apakah sudah absen hari ini
    $checkQuery = "SELECT * FROM absensi WHERE id_wajah = $id_wajah AND tanggal_absen = '$tanggal_absen'";
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Anda sudah absen hari ini.']);
    } else {
        // Query untuk menyimpan data
        $sql = "INSERT INTO absensi (id_wajah, username, waktu_absen, tanggal_absen) 
                VALUES ($id_wajah, '$username', '$waktu_absen', '$tanggal_absen')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Data absensi berhasil disimpan.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data absensi: ' . $conn->error]);
        }
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>

