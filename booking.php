<?php
session_start();
require 'koneksi.php';

// Cek Login & Role
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { header("Location: dashboard.php"); exit(); }

 $user_id = $_SESSION['user_id'];
 $promo_cars = ['avanza', 'veloz', 'rush', 'calya', 'agya']; // Promo tetap dipakai untuk logika harga
 $success = ""; $error = "";

// Ambil pesan flash dari session jika ada
if (isset($_SESSION['booking_success'])) {
    $success = $_SESSION['booking_success'];
    unset($_SESSION['booking_success']);
}
if (isset($_SESSION['booking_error'])) {
    $error = $_SESSION['booking_error'];
    unset($_SESSION['booking_error']);
}

// ==========================================
// LOGIKA PROSES BOOKING
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    // Cek dulu apakah user punya booking aktif
    $check_active = $conn->prepare("SELECT id FROM bookings WHERE user_id = ? AND status IN ('menunggu', 'disetujui')");
    $check_active->bind_param("i", $user_id);
    $check_active->execute();
    if($check_active->get_result()->num_rows > 0) {
        $error = "Anda masih memiliki booking aktif. Silakan selesaikan atau tunggu booking saat ini selesai.";
    } else {
        $nama = $_POST['nama']; 
        $model = $_POST['model_kendaraan']; // Data dari Radio Button
        $tahun = $_POST['tahun_kendaraan'];  // Data Baru
        $plat = $_POST['no_polisi'];
        $hp = $_POST['no_hp']; 
        $paket = $_POST['paket']; 
        $tgl = $_POST['tanggal']; 
        $jam = $_POST['jam']; 
        $alamat = $_POST['alamat'];

        // Validasi Hari
        if(date('N', strtotime($tgl)) > 5) {
            $error = "Booking hanya Senin - Jumat.";
        } elseif(strtotime($tgl) < strtotime(date('Y-m-d'))) {
            $error = "Tanggal tidak boleh masa lalu.";
        } elseif(empty($tahun) || $tahun < 1990 || $tahun > date('Y') + 1) {
            $error = "Tahun kendaraan tidak valid.";
        } else {
            // LOGIKA HARGA & DESKRIPSI
            $harga = 0;
            $deskripsi_paket = "";

            if($paket == 'hemat') {
                $harga = 350000;
                $deskripsi_paket = "Paket Hemat: Ganti Oli Mesin & Filter Oli (TMO 10w-40), Servis Rem (Keamanan Utama!), Pemeriksaan Menyeluruh (Cek kesehatan mobil).";
            } else {
                $is_promo = false;
                // Cek promo berdasarkan model yang dipilih (case insensitive)
                foreach($promo_cars as $car) { if(stripos($model, $car) !== false) { $is_promo = true; break; } }
                $harga = $is_promo ? 350000 : 500000;
                $deskripsi_paket = "Paket Reguler / Servis Sesuai Keluhan.";
            }

            // Upload File
            $target_dir = "uploads/";
            if(!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            
            $stnk = time() . "_stnk_" . basename($_FILES["foto_stnk"]["name"]);
            $ktp = time() . "_ktp_" . basename($_FILES["foto_ktp"]["name"]);

            if(move_uploaded_file($_FILES["foto_stnk"]["tmp_name"], $target_dir.$stnk) && move_uploaded_file($_FILES["foto_ktp"]["tmp_name"], $target_dir.$ktp)){
                // QUERY UPDATE: Menambahkan kolom tahun_kendaraan
                $stmt = $conn->prepare("INSERT INTO bookings (user_id, nama, alamat, model_kendaraan, tahun_kendaraan, no_polisi, no_hp, foto_stnk, foto_ktp, permintaan_servis, tanggal, jam, harga, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'menunggu')");
                
                // BIND PARAM UPDATE: Menambahkan variabel $tahun (type 'i' = integer)
                $stmt->bind_param("issssssssssid", $user_id, $nama, $alamat, $model, $tahun, $plat, $hp, $stnk, $ktp, $deskripsi_paket, $tgl, $jam, $harga);
                
                if($stmt->execute()){
                    $_SESSION['booking_success'] = "Booking berhasil dikirim! Menunggu verifikasi admin.";
                    header("Location: booking.php");
                    exit();
                } else {
                    $error = "Gagal menyimpan data: " . $stmt->error;
                }
            } else { $error = "Gagal upload foto."; }
        }
    }
}

