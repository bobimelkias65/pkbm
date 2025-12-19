<?php
/**
 * Helper Keamanan untuk Upload File
 * =================================
 * Fungsi ini digunakan untuk menggantikan move_uploaded_file() standar.
 * Mencegah serangan Shell Upload, Double Extension, dan Null Byte.
 * * Cara Pakai di Admin:
 * require_once '../includes/upload_helper.php';
 * try {
 * $nama_file = uploadImageSecure($_FILES['gambar_kursus'], '../assets/uploads/');
 * // Simpan $nama_file ke database
 * } catch (Exception $e) {
 * echo "Error: " . $e->getMessage();
 * }
 */

function uploadImageSecure($fileInput, $targetDir = '../assets/uploads/') {
    // 1. Cek Error Upload dari PHP
    if ($fileInput['error'] !== UPLOAD_ERR_OK) {
        switch ($fileInput['error']) {
            case UPLOAD_ERR_INI_SIZE:
                throw new Exception("Ukuran file melebihi batas server (upload_max_filesize).");
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception("Ukuran file melebihi batas form (MAX_FILE_SIZE).");
            case UPLOAD_ERR_PARTIAL:
                throw new Exception("File hanya terupload sebagian.");
            case UPLOAD_ERR_NO_FILE:
                throw new Exception("Tidak ada file yang diupload.");
            default:
                throw new Exception("Terjadi error sistem saat upload.");
        }
    }

    // 2. Validasi Ukuran File (Contoh: Maks 2MB)
    $maxSize = 2 * 1024 * 1024; // 2MB dalam bytes
    if ($fileInput['size'] > $maxSize) {
        throw new Exception("Ukuran file terlalu besar. Maksimal 2MB.");
    }

    // 3. Validasi Tipe File (MIME Type & Ekstensi)
    // Whitelist ekstensi yang aman
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    // Whitelist MIME type yang aman
    $allowedMimes = [
        'image/jpeg', 
        'image/png', 
        'image/gif', 
        'image/webp'
    ];

    // Cek MIME Type Asli menggunakan FileInfo (Lebih aman dari $_FILES['type'])
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($fileInput['tmp_name']);

    if (!in_array($mime, $allowedMimes)) {
        throw new Exception("Format file tidak valid. Terdeteksi: " . $mime);
    }

    // Cek Ekstensi File
    $ext = strtolower(pathinfo($fileInput['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        throw new Exception("Ekstensi file tidak diizinkan.");
    }

    // 4. Sanitasi & Rename File (Sangat Penting!)
    // Jangan gunakan nama asli user. Gunakan hash unik.
    // Contoh output: img_64a7b189c.png
    $newFileName = 'img_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $targetFile = $targetDir . $newFileName;

    // 5. Pastikan folder tujuan ada
    if (!is_dir($targetDir)) {
        // Buat folder jika belum ada (permission 0755)
        mkdir($targetDir, 0755, true);
    }

    // 6. Pindahkan File
    if (move_uploaded_file($fileInput['tmp_name'], $targetFile)) {
        return $newFileName; // Kembalikan nama file baru untuk disimpan di DB
    } else {
        throw new Exception("Gagal memindahkan file ke folder tujuan. Cek permission folder.");
    }
}
?>
