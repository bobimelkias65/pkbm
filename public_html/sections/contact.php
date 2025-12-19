<!-- CTA / Contact Section -->
<section id="kontak" class="py-24 bg-gradient-to-br from-slate-900 to-slate-800 text-white relative overflow-hidden">
    <!-- Decoration -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-primary rounded-full blur-[100px] opacity-20"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-secondary rounded-full blur-[100px] opacity-20"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
            <div>
                <h2 class="text-3xl md:text-4xl font-extrabold mb-6">Siap Melanjutkan Pendidikan?</h2>
                <p class="text-slate-300 text-lg mb-8">
                    Jangan biarkan apapun menghalangi cita-cita Anda. Hubungi kami untuk konsultasi program atau pendaftaran langsung.
                </p>
                
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center text-primary text-xl flex-shrink-0">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg text-white">Alamat Kami</h4>
                            <!-- Data Dinamis -->
                            <p class="text-slate-400"><?= get_setting('address') ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center text-secondary text-xl flex-shrink-0">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg text-white">Telepon / WhatsApp</h4>
                            <!-- Data Dinamis -->
                            <p class="text-slate-400"><?= get_setting('phone') ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center text-emerald-400 text-xl flex-shrink-0">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg text-white">Email</h4>
                            <!-- Data Dinamis -->
                            <p class="text-slate-400"><?= get_setting('email') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-8 text-slate-800 shadow-2xl">
                <h3 class="text-2xl font-bold mb-6">Formulir Pendaftaran Awal</h3>

                <!-- Notifikasi PHP -->
                <?php if (isset($_GET['status'])): ?>
                    <?php if ($_GET['status'] == 'success'): ?>
                        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm flex items-start gap-2 animate-bounce-in">
                            <i class="fas fa-check-circle mt-0.5"></i>
                            <div>
                                <strong>Berhasil!</strong> Data Anda telah terkirim. Admin kami akan segera menghubungi Anda via WhatsApp.
                            </div>
                        </div>
                    <?php elseif ($_GET['status'] == 'error'): ?>
                        <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm flex items-start gap-2">
                            <i class="fas fa-exclamation-circle mt-0.5"></i>
                            <div>
                                <strong>Gagal!</strong> <?= isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Terjadi kesalahan.' ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Form Action diarahkan ke process_registration.php -->
                <form action="process_registration.php" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1 text-slate-600">Nama Lengkap</label>
                            <input type="text" name="name" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="Nama Anda" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1 text-slate-600">No. WhatsApp</label>
                            <input type="tel" name="whatsapp" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="08xxx" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1 text-slate-600">Pilihan Program</label>
                        <select name="program_interest" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                            <option value="Paket A">Paket A (SD)</option>
                            <option value="Paket B">Paket B (SMP)</option>
                            <option value="Paket C">Paket C (SMA)</option>
                            <option value="Kursus">Kursus Keterampilan</option>
                        </select>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-1 text-slate-600">Pesan / Pertanyaan</label>
                        <textarea name="message" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition h-24" placeholder="Tulis pesan Anda disini..."></textarea>
                    </div>
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-primary to-sky-500 text-white font-bold rounded-lg hover:shadow-lg transition transform hover:-translate-y-1">
                        Kirim Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>