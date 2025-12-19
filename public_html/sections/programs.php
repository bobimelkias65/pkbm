<!-- Program Section -->
<section id="program" class="py-24 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- BAGIAN 1: PENDIDIKAN KESETARAAN (PAKET A, B, C) -->
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-4">Program Unggulan</h2>
            <p class="text-slate-600 text-lg">Kami menyediakan berbagai jalur pendidikan kesetaraan untuk membantu Anda meraih masa depan yang lebih cerah.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-20">
            <?php
            // Query ambil data program (Paket A, B, C)
            $query_programs = "SELECT * FROM programs ORDER BY display_order ASC";
            $result_programs = $conn->query($query_programs);

            if ($result_programs && $result_programs->num_rows > 0) {
                while($row = $result_programs->fetch_assoc()) {
                    // Tentukan warna tema
                    $bg_icon = 'bg-sky-100 text-primary';
                    $bg_decor = 'bg-sky-50 group-hover:bg-sky-100';
                    if($row['theme_color'] == 'orange') {
                        $bg_icon = 'bg-orange-100 text-orange-600';
                        $bg_decor = 'bg-orange-50 group-hover:bg-orange-100';
                    } elseif($row['theme_color'] == 'emerald') {
                        $bg_icon = 'bg-emerald-100 text-emerald-600';
                        $bg_decor = 'bg-emerald-50 group-hover:bg-emerald-100';
                    }
            ?>
            <div class="program-card bg-white rounded-2xl p-8 border border-slate-100 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 rounded-bl-full -mr-16 -mt-16 transition <?= $bg_decor ?>"></div>
                <div class="w-16 h-16 <?= $bg_icon ?> rounded-2xl flex items-center justify-center text-3xl mb-6 relative z-10">
                    <i class="<?= $row['icon_class'] ?>"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-3"><?= $row['title'] ?> <span class="text-sm font-normal text-slate-500 block mt-1"><?= $row['subtitle'] ?></span></h3>
                <p class="text-slate-600 mb-6 text-sm leading-relaxed"><?= $row['description'] ?></p>
                <div class="border-t border-slate-100 pt-6">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Fokus:</span>
                    <div class="flex flex-wrap gap-2">
                        <?php 
                        $tags = explode(',', $row['tags']);
                        foreach($tags as $tag): 
                        ?>
                        <span class="px-2 py-1 bg-slate-50 border border-slate-100 text-xs font-semibold text-slate-600 rounded"><?= trim($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php 
                } 
            }
            ?>
        </div>
        
        <!-- BAGIAN 2: KURSUS & KETERAMPILAN (DINAMIS) -->
        <!-- PENTING: ID="kursus" ditambahkan di sini agar menu navigasi berfungsi -->
        <div id="kursus" class="mb-12 scroll-mt-28">
            <div class="flex items-center gap-4 mb-8">
                <div class="h-px bg-slate-200 flex-1"></div>
                <h3 class="text-2xl font-bold text-slate-800 text-center">Kursus & Keterampilan</h3>
                <div class="h-px bg-slate-200 flex-1"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                $query_courses = "SELECT * FROM courses ORDER BY created_at DESC";
                $result_courses = $conn->query($query_courses);

                if ($result_courses && $result_courses->num_rows > 0) {
                    while($course = $result_courses->fetch_assoc()) {
                ?>
                <!-- Card Kursus Dengan Gambar -->
                <div class="bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 group flex flex-col h-full">
                    <!-- Image Box -->
                    <div class="h-48 w-full bg-slate-200 relative overflow-hidden">
                        <?php if(!empty($course['image_url'])): ?>
                            <!-- Cek apakah path lokal atau external -->
                            <?php $imgSrc = (strpos($course['image_url'], 'assets/') === 0) ? $course['image_url'] : $course['image_url']; ?>
                            
                            <!-- Gambar dengan Fallback ke Placeholder jika rusak -->
                            <img src="<?= htmlspecialchars($imgSrc) ?>" 
                                 alt="<?= htmlspecialchars($course['course_name']) ?>" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                 
                            <!-- Placeholder (Hidden by default, shown on error) -->
                            <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300" style="display: none;">
                                <i class="fas fa-laptop-code text-5xl"></i>
                            </div>
                        <?php else: ?>
                            <!-- Default Pattern jika tidak ada gambar di DB -->
                            <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-300">
                                <i class="fas fa-laptop-code text-5xl"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Date Badge -->
                        <?php if(!empty($course['start_date'])): ?>
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg shadow-md text-xs font-bold text-primary flex flex-col items-center border border-white">
                            <span class="text-lg leading-none"><?= date('d', strtotime($course['start_date'])) ?></span>
                            <span class="uppercase text-[10px]"><?= date('M', strtotime($course['start_date'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-6 flex-1 flex flex-col">
                        <h4 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-primary transition"><?= htmlspecialchars($course['course_name']) ?></h4>
                        
                        <!-- Jadwal Badge -->
                        <div class="mb-4">
                            <span class="inline-block px-3 py-1 bg-pink-50 text-pink-600 text-xs font-bold rounded-full">
                                <i class="fas fa-clock mr-1"></i> <?= htmlspecialchars($course['schedule']) ?>
                            </span>
                        </div>

                        <p class="text-slate-500 text-sm mb-6 flex-1 leading-relaxed line-clamp-3"><?= htmlspecialchars($course['description']) ?></p>
                        
                        <div class="border-t border-slate-100 pt-4 mt-auto flex justify-between items-center">
                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="font-medium"><?= htmlspecialchars($course['instructor']) ?></span>
                            </div>
                            <a href="#kontak" class="text-primary font-bold text-sm hover:underline">Daftar <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo '<div class="col-span-3 text-center py-12 text-slate-400 bg-white rounded-xl border border-dashed border-slate-200">Belum ada data kursus yang tersedia saat ini.</div>';
                }
                ?>
            </div>
        </div>

        <!-- Call to Action Banner -->
        <div class="mt-12 bg-dark rounded-3xl p-8 md:p-12 relative overflow-hidden text-center md:text-left shadow-2xl">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary/20 rounded-full blur-3xl -mr-16 -mt-16"></div>
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                <div>
                    <h3 class="text-2xl md:text-3xl font-bold text-white mb-2">Ingin Mendaftar?</h3>
                    <p class="text-slate-400">Segera daftarkan diri Anda untuk mengikuti program kesetaraan atau kursus keterampilan.</p>
                </div>
                <a href="#kontak" class="whitespace-nowrap px-8 py-4 bg-white text-dark font-bold rounded-xl hover:bg-slate-200 transition shadow-lg transform hover:-translate-y-1">
                    Hubungi Admin
                </a>
            </div>
        </div>

    </div>
</section>