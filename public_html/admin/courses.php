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

// 3. Handle Form Submission (Tambah Kursus)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_course'])) {
    
    // Validasi CSRF Token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Token Validasi Gagal. Silakan refresh halaman.");
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    // Validasi Input
    if (empty($title) || empty($description)) {
        $error = "Judul dan Deskripsi wajib diisi.";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
        $error = "Silakan pilih gambar sampul kursus.";
    } else {
        try {
            // A. UPLOAD AMAN
            $new_filename = uploadImageSecure($_FILES['image'], '../assets/uploads/');

            // B. SIMPAN KE DATABASE (Prepared Statement)
            // Asumsi tabel 'courses' memiliki kolom: id, title, description, image, created_at
            $stmt = $conn->prepare("INSERT INTO courses (title, description, image, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $title, $description, $new_filename);

            if ($stmt->execute()) {
                $message = "Kursus berhasil ditambahkan!";
            } else {
                // Hapus gambar jika gagal simpan ke DB
                unlink('../assets/uploads/' . $new_filename);
                $error = "Gagal menyimpan data ke database.";
            }
            $stmt->close();

        } catch (Exception $e) {
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

    // Ambil nama file gambar sebelum menghapus record
    $stmt = $conn->prepare("SELECT image FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $file_path = '../assets/uploads/' . $row['image'];
        
        // Hapus record dari DB
        $del_stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        
        if ($del_stmt->execute()) {
            // Hapus file fisik
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $message = "Kursus berhasil dihapus.";
        } else {
            $error = "Gagal menghapus data.";
        }
        $del_stmt->close();
    }
    $stmt->close();
}

// 5. Ambil Data Kursus
$result = $conn->query("SELECT * FROM courses ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kursus - Admin PKBM</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container { display: flex; }
        .main-content { flex: 1; padding: 20px; }
        .form-upload { background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #ddd; max-width: 600px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"], 
        .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-submit { background: #2ecc71; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
        
        /* Grid Layout untuk Kursus */
        .course-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .course-item { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .course-item img { width: 100%; height: 150px; object-fit: cover; }
        .course-content { padding: 15px; }
        .course-content h4 { margin: 0 0 10px; color: #2c3e50; }
        .course-content p { font-size: 14px; color: #666; line-height: 1.5; margin-bottom: 15px; }
        .btn-delete { background: #e74c3c; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px; width: 100%; }
        .btn-delete:hover { background: #c0392b; }
        
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="admin-container">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h2>Manajemen Kursus & Program</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Form Tambah Kursus -->
        <div class="form-upload">
            <h3>Tambah Kursus Baru</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="add_course" value="1">

                <div class="form-group">
                    <label>Judul Kursus</label>
                    <input type="text" name="title" required placeholder="Contoh: Paket C Setara SMA">
                </div>

                <div class="form-group">
                    <label>Deskripsi Singkat</label>
                    <textarea name="description" rows="4" required placeholder="Jelaskan detail kursus..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Gambar Sampul (Max 2MB)</label>
                    <input type="file" name="image" required accept="image/png, image/jpeg, image/jpg">
                </div>

                <button type="submit" class="btn-submit">Simpan Kursus</button>
            </form>
        </div>

        <hr>

        <h3>Daftar Kursus Aktif</h3>
        <div class="course-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="course-item">
                        <!-- Tampilkan Gambar -->
                        <img src="../assets/uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                        
                        <div class="course-content">
                            <!-- Judul & Deskripsi Aman -->
                            <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                            <p><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></p>
                            
                            <!-- Tombol Hapus Aman -->
                            <form action="" method="POST" onsubmit="return confirm('Yakin ingin menghapus kursus ini?');">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn-delete">Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Belum ada data kursus.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
