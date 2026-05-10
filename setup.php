<?php
require 'koneksi.php';

// 1. Hapus tabel lama jika ada (agar fresh)
 $conn->query("DROP TABLE IF EXISTS bookings");
 $conn->query("DROP TABLE IF EXISTS users");
 $conn->query("DROP TABLE IF EXISTS settings");

// 2. Buat Tabel Users
 $conn->query("CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'customer'
)");

// Insert Admin Default Hardcoded
 $admin_username = 'milleniumbengkeltkr';
 $admin_password = password_hash('milleniumdantoyota', PASSWORD_DEFAULT);

 $checkAdmin = $conn->query("SELECT * FROM users WHERE username='$admin_username'");
if ($checkAdmin->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param("sss", $admin_username, $admin_username . "@bengkel.com", $admin_password);
    $stmt->execute();
}

// 3. Buat Tabel Settings (CMS)
 $conn->query("CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) UNIQUE NOT NULL,
    isi TEXT
)");

// 4. Buat Tabel Bookings (DENGAN UNIQUE KEY UNTUK ANTI DUPLIKASI)
 $conn->query("CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    model_kendaraan VARCHAR(100) NOT NULL, 
    no_polisi VARCHAR(20) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    foto_stnk VARCHAR(255) NOT NULL,
    foto_ktp VARCHAR(255) NOT NULL,
    permintaan_servis TEXT NOT NULL,
    tanggal DATE NOT NULL,
    jam TIME NOT NULL,
    harga DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_active_booking (user_id, tanggal, status) 
    -- Baris di atas mencegah user memasukkan 2 booking aktif di tanggal yang sama
)");

// Insert Default Data Awal
 $defaults = [
    'hero_title' => 'Servis Mobil Anda, Sambil Dukung Siswa TKR Berkarya',
    'hero_desc' => 'Solusi terbaik perawatan kendaraan Anda bersama bengkel terpercaya.',
    'card1_title' => 'Servis Rutin', 'card1_text' => 'Periksa dan ganti komponen dasar mobil agar tetap prima.',
    'card2_title' => 'Ganti Oli', 'card2_text' => 'Pelayanan ganti oli dengan oli berkualitas.',
    'card3_title' => 'Perbaikan Mesin', 'card3_text' => 'Servis dan perbaikan mesin menggunakan alat modern.',
    'kontak_alamat' => 'Jl. Raya Cibinong No. 123, Bogor',
    'kontak_telepon' => '(021) 875-xxxx',
    'kontak_email' => 'info@bengkeltkr.com',
    'kontak_jam' => 'Senin - Jumat: 09.00 - 16.30 WIB',
    'layanan_judul_halaman' => 'Layanan Kami',
    'layanan_judul1' => 'Servis Rutin', 'layanan_isi1' => 'Pengecekan berkala 25 item standar pabrikan.',
    'layanan_judul2' => 'Ganti Oli & Filter', 'layanan_isi2' => 'Mengganti oli mesin, oli transmisi, dan filter udara.',
    'layanan_judul3' => 'Tune Up & Kelistrikan', 'layanan_isi3' => 'Perbaikan sistem kelistrikan dan tune up.',
    'tentang_profil' => 'Bengkel TKR SMKN 1 Cibinong adalah tempat belajar sekaligus tempat servis profesional yang dikelola oleh siswa berprestasi.',
    'tentang_visi' => 'Menjadi bengkel sekolah unggulan berbasis teknologi.',
    'tentang_misi' => 'Mencetak mekanik handal\nMemberikan pelayanan terbaik',
    'tentang_keunggulan' => 'Mekanik Bersertifikat\nAlat Modern\nHarga Transparan',
    'tentang_kerjasama' => 'Toyota Astra Motor\nHonda'
];

foreach ($defaults as $key => $val) {
    $stmt = $conn->prepare("INSERT IGNORE INTO settings (nama, isi) VALUES (?, ?)");
    $stmt->bind_param("ss", $key, $val);
    $stmt->execute();
}

echo "Setup database berhasil! Silakan <a href='index.php'>ke Halaman Utama</a>.<br>";
echo "Admin Login: <b>milleniumbengkeltkr</b> / <b>milleniumdantoyota</b>";
?>