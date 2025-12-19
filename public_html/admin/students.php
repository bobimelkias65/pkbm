<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 1. Tambah / Edit Data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $nisn = $_POST['nisn'];
    $program_type = $_POST['program_type'];
    $class_year = $_POST['class_year'];
    $status = $_POST['status'];
    $id = $_POST['id'] ?? '';

    if ($id) {
        $stmt = $conn->prepare("UPDATE students SET name=?, nisn=?, program_type=?, class_year=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $nisn, $program_type, $class_year, $status, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO students (name, nisn, program_type, class_year, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $nisn, $program_type, $class_year, $status);
    }
    
    if ($stmt->execute()) {
        header("Location: students.php?msg=saved");
        exit;
    }
}

// 2. Hapus Data
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete_id']);
    $stmt->execute();
    header("Location: students.php?msg=deleted");
    exit;
}

// 3. Filter Data
$filter_paket = $_GET['paket'] ?? '';
$sql = "SELECT * FROM students";
if ($filter_paket) {
    $sql .= " WHERE program_type = '$filter_paket'";
}
$sql .= " ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - PKBM Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">
    <!-- Navbar sederhana (Idealnya include sidebar yang sama) -->
    <div class="flex min-h-screen">
        <!-- Sidebar placeholder (Copy dari index.php jika ingin konsisten penuh) -->
        <aside class="w-64 bg-slate-900 text-white hidden md:block fixed h-full z-10">
            <div class="h-20 flex items-center px-6 border-b border-slate-800"><span class="text-xl font-bold">PKBM Admin</span></div>
            <nav class="p-4 space-y-2">
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"><i class="fas fa-home w-5"></i> Dashboard</a>
                <div class="pt-4 pb-2 px-4 text-xs font-bold text-slate-500 uppercase">Master Data</div>
                <a href="students.php" class="flex items-center gap-3 px-4 py-3 bg-sky-600 text-white rounded-lg shadow-lg"><i class="fas fa-user-graduate w-5"></i> Data Siswa</a>
                <a href="courses.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"><i class="fas fa-chalkboard-teacher w-5"></i> Data Kursus</a>
                <div class="pt-8 border-t border-slate-800 mt-4"><a href="logout.php" class="flex px-4 py-2 text-red-400 hover:bg-slate-800 rounded">Keluar</a></div>
            </nav>
        </aside>

        <main class="flex-1 md:ml-64 p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Data Siswa</h1>
                    <p class="text-slate-500 text-sm">Kelola data peserta didik Paket A, B, dan C.</p>
                </div>
                <button onclick="openModal()" class="bg-sky-600 text-white px-5 py-2.5 rounded-lg font-bold shadow hover:bg-sky-700 flex gap-2 items-center">
                    <i class="fas fa-plus"></i> Tambah Siswa
                </button>
            </div>

            <!-- Filter Tabs -->
            <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                <a href="students.php" class="px-4 py-2 rounded-full text-sm font-bold <?= $filter_paket == '' ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 border border-slate-200' ?>">Semua</a>
                <a href="students.php?paket=Paket A" class="px-4 py-2 rounded-full text-sm font-bold <?= $filter_paket == 'Paket A' ? 'bg-sky-600 text-white' : 'bg-white text-slate-600 border border-slate-200' ?>">Paket A (SD)</a>
                <a href="students.php?paket=Paket B" class="px-4 py-2 rounded-full text-sm font-bold <?= $filter_paket == 'Paket B' ? 'bg-orange-500 text-white' : 'bg-white text-slate-600 border border-slate-200' ?>">Paket B (SMP)</a>
                <a href="students.php?paket=Paket C" class="px-4 py-2 rounded-full text-sm font-bold <?= $filter_paket == 'Paket C' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200' ?>">Paket C (SMA)</a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="p-4">Nama Siswa</th>
                            <th class="p-4">NISN</th>
                            <th class="p-4">Program</th>
                            <th class="p-4">Tahun Masuk</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($row['name']) ?></td>
                                <td class="p-4 text-slate-600"><?= htmlspecialchars($row['nisn']) ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-1 text-xs font-bold rounded 
                                        <?= $row['program_type'] == 'Paket A' ? 'bg-sky-100 text-sky-700' : 
                                           ($row['program_type'] == 'Paket B' ? 'bg-orange-100 text-orange-700' : 'bg-emerald-100 text-emerald-700') ?>">
                                        <?= $row['program_type'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-slate-600"><?= htmlspecialchars($row['class_year']) ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-slate-100 text-slate-600">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <button onclick='editData(<?= json_encode($row) ?>)' class="text-blue-600 hover:bg-blue-50 p-2 rounded"><i class="fas fa-edit"></i></button>
                                    <a href="students.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Hapus siswa?')" class="text-red-600 hover:bg-red-50 p-2 rounded"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="p-8 text-center text-slate-400">Tidak ada data siswa.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Form -->
    <div id="modalForm" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl p-6">
            <h3 class="text-xl font-bold mb-4" id="modalTitle">Tambah Siswa</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="id" id="inputId">
                <div>
                    <label class="block text-sm font-bold mb-1">Nama Lengkap</label>
                    <input type="text" name="name" id="inputName" required class="w-full border rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1">NISN</label>
                        <input type="text" name="nisn" id="inputNisn" class="w-full border rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Tahun Masuk</label>
                        <input type="text" name="class_year" id="inputYear" placeholder="2024/2025" required class="w-full border rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1">Program</label>
                        <select name="program_type" id="inputType" class="w-full border rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="Paket A">Paket A (SD)</option>
                            <option value="Paket B">Paket B (SMP)</option>
                            <option value="Paket C">Paket C (SMA)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Status</label>
                        <select name="status" id="inputStatus" class="w-full border rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="Aktif">Aktif</option>
                            <option value="Lulus">Lulus</option>
                            <option value="Cuti">Cuti</option>
                            <option value="Keluar">Keluar</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalForm');
        function openModal() { document.querySelector('form').reset(); document.getElementById('inputId').value=''; document.getElementById('modalTitle').innerText='Tambah Siswa'; modal.classList.remove('hidden'); modal.classList.add('flex'); }
        function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        function editData(data) {
            document.getElementById('modalTitle').innerText='Edit Siswa';
            document.getElementById('inputId').value=data.id;
            document.getElementById('inputName').value=data.name;
            document.getElementById('inputNisn').value=data.nisn;
            document.getElementById('inputYear').value=data.class_year;
            document.getElementById('inputType').value=data.program_type;
            document.getElementById('inputStatus').value=data.status;
            modal.classList.remove('hidden'); modal.classList.add('flex');
        }
        window.onclick = e => { if(e.target == modal) closeModal(); }
    </script>
</body>
</html>