<?php
// Mendapatkan nama file script yang sedang berjalan
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-slate-900 text-white hidden md:block fixed h-full z-10 overflow-y-auto">
    <div class="h-20 flex items-center px-6 border-b border-slate-800">
        <span class="text-xl font-bold tracking-tight">PKBM Admin</span>
    </div>
    <nav class="p-4 space-y-2">
        <!-- Dashboard: Link ke ./ agar index.php tidak muncul -->
        <a href="./" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?= ($current_page == 'index.php') ? 'bg-sky-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
            <i class="fas fa-home w-5 text-center"></i>
            <span class="font-medium">Dashboard</span>
        </a>
        
        <div class="pt-4 pb-2 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Master Data</div>
        
        <a href="students.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?= ($current_page == 'students.php') ? 'bg-sky-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
            <i class="fas fa-user-graduate w-5 text-center"></i>
            <span class="font-medium">Data Siswa</span>
        </a>
        <a href="courses.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?= ($current_page == 'courses.php') ? 'bg-sky-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
            <i class="fas fa-chalkboard-teacher w-5 text-center"></i>
            <span class="font-medium">Data Kursus</span>
        </a>
        <a href="programs.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?= ($current_page == 'programs.php') ? 'bg-sky-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
            <i class="fas fa-book w-5 text-center"></i>
            <span class="font-medium">Program</span>
        </a>

        <div class="pt-4 pb-2 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Konten Web</div>

        <a href="gallery.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?= ($current_page == 'gallery.php') ? 'bg-sky-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
            <i class="fas fa-images w-5 text-center"></i>
            <span class="font-medium">Galeri</span>
        </a>
        <a href="inquiries.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?= ($current_page == 'inquiries.php') ? 'bg-sky-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
            <i class="fas fa-inbox w-5 text-center"></i>
            <span class="font-medium">Pesan Masuk</span>
            <?php 
            // Cek variable global count_inbox
            if(isset($GLOBALS['count_inbox']) && $GLOBALS['count_inbox'] > 0): 
            ?>
            <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $GLOBALS['count_inbox'] ?></span>
            <?php endif; ?>
        </a>
        <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?= ($current_page == 'settings.php') ? 'bg-sky-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
            <i class="fas fa-cog w-5 text-center"></i>
            <span class="font-medium">Pengaturan</span>
        </a>
        
        <div class="pt-4 mt-4 border-t border-slate-800">
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-slate-800 rounded-lg transition">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span class="font-medium">Keluar</span>
            </a>
        </div>
    </nav>
</aside>