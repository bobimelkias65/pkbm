<?php
session_start();
require_once '../includes/db_connect.php';

// Jika sudah login, lempar ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Ambil data situs untuk ditampilkan
$site_icon = get_setting('site_icon');
$site_name = get_setting('site_name');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Ambil data user dari database
        $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verifikasi Password Hash
            if (password_verify($password, $user['password'])) {
                // KEAMANAN: Regenerasi Session ID untuk mencegah Session Fixation
                session_regenerate_id(true);

                // Set Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                header("Location: index.php");
                exit;
            } else {
                $error = "Password yang Anda masukkan salah.";
            }
        } else {
            $error = "Username tidak ditemukan.";
        }
    } else {
        $error = "Mohon isi username dan password.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - <?= $site_name ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        /* Pattern Background yang halus */
        .bg-pattern {
            background-color: #f8fafc;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-pattern h-screen flex items-center justify-center px-4 text-slate-800">

    <div class="bg-white p-8 md:p-10 rounded-3xl shadow-2xl w-full max-w-sm border border-slate-100 relative overflow-hidden transform transition-all duration-500 hover:scale-[1.02]">
        
        <!-- Decorative Top Bar -->
        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-sky-500 via-blue-500 to-emerald-500"></div>

        <div class="text-center mb-8 mt-2">
            <!-- Logo Container -->
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-sky-500 to-emerald-500 text-white shadow-lg mb-6 p-1">
                <?php if (!empty($site_icon)): ?>
                    <!-- Tampilkan Logo dari Database -->
                    <img src="../<?= $site_icon ?>" alt="Logo" class="w-full h-full object-cover rounded-xl bg-white">
                <?php else: ?>
                    <!-- Default Icon jika belum ada logo -->
                    <i class="fas fa-book-open text-3xl"></i>
                <?php endif; ?>
            </div>
            
            <h2 class="text-2xl font-extrabold text-slate-800 mb-1">Selamat Datang</h2>
            <p class="text-slate-500 text-sm">Silakan masuk untuk mengelola <br><span class="font-semibold text-sky-600"><?= $site_name ?></span></p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-xl text-sm mb-6 text-center border border-red-100 flex items-center justify-center gap-2 animate-pulse">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
            <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-2 ml-1">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" name="username" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-100 outline-none transition duration-200 font-medium placeholder-slate-400" required placeholder="Masukkan username">
                </div>
            </div>
            
            <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-2 ml-1">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" name="password" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-100 outline-none transition duration-200 font-medium placeholder-slate-400" required placeholder="Masukkan kata sandi">
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-700 hover:to-blue-700 text-white font-bold py-3.5 rounded-xl transition transform hover:-translate-y-0.5 shadow-lg hover:shadow-sky-200 flex items-center justify-center gap-2 group">
                <span>MASUK DASHBOARD</span>
                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </button>
        </form>
        
        <div class="mt-8 text-center border-t border-slate-100 pt-6">
            <p class="text-xs text-slate-400">
                &copy; <?= date('Y') ?> <?= $site_name ?>.<br>Semua hak dilindungi undang-undang.
            </p>
        </div>
    </div>

</body>
</html>