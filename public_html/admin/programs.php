<?php
session_start();
require_once '../includes/db_connect.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- LOGIKA PHP UNTUK CRUD ---

// 1. Tambah / Edit Data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = $_POST['title'];
    $subtitle    = $_POST['subtitle'];
    $description = $_POST['description'];
    $icon_class  = $_POST['icon_class'];
    $theme_color = $_POST['theme_color'];
    $tags        = $_POST['tags'];
    $display_order = (int)$_POST['display_order'];
    $id          = isset($_POST['id']) ? $_POST['id'] : '';

    if ($id) {
        // Update Data Existing
        $stmt = $conn->prepare("UPDATE programs SET title=?, subtitle=?, description=?, icon_class=?, theme_color=?, tags=?, display_order=? WHERE id=?");
        $stmt->bind_param("ssssssii", $title, $subtitle, $description, $icon_class, $theme_color, $tags, $display_order, $id);
    } else {
        // Insert Data Baru
        $stmt = $conn->prepare("INSERT INTO programs (title, subtitle, description, icon_class, theme_color, tags, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $title, $subtitle, $description, $icon_class, $theme_color, $tags, $display_order);
    }

    if ($stmt->execute()) {
        header("Location: programs.php?msg=saved");
        exit;
    } else {
        $error = "Gagal menyimpan data.";
    }
}

// 2. Hapus Data
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM programs WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: programs.php?msg=deleted");
        exit;
    }
}

// 3. Ambil Data untuk Ditampilkan
$result = $conn->query("SELECT * FROM programs ORDER BY display_order ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Program - Admin PKBM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-8">
            <!-- Header Mobile -->
            <div class="md:hidden mb-8 flex justify-between items-center">
                <h1 class="text-2xl font-bold">Kelola Program</h1>
                <a href="logout.php" class="text-red-500"><i class="fas fa-sign-out-alt text-xl"></i></a>
            </div>

            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Kelola Program Pendidikan</h1>
                    <p class="text-slate-500 text-sm">Tambah, edit, atau hapus program layanan PKBM.</p>
                </div>
                <button onclick="openModal()" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-lg font-bold shadow-md transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Program
                </button>
            </div>

            <!-- Notifikasi -->
            <?php if (isset($_GET['msg'])): ?>
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <?= $_GET['msg'] == 'saved' ? 'Data berhasil disimpan!' : 'Data berhasil dihapus!' ?>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs tracking-wider">
                            <th class="p-4 font-bold">Urutan</th>
                            <th class="p-4 font-bold">Program</th>
                            <th class="p-4 font-bold">Tema Warna</th>
                            <th class="p-4 font-bold">Tags (Fokus)</th>
                            <th class="p-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 text-center font-bold text-slate-400"><?= $row['display_order'] ?></td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-slate-100 text-slate-600">
                                            <i class="<?= $row['icon_class'] ?>"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800"><?= htmlspecialchars($row['title']) ?></div>
                                            <div class="text-xs text-slate-500"><?= htmlspecialchars($row['subtitle']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                                        <?php 
                                            if($row['theme_color']=='sky') echo 'bg-sky-100 text-sky-700';
                                            elseif($row['theme_color']=='orange') echo 'bg-orange-100 text-orange-700';
                                            elseif($row['theme_color']=='emerald') echo 'bg-emerald-100 text-emerald-700';
                                        ?>">
                                        <?= $row['theme_color'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-slate-600 max-w-xs truncate">
                                    <?= htmlspecialchars($row['tags']) ?>
                                </td>
                                <td class="p-4 text-center">
                                    <button onclick='editData(<?= json_encode($row) ?>)' class="text-blue-600 hover:text-blue-800 mx-1 p-2 bg-blue-50 rounded hover:bg-blue-100 transition">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="programs.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus program ini?')" class="text-red-600 hover:text-red-800 mx-1 p-2 bg-red-50 rounded hover:bg-red-100 transition">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="p-8 text-center text-slate-400">Belum ada data program.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- MODAL FORM -->
    <div id="modalForm" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl transform transition-all scale-100">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-800" id="modalTitle">Tambah Program Baru</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 text-2xl">&times;</button>
            </div>
            
            <form method="POST" action="" class="p-6 space-y-4">
                <input type="hidden" name="id" id="inputId">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Judul Program</label>
                        <input type="text" name="title" id="inputTitle" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none" placeholder="Contoh: Paket A">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Sub Judul</label>
                        <input type="text" name="subtitle" id="inputSubtitle" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none" placeholder="Contoh: Setara SD">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Deskripsi Singkat</label>
                    <textarea name="description" id="inputDescription" required rows="3" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none" placeholder="Penjelasan singkat program..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">
                            Ikon FontAwesome 
                            <a href="https://fontawesome.com/search?m=free" target="_blank" class="text-xs text-sky-500 font-normal ml-1">(Cari Ikon)</a>
                        </label>
                        <input type="text" name="icon_class" id="inputIcon" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none" placeholder="fas fa-child">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Urutan Tampil</label>
                        <input type="number" name="display_order" id="inputOrder" value="0" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Warna Tema</label>
                        <select name="theme_color" id="inputTheme" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none">
                            <option value="sky">Sky Blue (Biru)</option>
                            <option value="orange">Orange (Jingga)</option>
                            <option value="emerald">Emerald (Hijau)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Tags (Pisahkan Koma)</label>
                        <input type="text" name="tags" id="inputTags" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none" placeholder="Calistung, Karakter">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg font-bold">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg font-bold shadow-lg">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalForm');
        const modalTitle = document.getElementById('modalTitle');

        function openModal() {
            document.querySelector('form').reset();
            document.getElementById('inputId').value = ''; 
            modalTitle.innerText = "Tambah Program Baru";
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function editData(data) {
            modalTitle.innerText = "Edit Program";
            document.getElementById('inputId').value = data.id;
            document.getElementById('inputTitle').value = data.title;
            document.getElementById('inputSubtitle').value = data.subtitle;
            document.getElementById('inputDescription').value = data.description;
            document.getElementById('inputIcon').value = data.icon_class;
            document.getElementById('inputOrder').value = data.display_order;
            document.getElementById('inputTheme').value = data.theme_color;
            document.getElementById('inputTags').value = data.tags;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>