<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- LOGIKA PHP UNTUK CRUD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = $_POST['course_name'];
    $instructor  = $_POST['instructor'];
    $schedule    = $_POST['schedule'];
    $start_date  = $_POST['start_date']; 
    $description = $_POST['description'];
    $id          = $_POST['id'] ?? '';
    $error       = ''; // Variabel error
    
    // Handle Image Upload
    $image_url = $_POST['current_image_url'] ?? '';
    
    if (isset($_FILES['course_image']) && $_FILES['course_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileTmpPath = $_FILES['course_image']['tmp_name'];
        $fileName    = $_FILES['course_image']['name'];
        $fileSize    = $_FILES['course_image']['size'];

        // --- KEAMANAN: Validasi Ukuran File (Maks 2MB) ---
        if ($fileSize > 2 * 1024 * 1024) {
            $error = "Ukuran file terlalu besar (Maks 2MB).";
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
                $error = "File yang diupload bukan gambar yang valid (Hanya JPG, PNG, WEBP).";
            } else {
                // --- KEAMANAN: Generate Nama File Baru ---
                $ext = $allowedMimeTypes[$mimeType];
                $newFileName = 'course_' . time() . '.' . $ext;
                
                if(move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                    // Hapus file lama jika ada
                    if ($id && !empty($image_url) && strpos($image_url, 'assets/uploads/') !== false && file_exists('../' . $image_url)) {
                        unlink('../' . $image_url);
                    }
                    $image_url = 'assets/uploads/' . $newFileName;
                } else {
                    $error = "Gagal memindahkan file upload.";
                }
            }
        }
    }

    // Lanjut simpan jika tidak ada error
    if (empty($error)) {
        if ($id) {
            $stmt = $conn->prepare("UPDATE courses SET course_name=?, image_url=?, instructor=?, schedule=?, start_date=?, description=? WHERE id=?");
            $stmt->bind_param("ssssssi", $course_name, $image_url, $instructor, $schedule, $start_date, $description, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO courses (course_name, image_url, instructor, schedule, start_date, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $course_name, $image_url, $instructor, $schedule, $start_date, $description);
        }
        
        if ($stmt->execute()) {
            header("Location: courses.php?msg=saved");
            exit;
        } else {
            $error = "Gagal menyimpan data ke database.";
        }
    }
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    // Hapus file fisik jika ada
    $stmt = $conn->prepare("SELECT image_url FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (!empty($row['image_url']) && strpos($row['image_url'], 'assets/uploads/') !== false && file_exists('../' . $row['image_url'])) {
            unlink('../' . $row['image_url']);
        }
    }

    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: courses.php?msg=deleted");
        exit;
    }
}

