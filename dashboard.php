<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

 $role = $_SESSION['role'] ?? 'customer';
 $username = $_SESSION['username'];

// ==========================================
// LOGIKA KHUSUS ADMIN
// ==========================================
if ($role === 'admin') {
    $result = $conn->query("SELECT * FROM bookings ORDER BY tanggal DESC, jam DESC");
    $all_bookings = $result->fetch_all(MYSQLI_ASSOC);

    $permintaan = []; $kehadiran = []; $selesai = [];
    foreach ($all_bookings as $b) {
        if($b['status'] == 'menunggu') $permintaan[] = $b;
        elseif($b['status'] == 'disetujui') $kehadiran[] = $b;
        elseif($b['status'] == 'selesai') $selesai[] = $b;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | Millenium Auto Service</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Style Dasar Dashboard */
        .dashboard-header { text-align: center; margin-bottom: 30px; padding: 30px; background: linear-gradient(135deg, var(--primary), #233852); color: white; border-radius: 12px; }
        .dashboard-header h1 { margin: 0; font-size: 2rem; }
        
        /* GRID MENU (DUA MENU BESAR) */
        .menu-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 20px; 
            margin-bottom: 30px; 
        }

        .menu-card { 
            background: white; 
            padding: 50px 20px; 
            border-radius: 12px; 
            text-align: center; 
            border: 2px solid #E2E8F0; 
            transition: all 0.3s; 
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .menu-card:hover { 
            border-color: var(--primary); 
            transform: translateY(-5px); 
            box-shadow: var(--shadow); 
            background: #F8FAFC;
        }

        .menu-icon { font-size: 3.5rem; margin-bottom: 15px; display: block; }
        .menu-title { font-size: 1.4rem; font-weight: bold; color: var(--primary); display: block; margin-bottom: 10px; }
        .menu-desc { color: var(--text-light); font-size: 1rem; }

        /* Style Kanban Admin */
        .kanban { display: flex; gap: 20px; flex-wrap: wrap; }
        .kanban-col { flex: 1; min-width: 300px; background: #F8FAFC; padding: 15px; border-radius: 12px; }
        .kanban-header { padding: 10px; color: white; border-radius: 8px; margin-bottom: 15px; font-weight: bold; text-align: center; }
        .bg-wait { background: #F59E0B; }
        .bg-approve { background: var(--primary); }
        .bg-done { background: var(--success); }
        
        .booking-card { background: white; padding: 15px; border-radius: 8px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid #ccc; }
        .booking-card h4 { margin: 0 0 5px 0; color: var(--text-main); font-size: 1rem; }
        .booking-card p { margin: 2px 0; font-size: 0.85rem; color: var(--text-light); }
        .actions { margin-top: 10px; display: flex; gap: 5px; flex-wrap: wrap; }
        .btn-xs { padding: 5px 10px; font-size: 0.8rem; text-decoration: none; color: white; border-radius: 4px; display: inline-block; border: none; cursor: pointer; }
        .btn-detail { background: #64748b; }
        .btn-detail:hover { background: #475569; }

        /* --- STYLE MODAL DETAIL (Diambil dari manage_bookings) --- */
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
    <!-- HEADER DASHBOARD -->
    <div class="dashboard-header">
        <h1>Halo, <?= htmlspecialchars($username); ?>! 👋</h1>
        <p><?= $role === 'admin' ? 'Panel Kontrol Bengkel' : 'Selamat datang di area pelanggan' ?></p>
    </div>

    <!-- ==========================================
         TAMPILAN ADMIN
    ========================================== -->
    <?php if ($role === 'admin'): ?>
        
        <!-- TOOLBAR ADMIN (TETAP DIPERTAHANKAN) -->
        <div style="display:flex; justify-content:flex-end; gap:10px; margin-bottom:20px;">
            <a href="manage_users.php" class="btn btn-secondary">👥 Kelola User</a>
            <a href="index.php" class="btn btn-secondary">✏️ Edit Website</a>
        </div>

        <!-- KANBAN BOARD -->
        <div class="kanban">
            <!-- KOLOM 1: PERMINTAAN -->
            <div class="kanban-col">
                <div class="kanban-header bg-wait">📨 Permintaan Masuk (<?= count($permintaan) ?>)</div>
                <?php foreach($permintaan as $b): ?>
                    <div class="booking-card">
                        <h4><?= htmlspecialchars($b['nama']) ?> (<?= htmlspecialchars($b['no_polisi']) ?>)</h4>
                        <p><?= date('d/m', strtotime($b['tanggal'])) ?> - <?= $b['jam'] ?></p>
                        <p><?= htmlspecialchars($b['model_kendaraan']) ?></p>
                        <div class="actions">
                            <!-- Tombol Detail Baru -->
                            <button onclick="viewDetail(<?= htmlspecialchars(json_encode($b)) ?>)" class="btn-xs btn-detail">📄 Detail</button>
                            
                            <a href="action_booking.php?id=<?= $b['id'] ?>&status=disetujui" class="btn-xs" style="background:var(--success);" onclick="return confirm('Setujui?')">✔ Setujui</a>
                            <a href="action_booking.php?id=<?= $b['id'] ?>&status=ditolak" class="btn-xs" style="background:var(--danger);" onclick="return confirm('Tolak?')">✖ Tolak</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- KOLOM 2: KEHADIRAN (SCAN) -->
            <div class="kanban-col">
                <div class="kanban-header bg-approve">📅 Menunggu Kehadiran (<?= count($kehadiran) ?>)</div>
                <?php foreach($kehadiran as $b): ?>
                    <div class="booking-card" style="border-left-color: var(--primary);">
                        <h4><?= htmlspecialchars($b['nama']) ?></h4>
                        <p><?= date('d/m/Y H:i', strtotime($b['tanggal'].' '.$b['jam'])) ?></p>
                        <div class="actions">
                            <!-- Tombol Detail Baru -->
                            <button onclick="viewDetail(<?= htmlspecialchars(json_encode($b)) ?>)" class="btn-xs btn-detail">📄 Detail</button>
                            
                            <button onclick="openScanner()" class="btn-xs" style="background:#F59E0B; color:white; flex:1;">📷 Scan QR</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- KOLOM 3: SELESAI -->
            <div class="kanban-col">
                <div class="kanban-header bg-done">✅ Selesai (<?= count($selesai) ?>)</div>
                <?php foreach($selesai as $b): ?>
                    <div class="booking-card" style="opacity:0.7;">
                        <h4><?= htmlspecialchars($b['nama']) ?></h4>
                        <p>Selesai: <?= date('d/m/Y', strtotime($b['tanggal'])) ?></p>
                        <div class="actions">
                            <!-- Tombol Detail Baru -->
                            <button onclick="viewDetail(<?= htmlspecialchars(json_encode($b)) ?>)" class="btn-xs btn-detail">📄 Lihat Data</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- MODAL SCANNER (ADMIN) - TETAP DIPERTAHANKAN -->
        <div id="scanModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
            <div style="background:white; padding:30px; border-radius:12px; width:90%; max-width:400px; text-align:center;">
                <h3>📷 Scan Tiket</h3>
                <p style="margin-bottom:15px; font-size:0.9rem;">Gunakan Scanner atau ketik manual.</p>
                <form action="scan.php" method="POST">
                    <input type="text" name="scan_code" placeholder="Scan QR..." autofocus style="font-size:1.2rem; text-align:center; letter-spacing:2px; width:80%; padding:10px; margin-bottom:10px;">
                    <button type="submit" class="btn" style="width:100%;">Proses</button>
                </form>
                <button onclick="document.getElementById('scanModal').style.display='none'" style="margin-top:10px; background:none; border:none; text-decoration:underline; cursor:pointer;">Batal</button>
            </div>
        </div>

        <!-- MODAL POPUP DETAIL (BARU) -->
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
                        
                        <!-- Peringatan Privasi -->
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
            // Fungsi Scan QR (Lama)
            function openScanner(){
                document.getElementById('scanModal').style.display='flex';
                document.querySelector('input[name="scan_code"]').focus();
            }

            // Fungsi Modal Detail (Baru - Sama persis seperti di manage_bookings)
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
                    // JIKA STATUS SELESAI: SEMBUNYIKAN GAMBAR
                    divStnk.style.display = 'none';
                    divKtp.style.display = 'none';
                    privacyNote.style.display = 'block'; 
                } else {
                    // JIKA STATUS MASIH PROSES: TAMPILKAN GAMBAR
                    divStnk.style.display = 'block';
                    divKtp.style.display = 'block';
                    privacyNote.style.display = 'none'; 
                    
                    // Set src gambar
                    imgStnk.src = 'uploads/' + data.foto_stnk;
                    imgKtp.src = 'uploads/' + data.foto_ktp;
                }

                // Buka Modal
                document.getElementById('detailModal').style.display = 'block';
            }

            function closeModal() { 
                document.getElementById('detailModal').style.display = 'none'; 
            }
            
            // Klik di luar modal detail untuk menutup
            window.onclick = function(event) { 
                if (event.target == document.getElementById('detailModal')) closeModal(); 
            }
        </script>

    <!-- ==========================================
         TAMPILAN CUSTOMER
    ========================================== -->
    <?php else: ?>
        
        <!-- DUA MENU UTAMA (CUSTOMER) -->
        <div class="menu-grid">
            <!-- MENU 1: BOOKING -->
            <a href="booking.php" class="menu-card">
                <span class="menu-icon">🚗</span>
                <span class="menu-title">Booking Servis</span>
                <span class="menu-desc">Buat jadwal perbaikan kendaraan Anda.</span>
            </a>

            <!-- MENU 2: KELOLA AKUN -->
            <a href="edit_profile.php" class="menu-card">
                <span class="menu-icon">👤</span>
                <span class="menu-title">Kelola Akun</span>
                <span class="menu-desc">Edit profil dan keamanan akun.</span>
            </a>
        </div>

    <?php endif; ?>

</div>
</body>
</html>