<?php
/**
 * Tool Sederhana untuk Generate Hash Password
 * Gunakan file ini untuk mendapatkan kode hash yang akan disimpan di database (tabel users).
 * * Cara Penggunaan:
 * 1. Buka file ini di browser: http://localhost/pkbm_harapan_kasih/admin/generate_hash.php
 * 2. Secara default akan membuat hash untuk password "admin123"
 * 3. Untuk password lain, tambahkan ?password=... di URL
 * Contoh: generate_hash.php?password=RahasiaNegara
 */

$password = isset($_GET['password']) ? $_GET['password'] : 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Password Hash</title>
    <style>
        body { font-family: sans-serif; padding: 20px; line-height: 1.6; background: #f1f5f9; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        code { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
        textarea { width: 100%; padding: 10px; font-family: monospace; border: 1px solid #cbd5e1; border-radius: 5px; margin-top: 5px; }
        .note { font-size: 0.9em; color: #64748b; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔑 Password Hash Generator</h2>
        
        <p>Password Asli: <strong><?= htmlspecialchars($password) ?></strong></p>
        
        <label><strong>Hash Password (Copy ini ke Database):</strong></label>
        <textarea rows="3" readonly onclick="this.select()"><?= $hash ?></textarea>
        
        <div class="note">
            <p><strong>Cara Mengganti Password Admin:</strong></p>
            <ol>
                <li>Copy kode Hash di dalam kotak di atas.</li>
                <li>Buka <strong>phpMyAdmin</strong>.</li>
                <li>Buka database <code>pkbm_harapan_kasih</code> -> tabel <code>users</code>.</li>
                <li>Edit user admin, lalu paste kode hash tersebut ke kolom <code>password</code>.</li>
                <li>Simpan perubahan. Sekarang Anda bisa login dengan password: <code><?= htmlspecialchars($password) ?></code></li>
            </ol>
            <p style="margin-top: 15px;">
                Ingin buat password lain? <br>
                <a href="?password=admin123">Default (admin123)</a> | 
                <a href="?password=passwordBaru">Contoh Lain</a>
            </p>
        </div>
    </div>
</body>
</html>