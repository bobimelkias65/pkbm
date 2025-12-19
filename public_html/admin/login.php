<?php
session_start();
// File db_connect.php harus di-include. Pastikan path-nya benar.
// Kita gunakan '../' karena file ini ada di dalam folder admin/
require_once '../includes/db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi!";
    } else {
        // 1. SIAPKAN QUERY (PREPARED STATEMENT)
        // Gunakan tanda tanya (?) sebagai placeholder, jangan masukkan variabel langsung
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // 2. BIND PARAMETER
            // "s" berarti tipe datanya string
            $stmt->bind_param("s", $username);
            
            // 3. EKSEKUSI
            $stmt->execute();
            
            // 4. AMBIL HASIL
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                
                // 5. VERIFIKASI PASSWORD (HASH)
                // Asumsi password di database sudah di-hash menggunakan password_hash()
                if (password_verify($password, $row['password'])) {
                    
                    // 6. REGENERATE SESSION ID (PENTING UNTUK KEAMANAN)
                    // Mencegah serangan Session Fixation
                    session_regenerate_id(true);
                    
                    // Set variabel session
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $row['role']; // Pastikan kolom role ada di DB
                    
                    // Redirect ke dashboard
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Password salah!";
                }
            } else {
                $error = "Username tidak ditemukan!";
            }
            $stmt->close();
        } else {
            $error = "Terjadi kesalahan sistem.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - PKBM</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; }
        .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-login { width: 100%; padding: 10px; background-color: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-login:hover { background-color: #34495e; }
        .error-msg { color: red; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>

<div class="login-container">
    <h2 style="text-align: center;">Login Admin</h2>
    
    <?php if($error): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn-login">Masuk</button>
    </form>
</div>

</body>
</html>
