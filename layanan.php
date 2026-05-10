<?php
session_start();
require 'koneksi.php';

// Ambil data konten layanan
 $data = [];
 $result = $conn->query("SELECT nama, isi FROM settings WHERE nama LIKE 'layanan_%'");
while ($row = $result->fetch_assoc()) {
    $data[$row['nama']] = $row['isi'];
}

// Cek Mode Edit
 $edit = isset($_GET['edit']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Simpan Perubahan
if (isset($_POST['simpan'])) {
    foreach ($_POST['konten'] as $nama => $isi) {
        $stmt = $conn->prepare("INSERT INTO settings (nama, isi) VALUES (?, ?) ON DUPLICATE KEY UPDATE isi = VALUES(isi)");
        $stmt->bind_param("ss", $nama, $isi);
        $stmt->execute();
    }
    header("Location: layanan.php");
    exit();
}

function tampil($key, $default = '') {
    global $data;
    return htmlspecialchars($data[$key] ?? $default);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Layanan | Millenium Auto Service</title>
    <link rel="stylesheet" href="style.css">
    
    <!-- CSS KHUSUS LAYANAN -->
    <style>
        .services-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 60px 20px;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .page-header { text-align: center; margin-bottom: 50px; }
        .page-header h2 { font-size: 2.2rem; color: #98bcf1; margin-bottom: 10px; }
        .page-header p { color: #ffffff; font-size: 1.1rem; }

        /* GRID LAYANAN */
        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .service-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #eee;
        }
        .service-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }

        /* BAGIAN GAMBAR / ICON */
        .service-icon-area {
            height: 180px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 4rem;
        }
        .service-content { padding: 25px; }
        
        .service-content h3 { color: #0d47a1; margin-top: 0; margin-bottom: 10px; font-size: 1.4rem; }
        .service-content p { color: #555; line-height: 1.6; margin-bottom: 20px; font-size: 0.95rem; }
        
        .btn-book {
            display: inline-block;
            background: #1976d2; color: white;
            padding: 10px 20px; border-radius: 5px;
            text-decoration: none; font-weight: 600;
            transition: background 0.3s;
        }
        .btn-book:hover { background: #0d47a1; }

        /* ADMIN EDIT STYLE */
        .edit-wrapper { background: #fff3cd; padding: 30px; border-radius: 10px; margin-bottom: 40px; }
        .edit-row { display: grid; grid-template-columns: 1fr; gap: 20px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="services-wrapper">

    <?php if ($edit): ?>
        <!-- MODE EDIT -->
        <div class="edit-wrapper">
            <h2 style="text-align: center; color: #856404;">Edit Layanan</h2>
            <form method="POST">
                <label>Judul Halaman:</label>
                <input type="text" name="konten[layanan_judul_halaman]" value="<?= tampil('layanan_judul_halaman') ?>" style="width:100%; padding:10px; margin-bottom:20px;">

                <?php for($i=1; $i<=3; $i++): ?>
                <div class="edit-row">
                    <h4>Layanan #<?= $i ?></h4>
                    <input type="text" name="konten[layanan_judul<?= $i ?>]" value="<?= tampil("layanan_judul{$i}") ?>" placeholder="Judul Layanan" style="width:100%; padding:10px; margin-bottom:10px;">
                    <textarea name="konten[layanan_isi<?= $i ?>]" rows="3" placeholder="Deskripsi Layanan" style="width:100%; padding:10px;"><?= tampil("layanan_isi{$i}") ?></textarea>
                </div>
                <?php endfor; ?>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" name="simpan" class="btn" style="background:#2e7d32;">💾 Simpan Perubahan</button>
                    <a href="layanan.php" class="btn" style="background:#666; margin-left:10px;">Batal</a>
                </div>
            </form>
        </div>

    <?php else: ?>
        <!-- MODE TAMPILAN -->
        
        <div class="page-header">
            <h2><?= tampil('layanan_judul_halaman', 'Layanan Kami') ?></h2>
            <p>Solusi lengkap untuk perawatan kendaraan kesayangan Anda</p>
        </div>

        <div class="service-grid">
            <?php for($i=1; $i<=3; $i++): 
                $judul = tampil("layanan_judul{$i}");
                $isi = tampil("layanan_isi{$i}");
                // Tentukan icon emoji berdasarkan urutan (bisa diubah nanti)
                $emojis = ['🛠️', '🛢️', '⚡'];
                $emoji = $emojis[$i-1] ?? '🔧';
            ?>
            <div class="service-card">
                <div class="service-icon-area">
                    <?= $emoji ?>
                </div>
                <div class="service-content">
                    <h3><?= $judul ?></h3>
                    <p><?= $isi ?></p>
                    <a href="booking.php" class="btn-book">Booking Layanan Ini</a>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div style="text-align: center; margin-top: 50px; padding-top:20px; border-top:1px dashed #ccc;">
                <a href="?edit=true" class="btn" style="background:#f57c00;">✏️ Edit Halaman Layanan</a>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>
<?php include 'footer.php'; ?>