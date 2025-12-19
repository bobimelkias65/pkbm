<?php
// Pastikan file koneksi database tersedia
require_once 'includes/db_connect.php';

// Cek apakah form dikirim menggunakan metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Ambil dan Bersihkan Data Input (Sanitasi)
    // htmlspecialchars digunakan untuk mencegah serangan XSS
    $name             = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $whatsapp         = isset($_POST['whatsapp']) ? htmlspecialchars(trim($_POST['whatsapp'])) : '';
    $program_interest = isset($_POST['program_interest']) ? htmlspecialchars(trim($_POST['program_interest'])) : '';
    $message          = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';

    // 2. Validasi Sederhana
    if (empty($name) || empty($whatsapp) || empty($program_interest)) {
        // Jika ada field wajib yang kosong, kembalikan dengan pesan error
        header("Location: index.php?status=error&msg=Mohon lengkapi Nama, WhatsApp, dan Pilihan Program.#kontak");
        exit();
    }

    // 3. Simpan ke Database
    // Menggunakan Prepared Statement untuk keamanan (mencegah SQL Injection)
    $sql = "INSERT INTO inquiries (name, whatsapp, program_interest, message, status) VALUES (?, ?, ?, ?, 'new')";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameter: "ssss" artinya 4 parameter bertipe string
        $stmt->bind_param("ssss", $name, $whatsapp, $program_interest, $message);

        if ($stmt->execute()) {
            // Jika berhasil disimpan
            header("Location: index.php?status=success#kontak");
            exit();
        } else {
            // Jika eksekusi query gagal
            header("Location: index.php?status=error&msg=Terjadi kesalahan sistem. Silakan coba lagi.#kontak");
            exit();
        }
        $stmt->close();
    } else {
        // Jika prepare statement gagal
        header("Location: index.php?status=error&msg=Gagal terhubung ke database.#kontak");
        exit();
    }
    
    $conn->close();

} else {
    // Jika file ini diakses langsung tanpa submit form, lempar kembali ke index
    header("Location: index.php");
    exit();
}
?>