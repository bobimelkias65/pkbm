<!-- Gallery Section -->
<section id="galeri" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h4 class="text-primary font-bold uppercase tracking-wider text-sm mb-2">Dokumentasi</h4>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900">Galeri Kegiatan</h2>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 auto-rows-[200px]">
            <?php
            // Mengambil data galeri terbaru, dibatasi 8 foto
            $query_gallery = "SELECT * FROM gallery ORDER BY created_at DESC LIMIT 8";
            $result_gallery = $conn->query($query_gallery);

            if ($result_gallery && $result_gallery->num_rows > 0) {
                while($row = $result_gallery->fetch_assoc()) {
                    // Logika Layout: Tentukan class span berdasarkan tipe ukuran
                    $span_class = '';
                    if ($row['size_type'] == 'large') {
                        $span_class = 'col-span-2 row-span-2';
                    } elseif ($row['size_type'] == 'wide') {
                        $span_class = 'col-span-2';
                    }
            ?>
            <!-- Dynamic Item -->
            <div class="<?= $span_class ?> relative rounded-xl overflow-hidden group cursor-pointer">
                <img src="<?= $row['image_url'] ?>" alt="<?= $row['title'] ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-end p-4 md:p-6">
                    <p class="text-white font-bold <?= $row['size_type'] == 'large' ? 'text-lg' : 'text-sm' ?>"><?= $row['title'] ?></p>
                </div>
            </div>
            <?php 
                } 
            } else {
                echo "<div class='col-span-full text-center py-12 text-slate-400 italic'>Belum ada dokumentasi yang diunggah.</div>";
            }
            ?>
        </div>

        <!-- Tombol Lihat Semua -->
        <div class="mt-12 text-center">
            <a href="gallery_detail.php" class="inline-flex items-center gap-2 px-8 py-3 bg-white text-primary font-bold rounded-full shadow-md hover:shadow-lg hover:bg-primary hover:text-white transition border border-slate-100 transform hover:-translate-y-1">
                Lihat Seluruh Galeri <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>