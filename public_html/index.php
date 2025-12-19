<?php
// 1. Load Koneksi Database Terlebih Dahulu
// Pastikan file db_connect.php ada di folder 'includes'
require_once 'includes/db_connect.php';

// 2. Menyertakan Header
include 'includes/header.php';

// 3. Menyertakan Section (Konten)
include 'sections/hero.php';
include 'sections/about.php';    // Konten statis (HTML biasa)

// --- BAGIAN BARU: VISI & MISI ---
include 'sections/vision.php';   // Konten statis

include 'sections/programs.php'; // Konten dinamis (dari tabel programs)
include 'sections/gallery.php';  // Konten dinamis (dari tabel gallery)
include 'sections/contact.php';  // Konten dinamis (dari site_settings)

// 4. Menyertakan Footer
include 'includes/footer.php';
?>