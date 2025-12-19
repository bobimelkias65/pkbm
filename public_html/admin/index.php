<?php
session_start();
require_once '../includes/db_connect.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Mengambil Statistik Ringkas
$count_programs = $conn->query("SELECT COUNT(*) as total FROM programs")->fetch_assoc()['total'];
$count_gallery  = $conn->query("SELECT COUNT(*) as total FROM gallery")->fetch_assoc()['total'];
$count_inbox    = $conn->query("SELECT COUNT(*) as total FROM inquiries")->fetch_assoc()['total'];

// Membuat variabel global agar bisa dibaca oleh sidebar.php
$GLOBALS['count_inbox'] = $count_inbox;

// Statistik Baru: Siswa & Kursus (Gunakan try-catch agar tidak error jika tabel belum dibuat)
$count_students = 0;
$count_courses = 0;
try {
    $res_stu = $conn->query("SELECT COUNT(*) as total FROM students");
    if($res_stu) $count_students = $res_stu->fetch_assoc()['total'];
    
    $res_cour = $conn->query("SELECT COUNT(*) as total FROM courses");
    if($res_cour) $count_courses = $res_cour->fetch_assoc()['total'];
} catch (Exception $e) { /* Ignore if table not exists */ }

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PKBM Harapan Kasih</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

    <div class="flex min-h-screen">
        <!-- Memanggil Sidebar dari file terpisah -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-8">
            <!-- Header Mobile -->
            <div class="md:hidden mb-8 flex justify-between items-center">
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <a href="logout.php" class="text-red-500"><i class="fas fa-sign-out-alt text-xl"></i></a>
            </div>

            <!-- Welcome Banner -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 mb-8 flex flex-col-reverse md:flex-row justify-between items-center gap-6">
                
                <!-- Teks Sambutan -->
                <div class="text-center md:text-left">
                    <h1 class="text-2xl font-bold text-slate-800 mb-2">
                        Selamat Datang, <a href="profile.php" class="text-sky-600 hover:underline"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></a>! 👋
                    </h1>
                    <p class="text-slate-500">Ini adalah panel kontrol utama untuk mengelola konten website PKBM Harapan Kasih.</p>
                </div>

                <!-- Gambar Profil -->
                <a href="profile.php" class="flex-shrink-0 group relative" title="Ke Profil Saya">
                    <div class="w-20 h-20 rounded-full p-1 border-2 border-sky-100 group-hover:border-sky-300 transition-all duration-300">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_lengkap']) ?>&background=0ea5e9&color=fff&size=128&bold=true" 
                             alt="Profil" 
                             class="w-full h-full rounded-full object-cover shadow-sm group-hover:shadow-md transition">
                    </div>
                    <div class="absolute bottom-0 right-0 bg-white text-slate-600 rounded-full w-7 h-7 flex items-center justify-center shadow-md border border-slate-100 group-hover:bg-sky-600 group-hover:text-white transition">
                        <i class="fas fa-pen text-xs"></i>
                    </div>
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Card Siswa -->
                <a href="students.php" class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-slate-500 font-medium">Data Siswa</div>
                        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-slate-800"><?= $count_students ?></div>
                    <p class="text-xs text-slate-400 mt-2">Paket A, B, & C</p>
                </a>

                <!-- Card Kursus -->
                <a href="courses.php" class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-slate-500 font-medium">Kursus</div>
                        <div class="w-10 h-10 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-slate-800"><?= $count_courses ?></div>
                    <p class="text-xs text-slate-400 mt-2">Kelas Keterampilan</p>
                </a>

                <!-- Card Gallery -->
                <a href="gallery.php" class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-slate-500 font-medium">Galeri Foto</div>
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fas fa-images"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-slate-800"><?= $count_gallery ?></div>
                    <p class="text-xs text-slate-400 mt-2">Dokumentasi</p>
                </a>

                <!-- Card Inbox -->
                <a href="inquiries.php" class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-slate-500 font-medium">Pesan Masuk</div>
                        <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fas fa-inbox"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-slate-800"><?= $count_inbox ?></div>
                    <p class="text-xs text-slate-400 mt-2">Pendaftaran Baru</p>
                </a>
            </div>

            <!-- Quick Info Website -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Informasi Website</h3>
                    <a href="../" target="_blank" class="text-sm text-sky-600 hover:underline">Lihat Website <i class="fas fa-external-link-alt ml-1"></i></a>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Nama Situs</label>
                            <p class="font-medium text-slate-800"><?= get_setting('site_name') ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Tagline</label>
                            <p class="font-medium text-slate-800"><?= get_setting('site_tagline') ?></p>
                        </div>
                        <div class="md:col-span-2 pt-2">
                             <a href="settings.php" class="text-sm font-bold text-sky-600 hover:text-sky-800">Ubah Informasi <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>