<!-- Hero Section -->
<!-- HAPUS class 'overflow-hidden' dari sini agar stats tidak terpotong -->
<section id="beranda" class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 hero-pattern">
    <div class="absolute inset-0 bg-gradient-to-t from-dark via-dark/80 to-transparent"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-block py-1 px-3 rounded-full bg-white/10 text-secondary border border-white/20 text-sm font-semibold mb-6 backdrop-blur-sm animate-fade-in-up">
            🎓 Pendidikan Kesetaraan & Keterampilan
        </span>
        <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold text-white mb-6 tracking-tight leading-tight">
            Membangun Generasi Emas <br class="hidden md:block" />
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">Tanah Dogiyai</span>
        </h1>
        <p class="mt-4 max-w-2xl mx-auto text-xl text-slate-300 mb-10">
            <!-- Menggunakan Nama Situs dari Database -->
            Wujudkan impian pendidikan Anda bersama <strong><?= get_setting('site_name') ?></strong>. Kami menyediakan layanan pendidikan kesetaraan Paket A, B, C dan keterampilan hidup yang inklusif dan berkualitas.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="#program" class="px-8 py-4 bg-primary hover:bg-sky-600 text-white font-bold rounded-full text-lg shadow-lg hover:shadow-sky-500/50 transition transform hover:-translate-y-1">
                Lihat Program
            </a>
            <a href="#kontak" class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white border border-white/30 backdrop-blur-sm font-bold rounded-full text-lg transition">
                Hubungi Kami
            </a>
        </div>
    </div>

    <!-- Stats Floating -->
    <!-- Tambahkan z-20 agar posisinya di atas section About -->
    <div class="hidden lg:flex absolute bottom-0 left-0 right-0 justify-center translate-y-1/2 z-20">
        <div class="bg-white rounded-2xl shadow-xl p-8 grid grid-cols-3 gap-12 max-w-4xl border border-slate-100">
            <div class="text-center">
                <div class="text-4xl font-bold text-primary mb-1">250+</div>
                <div class="text-sm text-slate-500 font-medium uppercase tracking-wide">Warga Belajar</div>
            </div>
            <div class="text-center border-l border-slate-100 pl-12">
                <div class="text-4xl font-bold text-secondary mb-1">15+</div>
                <div class="text-sm text-slate-500 font-medium uppercase tracking-wide">Tutor Berdedikasi</div>
            </div>
            <div class="text-center border-l border-slate-100 pl-12">
                <div class="text-4xl font-bold text-dogiyaiGreen mb-1">100%</div>
                <div class="text-sm text-slate-500 font-medium uppercase tracking-wide">Lulusan Kompeten</div>
            </div>
        </div>
    </div>
</section>