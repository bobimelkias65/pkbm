<!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 pt-12 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-8">
                <span class="text-2xl font-bold text-white tracking-tight"><?= get_setting('site_name') ?></span>
                <p class="text-slate-500 mt-2"><?= get_setting('site_tagline') ?></p>
            </div>
            
            <div class="flex justify-center gap-6 mb-8">
                <!-- Facebook -->
                <?php if(get_setting('facebook_url') && get_setting('facebook_url') != '#'): ?>
                <a href="<?= get_setting('facebook_url') ?>" target="_blank" class="w-10 h-10 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center hover:bg-primary hover:text-white transition">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <?php endif; ?>

                <!-- Instagram -->
                <?php if(get_setting('instagram_url') && get_setting('instagram_url') != '#'): ?>
                <a href="<?= get_setting('instagram_url') ?>" target="_blank" class="w-10 h-10 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center hover:bg-pink-600 hover:text-white transition">
                    <i class="fab fa-instagram"></i>
                </a>
                <?php endif; ?>

                <!-- Youtube -->
                <?php if(get_setting('youtube_url') && get_setting('youtube_url') != '#'): ?>
                <a href="<?= get_setting('youtube_url') ?>" target="_blank" class="w-10 h-10 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center hover:bg-red-600 hover:text-white transition">
                    <i class="fab fa-youtube"></i>
                </a>
                <?php endif; ?>
            </div>

            <div class="text-slate-600 text-sm">
                &copy; <?= date('Y') ?> <?= get_setting('site_name') ?> Dogiyai. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- JavaScript Custom -->
    <script src="assets/js/script.js"></script>
</body>
</html>