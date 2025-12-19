<?php
session_start();
require_once '../includes/db_connect.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- LOGIKA PHP UNTUK CRUD ---

// 1. Tambah / Edit Data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = $_POST['title'];
    $size_type  = $_POST['size_type'];
    $id         = isset($_POST['id']) ? $_POST['id'] : '';
    
    // Ambil URL lama (jika ada) sebagai default
    $image_url  = isset($_POST['current_image_url']) ? $_POST['current_image_url'] : '';
    $error      = ''; // Variabel untuk menampung pesan error

    // Cek jika ada file yang diupload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/';
        
        // Buat folder jika belum ada
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmpPath   = $_FILES['image_file']['tmp_name'];
        $fileName      = $_FILES['image_file']['name'];
        $fileSize      = $_FILES['image_file']['size'];
        
        // --- KEAMANAN: Validasi Ukuran File (Maks 2MB) ---
        if ($fileSize > 2 * 1024 * 1024) {
            $error = "Ukuran file terlalu besar (Maks 2MB).";
        } else {
            // --- KEAMANAN: Validasi MIME Type (Bukan hanya ekstensi) ---
            // Menggunakan finfo untuk memeriksa tipe file yang sebenarnya
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($fileTmpPath);
            
            // Daftar MIME type yang diizinkan dan ekstensinya
            $allowedMimeTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif'
            ];

            if (!array_key_exists($mimeType, $allowedMimeTypes)) {
                $error = "File yang diupload bukan gambar yang valid atau format tidak didukung.";
            } else {
                // --- KEAMANAN: Generate Nama File Baru yang Aman ---
                // Gunakan ekstensi dari daftar yang diizinkan, bukan dari input user
                $ext = $allowedMimeTypes[$mimeType]; 
                $newFileName = md5(uniqid(rand(), true)) . '.' . $ext;
                $dest_path   = $uploadDir . $newFileName;

                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Jika ada foto lama (upload lokal), hapus dulu untuk hemat space
                    if ($id && !empty($image_url) && strpos($image_url, 'assets/uploads/') !== false && file_exists('../' . $image_url)) {
                        unlink('../' . $image_url);
                    }

                    // Simpan path relatif untuk database
                    $image_url = 'assets/uploads/' . $newFileName;
                } else {
                    $error = "Gagal memindahkan file upload.";
                }
            }
        }
    } 
    // Jika tidak ada file upload, cek apakah user input URL manual
    elseif (isset($_POST['image_url_text']) && !empty($_POST['image_url_text'])) {
        // Sanitasi URL inputan user
        $image_url = filter_var($_POST['image_url_text'], FILTER_SANITIZE_URL);
    }

    // Lanjut simpan ke DB jika tidak ada error
    if (empty($error)) {
        if ($id) {
            // Update Data Existing
            $stmt = $conn->prepare("UPDATE gallery SET title=?, image_url=?, size_type=? WHERE id=?");
            $stmt->bind_param("sssi", $title, $image_url, $size_type, $id);
        } else {
            // Insert Data Baru
            $stmt = $conn->prepare("INSERT INTO gallery (title, image_url, size_type) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $image_url, $size_type);
        }

        if ($stmt->execute()) {
            header("Location: gallery.php?msg=saved");
            exit;
        } else {
            $error = "Gagal menyimpan data ke database.";
        }
    }
}

// 2. Hapus Data
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    // (Opsional) Hapus file fisik jika itu file upload lokal
    $stmt = $conn->prepare("SELECT image_url FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $file_path = '../' . $row['image_url'];
        // Pastikan hanya menghapus file di dalam folder uploads kita
        if (file_exists($file_path) && strpos($row['image_url'], 'assets/uploads/') !== false) {
            unlink($file_path); 
        }
    }

    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: gallery.php?msg=deleted");
        exit;
    }
}