// ==========================================
// AMBIL DATA (ANTRIAN AKTIF & RIWAYAT)
// ==========================================       

// 1. Cek ANTRIAN AKTIF
 $stmt_active = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? AND status IN ('menunggu', 'disetujui') ORDER BY id DESC LIMIT 1");
 $stmt_active->bind_param("i", $user_id); 
 $stmt_active->execute();
 $result_active = $stmt_active->get_result();
 $active_booking = null;

if ($result_active->num_rows > 0) {
    $active_booking = $result_active->fetch_assoc();
    
    // Cek Kadaluarsa hanya jika status sudah Disetujui
    if ($active_booking['status'] == 'disetujui') {
        $booking_ts = strtotime($active_booking['tanggal'] . ' ' . $active_booking['jam']);
        if (time() > ($booking_ts + 7200)) {
            $conn->query("UPDATE bookings SET status='ditolak' WHERE id=".$active_booking['id']);
            $active_booking = null;
        }
    }
}

// 2. Ambil Semua Riwayat
 $stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY tanggal DESC, jam DESC");
 $stmt->bind_param("i", $user_id); $stmt->execute();
 $result = $stmt->get_result();
 $history = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Servis | Millenium Auto Service</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .page-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: -1; }
        .booking-container { max-width: 1000px; margin: 40px auto; position: relative; z-index: 1; }

        /* STYLE KARTU ANTRIAN */
        .active-queue-card { background: white; border-radius: 15px; padding: 0; margin-bottom: 40px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); overflow: hidden; text-align: center; }
        .queue-header { padding: 25px; background: linear-gradient(135deg, #0d47a1, #1976d2); color: white; }
        .queue-header h2 { margin: 0; font-size: 1.8rem; }
        .queue-body { padding: 30px; }

        .status-badge { display: inline-block; padding: 8px 20px; border-radius: 50px; font-weight: bold; font-size: 1.1rem; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .status-waiting { background: #FFF3E0; color: #F57C00; border: 2px solid #F57C00; }
        .status-approved { background: #E8F5E9; color: #2E7D32; border: 2px solid #2E7D32; }

        .timer-box { font-size: 1.8rem; font-weight: bold; color: #0d47a1; background: #e3f2fd; padding: 15px 30px; border-radius: 12px; display: inline-block; margin: 20px 0; border: 2px solid #0d47a1; width: 100%; max-width: 350px; }

        /* FORM SECTION */
        .form-section { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); margin-bottom: 50px; animation: fadeIn 0.5s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* PILIHAN KENDARAAN (RADIO GRID) */
        .vehicle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .vehicle-option {
            position: relative;
        }
        .vehicle-option input {
            position: absolute; opacity: 0; cursor: pointer;
        }
        .vehicle-label {
            display: block; padding: 10px 5px; border: 2px solid #e2e8f0;
            border-radius: 8px; text-align: center; cursor: pointer;
            font-size: 0.9rem; transition: 0.3s; color: #555;
        }
        /* Efek saat dipilih */
        .vehicle-option input:checked + .vehicle-label {
            border-color: #0d47a1; background-color: #e3f2fd; color: #0d47a1; font-weight: bold;
        }
        .vehicle-option:hover .vehicle-label { border-color: #90caf9; }

        /* Pilihan Paket */
        .paket-option { display: flex; align-items: flex-start; gap: 15px; background: #f9fafb; padding: 15px; border: 2px solid #e2e8f0; border-radius: 8px; margin-bottom: 10px; cursor: pointer; transition: 0.3s; }
        .paket-option:hover { border-color: #0d47a1; background: #f0f7ff; }
        .paket-option input { margin-top: 5px; transform: scale(1.2); }
        .paket-label strong { color: #0d47a1; display: block; margin-bottom: 5px; font-size: 1.1rem; }
        .paket-label small { color: #666; line-height: 1.4; display: block; }

        .section-divider { text-align: center; margin: 50px 0; position: relative; }
        .section-divider::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 2px; background: rgba(255,255,255,0.5); z-index: 0; }
        .section-divider span { background: rgba(13, 71, 161, 0.9); color: white; padding: 10px 30px; border-radius: 20px; font-weight: bold; font-size: 1.2rem; position: relative; z-index: 1; text-transform: uppercase; letter-spacing: 2px; }

        .history-section { background: rgba(255, 255, 255, 0.9); padding: 30px; border-radius: 12px; }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 0; border-radius: 15px; width: 90%; max-width: 600px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); animation: slideDown 0.4s ease; }
        @keyframes slideDown { from {transform: translateY(-50px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
        .modal-header { background: var(--primary); color: white; padding: 20px; border-radius: 15px 15px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 30px; }
        .detail-row { display: flex; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .detail-label { width: 140px; font-weight: bold; color: #555; }
        .detail-value { flex: 1; color: #333; }
        .close-modal { color: white; font-size: 28px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

    <div class="page-overlay"></div>
    <?php include 'navbar.php'; ?>

    <div class="booking-container">
        
        <!-- BAGIAN 1: TAMPILKAN ANTRIAN AKTIF -->
        <?php if ($active_booking): ?>
            <div class="active-queue-card">
                <div class="queue-header">
                    <h2>Status Booking Anda</h2>
                </div>
                <div class="queue-body">
                    <?php if($active_booking['status'] == 'menunggu'): ?>
                        <div class="status-badge status-waiting">⏳ SEDANG DIPROSES</div>
                        <p style="font-size: 1.1rem; margin-bottom: 20px;">Data booking Anda sedang diverifikasi oleh admin bengkel.<br>Silakan tunggu notifikasi selanjutnya.</p>
                        <div style="background: #f0f7ff; padding: 15px; border-radius: 8px; text-align: left;">
                            <p><strong>Pelanggan:</strong> <?= htmlspecialchars($active_booking['nama']) ?></p>
                            <p><strong>Mobil:</strong> <?= htmlspecialchars($active_booking['model_kendaraan']) ?> (<?= htmlspecialchars($active_booking['no_polisi']) ?>)</p>
                            <p><strong>Paket:</strong> Paket Booking</p>
                            <p><strong>Tanggal Request:</strong> <?= date('d/m/Y', strtotime($active_booking['tanggal'])) ?></p>
                        </div>
                    <?php elseif($active_booking['status'] == 'disetujui'): ?>
                        <div class="status-badge status-approved">✅ BOOKING DISETUJUI</div>
                        <p style="font-size: 1.1rem; margin-bottom: 20px;">Silakan datang ke bengkel sesuai jadwal.<br>Tunjukkan QR Code di bawah ini kepada petugas.</p>
                        <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; text-align: left; margin-bottom: 20px;">
                            <p><strong>Pelanggan:</strong> <?= htmlspecialchars($active_booking['nama']) ?></p>
                            <p><strong>Mobil:</strong> <?= htmlspecialchars($active_booking['model_kendaraan']) ?> (<?= htmlspecialchars($active_booking['no_polisi']) ?>)</p>
                            <p><strong>Jadwal:</strong> <?= date('d/m/Y', strtotime($active_booking['tanggal'])) ?> pukul <?= $active_booking['jam'] ?></p>
                            <p><strong>Paket:</strong> <?= $active_booking['harga'] == 350000 ? 'Paket Hemat (350k)' : 'Paket Reguler' ?></p>
                        </div>
                        <div style="background: #e3f2fd; padding: 20px; border-radius: 12px; border: 2px dashed #0d47a1;">
                            <p>⏰ Sisa Waktu Kedatangan (Maks 2 Jam)</p>
                            <div id="countdown" class="timer-box">Loading...</div>
                            <?php 
                                $qrData = "$user_id|{$active_booking['id']}|{$active_booking['tanggal']}|{$active_booking['jam']}";
                                $expiryTs = strtotime($active_booking['tanggal'].' '.$active_booking['jam']) + 7200;
                            ?>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode($qrData) ?>" style="border: 5px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.2); margin-top: 15px;">
                            <div style="margin-top:15px; background: white; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                                <small style="color: #666; font-weight: bold;">Code:</small><br>
                                <span style="font-family: monospace; font-size: 1.1rem; letter-spacing: 1px; color: #333;"><?= htmlspecialchars($qrData) ?></span>
                            </div>
                        </div>
                        <script>
                            const targetTime = "<?= date('Y-m-d H:i:s', $expiryTs) ?>";
                            const countInterval = setInterval(function() {
                                const now = new Date().getTime();
                                const distance = new Date(targetTime).getTime() - now;
                                if (distance < 0) {
                                    document.getElementById("countdown").innerHTML = "WAKTU HABIS";
                                    clearInterval(countInterval);
                                } else {
                                    const d = Math.floor(distance / (1000 * 60 * 60 * 24));
                                    const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                    const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                    const s = Math.floor((distance % (1000 * 60)) / 1000);
                                    let displayString = "";
                                    if(d > 0){ displayString += d + " Hari "; }
                                    displayString += (h<10?"0"+h:h) + ":" + (m<10?"0"+m:m) + ":" + (s<10?"0"+s:s);
                                    document.getElementById("countdown").innerHTML = displayString;
                                }
                            }, 1000);
                            setInterval(function() { location.reload(); }, 5000); 
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- BAGIAN 2: FORM BOOKING BARU -->
        <?php if (!$active_booking): ?>
            <div class="form-section">
                <h2 style="color: var(--primary); text-align: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 25px;">Booking Servis Baru</h2>
                
                <?php if($success) echo "<p class='alert alert-success'>$success</p>"; ?>
                <?php if($error) echo "<p class='alert alert-error'>$error</p>"; ?>

                <form method="POST" enctype="multipart/form-data">
                    <label>Nama Lengkap</label><input type="text" name="nama" required>
                    <label>Alamat</label><textarea name="alamat" rows="2" required></textarea>
                    <label>No HP / WhatsApp</label><input type="text" name="no_hp" required>
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div><label>No Polisi</label><input type="text" name="no_polisi" placeholder="B 1234 XYZ" style="text-transform:uppercase;" required></div>
                        <!-- INPUT TAHUN KENDARAAN (BARU) -->
                        <div><label>Tahun Kendaraan</label><input type="number" name="tahun_kendaraan" placeholder="Cth: 2020" min="1990" max="<?= date('Y')+1 ?>" required></div>
                    </div>

                    <!-- PILIHAN MODEL KENDARAAN (GRID CHECKBOX/RADIO) -->
                    <label style="margin-top:10px;">Jenis Kendaraan:</label>
                    <div class="vehicle-grid">
                        <div class="vehicle-option">
                            <input type="radio" name="model_kendaraan" id="m1" value="Agya" required>
                            <label for="m1" class="vehicle-label">Agya</label>
                        </div>
                        <div class="vehicle-option">
                            <input type="radio" name="model_kendaraan" id="m2" value="Ayla">
                            <label for="m2" class="vehicle-label">Ayla</label>
                        </div>
                        <div class="vehicle-option">
                            <input type="radio" name="model_kendaraan" id="m3" value="Calya">
                            <label for="m3" class="vehicle-label">Calya</label>
                        </div>
                        <div class="vehicle-option">
                            <input type="radio" name="model_kendaraan" id="m4" value="Sigra">
                            <label for="m4" class="vehicle-label">Sigra</label>
                        </div>
                        <div class="vehicle-option">
                            <input type="radio" name="model_kendaraan" id="m5" value="Avanza">
                            <label for="m5" class="vehicle-label">Avanza</label>
                        </div>
                        <div class="vehicle-option">
                            <input type="radio" name="model_kendaraan" id="m6" value="Xenia">
                            <label for="m6" class="vehicle-label">Xenia</label>
                        </div>
                        <div class="vehicle-option">
                            <input type="radio" name="model_kendaraan" id="m7" value="Rush">
                            <label for="m7" class="vehicle-label">Rush</label>
                        </div>
                        <div class="vehicle-option">
                            <input type="radio" name="model_kendaraan" id="m8" value="Terios">
                            <label for="m8" class="vehicle-label">Terios</label>
                        </div>
                    </div>

                    <label>Foto STNK</label><input type="file" name="foto_stnk" accept="image/*" required>
                    <label>Foto KTP</label><input type="file" name="foto_ktp" accept="image/*" required>

                    <!-- PILIHAN PAKET -->
                    <label style="font-size: 1.1rem; color: var(--primary); margin-top: 10px;">Pilih Paket Servis:</label>
                    <label class="paket-option">
                        <input type="radio" name="paket" value="hemat" checked>
                        <div class="paket-label">
                            <strong>Paket Hemat (Rp 350.000)</strong>
                            <small>Ganti Oli Mesin & Filter Oli (TMO 10W-40)<br>Servis Rem (Keamanan Utama!)<br>Pemeriksaan Menyeluruh (Cek kesehatan mobil)<br><em>Berlaku untuk: Agya, Ayla, Calya, Sigra, Avanza, Xenia, Rush, Terios.</em></small>
                        </div>
                    </label>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top: 20px;">
                        <div><label>Tanggal</label><input type="date" name="tanggal" min="<?= date('Y-m-d') ?>" required></div>
                        <!-- PILIHAN JAM (DIBATASI 09.00 & 13.00) -->
                        <div>
                            <label>Sesi Jam</label>
                            <select name="jam" required>
                                <option value="">-- Pilih Sesi --</option>
                                <option value="09:00">09.00 WIB</option>
                                <option value="13:00">13.00 WIB</option>
                            </select>
                        </div>
                    </div>
                    <p style="font-size:0.8rem; color:#666;">*Booking hanya Senin s.d Jumat.</p>

                    <button type="submit" name="book" class="btn" style="width:100%; margin-top:15px; padding: 15px;">Kirim Booking</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- PEMISAH VISUAL -->
        <div class="section-divider"><span>Riwayat Booking</span></div>

        <!-- BAGIAN 3: RIWAYAT -->
        <div class="history-section">
            <table style="background: white;">
                <tr>
                    <th>Tanggal</th>
                    <th>Jadwal</th>
                    <th>Mobil</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                <?php if(empty($history)): ?>
                    <tr><td colspan="5" style="text-align:center; padding: 20px;">Belum ada riwayat booking.</td></tr>
                <?php else: ?>
                    <?php foreach($history as $h): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($h['tanggal'])) ?></td>
                        <td><?= $h['jam'] ?></td>
                        <td style="font-weight:bold; color: #0d47a1;"><?= htmlspecialchars($h['model_kendaraan']) ?></td>
                        <td>
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.8rem; font-weight:bold; color: white; display:inline-block; 
                                background: <?= $h['status']=='disetujui'?'#c62828':($h['status']=='selesai'?'#2e7d32':($h['status']=='menunggu'?'#f57c00':'#999')) ?>;">
                                <?= ucfirst($h['status']) ?>
                            </span>
                        </td>
                        <td>
                            <button onclick="viewDetail(<?= htmlspecialchars(json_encode($h)) ?>)" class="btn-xs" style="background:#607D8B;">🔍 Detail</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
            <div style="margin-top: 30px; text-align: center;">
                <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>

    <!-- MODAL POPUP DETAIL -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="margin:0;">Detail Pemesanan</h3>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="detail-row"><div class="detail-label">ID Booking</div><div class="detail-value" id="mId">#</div></div>
                <div class="detail-row"><div class="detail-label">Tanggal</div><div class="detail-value" id="mTanggal">#</div></div>
                <div class="detail-row"><div class="detail-label">Jadwal</div><div class="detail-value" id="mJam">#</div></div>
                <div class="detail-row"><div class="detail-label">Pelanggan</div><div class="detail-value" id="mNama">#</div></div>
                <div class="detail-row"><div class="detail-label">Kendaraan</div><div class="detail-value" id="mKendaraan">#</div></div>
                <div class="detail-row"><div class="detail-label">Tahun</div><div class="detail-value" id="mTahun">#</div></div>   
                <div class="detail-row"><div class="detail-label">Plat</div><div class="detail-value" id="mPlat">#</div></div>
                <div class="detail-row"><div class="detail-label">Paket / Layanan</div><div class="detail-value" id="mPaket" style="line-height: 1.5; font-size: 0.9rem;">#</div></div>
                <div class="detail-row"><div class="detail-label">Harga</div><div class="detail-value" id="mHarga" style="font-weight: bold; color: #2e7d32;">#</div></div>
                <div class="detail-row"><div class="detail-label">Status</div><div class="detail-value" id="mStatus">#</div></div>
            </div>
        </div>
    </div>

    <script>
    function viewDetail(data) {
        document.getElementById('mId').innerText = '#' + data.id;
        document.getElementById('mTanggal').innerText = data.tanggal;
        document.getElementById('mJam').innerText = data.jam + ' WIB';
        document.getElementById('mNama').innerText = data.nama;
        document.getElementById('mKendaraan').innerText = data.model_kendaraan;
        document.getElementById('mTahun').innerText = data.tahun_kendaraan; 
        document.getElementById('mPlat').innerText = data.no_polisi;
        document.getElementById('mPaket').innerText = data.permintaan_servis;
        document.getElementById('mHarga').innerText = 'Rp. ' + parseInt(data.harga).toLocaleString('id-ID');
        document.getElementById('mStatus').innerText = data.status.toUpperCase();
        document.getElementById('detailModal').style.display = 'block';
    }
    function closeModal() { document.getElementById('detailModal').style.display = 'none'; }
    window.onclick = function(event) { if (event.target == document.getElementById('detailModal')) closeModal(); }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>