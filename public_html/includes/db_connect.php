<?php
/**
 * Konfigurasi Database
 * Sesuaikan dengan settingan server lokal/hosting Anda
 */
$host = 'localhost';     // Host database (biasanya localhost)
$user = 'u347230510_pkbm';          // Username database (default XAMPP: root)
$pass = 'Pkbmharapankasih1';              // Password database (default XAMPP: kosong)
$db   = 'u347230510_pkbm'; // Nama database sesuai file SQL

// Membuat koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

// Set charset ke UTF-8 agar karakter khusus aman
$conn->set_charset("utf8mb4");

/**
 * Helper Function: Mengambil Setting Situs
 * Mengambil value dari tabel site_settings berdasarkan key
 */
function get_setting($key) {
    global $conn;
    $sql = "SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1";
    
    // Menggunakan Prepared Statement untuk keamanan
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_object();
            return $row->setting_value;
        }
        $stmt->close();
    }
    
    return ""; // Return kosong jika tidak ditemukan
}
?>