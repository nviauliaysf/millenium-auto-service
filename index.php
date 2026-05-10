<?php
session_start();
require 'koneksi.php';

// Ambil data dari database untuk konten dinamis
 $data = [];
 $res = $conn->query("SELECT nama, isi FROM settings");
while ($r = $res->fetch_assoc()) {
    $data[$r['nama']] = $r['isi'];
}

// Cek apakah sedang dalam mode edit (Admin)
 $edit = isset($_GET['edit']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Logika Simpan (Jika Admin menekan tombol simpan)
if (isset($_POST['simpan'])) {
    foreach ($_POST['konten'] as $k => $v) {
        $stmt = $conn->prepare("INSERT INTO settings (nama, isi) VALUES (?, ?) ON DUPLICATE KEY UPDATE isi = VALUES(isi)");
        $stmt->bind_param("ss", $k, $v);
        $stmt->execute();
    }
    header("Location: index.php");
    exit();
}

function show($k, $d = '') {
    global $data;
    return htmlspecialchars($data[$k] ?? $d);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | Millenium Auto Service</title>
    
    <!-- Memanggil CSS Utama -->
    <link rel="stylesheet" href="style.css?v=1.1">
    
    <!-- CSS KHUSUS HALAMAN INDEX -->
    <style>
        /* 1. HERO SECTION (Bagian Biru di Atas) */
        .hero {
            background: rgba(0, 0, 0, 0.4); 
            color: white;
            padding: 80px 20px;
            text-align: center;
            border-radius: 0 0 30px 30px;
            margin-bottom: 60px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .hero p {
            font-size: 1.3rem;
            opacity: 0.9;
            max-width: 700px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }

        .btn-cta {
            background: #142278;
            color: white;
            padding: 15px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            transition: 0.3s;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
        }
        .btn-cta:hover {
            background: #1f06d99e;
            transform: translateY(-3px);
        }

        /* 2. SECTION LAYANAN UNGGULAN (Desain Background Gelap dari Request Kamu) */
        .layanan-unggulan-section {
            background: rgba(255, 255, 255, 0.8); /* Putih transparan */
            color: #333; /* Ubah teks jadi hitam agar kontras */
            padding: 80px 20px;
            text-align: center;
            margin-bottom: 60px;
            border-radius: 20px;
        }

        .judul-unggulan {
            color: #0D47A1; 
            font-size: 2.5rem;
            margin-bottom: 50px;
            font-weight: 800;
            position: relative;
            display: inline-block;
        }

        .judul-unggulan::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: #0D47A1; /* Aksen Biru */
            margin: 15px auto 0;
            border-radius: 2px;
        }

        .grid-kartu-lu {
            display: grid;
            /* Responsive: 3 kolom di PC, 1 kolom di HP */
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .kartu-lu {
            background: white;
            color: #333;
            padding: 40px 30px;
            border-radius: 12px;
            text-align: center;
            /* Bayangan agar kartu menonjol dari background gelap */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3); 
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-bottom: 4px solid #0D47A1;
        }

        .kartu-lu:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(13, 71, 161, 0.4);
        }

        .icon-lu {
            font-size: 3.5rem;
            margin-bottom: 20px;
            display: inline-block;
            /* Animasi sedikit berdenyut */
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .kartu-lu h3 {
            font-size: 1.4rem;
            margin-bottom: 10px;
            color: #111;
        }

        .kartu-lu p {
            font-size: 1rem;
            color: #666;
            line-height: 1.6;
        }

        /* 3. STYLE MODE EDIT ADMIN (Agar form edit rapi) */
        .edit-container {
            background: #FFFBEB;
            padding: 40px;
            border-radius: 12px;
            border: 2px dashed #F59E0B;
            margin-bottom: 40px;
        }
        .edit-group { margin-bottom: 20px; }
        .edit-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .edit-input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .judul-unggulan { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<?php if($edit): ?>
    <!-- ==========================================
         MODE EDIT (Hanya Muncul Kalau Admin Login & Klik Edit)
    ========================================== -->
    <div class="wrapper">
        <div class="edit-container">
            <h2 style="text-align: center; color: #856404;">✏️ Edit Halaman Beranda</h2>
            <form method="POST">
                <div class="edit-group">
                    <label>Judul Utama (Hero)</label>
                    <input type="text" name="konten[hero_title]" value="<?= show('hero_title') ?>" class="edit-input">
                </div>
                <div class="edit-group">
                    <label>Deskripsi Hero</label>
                    <textarea name="konten[hero_desc]" rows="3" class="edit-input"><?= show('hero_desc') ?></textarea>
                </div>
                
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;">
                
                <?php for($i=1; $i<=3; $i++): ?>
                    <div class="edit-group">
                        <strong>Fitur #<?= $i ?></strong><br>
                        <label>Judul</label>
                        <input type="text" name="konten[card<?= $i ?>_title]" value="<?= show("card{$i}_title") ?>" class="edit-input">
                        <label>Isi</label>
                        <textarea name="konten[card<?= $i ?>_text]" rows="2" class="edit-input"><?= show("card{$i}_text") ?></textarea>
                    </div>
                <?php endfor; ?>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" name="simpan" class="btn">💾 Simpan Perubahan</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

<?php else: ?>
    <!-- ==========================================
         MODE TAMPILAN PUBLIK (User Liat Ini)
    ========================================== -->
    
    <!-- 1. HERO SECTION -->
    <section class="hero">
        <div class="wrapper" style="background: transparent; box-shadow: none; padding: 0; margin: 0 auto;">
            <h1><?= show('hero_title', 'Servis Mobil Profesional') ?></h1>
            <p><?= show('hero_desc', 'Solusi terbaik dan terpercaya untuk kendaraan Anda.') ?></p>
            <a href="booking.php" class="btn-cta">Booking Sekarang</a>
        </div>
    </section>

    <!-- 2. LAYANAN UNGGULAN (Background Gelap + Kartu Putih) -->
    <section class="layanan-unggulan-section">
        <div class="container-lu">
            <h2 class="judul-unggulan">Layanan Unggulan</h2>
            
            <div class="grid-kartu-lu">
                <!-- KARTU 1 -->
                <div class="kartu-lu">
                    <div class="icon-lu"> <img src="assets/ganti_oli.png"></div>
                    <h3>Ganti Oli Mesin & Filter Oli</h3>
                    <p>Full synthetic oil protection untuk performa mesin maksimal dan umur mesin panjang.</p>
                </div>

                <!-- KARTU 2 -->
                <div class="kartu-lu">
                    <div class="icon-lu"> <img src="assets/body.png"></div>
                    <h3>Service Rem</h3>
                    <p>Brake system maintenance untuk pengereman maksimal dan keamanan berkendara lebih optimal.</p>
                </div>

                <!-- KARTU 3 -->
                <div class="kartu-lu">
                    <div class="icon-lu"><img src="assets/tune.png"></div>
                    <h3>Pemeriksaan Menyeluruh</h3>
                    <p>Comprehensive inspection untuk memastikan seluruh komponen kendaraan bekerja optimal dan menjaga performa tetap prima.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. FEATURES SECTION (Konten Dinamis dari Database) -->
    <div class="wrapper">
        <h2 style="text-align: center; margin-bottom: 40px; color: var(--primary);">Mengapa Memilih Kami?</h2>
        <div class="features">
            <?php for($i=1; $i<=3; $i++): ?>
            <div class="feature">
                <div class="feature-icon">⭐</div>
                <h3><?= show("card{$i}_title", "Keunggulan $i") ?></h3>
                <p><?= show("card{$i}_text", "Deskripsi singkat mengenai keunggulan layanan kami.") ?></p>
            </div>
            <?php endfor; ?>
        </div>
        
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <div style="text-align:center; margin-top:50px;">
                <a href="?edit=true" class="btn btn-secondary">✏️ Edit Halaman Ini</a>
            </div>
        <?php endif; ?>
    </div>

<?php endif; ?>

<?php include 'footer.php'; ?>

</body>
</html>