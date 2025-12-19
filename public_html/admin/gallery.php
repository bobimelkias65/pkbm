<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/upload_helper.php';

// 1. Cek Login Admin (Security Guard)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Generate CSRF Token (Anti-Hack)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";
$error = "";

// 3. Handle Form Submission (Tambah Gambar)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_gallery'])) {
    
    // Validasi CSRF Token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Token Validasi Gagal. Silakan refresh halaman.");
    }

    $caption = trim($_POST['caption']);

    // Validasi Input
    if (empty($caption)) {
        $error = "Caption/Judul gambar wajib diisi.";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
        $error = "Silakan pilih gambar untuk diupload.";
    } else {
        try {
            // A. UPLOAD AMAN MENGGUNAKAN HELPER
            // Fungsi ini akan melempar Exception jika upload tidak valid
            $new_filename = uploadImageSecure($_FILES['image'], '../assets/uploads/');

            // B. SIMPAN KE DATABASE (Prepared Statement)
            $stmt = $conn->prepare("INSERT INTO gallery (image, caption, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $new_filename, $caption);

            if ($stmt->execute()) {
                $message = "Gambar berhasil ditambahkan ke galeri!";
            } else {
                // Jika insert DB gagal, hapus gambar yang baru diupload agar tidak jadi sampah
                unlink('../assets/uploads/' . $new_filename);
                $error = "Gagal menyimpan data ke database.";
            }
            $stmt->close();

        } catch (Exception $e) {
            // Tangkap error dari upload_helper
            $error = "Gagal Upload: " . $e->getMessage();
        }
    }
}

// 4. Handle Delete Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    
    // Validasi CSRF Token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Token Validasi Gagal.");
    }

    $id = filter_var($_POST['delete_id'], FILTER_SANITIZE_NUMBER_INT);

    // Ambil nama file dulu untuk dihapus dari folder
    $stmt = $conn->prepare("SELECT image FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $file_path = '../assets/uploads/' . $row['image'];
        
        // Hapus data dari DB
        $del_stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        
        if ($del_stmt->execute()) {
            // Hapus file fisik jika ada
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $message = "Gambar berhasil dihapus.";
        } else {
            $error = "Gagal menghapus data.";
        }
        $del_stmt->close();
    }
    $stmt->close();
}

// 5. Ambil Data Galeri untuk Ditampilkan
$result = $conn->query("SELECT * FROM gallery ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Galeri - Admin PKBM</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* CSS Sederhana untuk Admin Gallery */
        .admin-container { display: flex; }
        .main-content { flex: 1; padding: 20px; }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
        .gallery-item { border: 1px solid #ddd; padding: 10px; border-radius: 5px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .gallery-item img { width: 100%; height: 150px; object-fit: cover; border-radius: 4px; }
        .gallery-item p { margin: 10px 0; font-size: 14px; color: #333; }
        .btn-delete { background: #e74c3c; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px; width: 100%; }
        .btn-delete:hover { background: #c0392b; }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .form-upload { background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #ddd; max-width: 500px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-submit { background: #2ecc71; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
    </style>
</head>
<body>

<div class="admin-container">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h2>Manajemen Galeri Foto</h2>

        <!-- Tampilkan Pesan Error/Sukses -->
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Form Upload -->
        <div class="form-upload">
            <h3>Tambah Foto Baru</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <!-- CSRF Token Wajib Ada -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="add_gallery" value="1">

                <div class="form-group">
                    <label>Caption / Judul Foto</label>
                    <input type="text" name="caption" required placeholder="Contoh: Kegiatan Belajar Outdoor">
                </div>
                
                <div class="form-group">
                    <label>Pilih Foto (Max 2MB, JPG/PNG)</label>
                    <input type="file" name="image" required accept="image/png, image/jpeg, image/jpg">
                </div>

                <button type="submit" class="btn-submit">Upload Foto</button>
            </form>
        </div>

        <hr>

        <!-- Daftar Galeri -->
        <h3>Daftar Foto Saat Ini</h3>
        <div class="gallery-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="gallery-item">
                        <!-- Tampilkan Gambar -->
                        <img src="../assets/uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Gallery Image">
                        
                        <!-- Tampilkan Caption Aman (XSS Prevention) -->
                        <p><?php echo htmlspecialchars($row['caption']); ?></p>
                        
                        <!-- Tombol Hapus Aman (CSRF Prevention) -->
                        <form action="" method="POST" onsubmit="return confirm('Yakin ingin menghapus foto ini?');">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn-delete">Hapus</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Belum ada foto di galeri.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