$result = $conn->query("SELECT * FROM courses ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kursus - PKBM Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 md:ml-64 p-8">
            <!-- Header Mobile -->
            <div class="md:hidden mb-8 flex justify-between items-center">
                <h1 class="text-2xl font-bold">Kelola Kursus</h1>
                <a href="logout.php" class="text-red-500"><i class="fas fa-sign-out-alt text-xl"></i></a>
            </div>

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Data Kursus</h1>
                    <p class="text-slate-500 text-sm">Kelola kelas keterampilan dan pelatihan.</p>
                </div>
                <button onclick="openModal()" class="bg-pink-600 text-white px-5 py-2.5 rounded-lg font-bold shadow hover:bg-pink-700 flex gap-2 items-center">
                    <i class="fas fa-plus"></i> Tambah Kursus
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition group flex flex-col h-full">
                        <!-- Image Preview in Card -->
                        <div class="h-40 w-full bg-slate-100 relative overflow-hidden">
                            <?php if(!empty($row['image_url'])): ?>
                                <!-- Cek path gambar -->
                                <?php $imgSrc = (strpos($row['image_url'], 'assets/') === 0) ? '../'.$row['image_url'] : $row['image_url']; ?>
                                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="Course" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                            <?php else: ?>
                                <div class="flex items-center justify-center h-full text-slate-300">
                                    <i class="fas fa-image text-4xl"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="absolute top-2 right-2 flex gap-1">
                                <button onclick='editData(<?= json_encode($row) ?>)' class="bg-white/90 hover:bg-white text-blue-600 p-2 rounded-full shadow-sm text-xs transition"><i class="fas fa-edit"></i></button>
                                <a href="courses.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Hapus kursus?')" class="bg-white/90 hover:bg-white text-red-600 p-2 rounded-full shadow-sm text-xs transition"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <h3 class="text-lg font-bold text-slate-800 mb-2"><?= htmlspecialchars($row['course_name']) ?></h3>
                            <p class="text-sm text-slate-500 mb-4 flex-1 line-clamp-3"><?= htmlspecialchars($row['description']) ?></p>
                            
                            <div class="space-y-2 pt-4 border-t border-slate-100 text-xs text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user-tie w-4 text-center text-pink-500"></i>
                                    <span><?= htmlspecialchars($row['instructor']) ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock w-4 text-center text-pink-500"></i>
                                    <span><?= htmlspecialchars($row['schedule']) ?></span>
                                </div>
                                <?php if(!empty($row['start_date'])): ?>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-alt w-4 text-center text-pink-500"></i>
                                    <span>Mulai: <?= date('d M Y', strtotime($row['start_date'])) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-3 text-center py-12 text-slate-400">Belum ada data kursus.</div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal Form -->
    <div id="modalForm" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white z-10">
                <h3 class="text-xl font-bold text-slate-800" id="modalTitle">Tambah Kursus</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 text-2xl">&times;</button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                <input type="hidden" name="id" id="inputId">
                <input type="hidden" name="current_image_url" id="inputCurrentUrl">

                <!-- Image Upload -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Gambar Kursus</label>
                    <div class="flex items-center gap-4">
                        <div id="previewContainer" class="w-20 h-20 bg-slate-100 rounded-lg overflow-hidden hidden border border-slate-200">
                            <img id="previewImage" src="" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/150?text=No+Image'">
                        </div>
                        <div class="flex-1">
                            <input type="file" name="course_image" accept="image/*" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                            <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, WEBP (Maks 2MB). Rasio 16:9.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nama Kursus</label>
                    <input type="text" name="course_name" id="inputName" placeholder="Contoh: Komputer Dasar" required class="w-full border border-slate-300 rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Instruktur</label>
                        <input type="text" name="instructor" id="inputInstructor" class="w-full border border-slate-300 rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="inputStartDate" class="w-full border border-slate-300 rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Jadwal Mingguan</label>
                    <input type="text" name="schedule" id="inputSchedule" placeholder="Contoh: Senin & Kamis, 14.00 WIT" class="w-full border border-slate-300 rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="inputDesc" rows="3" class="w-full border border-slate-300 rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-pink-500"></textarea>
                </div>

                <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold transition">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg font-bold shadow transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalForm');
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');

        function openModal() { 
            document.querySelector('form').reset(); 
            document.getElementById('inputId').value=''; 
            document.getElementById('inputCurrentUrl').value='';
            previewContainer.classList.add('hidden');
            document.getElementById('modalTitle').innerText='Tambah Kursus'; 
            modal.classList.remove('hidden'); 
            modal.classList.add('flex'); 
        }
        
        function closeModal() { 
            modal.classList.add('hidden'); 
            modal.classList.remove('flex'); 
        }
        
        function editData(data) {
            document.getElementById('modalTitle').innerText='Edit Kursus';
            document.getElementById('inputId').value=data.id;
            document.getElementById('inputName').value=data.course_name;
            document.getElementById('inputInstructor').value=data.instructor;
            document.getElementById('inputSchedule').value=data.schedule;
            document.getElementById('inputStartDate').value=data.start_date;
            document.getElementById('inputDesc').value=data.description;
            document.getElementById('inputCurrentUrl').value=data.image_url;

            if(data.image_url) {
                let src = data.image_url.startsWith('assets/') ? '../'+data.image_url : data.image_url;
                previewImage.src = src;
                previewContainer.classList.remove('hidden');
            } else {
                previewContainer.classList.add('hidden');
            }

            modal.classList.remove('hidden'); 
            modal.classList.add('flex');
        }
        
        window.onclick = e => { if(e.target == modal) closeModal(); }
    </script>
</body>
</html>