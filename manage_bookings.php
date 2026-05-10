<?php
session_start();
require 'koneksi.php';
if ($_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }

 $result = $conn->query("SELECT * FROM bookings ORDER BY tanggal DESC, jam DESC");
 $bookings = $result->fetch_all(MYSQLI_ASSOC);

 $permintaan = []; $kehadiran = []; $selesai = [];
foreach ($bookings as $b) {
    if($b['status'] == 'menunggu') $permintaan[] = $b;
    elseif($b['status'] == 'disetujui') $kehadiran[] = $b;
    elseif($b['status'] == 'selesai') $selesai[] = $b;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Booking | Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .kanban { display: flex; gap: 20px; flex-wrap: wrap; }
        .col { flex: 1; min-width: 300px; background: #F8FAFC; padding: 15px; border-radius: 12px; }
        .col-header { padding: 10px; color: white; border-radius: 8px; margin-bottom: 15px; font-weight: bold; text-align: center; }
        .bg-wait { background: #F59E0B; }
        .bg-approve { background: var(--primary); }
        .bg-done { background: var(--success); }
        
        .card { background: white; padding: 15px; border-radius: 8px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid #ccc; }
        .card h4 { margin: 0 0 5px 0; color: var(--text-main); font-size: 1rem; }
        .card p { margin: 2px 0; font-size: 0.85rem; color: var(--text-light); }
        .actions { margin-top: 10px; display: flex; gap: 5px; flex-wrap: wrap; }
        .btn-xs { padding: 5px 10px; font-size: 0.75rem; cursor: pointer; border: none; color: white; border-radius: 4px; text-decoration: none; display: inline-block; }
        .btn-detail { background: #64748b; }
        .btn-detail:hover { background: #475569; }

        /* --- STYLE MODAL DETAIL --- */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px); }
        .modal-content { background-color: #fff; margin: 5% auto; padding: 0; border-radius: 12px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); animation: slideDown 0.3s ease; }
        @keyframes slideDown { from {transform: translateY(-50px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
        
        .modal-header { background: var(--primary); color: white; padding: 15px 20px; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 20px; }
        .detail-row { display: flex; margin-bottom: 12px; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        .detail-label { width: 130px; font-weight: bold; color: #555; font-size: 0.9rem; flex-shrink: 0; }
        .detail-value { flex: 1; color: #333; font-size: 0.9rem; word-break: break-word; }
        
        .img-preview-container { margin-top: 10px; margin-bottom: 15px; }
        .img-preview { width: 100%; max-width: 250px; height: auto; border: 1px solid #ddd; border-radius: 8px; display: block; margin-top: 5px; cursor: pointer; }
        .img-label { font-weight: bold; font-size: 0.85rem; color: #333; display: block; }
        
        .privacy-warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 6px; font-size: 0.85rem; text-align: center; border: 1px solid #ffeeba; display: none; }

        .close-modal { color: white; font-size: 24px; font-weight: bold; cursor: pointer; line-height: 1; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="wrapper">
    <h2 style="margin-bottom:20px;">Dashboard Booking Admin</h2>
    
    <div class="kanban">
        <!-- KOLOM 1: PERMINTAAN MASUK -->
        <div class="col">
            <div class="col-header bg-wait">📨 Permintaan Masuk (<?= count($permintaan) ?>)</div>
            <?php if(empty($permintaan)): ?>
                <p style="text-align:center; color:#999; font-size:0.9rem;">Tidak ada data.</p>
            <?php else: ?>
                <?php foreach($permintaan as $b): ?>
                    <div class="card">
                        <h4><?= htmlspecialchars($b['nama']) ?> (<?= htmlspecialchars($b['no_polisi']) ?>)</h4>
                        <p><?= date('d/m', strtotime($b['tanggal'])) ?> - <?= $b['jam'] ?></p>
                        <p><?= htmlspecialchars($b['model_kendaraan']) ?></p>
                        <div class="actions">
                            <button onclick="viewDetail(<?= htmlspecialchars(json_encode($b)) ?>)" class="btn-xs btn-detail">📄 Lihat Detail</button>
                            <a href="action_booking.php?id=<?= $b['id'] ?>&status=disetujui" class="btn-xs btn-success" onclick="return confirm('Setujui booking ini?')">✔ Setujui</a>
                            <a href="action_booking.php?id=<?= $b['id'] ?>&status=ditolak" class="btn-xs btn-danger" onclick="return confirm('Tolak booking ini?')">✖ Tolak</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- KOLOM 2: MENUNGGU KEHADIRAN (SCAN) -->
        <div class="col">
            <div class="col-header bg-approve">📅 Menunggu Kehadiran (<?= count($kehadiran) ?>)</div>
            <?php if(empty($kehadiran)): ?>
                <p style="text-align:center; color:#999; font-size:0.9rem;">Tidak ada data.</p>
            <?php else: ?>
                <?php foreach($kehadiran as $b): ?>
                    <div class="card" style="border-left-color: var(--primary);">
                        <h4><?= htmlspecialchars($b['nama']) ?></h4>
                        <p><?= date('d/m/Y H:i', strtotime($b['tanggal'].' '.$b['jam'])) ?></p>
                        <div class="actions">
                            <button onclick="viewDetail(<?= htmlspecialchars(json_encode($b)) ?>)" class="btn-xs btn-detail">📄 Detail</button>
                            <a href="scan.php" class="btn-xs btn-warning" style="width:100%; display:block; text-align:center; text-decoration:none; color:white;">📷 Scan QR</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- KOLOM 3: RIWAYAT / SELESAI -->
        <div class="col">
            <div class="col-header bg-done">✅ Riwayat Selesai (<?= count($selesai) ?>)</div>
            <?php if(empty($selesai)): ?>
                <p style="text-align:center; color:#999; font-size:0.9rem;">Belum ada riwayat.</p>
            <?php else: ?>
                <?php foreach($selesai as $b): ?>
                    <div class="card" style="opacity:0.8;">
                        <h4><?= htmlspecialchars($b['nama']) ?></h4>
                        <p>Selesai: <?= date('d/m/Y', strtotime($b['tanggal'])) ?></p>
                        <div class="actions">
                            <!-- Tombol Detail untuk Riwayat -->
                            <button onclick="viewDetail(<?= htmlspecialchars(json_encode($b)) ?>)" class="btn-xs btn-detail">📄 Lihat Data</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <a href="dashboard.php" class="btn btn-secondary" style="margin-top:20px;">Kembali Dashboard</a>
</div>

<!-- MODAL POPUP DETAIL -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin:0; font-size:1.2rem;">Detail Booking</h3>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <!-- Info Dasar -->
            <div class="detail-row"><div class="detail-label">ID Booking</div><div class="detail-value">#<span id="mId"></span></div></div>
            <div class="detail-row"><div class="detail-label">Tanggal</div><div class="detail-value" id="mTanggal"></div></div>
            <div class="detail-row"><div class="detail-label">Jadwal</div><div class="detail-value" id="mJam"></div></div>
            <div class="detail-row"><div class="detail-label">Nama</div><div class="detail-value" id="mNama"></div></div>
            <div class="detail-row"><div class="detail-label">No. Polisi</div><div class="detail-value" id="mPlat"></div></div>
            <div class="detail-row"><div class="detail-label">Kendaraan</div><div class="detail-value" id="mKendaraan"></div></div>
            <div class="detail-row"><div class="detail-label">No HP</div><div class="detail-value" id="mHp"></div></div>
            <div class="detail-row"><div class="detail-label">Paket</div><div class="detail-value" id="mPaket"></div></div>
            <div class="detail-row"><div class="detail-label">Alamat</div><div class="detail-value" id="mAlamat"></div></div>

            <!-- AREA GAMBAR SENSITIF (STNK & KTP) -->
            <div id="sensitiveDataArea">
                <hr style="margin: 15px 0; border-top: 1px dashed #ccc;">
                
                <!-- Peringatan Privasi (Hanya muncul jika status Selesai) -->
                <div id="privacyNote" class="privacy-warning">
                    🔒 Data sensitif (STNK & KTP) disembunyikan untuk melindungi privasi pelanggan karena status booking sudah selesai.
                </div>

                <!-- Tampilan STNK -->
                <div id="divStnk" class="img-preview-container">
                    <span class="img-label">Foto STNK:</span>
                    <img id="imgStnk" src="" alt="STNK" class="img-preview" onclick="window.open(this.src)">
                </div>

                <!-- Tampilan KTP -->
                <div id="divKtp" class="img-preview-container">
                    <span class="img-label">Foto KTP:</span>
                    <img id="imgKtp" src="" alt="KTP" class="img-preview" onclick="window.open(this.src)">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function viewDetail(data) {
        // Isi Data Teks
        document.getElementById('mId').innerText = data.id;
        document.getElementById('mTanggal').innerText = data.tanggal;
        document.getElementById('mJam').innerText = data.jam + ' WIB';
        document.getElementById('mNama').innerText = data.nama;
        document.getElementById('mPlat').innerText = data.no_polisi;
        document.getElementById('mKendaraan').innerText = data.model_kendaraan;
        document.getElementById('mHp').innerText = data.no_hp;
        document.getElementById('mPaket').innerText = data.permintaan_servis;
        document.getElementById('mAlamat').innerText = data.alamat;

        // LOGIKA PRIVASI DATA SENSITIF
        const divStnk = document.getElementById('divStnk');
        const divKtp = document.getElementById('divKtp');
        const privacyNote = document.getElementById('privacyNote');
        const imgStnk = document.getElementById('imgStnk');
        const imgKtp = document.getElementById('imgKtp');

        if (data.status === 'selesai') {
            // --- JIKA STATUS SELESAI: SEMBUNYIKAN GAMBAR ---
            divStnk.style.display = 'none';
            divKtp.style.display = 'none';
            privacyNote.style.display = 'block'; // Tampilkan peringatan
        } else {
            // --- JIKA STATUS MASIH PROSES: TAMPILKAN GAMBAR ---
            divStnk.style.display = 'block';
            divKtp.style.display = 'block';
            privacyNote.style.display = 'none'; // Sembunyikan peringatan
            
            // Set src gambar (pastikan folder uploads benar)
            imgStnk.src = 'uploads/' + data.foto_stnk;
            imgKtp.src = 'uploads/' + data.foto_ktp;
        }

        // Buka Modal
        document.getElementById('detailModal').style.display = 'block';
    }

    function closeModal() { 
        document.getElementById('detailModal').style.display = 'none'; 
    }
    
    // Klik di luar modal untuk menutup
    window.onclick = function(event) { 
        if (event.target == document.getElementById('detailModal')) closeModal(); 
    }
</script>

</body>
</html>