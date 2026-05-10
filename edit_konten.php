<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['simpan'])) {
    foreach ($_POST['konten'] as $nama => $isi) {
        $stmt = $conn->prepare("UPDATE settings SET isi=? WHERE nama=?");
        $stmt->bind_param("ss", $isi, $nama);
        $stmt->execute();
    }
    $success = "Konten berhasil diperbarui";
}

$data = [];
$result = $conn->query("SELECT * FROM settings");
while ($row = $result->fetch_assoc()) {
    $data[$row['nama']] = $row['isi'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- TAMBAHKAN INI -->
    <title>Layanan | Bengkel TKR</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="manage-container">
    <h2>Kelola Konten Website</h2>

    <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>

    <form method="POST">
        <h3>Hero Section</h3>
        <label>Judul Hero</label>
        <input type="text" name="konten[hero_title]" value="<?= htmlspecialchars($data['hero_title'] ?? ''); ?>">

        <h3>Highlight Cards</h3>
        <label>Judul Card 1</label>
        <input type="text" name="konten[card1_title]" value="<?= htmlspecialchars($data['card1_title'] ?? ''); ?>">

        <label>Teks Card 1</label>
        <textarea name="konten[card1_text]" rows="3"><?= htmlspecialchars($data['card1_text'] ?? ''); ?></textarea>

        <label>Judul Card 2</label>
        <input type="text" name="konten[card2_title]" value="<?= htmlspecialchars($data['card2_title'] ?? ''); ?>">

        <label>Teks Card 2</label>
        <textarea name="konten[card2_text]" rows="3"><?= htmlspecialchars($data['card2_text'] ?? ''); ?></textarea>

        <label>Judul Card 3</label>
        <input type="text" name="konten[card3_title]" value="<?= htmlspecialchars($data['card3_title'] ?? ''); ?>">

        <label>Teks Card 3</label>
        <textarea name="konten[card3_text]" rows="3"><?= htmlspecialchars($data['card3_text'] ?? ''); ?></textarea>

        <h3>Informasi Kontak</h3>
        <label>Tentang Bengkel</label>
        <textarea name="konten[tentang]" rows="5"><?= htmlspecialchars($data['tentang'] ?? ''); ?></textarea>

        <label>Alamat</label>
        <input type="text" name="konten[alamat]" value="<?= htmlspecialchars($data['alamat'] ?? ''); ?>">

        <label>Telepon</label>
        <input type="text" name="konten[telepon]" value="<?= htmlspecialchars($data['telepon'] ?? ''); ?>">

        <label>Email</label>
        <input type="email" name="konten[email]" value="<?= htmlspecialchars($data['email'] ?? ''); ?>">

        <button type="submit" name="simpan">Simpan Perubahan</button>
    </form>
</div>

</body>
</html>
