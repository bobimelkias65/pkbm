<?php
session_start();
require_once '../includes/db_connect.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success_msg = '';
$error_msg = '';
$user_id = $_SESSION['user_id'];

// --- LOGIKA UPDATE PROFIL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi input
    $nama_lengkap = htmlspecialchars(trim($_POST['nama_lengkap']));
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Handle Upload Foto Profil
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/users/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileSize    = $_FILES['avatar']['size'];

        // --- KEAMANAN: Validasi Ukuran File (Maks 2MB) ---
        if ($fileSize > 2 * 1024 * 1024) {
            $error_msg = "Ukuran file foto terlalu besar (Maks 2MB).";
        } else {
            // --- KEAMANAN: Validasi MIME Type ---
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($fileTmpPath);
            
            $allowedMimeTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            if (!array_key_exists($mimeType, $allowedMimeTypes)) {
                $error_msg = "File yang diupload bukan gambar yang valid (Hanya JPG, PNG, WEBP).";
            } else {
                // --- KEAMANAN: Generate Nama File Baru ---
                $ext = $allowedMimeTypes[$mimeType];
                $newFileName = 'admin_' . $user_id . '_' . time() . '.' . $ext;
                $dest_path   = $uploadDir . $newFileName;

                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Hapus foto lama jika ada
                    $stmt_old = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
                    $stmt_old->bind_param("i", $user_id);
                    $stmt_old->execute();
                    $res_old = $stmt_old->get_result();
                    if ($res_old->num_rows > 0) {
                        $old_avatar = $res_old->fetch_assoc()['avatar'];
                        if ($old_avatar && file_exists('../' . $old_avatar)) {
                            unlink('../' . $old_avatar);
                        }
                    }

                    $avatarPath = 'assets/uploads/users/' . $newFileName;
                    $stmt_img = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $stmt_img->bind_param("si", $avatarPath, $user_id);
                    $stmt_img->execute();
                } else {
                    $error_msg = "Gagal menyimpan foto profil.";
                }
            }
        }
    }

    // 2. Update Nama Lengkap (jika tidak ada error foto)
    if (empty($error_msg)) {
        if (empty($nama_lengkap)) {
            $error_msg = "Nama lengkap tidak boleh kosong.";
        } else {
            $stmt_update = $conn->prepare("UPDATE users SET nama_lengkap = ? WHERE id = ?");
            $stmt_update->bind_param("si", $nama_lengkap, $user_id);
            if ($stmt_update->execute()) {
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $success_msg = "Profil berhasil diperbarui.";
            } else {
                $error_msg = "Gagal memperbarui profil.";
            }
        }
    }

    // 3. Logika Ganti Password (jika tidak ada error sebelumnya)
    if (empty($error_msg) && (!empty($current_password) || !empty($new_password))) {
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
             $error_msg = "Mohon lengkapi semua kolom jika ingin mengganti password.";
        } else {
            $stmt_pwd = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt_pwd->bind_param("i", $user_id);
            $stmt_pwd->execute();
            $stored_password = $stmt_pwd->get_result()->fetch_assoc()['password'];

            if (password_verify($current_password, $stored_password)) {
                if ($new_password === $confirm_password) {
                    // Validasi kekuatan password sederhana (min 6 karakter)
                    if (strlen($new_password) < 6) {
                        $error_msg = "Password baru minimal 6 karakter.";
                    } else {
                        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt_update_pwd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt_update_pwd->bind_param("si", $new_hash, $user_id);
                        
                        if ($stmt_update_pwd->execute()) {
                            $success_msg = "Password berhasil diubah!";
                        } else {
                            $error_msg = "Gagal mengubah password.";
                        }
                    }
                } else {
                    $error_msg = "Konfirmasi password baru tidak cocok.";
                }
            } else {
                $error_msg = "Password saat ini salah.";
            }
        }
    }
}

// Ambil data user terbaru untuk ditampilkan
$stmt = $conn->prepare("SELECT username, nama_lengkap, avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - PKBM Harapan Kasih</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-8">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-2xl font-bold text-slate-800 mb-2">Profil Saya</h1>
                <p class="text-slate-500 text-sm mb-8">Kelola informasi akun, foto profil, dan kata sandi Anda.</p>

                <?php if ($success_msg): ?>
                    <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <!-- Header Profil -->
                    <div class="p-6 border-b border-slate-100 bg-slate-50">
                        <div class="flex items-center gap-6">
                            <div class="relative group">
                                <div class="w-20 h-20 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center text-3xl font-bold overflow-hidden border-4 border-white shadow-md">
                                    <?php if (!empty($current_user['avatar'])): ?>
                                        <!-- Cek path gambar lokal atau external -->
                                        <?php $avatarSrc = (strpos($current_user['avatar'], 'assets/') === 0) ? '../'.$current_user['avatar'] : $current_user['avatar']; ?>
                                        <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="Profile" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <!-- Default Avatar (Inisial Nama) -->
                                        <?= strtoupper(substr($current_user['nama_lengkap'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <h2 class="font-bold text-xl text-slate-800"><?= htmlspecialchars($current_user['nama_lengkap']) ?></h2>
                                <p class="text-slate-500 text-sm">Username: <span class="font-mono bg-slate-200 px-2 py-0.5 rounded text-slate-600"><?= htmlspecialchars($current_user['username']) ?></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Edit -->
                    <form method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-6">
                        
                        <!-- Ganti Foto -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Ganti Foto Profil</label>
                            <input type="file" name="avatar" accept="image/*" class="block w-full text-sm text-slate-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-sky-50 file:text-sky-700
                                hover:file:bg-sky-100
                                transition
                            "/>
                            <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, WEBP (Maks 2MB)</p>
                        </div>

                        <hr class="border-slate-100">

                        <!-- Update Nama -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($current_user['nama_lengkap']) ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition" required>
                        </div>

                        <hr class="border-slate-100">

                        <!-- Ganti Password -->
                        <div>
                            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-lock text-slate-400"></i> Ganti Password
                            </h3>
                            <div class="space-y-4 bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-600 mb-1">Password Saat Ini</label>
                                    <input type="password" name="current_password" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition bg-white" placeholder="Kosongkan jika tidak ingin mengubah password">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-600 mb-1">Password Baru</label>
                                        <input type="password" name="new_password" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-600 mb-1">Konfirmasi Password Baru</label>
                                        <input type="password" name="confirm_password" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition bg-white">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-8 py-3 rounded-lg font-bold shadow-md transition transform hover:-translate-y-0.5 flex items-center gap-2">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>