<?php
session_start();
require_once '../includes/db_connect.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- LOGIKA PHP UNTUK CRUD ---

// 1. Hapus Pesan
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM inquiries WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: inquiries.php?msg=deleted");
        exit;
    }
}

// 2. Update Status (Tandai Sudah Dibaca / Dihubungi)
if (isset($_GET['status']) && isset($_GET['id'])) {
    $status = $_GET['status']; // 'read' or 'contacted'
    $id = $_GET['id'];
    
    // Validasi input status agar aman
    $allowed_status = ['new', 'read', 'contacted'];
    if (in_array($status, $allowed_status)) {
        $stmt = $conn->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            header("Location: inquiries.php?msg=updated");
            exit;
        }
    }
}

// 3. Ambil Data
$result = $conn->query("SELECT * FROM inquiries ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Masuk - Admin PKBM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white hidden md:block fixed h-full z-10">
            <div class="h-20 flex items-center px-6 border-b border-slate-800">
                <span class="text-xl font-bold tracking-tight">PKBM Admin</span>
            </div>
            <nav class="p-4 space-y-2">
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition">
                    <i class="fas fa-home w-5 text-center"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="programs.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition">
                    <i class="fas fa-graduation-cap w-5 text-center"></i>
                    <span class="font-medium">Program</span>
                </a>
                <a href="gallery.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition">
                    <i class="fas fa-images w-5 text-center"></i>
                    <span class="font-medium">Galeri</span>
                </a>
                <a href="inquiries.php" class="flex items-center gap-3 px-4 py-3 bg-sky-600 rounded-lg text-white shadow-lg transition">
                    <i class="fas fa-inbox w-5 text-center"></i>
                    <span class="font-medium">Pesan Masuk</span>
                </a>
                <div class="pt-8 mt-8 border-t border-slate-800">
                    <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-slate-800 rounded-lg transition">
                        <i class="fas fa-sign-out-alt w-5 text-center"></i>
                        <span class="font-medium">Keluar</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Pesan Masuk</h1>
                    <p class="text-slate-500 text-sm">Kelola pendaftaran dan pertanyaan dari calon warga belajar.</p>
                </div>
            </div>

            <!-- Notifikasi -->
            <?php if (isset($_GET['msg'])): ?>
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <?php
                        if ($_GET['msg'] == 'deleted') echo 'Pesan berhasil dihapus!';
                        elseif ($_GET['msg'] == 'updated') echo 'Status pesan berhasil diperbarui!';
                    ?>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs tracking-wider">
                            <th class="p-4 font-bold">Tanggal</th>
                            <th class="p-4 font-bold">Pengirim</th>
                            <th class="p-4 font-bold">Minat Program</th>
                            <th class="p-4 font-bold">Pesan</th>
                            <th class="p-4 font-bold">Status</th>
                            <th class="p-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition <?= $row['status'] == 'new' ? 'bg-sky-50/50' : '' ?>">
                                <td class="p-4 text-sm text-slate-500 whitespace-nowrap">
                                    <?= date('d M Y', strtotime($row['created_at'])) ?><br>
                                    <span class="text-xs"><?= date('H:i', strtotime($row['created_at'])) ?></span>
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-slate-800"><?= htmlspecialchars($row['name']) ?></div>
                                    <a href="https://wa.me/<?= preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $row['whatsapp'])) ?>" target="_blank" class="text-sm text-green-600 hover:text-green-700 flex items-center gap-1 mt-1">
                                        <i class="fab fa-whatsapp"></i> <?= htmlspecialchars($row['whatsapp']) ?>
                                    </a>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 bg-slate-100 rounded text-xs font-semibold text-slate-600 border border-slate-200">
                                        <?= htmlspecialchars($row['program_interest']) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-slate-600 max-w-xs truncate cursor-pointer" title="<?= htmlspecialchars($row['message']) ?>" onclick="viewMessage('<?= htmlspecialchars(addslashes($row['name'])) ?>', '<?= htmlspecialchars(addslashes($row['message'])) ?>')">
                                    <?= htmlspecialchars(substr($row['message'], 0, 50)) . (strlen($row['message']) > 50 ? '...' : '') ?>
                                </td>
                                <td class="p-4">
                                    <?php if($row['status'] == 'new'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Baru
                                        </span>
                                    <?php elseif($row['status'] == 'read'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Dibaca
                                        </span>
                                    <?php elseif($row['status'] == 'contacted'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Dihubungi
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- View Button -->
                                        <button onclick="viewMessage('<?= htmlspecialchars(addslashes($row['name'])) ?>', '<?= htmlspecialchars(addslashes($row['message'])) ?>')" class="text-sky-600 hover:text-sky-800 p-2 hover:bg-sky-50 rounded transition" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <!-- Mark as Contacted -->
                                        <?php if($row['status'] != 'contacted'): ?>
                                        <a href="inquiries.php?id=<?= $row['id'] ?>&status=contacted" class="text-green-600 hover:text-green-800 p-2 hover:bg-green-50 rounded transition" title="Tandai Sudah Dihubungi">
                                            <i class="fas fa-check-double"></i>
                                        </a>
                                        <?php endif; ?>

                                        <!-- Delete -->
                                        <a href="inquiries.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Hapus pesan ini?')" class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded transition" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="p-8 text-center text-slate-400">Belum ada pesan masuk.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal View Message -->
    <div id="messageModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl transform transition-all scale-100 p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Pesan dari <span id="modalSenderName" class="text-primary"></span></h3>
                </div>
                <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 text-2xl leading-none">&times;</button>
            </div>
            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100 mb-6 max-h-60 overflow-y-auto">
                <p id="modalMessageContent" class="text-slate-600 text-sm whitespace-pre-wrap leading-relaxed"></p>
            </div>
            <div class="flex justify-end">
                <button onclick="closeModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg font-bold text-sm transition">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function viewMessage(name, message) {
            document.getElementById('modalSenderName').innerText = name;
            document.getElementById('modalMessageContent').innerText = message;
            document.getElementById('messageModal').classList.remove('hidden');
            document.getElementById('messageModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('messageModal').classList.add('hidden');
            document.getElementById('messageModal').classList.remove('flex');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>