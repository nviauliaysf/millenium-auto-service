<header class="navbar">
    <div class="nav-container">
        
        <!-- 1. KIRI: LOGO & BRAND -->
        <div class="navbar-left">
            <div class="brand">
                <a href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;">
                    
                    <!-- LOGO GAMBAR -->
                    <img src="assets/logo.jpg" alt="Logo Millenium Auto Service" style="height: 45px; width: auto; border-radius: 8px; background: white; padding: 2px;">
                    
                    <!-- TEKS BRAND -->
                    <span style="font-size: 1.25rem; font-weight: 700; letter-spacing: -0.5px; line-height: 1;">
                        Millenium Auto Service
                    </span>
                </a>
            </div>
        </div>

        <!-- 2. TENGAH: MENU LINKS -->
        <ul class="nav-links">
            <!-- MENU UTAMA -->
            <li><a href="index.php">Beranda</a></li>
            <li><a href="layanan.php">Layanan</a></li>
            <li><a href="tentang.php">Tentang</a></li>
            <li><a href="kontak.php">Kontak</a></li>

            <?php if(isset($_SESSION['username'])): ?>
                <!-- MENU KHUSUS USER LOGIN -->
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php" style="color: #ffcccc;">Logout</a></li>
            <?php else: ?>
                <!-- MENU BELUM LOGIN -->
                <li><a href="login.php" style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px;">Login</a></li>
            <?php endif; ?>
        </ul>

    </div>
</header>
<link rel="stylesheet" href="style.css?v=1.1">