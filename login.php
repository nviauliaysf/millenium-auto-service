<?php
session_start();
require 'koneksi.php';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Cek admin hardcoded
    if ($username === 'milleniumbengkeltkr' && $password === 'milleniumdantoyota') {
        $_SESSION['user_id'] = 0; 
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'admin';
        header("Location: dashboard.php");
        exit;
    }

    // Cek User Database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'customer';

            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'dashboard.php';
            header("Location: $redirect");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "User tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Millenium Auto Service</title>
    <link rel="stylesheet" href="style.css">
    
    <!-- CSS KHUSUS LOGIN -->
    <style>
        /* Background full screen agar bersih */
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
            max-width: 400px;
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
            margin-bottom: 30px;
            font-size: 0.9rem;
        }

        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: #444; }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            box-sizing: border-box; /* Penting agar tidak melebar */
        }
        
        .form-input:focus {
            border-color: #0d47a1;
            outline: none;
            background: #f9fbff;
        }

        .btn-login {
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
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 71, 161, 0.3);
        }

        .error-msg {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid #ffcdd2;
        }

        .login-footer {
            margin-top: 20px;
            font-size: 0.85rem;
        }
        .login-footer a { color: #0d47a1; text-decoration: none; font-weight: 600; }
        .login-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-card">
    <!-- Logo / Brand kecil -->
    <div style="font-size: 3rem; margin-bottom: 10px;">🔧</div>
    <h2>Masuk Akun</h2>
    <p>Silakan login untuk mengakses layanan booking.</p>

    <?php if(isset($error)): ?>
        <div class="error-msg"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-input" placeholder="Masukkan username" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-input" placeholder="Masukkan password" required>
        </div>

        <button type="submit" name="login" class="btn-login">LOGIN</button>
    </form>
    <div class="login-footer">
        Belum punya akun? <a href="register.php">Daftar disini</a>
        <br><br>
        <a href="index.php" style="color: #999; text-decoration: none; font-size: 0.8rem;">← Kembali ke Beranda</a>
    </div>
</div>

</body>
</html>