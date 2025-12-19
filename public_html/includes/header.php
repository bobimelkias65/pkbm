<?php
// Deteksi halaman saat ini untuk memperbaiki link navigasi
$current_page = basename($_SERVER['PHP_SELF']);
$is_home = ($current_page == 'index.php' || $current_page == '');
$nav_prefix = $is_home ? '' : 'index.php';

// Ambil Icon dari database
$site_icon = get_setting('site_icon');
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Mengambil Judul dari Database -->
    <title><?= get_setting('site_name') ?> - <?= get_setting('site_tagline') ?></title>
    
    <!-- Favicon / Ikon Tab Browser -->
    <?php if (!empty($site_icon)): ?>
        <link rel="shortcut icon" href="<?= $site_icon ?>" type="image/x-icon">
    <?php endif; ?>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0ea5e9',   // Sky Blue
                        secondary: '#f59e0b', // Amber/Orange
                        dark: '#0f172a',      // Slate 900
                        papuaRed: '#dc2626',  // Red
                        dogiyaiGreen: '#059669' // Green
                    }
                }
            }
        }
    </script>
    
    <!-- Style Tambahan Khusus Header -->
    <style>
        .pattern-bg {
            background-color: rgba(255, 255, 255, 0.9);
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 24px 24px;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="font-sans text-slate-600 antialiased bg-slate-50">

    <!-- Navbar -->
    <!-- Menggunakan class 'pattern-bg' menggantikan 'glass-nav' agar tidak polos -->
    <nav class="pattern-bg fixed w-full z-50 transition-all duration-300 shadow-sm" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="index.php" class="flex-shrink-0 flex items-center gap-3 hover:opacity-80 transition duration-300">
                    <!-- Logika Tampilan Logo: Cek jika ada gambar upload -->
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-lg overflow-hidden
                        <?= !empty($site_icon) ? 'bg-transparent' : 'bg-gradient-to-br from-primary to-dogiyaiGreen text-white' ?>">
                        
                        <?php if (!empty($site_icon)): ?>
                            <!-- Tampilkan Logo Upload (Tanpa Filter Warna) -->
                            <img src="<?= $site_icon ?>" alt="Logo" class="w-full h-full object-cover">
                        <?php else: ?>
                            <!-- Default Icon Buku (Jika belum ada logo) -->
                            <i class="fas fa-book-open text-xl font-bold"></i>
                        <?php endif; ?>
                    </div>

                    <div>
                        <span class="block text-xl font-bold text-slate-800 leading-none"><?= get_setting('site_name') ?></span>
                        <span class="block text-xs font-medium text-dogiyaiGreen tracking-wider">DOGIYAI, PAPUA TENGAH</span>
                    </div>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-4 lg:space-x-6 items-center text-sm lg:text-base">
                    <a href="<?= $nav_prefix ?>#beranda" class="text-slate-600 hover:text-primary font-medium transition flex items-center gap-1.5">
                        <i class="fas fa-home text-xs"></i> Beranda
                    </a>
                    <a href="<?= $nav_prefix ?>#tentang" class="text-slate-600 hover:text-primary font-medium transition flex items-center gap-1.5">
                        <i class="fas fa-info-circle text-xs"></i> Tentang
                    </a>
                    <a href="<?= $nav_prefix ?>#visi-misi" class="text-slate-600 hover:text-primary font-medium transition flex items-center gap-1.5">
                        <i class="fas fa-bullseye text-xs"></i> Visi Misi
                    </a>
                    <a href="<?= $nav_prefix ?>#program" class="text-slate-600 hover:text-primary font-medium transition flex items-center gap-1.5">
                        <i class="fas fa-graduation-cap text-xs"></i> Paket A-C
                    </a>
                    <!-- Menu Baru: Kursus -->
                    <a href="<?= $nav_prefix ?>#kursus" class="text-slate-600 hover:text-primary font-medium transition flex items-center gap-1.5">
                        <i class="fas fa-laptop-code text-xs"></i> Kursus
                    </a>
                    <a href="<?= $nav_prefix ?>#galeri" class="text-slate-600 hover:text-primary font-medium transition flex items-center gap-1.5">
                        <i class="fas fa-images text-xs"></i> Galeri
                    </a>
                    <a href="<?= $nav_prefix ?>#kontak" class="px-4 py-2 bg-primary hover:bg-sky-600 text-white font-semibold rounded-full shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2">
                        <i class="fas fa-paper-plane text-xs"></i> Daftar
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-slate-600 hover:text-primary focus:outline-none p-2">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Panel -->
        <div id="mobile-menu" class="hidden md:hidden bg-white/95 backdrop-blur-xl border-t border-slate-200 absolute w-full shadow-xl">
            <div class="px-4 pt-2 pb-6 space-y-2">
                <a href="<?= $nav_prefix ?>#beranda" class="block px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-primary hover:bg-slate-50 flex items-center gap-3">
                    <i class="fas fa-home w-5 text-center"></i> Beranda
                </a>
                <a href="<?= $nav_prefix ?>#tentang" class="block px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-primary hover:bg-slate-50 flex items-center gap-3">
                    <i class="fas fa-info-circle w-5 text-center"></i> Tentang Kami
                </a>
                <a href="<?= $nav_prefix ?>#visi-misi" class="block px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-primary hover:bg-slate-50 flex items-center gap-3">
                    <i class="fas fa-bullseye w-5 text-center"></i> Visi Misi
                </a>
                <a href="<?= $nav_prefix ?>#program" class="block px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-primary hover:bg-slate-50 flex items-center gap-3">
                    <i class="fas fa-graduation-cap w-5 text-center"></i> Paket A, B, C
                </a>
                <a href="<?= $nav_prefix ?>#kursus" class="block px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-primary hover:bg-slate-50 flex items-center gap-3">
                    <i class="fas fa-laptop-code w-5 text-center"></i> Kursus & Keterampilan
                </a>
                <a href="<?= $nav_prefix ?>#galeri" class="block px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-primary hover:bg-slate-50 flex items-center gap-3">
                    <i class="fas fa-images w-5 text-center"></i> Galeri
                </a>
                <a href="<?= $nav_prefix ?>#kontak" class="block px-3 py-3 rounded-md text-base font-bold text-primary bg-sky-50 flex items-center gap-3">
                    <i class="fas fa-paper-plane w-5 text-center"></i> Daftar Sekarang
                </a>
            </div>
        </div>
    </nav>