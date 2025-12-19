<?php
require_once 'includes/db_connect.php';

// Cek apakah request datang dari metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data dan bersihkan spasi
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email        = trim($_POST['email']);
    $no_hp        = trim($_POST['no_hp']);
    $program_id   = trim($_POST['program_id']); // Asumsi form mengirim ID program
    $pesan        = trim($_POST['pesan']);

    // Validasi Sederhana
    if (empty($nama_lengkap) || empty($no_hp) || empty($program_id)) {
        die("Nama, Nomor HP, dan Pilihan Program wajib diisi.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Format email tidak valid.");
    }

    // 1. SIAPKAN QUERY (PREPARED STATEMENT)
    // Asumsi nama tabel adalah 'pendaftaran' atau 'students'
    // Sesuaikan nama tabel dan kolom dengan database Anda
    $sql = "INSERT INTO students (nama, email, no_hp, program_id, pesan, tanggal_daftar) VALUES (?, ?, ?, ?, ?, NOW())";

    if ($stmt = $conn->prepare($sql)) {
        // 2. BIND PARAMETER
        // "sssis" artinya: String, String, String, Integer, String
        $stmt->bind_param("sssis", $nama_lengkap, $email, $no_hp, $program_id, $pesan);

        // 3. EKSEKUSI
        if ($stmt->execute()) {
            // Sukses
            // Redirect ke halaman sukses atau tampilkan pesan
            echo "<script>
                    alert('Pendaftaran berhasil! Kami akan segera menghubungi Anda.');
                    window.location.href = 'index.php';
                  </script>";
        } else {
            // Gagal eksekusi query
            echo "Terjadi kesalahan: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Gagal prepare statement
        echo "Terjadi kesalahan sistem database.";
    }
    
    $conn->close();

} else {
    // Jika file diakses langsung tanpa submit form
    header("Location: index.php");
    exit();
}
?>
