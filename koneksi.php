<?php
date_default_timezone_set('Asia/Jakarta');
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'face_db';

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
