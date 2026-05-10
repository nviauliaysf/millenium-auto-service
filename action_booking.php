<?php
session_start();
require 'koneksi.php';
if ($_SESSION['role'] !== 'admin') exit("Akses Ditolak");

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    $allowed = ['disetujui', 'ditolak', 'selesai', 'menunggu'];
    
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }
    header("Location: manage_bookings.php");
}
?>