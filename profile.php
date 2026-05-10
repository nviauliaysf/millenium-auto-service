    <?php
    session_start();
    require 'koneksi.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Profil | Bengkel TKR</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <?php include 'navbar.php'; ?>

    <div class="profile-container">
        <h2>Profil Anda</h2>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
        <p><a href="edit_profile.php">Edit Profil</a></p>
        <p><a href="dashboard.php">Kembali ke Dashboard</a></p>
    </div>

    </body>
    </html>

    <?php include 'footer.php'; ?>