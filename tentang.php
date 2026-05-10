<?php
session_start();
require 'koneksi.php';

// --- 1. AMBIL DATA KONTEN ---
 $keys = [
    'tentang_profil', 'tentang_keunggulan', 'tentang_kerjasama', 
    'tentang_dok_1_judul', 'tentang_dok_1_isi', 'tentang_dok_1_img',
    'tentang_dok_2_judul', 'tentang_dok_2_isi', 'tentang_dok_2_img',
    'tentang_dok_3_judul', 'tentang_dok_3_isi', 'tentang_dok_3_img',
    'tentang_visi', 'tentang_misi' 
];

 $query_keys = implode("','", $keys);
 $result = $conn->query("SELECT nama, isi FROM settings WHERE nama IN ('$query_keys')");
 $data = [];
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
    // Handle Text Inputs
    foreach ($_POST['konten'] as $nama => $isi) {
        $stmt = $conn->prepare("INSERT INTO settings (nama, isi) VALUES (?, ?) ON DUPLICATE KEY UPDATE isi = VALUES(isi)");
        $stmt->bind_param("ss", $nama, $isi);
        $stmt->execute();
    }

    // Handle File Uploads (Foto Galeri)
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    for($i=1; $i<=3; $i++) {
        $file_key = "foto_dokumen_$i";
        $db_key = "tentang_dok_{$i}_img";

        // Cek apakah ada file baru diupload
        if(isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == 0) {
            $new_name = time() . "_dok_{$i}_" . basename($_FILES[$file_key]["name"]);
            $target_file = $target_dir . $new_name;
            
            if(move_uploaded_file($_FILES[$file_key]["tmp_name"], $target_file)) {
                // Update nama file di database
                $stmt = $conn->prepare("INSERT INTO settings (nama, isi) VALUES (?, ?) ON DUPLICATE KEY UPDATE isi = VALUES(isi)");
                $stmt->bind_param("ss", $db_key, $new_name);
                $stmt->execute();
            }
        }
    }

    header("Location: tentang.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tentang Kami | Millenium Auto Service</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="about-wrapper">

    <?php if ($edit): ?>
        <!-- ================= MODE EDIT ADMIN ================= -->
        <div class="edit-mode-wrapper">
            <h2>⚙️ Edit Halaman Tentang</h2>
            <form method="POST" enctype="multipart/form-data">
                
                <div class="edit-form-group">
                    <label class="edit-form-label">1. Profil Singkat</label>
                    <textarea name="konten[tentang_profil]" rows="4" class="admin-input"><?= tampil('tentang_profil') ?></textarea>
                </div>

                <div class="edit-form-group">
                    <label class="edit-form-label">2. Visi</label>
                    <input type="text" name="konten[tentang_visi]" value="<?= tampil('tentang_visi') ?>" class="admin-input">
                    
                    <label class="edit-form-label" style="margin-top:15px;">Misi</label>
                    <textarea name="konten[tentang_misi]" rows="3" class="admin-input"><?= tampil('tentang_misi') ?></textarea>
                </div>

                <div class="edit-form-group">
                    <label class="edit-form-label">3. Keunggulan (Pisahkan dengan Enter)</label>
                    <textarea name="konten[tentang_keunggulan]" rows="4" class="admin-input"><?= tampil('tentang_keunggulan') ?></textarea>
                </div>

                <div class="edit-form-group">
                    <label class="edit-form-label">4. Kerja Sama Industri (Pisahkan dengan Enter)</label>
                    <textarea name="konten[tentang_kerjasama]" rows="3" class="admin-input"><?= tampil('tentang_kerjasama') ?></textarea>
                </div>

                <div class="edit-form-group">
                    <label class="edit-form-label">5. Dokumentasi Kegiatan (Upload Foto)</label>
                    <?php for($i=1; $i<=3; $i++): ?>
                        <div style="border:1px dashed #ccc; padding:15px; border-radius:5px; margin-bottom:15px; background:white;">
                            <strong>Kegiatan <?= $i ?>:</strong><br>
                            <input type="text" name="konten[tentang_dok_<?= $i ?>_judul]" value="<?= tampil("tentang_dok_{$i}_judul") ?>" class="admin-input" placeholder="Judul Foto">
                            <textarea name="konten[tentang_dok_<?= $i ?>_isi]" rows="2" class="admin-input" placeholder="Deskripsi Singkat"><?= tampil("tentang_dok_{$i}_isi") ?></textarea>
                            
                            <!-- INPUT FILE BARU -->
                            <label>Ganti/Foto Baru:</label>
                            <input type="file" name="foto_dokumen_<?= $i ?>" accept="image/*">
                            
                            <?php 
                                $current_img = tampil("tentang_dok_{$i}_img");
                                if($current_img): 
                            ?>
                                <small>Foto saat ini: <a href="uploads/<?= $current_img ?>" target="_blank">Lihat</a></small>
                                <br><img src="uploads/<?= $current_img ?>" style="width:100px; height:auto; margin-top:5px; border:1px solid #ccc;">
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <button type="submit" name="simpan" class="btn-custom btn-success">💾 Simpan Perubahan</button>
                    <a href="tentang.php" class="btn-custom btn-cancel">Batal</a>
                </div>
            </form>
        </div>

    <?php else: ?>
        <!-- ================= MODE TAMPILAN PUBLIK ================= -->

        <h2 class="section-title">Tentang Kami</h2>
        
        <!-- 1. PROFIL -->
        <div class="profil-box">
            <?= nl2br(tampil('tentang_profil', 'Silakan login sebagai admin untuk mengisi Profil.')) ?>
        </div>

        <!-- 2. VISI & MISI -->
        <div class="vm-container">
            <div class="vm-card">
                <h3>Visi</h3>
                <p><?= tampil('tentang_visi', 'Belum ada visi.') ?></p>
            </div>
            <div class="vm-card">
                <h3>Misi</h3>
                <ul>
                    <?php 
                        $misiLines = explode("\n", tampil('tentang_misi'));
                        foreach($misiLines as $m) {
                            $m = trim($m);
                            if($m !== '') echo "<li>" . htmlspecialchars($m) . "</li>";
                        }
                    ?>
                </ul>
            </div>
        </div>

        <!-- 3. KEUNGGULAN -->
        <h2 class="section-title">Mengapa Memilih Kami?</h2>
        <div class="keunggulan-list">
            <?php 
                $list = explode("\n", tampil('tentang_keunggulan', 'Pelayanan Terbaik\nProfesional'));
                foreach($list as $item) {
                    $item = trim($item);
                    if($item) {
                        echo "<div class='keunggulan-item'>
                                <div class='icon-check'>✓</div>
                                <div>".htmlspecialchars($item)."</div>
                              </div>";
                    }
                }
            ?>
        </div>

        <!-- 4. KERJA SAMA -->
        <h2 class="section-title">Kerja Sama Industri</h2>
        <div class="partners-grid">
            <?php 
                $partners = explode("\n", tampil('tentang_kerjasama', 'Toyota\nHonda'));
                foreach($partners as $p) {
                    $p = trim($p);
                    if($p) echo "<div class='partner-card'>".htmlspecialchars($p)."</div>";
                }
            ?>
        </div>

        <!-- 5. DOKUMENTASI (DENGAN FOTO) -->
        <h2 class="section-title">Galeri Kegiatan</h2>
        <div class="doc-grid">
            <?php for($i=1; $i<=3; $i++): 
                $judul = tampil("tentang_dok_{$i}_judul");
                $isi = tampil("tentang_dok_{$i}_isi");
                $foto = tampil("tentang_dok_{$i}_img");
                if(!empty($judul)):
            ?>
            <div class="doc-card">
                <!-- TAMPILKAN FOTO ASLI JIKA ADA -->
                <?php if($foto): ?>
                    <div class="doc-img-real" style="height:200px; overflow:hidden;">
                        <img src="uploads/<?= $foto ?>" alt="<?= htmlspecialchars($judul) ?>" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                <?php else: ?>
                    <div class="doc-img-placeholder">Foto Tidak Tersedia</div>
                <?php endif; ?>
                
                <div class="doc-content">
                    <h4><?= $judul ?></h4>
                    <p><?= nl2br($isi) ?></p>
                </div>
            </div>
            <?php endif; endfor; ?>
        </div>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div style="text-align: center; margin-top: 50px; padding-top: 20px; border-top: 2px dashed #ccc;">
                <a href="?edit=true" class="btn-custom">✏️ Edit Halaman Ini</a>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<!-- STYLE KHUSUS TENTANG (Embeded) -->
<style>
    /* =========================================
       STYLE KHUSUS HALAMAN TENTANG KAMI
       Tema: Modern Depth & Soft Contrast
       ========================================= */

    .about-wrapper {
        max-width: 1100px;
        margin: 40px auto;
        padding: 60px 40px;
        font-family: 'Segoe UI', sans-serif;
        
        /* PERUBAHAN 1: Latar belakang Abu-abu Lembut (Soft Gray) */
        background-color: #F1F5F9; 
        border-radius: 20px;
        position: relative;
        z-index: 1;
        min-height: 80vh;
    }

    /* --- 1. JUDUL SECTION --- */
    .section-title {
        text-align: center;
        font-size: 2.2rem;
        color: #0D47A1; /* Warna Utama Branding */
        margin-bottom: 50px;
        font-weight: 800;
        letter-spacing: -1px;
        position: relative;
        padding-bottom: 20px;
    }

    /* Garis bawah judul yang elegan */
    .section-title::after {
        content: '';
        display: block;
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, #0D47A1, #1976D2);
        margin: 15px auto 0;
        border-radius: 2px;
    }

    /* --- 2. PROFIL BOX (Kartu Atas) --- */
    .profil-box {
        background: #FFFFFF; /* Kartu Putih */
        padding: 50px;
        border-radius: 16px;
        
        /* PERUBAHAN 2: Bayangan untuk Kedalaman (Depth) */
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        
        text-align: center;
        font-size: 1.1rem;
        line-height: 1.8;
        color: #334155;
        margin-bottom: 60px;
        border: 1px solid rgba(255,255,255,0.5);
        transition: transform 0.3s ease;
    }
    
    .profil-box:hover {
        transform: translateY(-5px); /* Efek melayang halus saat di-hover */
    }

    /* --- 3. VISI & MISI (Grid Layout) --- */
    .vm-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 30px;
        margin-bottom: 60px;
    }

    .vm-card {
        background: #FFFFFF; /* Kartu Putih */
        padding: 35px;
        border-radius: 16px;
        
        /* Border kiri warna branding */
        border-left: 6px solid #0D47A1;
        
        /* Bayangan Kedalaman */
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }

    .vm-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: translateY(-5px);
    }

    .vm-card h3 {
        color: #0D47A1;
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 1.5rem;
        border-bottom: 1px solid #E2E8F0;
        padding-bottom: 15px;
    }

    .vm-card ul {
        padding-left: 20px;
        line-height: 1.8;
        color: #475569;
    }

    /* --- 4. KEUNGGULAN (List Items) --- */
    .keunggulan-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 60px;
    }

    .keunggulan-item {
        background: #FFFFFF; /* Kartu Putih */
        padding: 25px;
        border-radius: 12px;
        display: flex;
        align-items: flex-start;
        gap: 20px;
        
        /* Bayangan Ringan */
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        border: 1px solid #E2E8F0;
        
        transition: transform 0.2s;
    }
    
    .keunggulan-item:hover {
        border-color: #1976D2;
        transform: translateX(5px);
    }

    .icon-check {
        background: #E3F2FD; /* Latar Ikon Biru Muda */
        color: #0D47A1;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        flex-shrink: 0;
        font-size: 1.2rem;
    }

    /* --- 5. KERJA SAMA (Chips) --- */
    .partners-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
        margin-bottom: 60px;
    }

    .partner-card {
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        color: #475569;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        transition: all 0.2s;
    }

    .partner-card:hover {
        background: #0D47A1;
        color: white;
        border-color: #0D47A1;
        transform: scale(1.05);
    }

    /* --- 6. DOKUMENTASI / GALERI --- */
    .doc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }

    .doc-card {
        background: #FFFFFF; /* Kartu Putih */
        border-radius: 16px;
        overflow: hidden;
        
        /* Bayangan Kedalaman */
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .doc-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .doc-img-real {
        height: 220px;
        width: 100%;
        background: #E2E8F0;
        overflow: hidden;
    }

    .doc-img-real img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .doc-card:hover .doc-img-real img {
        transform: scale(1.1); /* Zoom effect pada foto */
    }

    .doc-content {
        padding: 25px;
        flex: 1;
    }

    .doc-content h4 {
        color: #0D47A1;
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 1.25rem;
    }

    .doc-content p {
        font-size: 0.95rem;
        color: #64748B;
        line-height: 1.6;
        margin: 0;
    }

    /* --- 7. STYLE MODE EDIT ADMIN (Jika Login Admin) --- */
    .edit-mode-wrapper {
        background: #FFFBEB; /* Kuning Pucat untuk Edit */
        padding: 30px;
        border-radius: 16px;
        margin-bottom: 40px;
        border: 2px dashed #F59E0B;
    }
    .edit-mode-wrapper h2 { text-align: center; color: #92400E; margin-bottom: 20px; }
    .edit-form-group { margin-bottom: 20px; }
    .edit-form-label { display: block; font-weight: bold; margin-bottom: 8px; color: #374151; }
    .admin-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        font-family: inherit;
        margin-bottom: 10px;
        box-sizing: border-box;
        background: white;
    }
    .btn-custom {
        display: inline-block;
        padding: 10px 20px;
        background: #0D47A1;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background 0.3s;
    }
    .btn-custom:hover { background: #1565C0; }
    .btn-success { background: #059669; }
    .btn-cancel { background: #6B7280; margin-left: 10px; }

    /* Responsive Mobile */
    @media (max-width: 600px) {
        .about-wrapper {
            padding: 30px 20px;
            margin: 20px auto;
            border-radius: 0; /* Full width di HP */
        }
        .section-title { font-size: 1.6rem; }
        .profil-box { padding: 25px; }
        .doc-grid { grid-template-columns: 1fr; }
        .vm-container { grid-template-columns: 1fr; }
        .keunggulan-list { grid-template-columns: 1fr; }
    }
</style>

</body>
</html>
<?php include 'footer.php'; ?>