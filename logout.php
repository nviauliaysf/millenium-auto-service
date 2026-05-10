<?php
session_start();

// Handle Tombol Konfirmasi Logout
if (isset($_POST['confirm_logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Jika belum login, lempar ke login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Konfirmasi Logout | Millenium Auto Service</title>
    <link rel="stylesheet" href="style.css">
    
    <!-- CSS KHUSUS LOGOUT -->
    <style>
        body {
            background: linear-gradient(135deg, #fffbf0 0%, #fff3e0 100%); /* Background sedikit kekuningan (peringatan) */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }

        .logout-card {
            background: white;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 5px solid #d32f2f; /* Garis merah di atas */
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .logout-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
            color: #d32f2f;
        }

        .logout-card h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.5rem;
        }
        
        .logout-card p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .btn-logout {
            width: 100%;
            padding: 12px;
            background: #d32f2f;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-bottom: 10px;
        }
        
        .btn-logout:hover { background: #b71c1c; }

        .btn-cancel {
            display: block;
            width: 100%;
            padding: 12px;
            background: transparent;
            color: #666;
            border: 1px solid #ccc;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover { background: #f5f5f5; color: #333; }
    </style>
</head>
<body>

<div class="logout-card">
    <div class="logout-icon">⚠️</div>
    <h2>Yakin Ingin Keluar?</h2>
    <p>Anda akan diarahkan ke halaman login setelah logout.</p>

        <form method="POST">
        <button type="submit" name="confirm_logout" class="btn-logout">Ya, Keluar</button>
    </form>
    
    <!-- Ubah link href="dashboard.php" menjadi href="index.php" -->
    <a href="index.php" class="btn-cancel">Batal, Kembali ke Beranda</a>
</div>

</body>
</html>