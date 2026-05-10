<?php
session_start();
require 'koneksi.php';

if ($_SESSION['role'] !== 'admin') { 
    header("Location: login.php"); 
    exit(); 
}

 $response = ['status' => 'error', 'message' => 'Invalid request'];

// --- BACKEND: Proses Validasi ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['scan_code'] ?? '');
    $isAjax = isset($_POST['ajax']);

    if (!empty($code)) {
        $parts = explode("|", $code);
        // Format QR: IDUser|IDBooking|Tanggal|Jam
        if (count($parts) === 4) {
            $scan_id = $parts[1];
            $scan_date = $parts[2];
            $scan_time = $parts[3];

            // Ambil Data di Database
            $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND status = 'disetujui'");
            $stmt->bind_param("i", $scan_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $b = $result->fetch_assoc();
                
                // DEBUG MODE: Cek Perbedaan
                $db_date = $b['tanggal'];
                $db_time = $b['jam'];
                
                // Cek Kecocokan Tanggal & Jam
                if ($b['tanggal'] === $scan_date && $b['jam'] === $scan_time) {
                    
                    // Validasi Waktu (2 Jam)
                    $booking_ts = strtotime($b['tanggal'] . ' ' . $b['jam']);
                    if (time() > ($booking_ts + 7200)) {
                        $msg = "❌ KODE KADALUARSA (Lebih dari 2 jam).";
                        $response = ['status' => 'error', 'message' => $msg];
                    } else {
                        // SUKSES: Update Status
                        $upd = $conn->prepare("UPDATE bookings SET status='selesai' WHERE id=?");
                        $upd->bind_param("i", $scan_id);
                        $upd->execute();
                        
                        $msg = "✅ Tiket Valid: " . htmlspecialchars($b['nama']);
                        $response = ['status' => 'success', 'message' => $msg];
                    }
                } else {
                    // --- PESAN ERROR DEBUG ---
                    // Ini akan menunjukkan letak perbedaannya
                    $msg = "❌ Data QR TIDAK COCOK dengan Database.<br><br>";
                    $msg .= "<strong>Database:</strong> $db_date | $db_time<br>";
                    $msg .= "<strong>QR Scan:</strong> $scan_date | $scan_time<br><br>";
                    $msg .= "<em>Cek apakah ada spasi tambahan atau format salah.</em>";
                    
                    $response = ['status' => 'error', 'message' => $msg];
                }
            } else {
                $response = ['status' => 'error', 'message' => '❌ Booking tidak valid atau sudah diproses (bukan status Disetujui).'];
            }
        } else {
            $response = ['status' => 'error', 'message' => '❌ Format Kode Salah. Harap scan QR yang benar.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => '❌ Kode tidak boleh kosong.'];
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Tiket | Admin</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        body {
            background-color: #0f172a; color: #f8fafc;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; margin: 0; font-family: 'Segoe UI', sans-serif;
        }

        .scanner-container {
            width: 100%; max-width: 500px;
            background: #1e293b; padding: 25px; border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            text-align: center; border: 1px solid #334155;
            position: relative;
        }

        h2 { color: #60a5fa; margin-bottom: 5px; font-size: 1.5rem; }
        .subtitle { color: #94a3b8; font-size: 0.9rem; margin-bottom: 20px; }

        #reader {
            width: 100%; border-radius: 12px; overflow: hidden;
            border: 2px solid #334155; background: #000;
            margin-bottom: 20px; min-height: 250px;
            display: flex; align-items: center; justify-content: center;
            color: #64748b; padding: 10px; text-align: center;
            font-size: 0.9rem; position: relative;
        }

        .loading-overlay {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.8); z-index: 10;
            display: none; align-items: center; justify-content: center;
            flex-direction: column; color: white; border-radius: 12px;
        }
        .spinner {
            width: 30px; height: 30px; border: 3px solid #ffffff33;
            border-top: 3px solid #3b82f6; border-radius: 50%;
            animation: spin 1s linear infinite; margin-bottom: 10px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .control-group { margin-bottom: 15px; }
        
        .btn-main {
            width: 100%; background: linear-gradient(135deg, #3b82f6, #2563eb);
            border: none; color: white; padding: 12px;
            border-radius: 8px; font-weight: 600; cursor: pointer;
            display: flex; justify-content: center; align-items: center; gap: 8px;
            transition: 0.2s;
        }
        .btn-main:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-stop { background: #ef4444; }

        .manual-input {
            background: #0f172a; border: 1px solid #334155;
            color: white; padding: 10px; border-radius: 8px;
            width: 100%; margin-bottom: 10px; box-sizing: border-box;
            font-family: monospace;
        }
        .manual-input:focus { outline: 2px solid #3b82f6; border-color: transparent; }

        #status-msg {
            margin-top: 15px; padding: 10px; border-radius: 8px;
            font-size: 0.9rem; display: none; animation: fadeIn 0.3s;
            white-space: pre-line; /* Agar pesan error bisa turun baris */
        }
        .msg-success { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid #22c55e; }
        .msg-error { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        .back-link { display: block; color: #64748b; text-decoration: none; font-size: 0.85rem; margin-top: 15px;}
        .divider { margin: 20px 0; border-top: 1px solid #334155; position: relative; }
        .divider span { 
            position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
            background: #1e293b; padding: 0 10px; color: #64748b; font-size: 0.8rem;
        }
    </style>
</head>
<body>

<div class="scanner-container">
    <div style="font-size: 3rem; margin-bottom: 10px;">🎫</div>
    <h2>Verifikasi Tiket (Debug Mode)</h2>
    <p class="subtitle">Scan QR Code atau Masukkan Kode Manual</p>

    <div id="reader">
        <div id="loading" class="loading-overlay">
            <div class="spinner"></div>
            <div>Memproses...</div>
        </div>
        <span id="camera-status">Menunggu inisialisasi...</span>
    </div>

    <div id="status-msg"></div>

    <div class="control-group">
        <button id="btn-toggle-cam" class="btn-main">🎥 Buka Kamera</button>
    </div>

    <div class="divider"><span>ATAU</span></div>

    <div class="control-group">
        <input type="text" id="manual-code" class="manual-input" placeholder="Tempel kode QR di sini" autocomplete="off">
        <button id="btn-submit-manual" class="btn-main" style="background: #475569;">🔍 Proses Manual</button>
    </div>

    <a href="manage_bookings.php" class="back-link">← Kembali ke Dashboard</a>
</div>

<script>
    const html5QrCode = new Html5Qrcode("reader");
    const btnToggle = document.getElementById('btn-toggle-cam');
    const statusDiv = document.getElementById('status-msg');
    const loadingDiv = document.getElementById('loading');
    const manualInput = document.getElementById('manual-code');
    const btnManual = document.getElementById('btn-submit-manual');
    const readerStatus = document.getElementById('camera-status');
    
    let isScanning = false;

    function startCamera() {
        readerStatus.style.display = 'block';
        readerStatus.innerHTML = "Mengakses kamera...";
        readerStatus.style.color = "#cbd5e1";

        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        
        html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
        .then(() => {
            isScanning = true;
            btnToggle.innerHTML = "⏹️ Tutup Kamera";
            btnToggle.classList.add('btn-stop');
            btnToggle.onclick = stopCamera;
            readerStatus.style.display = 'none'; 
        })
        .catch(err => {
            console.error(err);
            readerStatus.innerHTML = "❌ Gagal akses kamera.<br>Gunakan Input Manual.";
            readerStatus.style.color = "#ef4444";
        });
    }

    function stopCamera() {
        if (isScanning) {
            html5QrCode.stop().then(() => {
                isScanning = false;
                readerStatus.style.display = 'block';
                readerStatus.innerHTML = "Kamera dimatikan.";
                readerStatus.style.color = "#64748b";
                btnToggle.innerHTML = "🎥 Buka Kamera";
                btnToggle.classList.remove('btn-stop');
                btnToggle.onclick = startCamera;
            }).catch(err => console.error(err));
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        if(isScanning) html5QrCode.pause();
        processValidation(decodedText);
    }

    btnManual.onclick = function() {
        const code = manualInput.value.trim();
        if(!code) {
            showStatus('error', 'Silakan masukkan kode QR.');
            return;
        }
        processValidation(code);
    };

    async function processValidation(code) {
        showLoading(true);
        hideStatus();

        try {
            const formData = new FormData();
            formData.append('scan_code', code);
            formData.append('ajax', 'true');

            const response = await fetch('scan.php', { method: 'POST', body: formData });
            const data = await response.json();

            showLoading(false);

            if (data.status === 'success') {
                showStatus('success', data.message);
                playBeep();
                setTimeout(() => {
                    window.location.href = 'manage_bookings.php?msg=' + encodeURIComponent(data.message);
                }, 1500);

            } else {
                // Tampilkan Error (Sekarang dengan info Debug)
                showStatus('error', data.message);
                if(isScanning) {
                    setTimeout(() => { html5QrCode.resume(); }, 3000);
                }
            }

        } catch (error) {
            showLoading(false);
            showStatus('error', 'Gagal menghubungi server.');
            if(isScanning) setTimeout(() => html5QrCode.resume(), 2000);
        }
    }

    function showStatus(type, msg) {
        statusDiv.style.display = 'block';
        statusDiv.className = type === 'success' ? 'msg-success' : 'msg-error';
        statusDiv.innerText = msg;
    }
    function hideStatus() { statusDiv.style.display = 'none'; }
    
    function showLoading(show) {
        loadingDiv.style.display = show ? 'flex' : 'none';
    }

    function playBeep() {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.frequency.value = 880; 
        gain.gain.value = 0.1;
        osc.start();
        osc.stop(audioCtx.currentTime + 0.1);
    }

    btnToggle.onclick = startCamera;
</script>

</body>
</html>