<?php
session_start();
require 'koneksi.php';

// --- 1. AMBIL DATA KONTAK ---
 $data = [];
 $result = $conn->query("SELECT nama, isi FROM settings WHERE nama IN ('kontak_alamat', 'kontak_telepon', 'kontak_email', 'kontak_jam')");
while ($row = $result->fetch_assoc()) {
    $data[$row['nama']] = $row['isi'];
}

function tampil($key, $default = '') {
    global $data;
    return htmlspecialchars($data[$key] ?? $default);
}

// --- 2. CEK MODE EDIT & SIMPAN ---
 $edit = isset($_GET['edit']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if (isset($_POST['simpan'])) {
    foreach ($_POST['konten'] as $nama => $isi) {
        $stmt = $conn->prepare("INSERT INTO settings (nama, isi) VALUES (?, ?) ON DUPLICATE KEY UPDATE isi = VALUES(isi)");
        $stmt->bind_param("ss", $nama, $isi);
        $stmt->execute();
    }
    header("Location: kontak.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kontak Kami | Millenium Auto Service</title>
    <link rel="stylesheet" href="style.css">
    
    <!-- CSS KHUSUS KONTAK -->
    <style>
        .contact-wrapper {
            max-width: 1100px;
            margin: 60px auto;
            padding: 0 20px;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .contact-header { text-align: center; margin-bottom: 50px; }
        .contact-header h2 { font-size: 2.2rem; color: #000916; margin-bottom: 10px; }
        .contact-header p { color: #ffffff; font-size: 1.1rem; }

        /* GRID LAYOUT: INFO KIRI - PETA KANAN */
        .contact-layout {
            display: grid;
            grid-template-columns: 1fr 1.5fr; /* Kiri 40%, Kanan 60% */
            gap: 40px;
            align-items: start;
        }

        /* KARTU INFO KONTAK */
        .info-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .info-card:hover { transform: translateX(5px); border-left: 5px solid #0d47a1; }
        
        .info-icon {
            background: #e3f2fd; color: #0d47a1;
            width: 50px; height: 50px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; flex-shrink: 0;
        }
        .info-text h4 { margin: 0 0 5px 0; color: #333; font-size: 1.1rem; }
        .info-text p { margin: 0; color: #666; line-height: 1.5; }

        /* CONTAINER PETA */
        .map-container {
            background: white;
            padding: 10px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: 100%;
            min-height: 400px;
        }
        .map-container iframe {
            width: 100%; height: 100%;
            border-radius: 10px; border: none;
        }

        /* STYLE ADMIN EDIT */
        .edit-box { background: #fff3cd; padding: 30px; border-radius: 10px; border: 1px solid #ffeeba; }
        .edit-box h2 { text-align: center; color: #856404; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        .form-input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }

        /* RESPONSIVE HP */
        @media (max-width: 768px) {
            .contact-layout { grid-template-columns: 1fr; }
            .map-container { min-height: 300px; }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="contact-wrapper">

    <?php if ($edit): ?>
        <!-- ================= MODE EDIT ADMIN ================= -->
        <div class="edit-box">
            <h2>✏️ Edit Informasi Kontak</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="konten[kontak_alamat]" rows="3" class="form-input"><?= tampil('kontak_alamat') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="konten[kontak_telepon]" value="<?= tampil('kontak_telepon') ?>" class="form-input">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="konten[kontak_email]" value="<?= tampil('kontak_email') ?>" class="form-input">
                </div>
                <div class="form-group">
                    <label>Jam Operasional</label>
                    <input type="text" name="konten[kontak_jam]" value="<?= tampil('kontak_jam') ?>" class="form-input" placeholder="Contoh: Senin - Jumat, 08.00 - 16.00">
                </div>
                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" name="simpan" class="btn" style="background:#2e7d32;">💾 Simpan Perubahan</button>
                    <a href="kontak.php" class="btn" style="background:#666; margin-left:10px;">Batal</a>
                </div>
            </form>
        </div>

    <?php else: ?>
        <!-- ================= MODE TAMPILAN PUBLIK ================= -->
        
        <div class="contact-header">
            <h2>Hubungi Kami</h2>
            <p>Kami siap melayani kebutuhan perawatan kendaraan Anda.</p>
        </div>

        <div class="contact-layout">
            <!-- KOLOM KIRI: INFO -->
            <div class="contact-info">
                <div class="info-card">
                    <div class="info-icon">📍</div>
                    <div class="info-text">
                        <h4>Alamat</h4>
                        <p><?= nl2br(tampil('kontak_alamat', 'Jl. Raya Cibinong No. 123')) ?></p>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon">📞</div>
                    <div class="info-text">
                        <h4>Telepon / WhatsApp</h4>
                        <p><?= tampil('kontak_telepon', '(021) 875-xxxx') ?></p>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon">✉️</div>
                    <div class="info-text">
                        <h4>Email</h4>
                        <p><?= tampil('kontak_email', 'info@bengkeltkr.com') ?></p>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon">⏰</div>
                    <div class="info-text">
                        <h4>Jam Operasional</h4>
                        <p><?= tampil('kontak_jam', 'Senin - Jumat: 09.00 - 16.30 WIB') ?></p>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: PETA -->
            <div class="map-container">
                <iframe 
                    src="https://www.google.com/maps?q=SMKN+1+Cibinong&output=embed" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>

        <!-- TOMBOL EDIT ADMIN -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div style="text-align: center; margin-top: 50px; padding-top:20px; border-top:1px dashed #ccc;">
                <a href="?edit=true" class="btn" style="background:#f57c00;">✏️ Edit Kontak</a>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>
<?php include 'footer.php'; ?>