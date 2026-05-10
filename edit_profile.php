<?php
session_start();
require 'koneksi.php';

// Cek Keamanan: Hanya customer yang boleh akses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: dashboard.php");
    exit();
}

 $user_id = $_SESSION['user_id'];
 $success = "";
 $error = "";

// Ambil data user saat ini
 $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
 $stmt->bind_param("i", $user_id);
 $stmt->execute();
 $user = $stmt->get_result()->fetch_assoc();

// PROSES UPDATE
if (isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $update_pass = false;
    $password_hash = $user['password']; // Default pakai password lama

    // --- LOGIKA GANTI PASSWORD ---
    // Jika user mengisi kolom password baru
    if (!empty($new_pass)) {
        // 1. Cek apakah password saat ini cocok
        if (password_verify($current_pass, $user['password'])) {
            // 2. Cek konfirmasi password
            if ($new_pass === $confirm_pass) {
                $password_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $update_pass = true;
            } else {
                $error = "Konfirmasi password baru tidak cocok.";
            }
        } else {
            $error = "Password saat ini salah.";
        }
    }

    // Jika tidak ada error password, lanjut update data
    if (empty($error)) {
        // Cek apakah username/email baru sudah dipakai orang LAIN
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check_stmt->bind_param("ssi", $new_username, $new_email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Username atau Email sudah digunakan oleh akun lain.";
        } else {
            // Update Database
            if ($update_pass) {
                // Update termasuk password
                $upd_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                $upd_stmt->bind_param("sssi", $new_username, $new_email, $password_hash, $user_id);
            } else {
                // Update hanya username & email
                $upd_stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $upd_stmt->bind_param("ssi", $new_username, $new_email, $user_id);
            }

            if ($upd_stmt->execute()) {
                // Update Session Username agar nama di Navbar berubah
                $_SESSION['username'] = $new_username;
                $success = "Profil berhasil diperbarui!";
                
                // Refresh data $user agar tampilan terbaru
                $user['username'] = $new_username;
                $user['email'] = $new_email;
            } else {
                $error = "Terjadi kesalahan sistem.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profil | Millenium Auto Service</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container { max-width: 600px; margin: 40px auto; }
        .profile-card { background: white; padding: 40px; border-radius: 16px; box-shadow: var(--shadow); }
        .form-section { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .form-section:last-child { border-bottom: none; }
        .form-section h3 { color: var(--primary); margin-bottom: 15px; font-size: 1.1rem; }
        .note { font-size: 0.85rem; color: var(--text-light); margin-top: 5px; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="profile-container">
        <div class="profile-card">
            <h2 style="text-align: center; color: var(--primary); margin-bottom: 30px;">⚙️ Kelola Akun</h2>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <!-- BAGIAN 1: INFO DIRI -->
                <div class="form-section">
                    <h3>Informasi Diri</h3>
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <!-- BAGIAN 2: KEAMANAN (PASSWORD) -->
                <div class="form-section">
                    <h3>Ubah Password</h3>
                    <p class="note">Biarkan kosong jika tidak ingin mengubah password.</p>
                    
                    <label>Password Saat Ini</label>
                    <input type="password" name="current_password">
                    
                    <label>Password Baru</label>
                    <input type="password" name="new_password">
                    
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password">
                </div>

                <button type="submit" name="update_profile" class="btn" style="width: 100%;">Simpan Perubahan</button>
                <a href="dashboard.php" class="btn btn-secondary" style="width: 100%; margin-top: 10px; text-align: center;">Kembali ke Dashboard</a>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
</body>
</html>