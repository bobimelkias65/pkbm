<?php
session_start();
require_once '../includes/db_connect.php';

// 1. CEK LOGIN (Security Guard)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. FITUR SESSION TIMEOUT (Keamanan & UX)
// Jika tidak ada aktivitas selama 30 menit (1800 detik), otomatis logout
$timeout_duration = 1800; 

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    // Redirect ke login dengan pesan timeout
    header("Location: login.php?msg=timeout");
    exit();
}

// Update waktu aktivitas terakhir setiap kali halaman di-refresh
$_SESSION['last_activity'] = time();

// 3. LOGIKA DASHBOARD (Mengambil Statistik Data)
$stats = [
    'students' => 0,
    'courses' => 0,
    'gallery' => 0,
    'inquiries' => 0
];

// Hitung Total Siswa
$query = $conn->query("SELECT COUNT(*) as total FROM students");
if ($query) $stats['students'] = $query->fetch_assoc()['total'];

// Hitung Total Kursus
$query = $conn->query("SELECT COUNT(*) as total FROM courses");
if ($query) $stats['courses'] = $query->fetch_assoc()['total'];

// Hitung Total Galeri
$query = $conn->query("SELECT COUNT(*) as total FROM gallery");
if ($query) $stats['gallery'] = $query->fetch_assoc()['total'];

// Hitung Pesan Masuk (Cek dulu jika tabel inquiries ada)
$check_table = $conn->query("SHOW TABLES LIKE 'inquiries'");
if ($check_table && $check_table->num_rows > 0) {
    $q_inq = $conn->query("SELECT COUNT(*) as total FROM inquiries");
    if ($q_inq) $stats['inquiries'] = $q_inq->fetch_assoc()['total'];
}

// 4. DATA PENDAFTAR TERBARU (Limit 5)
// Menampilkan 5 pendaftar terakhir untuk monitoring cepat
$latest_students = $conn->query("SELECT * FROM students ORDER BY tanggal_daftar DESC LIMIT 5");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - PKBM</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome untuk Ikon (Opsional, gunakan CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .admin-container { display: flex; min-height: 100vh; background-color: #f4f6f9; }
        .main-content { flex: 1; padding: 30px; }
        
        /* Kartu Statistik */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        .stat-card { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-info h3 { margin: 0; font-size: 28px; color: #2c3e50; }
        .stat-info p { margin: 5px 0 0; color: #7f8c8d; font-size: 14px; }
        .stat-icon { 
            font-size: 40px; 
            opacity: 0.2; 
        }
        
        /* Warna Ikon per Kategori */
        .icon-blue { color: #3498db; }
        .icon-green { color: #2ecc71; }
        .icon-orange { color: #e67e22; }
        .icon-purple { color: #9b59b6; }

        /* Tabel Terbaru */
        .recent-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .recent-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .recent-header h3 { margin: 0; color: #2c3e50; }
        .btn-sm { font-size: 12px; padding: 5px 10px; background: #34495e; color: white; text-decoration: none; border-radius: 4px; }
        
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        table th { background-color: #f8f9fa; color: #666; font-weight: 600; }
        .status-badge { 
            padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; background: #d4edda; color: #155724; 
        }
    </style>
</head>
<body>

<div class="admin-container">
    <!-- Sidebar Include -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div style="margin-bottom: 25px;">
            <h2 style="margin-bottom: 5px;">Selamat Datang, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</h2>
            <p style="color: #666;">Berikut adalah ringkasan aktivitas di PKBM Anda hari ini.</p>
        </div>

        <!-- Statistik Dashboard -->
        <div class="stats-grid">
            <!-- Card 1: Siswa -->
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $stats['students']; ?></h3>
                    <p>Total Siswa</p>
                </div>
                <div class="stat-icon icon-blue">
                    <i class="fas fa-users"></i>
                </div>
            </div>

            <!-- Card 2: Kursus -->
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $stats['courses']; ?></h3>
                    <p>Program Kursus</p>
                </div>
                <div class="stat-icon icon-green">
                    <i class="fas fa-book"></i>
                </div>
            </div>

            <!-- Card 3: Galeri -->
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $stats['gallery']; ?></h3>
                    <p>Foto Kegiatan</p>
                </div>
                <div class="stat-icon icon-orange">
                    <i class="fas fa-images"></i>
                </div>
            </div>

            <!-- Card 4: Pesan (Opsional) -->
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $stats['inquiries']; ?></h3>
                    <p>Pesan Masuk</p>
                </div>
                <div class="stat-icon icon-purple">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
        </div>

        <!-- Tabel Pendaftar Terbaru -->
        <div class="recent-section">
            <div class="recent-header">
                <h3>Pendaftar Terbaru</h3>
                <a href="students.php" class="btn-sm">Lihat Semua</a>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Tanggal Daftar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($latest_students && $latest_students->num_rows > 0): ?>
                            <?php while($row = $latest_students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                                <td><?php echo date('d M Y, H:i', strtotime($row['tanggal_daftar'])); ?></td>
                                <td><span class="status-badge">Baru</span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding: 20px;">Belum ada data pendaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>
