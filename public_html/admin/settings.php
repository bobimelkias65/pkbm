<?php
session_start();
require_once '../includes/db_connect.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';

// --- LOGIKA PHP UNTUK UPDATE ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Handle Upload Icon Website (Favicon)
    if (isset($_FILES['site_icon']) && $_FILES['site_icon']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileTmpPath = $_FILES['site_icon']['tmp_name'];
        $fileSize    = $_FILES['site_icon']['size'];

        // --- KEAMANAN: Validasi Ukuran File (Maks 2MB) ---
        if ($fileSize > 2 * 1024 * 1024) {
            $error = "Ukuran file ikon terlalu besar (Maks 2MB).";
        } else {
            // --- KEAMANAN: Validasi MIME Type ---
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($fileTmpPath);
            
            $allowedMimeTypes = [
                'image/x-icon' => 'ico',
                'image/vnd.microsoft.icon' => 'ico',
                'image/png'  => 'png',
                'image/jpeg' => 'jpg'
            ];

            if (!array_key_exists($mimeType, $allowedMimeTypes)) {
                $error = "File ikon tidak valid. Gunakan format .ico, .png, atau .jpg.";
            } else {
                $ext = $allowedMimeTypes[$mimeType];
                $newFileName = 'favicon_' . time() . '.' . $ext;
                $dest_path   = $uploadDir . $newFileName;

                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $iconPath = 'assets/uploads/' . $newFileName;
                    
                    // Simpan/Update ke database
                    $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('site_icon', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->bind_param("ss", $iconPath, $iconPath);
                    $stmt->execute();
                } else {
                    $error = "Gagal menyimpan file ikon.";
                }
            }
        }
    }

    // 2. Handle Text Settings Lainnya (hanya jika tidak ada error fatal pada upload)
    if (empty($error)) {
        $allowed_keys = [
            'site_name', 'site_tagline', 
            'address', 'phone', 'email', 
            'facebook_url', 'instagram_url', 'youtube_url'
        ];

        $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
        
        foreach ($allowed_keys as $key) {
            if (isset($_POST[$key])) {
                $value = $_POST[$key];
                $stmt->bind_param("ss", $value, $key);
                $stmt->execute();
            }
        }

        header("Location: settings.php?msg=saved");
        exit;
    }
}

// --- AMBIL DATA SAAT INI ---
$settings = [];
$result = $conn->query("SELECT * FROM site_settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Situs - Admin PKBM</title>
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
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Pengaturan Situs</h1>
                    <p class="text-slate-500 text-sm">Kelola informasi identitas, kontak, dan sosial media website.</p>
                </div>
            </div>

            <!-- Notifikasi -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'saved'): ?>
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    Pengaturan berhasil disimpan!
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Form dengan Enctype Multipart untuk Upload File -->
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-8 max-w-4xl">
                
                <!-- 1. Identitas Website -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-globe text-sky-600"></i> Identitas Website
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Situs / Lembaga</label>
                                <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition" required>
                                <p class="text-xs text-slate-400 mt-1">Muncul di Header, Footer, dan Judul Tab Browser.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tagline / Slogan</label>
                                <input type="text" name="site_tagline" value="<?= htmlspecialchars($settings['site_tagline'] ?? '') ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition" required>
                                <p class="text-xs text-slate-400 mt-1">Kalimat pendek di bawah logo atau judul.</p>
                            </div>
                        </div>

                        <!-- Kolom Upload Ikon Website -->
                        <div class="border-t border-slate-100 pt-6">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Favicon / Ikon Website</label>
                            <div class="flex items-start gap-6">
                                <div class="w-16 h-16 bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <?php if(!empty($settings['site_icon'])): ?>
                                        <!-- Cek path gambar lokal atau external -->
                                        <?php $iconSrc = (strpos($settings['site_icon'], 'assets/') === 0) ? '../'.$settings['site_icon'] : $settings['site_icon']; ?>
                                        <img src="<?= htmlspecialchars($iconSrc) ?>" alt="Icon" class="w-full h-full object-contain">
                                    <?php else: ?>
                                        <i class="fas fa-image text-slate-300 text-2xl"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="site_icon" accept=".ico,.png,.jpg,.jpeg" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 transition">
                                    <p class="text-xs text-slate-400 mt-2">Upload file gambar (ICO, PNG, JPG) untuk ikon di tab browser. Disarankan ukuran persegi (cth: 64x64 px). Maks 2MB.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Kontak & Alamat -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-address-card text-sky-600"></i> Kontak & Alamat
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Alamat Lengkap</label>
                            <textarea name="address" rows="2" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nomor Telepon / WhatsApp</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($settings['phone'] ?? '') ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Email Resmi</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($settings['email'] ?? '') ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Sosial Media -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-share-alt text-sky-600"></i> Sosial Media
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2 flex items-center gap-2">
                                    <i class="fab fa-facebook text-blue-600"></i> Link Facebook
                                </label>
                                <input type="text" name="facebook_url" value="<?= htmlspecialchars($settings['facebook_url'] ?? '#') ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition" placeholder="https://facebook.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2 flex items-center gap-2">
                                    <i class="fab fa-instagram text-pink-600"></i> Link Instagram
                                </label>
                                <input type="text" name="instagram_url" value="<?= htmlspecialchars($settings['instagram_url'] ?? '#') ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition" placeholder="https://instagram.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2 flex items-center gap-2">
                                    <i class="fab fa-youtube text-red-600"></i> Link YouTube
                                </label>
                                <input type="text" name="youtube_url" value="<?= htmlspecialchars($settings['youtube_url'] ?? '#') ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition" placeholder="https://youtube.com/...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-8 py-3 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg shadow-lg transform transition hover:-translate-y-1">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                    </button>
                </div>

            </form>
        </main>
    </div>

</body>
</html>