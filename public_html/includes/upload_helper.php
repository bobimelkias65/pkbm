<?php
/**
 * Helper Keamanan & Optimasi Upload File
 * ======================================
 * Fitur:
 * 1. Validasi Keamanan (MIME Type & Ekstensi).
 * 2. Rename File (Hash Acak).
 * 3. [BARU] Otomatis Convert ke WebP untuk performa lebih cepat.
 * 4. [BARU] Kompresi Kualitas (80%).
 */

function uploadImageSecure($fileInput, $targetDir = '../assets/uploads/') {
    // 1. Cek Error Upload
    if ($fileInput['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error upload kode: " . $fileInput['error']);
    }

    // 2. Validasi Ukuran (Max 5MB karena akan dikompres)
    $maxSize = 5 * 1024 * 1024; 
    if ($fileInput['size'] > $maxSize) {
        throw new Exception("Ukuran file terlalu besar. Maksimal 5MB.");
    }

    // 3. Validasi Tipe File (MIME Type)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($fileInput['tmp_name']);
    
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($mime, $allowedMimes)) {
        throw new Exception("Format file tidak valid. Hanya JPG, PNG, GIF, dan WebP.");
    }

    // 4. Siapkan Nama File Baru (Ekstensi dipaksa jadi .webp)
    $newFileName = 'img_' . bin2hex(random_bytes(8)) . '.webp';
    $targetFile = $targetDir . $newFileName;

    // Buat folder jika belum ada
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // 5. PROSES KONVERSI KE WEBP (GD Library)
    $tmpName = $fileInput['tmp_name'];
    $image = null;

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($tmpName);
            break;
        case 'image/png':
            $image = imagecreatefrompng($tmpName);
            // Handle Transparansi PNG
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($tmpName);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($tmpName);
            break;
    }

    if ($image) {
        // Simpan sebagai WebP dengan Kualitas 80% (Keseimbangan terbaik size vs kualitas)
        if (imagewebp($image, $targetFile, 80)) {
            imagedestroy($image); // Bersihkan memori
            return $newFileName;
        } else {
            throw new Exception("Gagal mengkonversi gambar ke WebP.");
        }
    } else {
        throw new Exception("Gagal memproses gambar. Pastikan library GD aktif di server.");
    }
}
?>
