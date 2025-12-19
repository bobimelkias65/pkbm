<!-- About Section -->
<section id="tentang" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="relative">
                <div class="absolute -top-4 -left-4 w-72 h-72 bg-secondary/20 rounded-full blur-3xl blob"></div>
                <div class="relative rounded-2xl overflow-hidden shadow-2xl border-4 border-white">
                    <?php 
                        // Ambil gambar dari database, jika kosong pakai default
                        $about_img = get_setting('about_image');
                        if (empty($about_img)) {
                            $about_img = 'https://images.unsplash.com/photo-1577896334614-549818824b82?q=80&w=1000&auto=format&fit=crop';
                        }
                    ?>
                    
                    <img src="<?= $about_img ?>" alt="Kegiatan Belajar" class="w-full h-auto object-cover transform hover:scale-105 transition duration-700">
                </div>
                <!-- Small floating card -->
                <div class="absolute -bottom-6 -right-6 bg-white p-6 rounded-xl shadow-xl border border-slate-100 max-w-xs">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xl">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Terakreditasi</p>
                            <p class="text-xs text-slate-500">Izin Operasional Resmi</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="text-primary font-bold uppercase tracking-wider text-sm mb-2">Tentang Kami</h4>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-6">Pusat Pendidikan Masyarakat di Jantung Dogiyai</h2>
                <p class="text-lg text-slate-600 mb-6 leading-relaxed">
                    PKBM Harapan Kasih hadir sebagai solusi pendidikan bagi masyarakat Dogiyai yang putus sekolah atau ingin meningkatkan keterampilan. Kami percaya bahwa setiap individu berhak mendapatkan akses pendidikan yang layak tanpa memandang usia.
                </p>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-sky-100 text-primary flex items-center justify-center mt-1 mr-3">
                            <i class="fas fa-check text-xs"></i>
                        </span>
                        <span class="text-slate-700">Kurikulum Nasional yang disesuaikan dengan kearifan lokal.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-sky-100 text-primary flex items-center justify-center mt-1 mr-3">
                            <i class="fas fa-check text-xs"></i>
                        </span>
                        <span class="text-slate-700">Fasilitas belajar yang nyaman dan kondusif.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-sky-100 text-primary flex items-center justify-center mt-1 mr-3">
                            <i class="fas fa-check text-xs"></i>
                        </span>
                        <span class="text-slate-700">Pembekalan Life Skills (Kewirausahaan).</span>
                    </li>
                </ul>
                <a href="#kontak" class="text-primary font-bold hover:text-sky-700 inline-flex items-center gap-2 group">
                    Pelajari Lebih Lanjut <i class="fas fa-arrow-right group-hover:translate-x-1 transition"></i>
                </a>
            </div>
        </div>
    </div>
</section>