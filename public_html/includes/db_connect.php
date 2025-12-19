<?php
// Mencegah akses langsung ke file ini
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Akses langsung tidak diizinkan.');
}

$host = "localhost";
$user = "root"; // Sesuaikan dengan user database hosting Anda
$pass = "";     // Sesuaikan dengan password database hosting Anda
$db   = "pkbm_db"; // Sesuaikan dengan nama database Anda

// Mengaktifkan pelaporan error yang ketat untuk debugging (matikan saat live/production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    
    // Set charset ke utf8mb4 untuk mendukung simbol dan emoji
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Jika gagal, jangan tampilkan error detail ke user, cukup log saja
    error_log($e->getMessage());
    die("Koneksi ke database gagal. Silakan coba lagi nanti.");
}
?>