// 3. Ambil Data untuk Ditampilkan
$result = $conn->query("SELECT * FROM gallery ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Galeri - Admin PKBM</title>
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
            <!-- Header Mobile -->
            <div class="md:hidden mb-8 flex justify-between items-center">
                <h1 class="text-2xl font-bold">Kelola Galeri</h1>
                <a href="logout.php" class="text-red-500"><i class="fas fa-sign-out-alt text-xl"></i></a>
            </div>

            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Kelola Galeri Foto</h1>
                    <p class="text-slate-500 text-sm">Upload dan atur dokumentasi kegiatan PKBM.</p>
                </div>
                <button onclick="openModal()" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-lg font-bold shadow-md transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Foto
                </button>
            </div>

            <!-- Notifikasi -->
            <?php if (isset($_GET['msg'])): ?>
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <?= $_GET['msg'] == 'saved' ? 'Data berhasil disimpan!' : 'Data berhasil dihapus!' ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Gallery Grid / Table -->
             <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs tracking-wider">
                            <th class="p-4 font-bold">Preview</th>
                            <th class="p-4 font-bold">Judul Foto</th>
                            <th class="p-4 font-bold">Ukuran Grid</th>
                            <th class="p-4 font-bold">Tanggal</th>
                            <th class="p-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                // Cek apakah URL external atau upload lokal
                                $is_local = strpos($row['image_url'], 'assets/') === 0;
                                $display_src = $is_local ? '../' . $row['image_url'] : $row['image_url'];
                            ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4">
                                    <div class="w-24 h-16 rounded-lg overflow-hidden border border-slate-200 bg-slate-100">
                                        <img src="<?= htmlspecialchars($display_src) ?>" alt="Preview" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/150?text=No+Image'">
                                    </div>
                                </td>
                                <td class="p-4 font-bold text-slate-800">
                                    <?= htmlspecialchars($row['title']) ?>
                                    <div class="text-xs text-slate-400 font-normal mt-1 truncate max-w-[200px]">
                                        <?= htmlspecialchars($row['image_url']) ?>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                                        <?php 
                                            if($row['size_type']=='small') echo 'bg-slate-100 text-slate-600';
                                            elseif($row['size_type']=='large') echo 'bg-purple-100 text-purple-700';
                                            elseif($row['size_type']=='wide') echo 'bg-blue-100 text-blue-700';
                                        ?>">
                                        <?= $row['size_type'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-slate-500">
                                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                                </td>
                                <td class="p-4 text-center">
                                    <button onclick='editData(<?= json_encode($row) ?>)' class="text-blue-600 hover:text-blue-800 mx-1 p-2 bg-blue-50 rounded hover:bg-blue-100 transition">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="gallery.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus foto ini?')" class="text-red-600 hover:text-red-800 mx-1 p-2 bg-red-50 rounded hover:bg-red-100 transition">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="p-8 text-center text-slate-400">Belum ada foto di galeri.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- MODAL FORM -->
    <div id="modalForm" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl transform transition-all scale-100">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-800" id="modalTitle">Tambah Foto Baru</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 text-2xl">&times;</button>
            </div>
            
            <!-- Tambahkan enctype="multipart/form-data" untuk support file upload -->
            <form method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-4">
                <input type="hidden" name="id" id="inputId">
                <input type="hidden" name="current_image_url" id="inputCurrentUrl">
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Judul Foto / Kegiatan</label>
                    <input type="text" name="title" id="inputTitle" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none" placeholder="Contoh: Kegiatan Belajar Outdoor">
                </div>

                <!-- Opsi 1: Upload File -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Upload Foto (Dari Komputer)</label>
                    <input type="file" name="image_file" accept="image/*" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                    <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, WEBP. Maks 2MB.</p>
                </div>

                <div class="text-center text-xs text-slate-400 font-bold border-b border-slate-100 leading-0">
                    <span class="bg-white px-2">ATAU</span>
                </div>

                <!-- Opsi 2: URL Eksternal (Backup) -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Gunakan Link Gambar (Opsional)</label>
                    <input type="text" name="image_url_text" id="inputUrlText" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none" placeholder="https://...">
                </div>

                <!-- Preview Gambar Lama (Saat Edit) -->
                <div id="previewContainer" class="hidden">
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Foto Saat Ini:</label>
                    <div class="w-24 h-16 rounded-lg overflow-hidden bg-slate-100 border border-slate-200">
                         <img id="previewImage" src="" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/150?text=No+Image'">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Ukuran Grid (Tampilan)</label>
                    <select name="size_type" id="inputType" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="small">Small (Kecil - 1 Kotak)</option>
                        <option value="large">Large (Besar - 2x2 Kotak)</option>
                        <option value="wide">Wide (Lebar - 2x1 Kotak)</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg font-bold">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg font-bold shadow-lg">Simpan Foto</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalForm');
        const modalTitle = document.getElementById('modalTitle');
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');

        function openModal() {
            document.querySelector('form').reset();
            document.getElementById('inputId').value = ''; 
            document.getElementById('inputCurrentUrl').value = ''; 
            previewContainer.classList.add('hidden');
            
            modalTitle.innerText = "Tambah Foto Baru";
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function editData(data) {
            modalTitle.innerText = "Edit Foto";
            document.getElementById('inputId').value = data.id;
            document.getElementById('inputTitle').value = data.title;
            document.getElementById('inputType').value = data.size_type;
            document.getElementById('inputCurrentUrl').value = data.image_url;
            
            // Jika URL bukan file lokal, tampilkan di kolom teks juga
            if(!data.image_url.startsWith('assets/')) {
                document.getElementById('inputUrlText').value = data.image_url;
            }

            // Tampilkan Preview
            let src = data.image_url;
            if(src.startsWith('assets/')) {
                src = '../' + src;
            }
            previewImage.src = src;
            previewContainer.classList.remove('hidden');

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>