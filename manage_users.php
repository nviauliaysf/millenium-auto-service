<?php
session_start();
require 'koneksi.php';
if ($_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
 $users = $conn->query("SELECT * FROM users")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><title>Kelola User</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php include 'navbar.php'; ?>
<div class="wrapper">
    <h2>Data Pengguna</h2>
    <table>
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>
        <?php foreach($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= $u['role'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="dashboard.php" class="btn btn-secondary" style="margin-top:20px;">Kembali</a>
</div>

</body>
</html>