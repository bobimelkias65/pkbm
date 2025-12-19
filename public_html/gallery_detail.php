<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="relative pt-32 pb-20 bg-slate-900 overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?q=80&w=2022&auto=format&fit=crop')] bg-cover bg-center opacity-20"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4">Galeri Kegiatan</h1>
        <p class="text-slate-400 text-lg max-w-2xl mx-auto">
            Kumpulan dokumentasi aktivitas belajar, kegiatan sosial, dan pencapaian warga belajar PKBM Harapan Kasih.
        </p>
    </div>
</section>

<!-- Gallery Grid -->
<section class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 auto-rows-[250px]">
            <?php
            // Ambil SEMUA data galeri (tanpa limit)
            $query_gallery = "SELECT * FROM gallery ORDER BY created_at DESC";
            $result_gallery = $conn->query($query_gallery);

            if ($result_gallery && $result_gallery->num_rows > 0) {
                while($row = $result_gallery->fetch_assoc()) {
                    // Logika Layout Grid (Masonry-like)
                    $span_class = '';
                    if ($row['size_type'] == 'large') {
                        $span_class = 'md:col-span-2 md:row-span-2';
                    } elseif ($row['size_type'] == 'wide') {
                        $span_class = 'md:col-span-2';
                    }
            ?>
            <!-- Tambahkan onclick event untuk membuka modal -->
            <div onclick="openGalleryModal('<?= htmlspecialchars($row['image_url']) ?>', '<?= htmlspecialchars(addslashes($row['title'])) ?>')" class="<?= $span_class ?> group relative rounded-2xl overflow-hidden bg-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer">
                <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['title']) ?>" class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                
                <!-- Overlay Gradient & Text -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-6">
                    <span class="text-xs font-bold text-primary uppercase tracking-wider mb-1">
                        <?= date('d M Y', strtotime($row['created_at'])) ?>
                    </span>
                    <h3 class="text-white font-bold text-lg leading-tight line-clamp-2">
                        <?= htmlspecialchars($row['title']) ?>
                    </h3>
                    <div class="mt-2 text-white/80 text-sm font-medium flex items-center gap-2">
                        <i class="fas fa-expand"></i> Klik untuk memperbesar
                    </div>
                </div>
            </div>
            <?php 
                }
            } else {
                echo '<div class="col-span-full text-center py-20 text-slate-500 italic">Belum ada foto di galeri.</div>';
            }
            ?>
        </div>

        <!-- Tombol Kembali -->
        <div class="mt-16 text-center">
            <a href="index.php" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-slate-700 font-bold rounded-full shadow-sm hover:bg-slate-50 hover:text-primary transition border border-slate-200">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>

    </div>
</section>

<!-- Lightbox Modal -->
<div id="galleryModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/95 backdrop-blur-sm p-4" onclick="closeGalleryModal()">
    <!-- Close Button -->
    <button onclick="closeGalleryModal()" class="absolute top-6 right-6 text-white/70 hover:text-white text-4xl focus:outline-none z-[110]">
        &times;
    </button>

    <!-- Modal Content Container (klik di sini tidak akan menutup modal) -->
    <div class="relative max-w-5xl w-full max-h-[90vh] flex flex-col items-center justify-center" onclick="event.stopPropagation()">
        <img id="modalImage" src="" alt="Gallery Preview" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl">
        <p id="modalCaption" class="mt-4 text-white text-lg font-medium text-center"></p>
    </div>
</div>

<!-- Script untuk Modal -->
<script>
    const galleryModal = document.getElementById('galleryModal');
    const modalImage = document.getElementById('modalImage');
    const modalCaption = document.getElementById('modalCaption');

    function openGalleryModal(imageSrc, title) {
        modalImage.src = imageSrc;
        modalCaption.textContent = title;
        
        galleryModal.classList.remove('hidden');
        galleryModal.classList.add('flex');
        
        // Mencegah scroll halaman belakang saat modal terbuka
        document.body.style.overflow = 'hidden';
    }

    function closeGalleryModal() {
        galleryModal.classList.add('hidden');
        galleryModal.classList.remove('flex');
        
        // Mengembalikan scroll halaman
        modalImage.src = '';
        document.body.style.overflow = 'auto';
    }

    // Tutup modal dengan tombol ESC keyboard
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            closeGalleryModal();
        }
    });
</script>

<?php
include 'includes/footer.php';
?>