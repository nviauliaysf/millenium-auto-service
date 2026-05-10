<?php
session_start();
require 'koneksi.php';

if(isset($_POST['register'])){
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // Validasi Konfirmasi Password
    if($password !== $confirm){
        $error = "Password dan konfirmasi tidak sama!";
    } else {
        // Cek apakah username/email sudah ada
        $check = $conn->query("SELECT * FROM users WHERE username='$username' OR email='$email'");
        if($check->num_rows > 0){
            $error = "Username atau email sudah terdaftar!";
        } else {
            // Hash Password dan Simpan
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt->bind_param("sss", $username, $email, $hashed);
            
            if($stmt->execute()){
                $success = "Registrasi berhasil! Silakan <a href='login.php' style='color:#0d47a1; font-weight:bold;'>Login disini</a>.";
            } else {
                $error = "Terjadi kesalahan sistem.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun | Millenium Auto Service</title>
    <link rel="stylesheet" href="style.css">
    
    <!-- CSS KHUSUS REGISTER (SAMA DENGAN LOGIN) -->
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-card {
            background: white;
            width: 100%;
            max-width: 420px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card h2 {
            color: #0d47a1;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }
        
        .login-card p {
            color: #666;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }

        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; color: #444; }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .form-input:focus {
            border-color: #0d47a1;
            outline: none;
            background: #f9fbff;
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, #0d47a1, #1976d2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 71, 161, 0.3);
        }

        .error-msg, .success-msg {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: left;
        }
        .error-msg { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .success-msg { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }

        .login-footer {
            margin-top: 20px;
            font-size: 0.85rem;
            color: #666;
        }
        .login-footer a { color: #0d47a1; text-decoration: none; font-weight: 600; }
        .login-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-card">
    <!-- Icon -->
    <div style="font-size: 3rem; margin-bottom: 10px;">📝</div>
    <h2>Buat Akun Baru</h2>
    <p>Daftar untuk mulai booking servis kendaraan.</p>

    <?php if(isset($error)): ?>
        <div class="error-msg"><?= $error; ?></div>
    <?php endif; ?>
    
    <?php if(isset($success)): ?>
        <div class="success-msg"><?= $success; ?></div>
    <?php endif; ?>

    <?php if(!isset($success)): ?>
    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-input" placeholder="Buat username" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-input" placeholder="Contoh: email@anda.com" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-input" placeholder="Buat password" required>
        </div>

        <div class="form-group">
            <label>Konfirmasi Password</label>
            <input type="password" name="confirm" class="form-input" placeholder="Ulangi password" required>
        </div>

        <button type="submit" name="register" class="btn-register">DAFTAR SEKARANG</button>
    </form>
    <?php endif; ?>

    <div class="login-footer">
        Sudah punya akun? <a href="login.php">Login di sini</a>
        <br><br>
        <a href="index.php" style="color: #999; text-decoration: none; font-size: 0.8rem;">← Kembali ke Beranda</a>
    </div>
</div>

</body>
</html>