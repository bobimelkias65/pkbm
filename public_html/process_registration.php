<?php
require_once 'includes/db_connect.php';

/**
 * Audit Keamanan:
 * 1. Mencegah Direct Access file.
 * 2. Membersihkan input dari tag HTML (XSS Prevention).
 * 3. Validasi tipe data ketat (Email & No HP).
 * 4. Menggunakan Prepared Statements (SQL Injection Prevention).
 */

// Cek apakah request datang dari metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. SECURITY: CSRF Protection Sederhana (Cek Referer)
    // Memastikan request berasal dari domain sendiri (bukan dari tool luar/website lain)
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
        die("Akses ditolak: Invalid Referer.");
    }

    // 2. SANITASI INPUT (Membersihkan data kotor)
    // strip_tags() menghapus tag HTML/PHP agar script jahat tidak tersimpan di database
    $nama_lengkap = strip_tags(trim($_POST['nama_lengkap']));
    
    // Sanitize Email membersihkan karakter ilegal dari email
    $email        = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    
    // Hapus apa pun selain angka dari No HP
    $no_hp        = preg_replace('/[^0-9]/', '', $_POST['no_hp']); 
    
    $program_id   = filter_var($_POST['program_id'], FILTER_SANITIZE_NUMBER_INT);
    $pesan        = strip_tags(trim($_POST['pesan']));

    // 3. VALIDASI INPUT (Memastikan format benar)
    $errors = [];

    // Cek Wajib Isi
    if (empty($nama_lengkap) || empty($no_hp) || empty($program_id)) {
        $errors[] = "Nama, Nomor HP, dan Pilihan Program wajib diisi.";
    }

    // Cek Format Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    // Cek Validitas No HP (Minimal 10 digit, Maksimal 14 digit)
    if (strlen($no_hp) < 10 || strlen($no_hp) > 14) {
        $errors[] = "Nomor HP tidak valid (harus 10-14 angka).";
    }

    // Cek Panjang Karakter (Mencegah error database jika kolom varchar terbatas)
    if (strlen($nama_lengkap) > 100) {
        $errors[] = "Nama terlalu panjang (maks 100 karakter).";
    }

    // Jika ada error, hentikan proses
    if (!empty($errors)) {
        // Gabungkan error menjadi string
        $error_msg = implode("\\n", $errors);
        echo "<script>alert('$error_msg'); window.history.back();</script>";
        exit();
    }

    // 4. PREPARED STATEMENT (Mencegah SQL Injection)
    $sql = "INSERT INTO students (nama, email, no_hp, program_id, pesan, tanggal_daftar) VALUES (?, ?, ?, ?, ?, NOW())";

    if ($stmt = $conn->prepare($sql)) {
        // "sssis" => String, String, String, Integer, String
        $stmt->bind_param("sssis", $nama_lengkap, $email, $no_hp, $program_id, $pesan);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Pendaftaran berhasil! Terima kasih telah mendaftar.');
                    window.location.href = 'index.php';
                  </script>";
        } else {
            // Log error asli di server, jangan tampilkan ke user (Information Disclosure)
            error_log("Database Error: " . $stmt->error);
            echo "<script>alert('Terjadi kesalahan sistem. Silakan coba lagi nanti.'); window.history.back();</script>";
        }
        $stmt->close();
    } else {
        error_log("Prepare Failed: " . $conn->error);
        echo "<script>alert('Gagal memproses data.'); window.history.back();</script>";
    }
    
    $conn->close();

} else {
    // Jika file diakses langsung tanpa submit form
    header("Location: index.php");
    exit();
}
?>
