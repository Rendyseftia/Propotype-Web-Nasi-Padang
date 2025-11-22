<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// LOGIK: RESET DATA
if (isset($_POST['reset_data'])) {
    mysqli_query($conn, "DELETE FROM reservasi_detail");
    mysqli_query($conn, "DELETE FROM reservasi");
    echo "<script>alert('SEMUA DATA BERHASIL DIHAPUS!'); window.location='index.php';</script>";
}

// LOGIK: TERIMA CASH
if (isset($_POST['confirm_cash'])) {
    $id = $_POST['id_reservasi'];
    mysqli_query($conn, "UPDATE reservasi SET status='Paid' WHERE id='$id'");
    unset($_SESSION['hasil_cari_kasir']); 
    echo "<script>window.location='index.php';</script>";
}

// LOGIK: BATALKAN CASH (REVISI BARU)
if (isset($_POST['reject_cash'])) {
    $id = $_POST['id_reservasi'];
    mysqli_query($conn, "UPDATE reservasi SET status='Batal' WHERE id='$id'");
    unset($_SESSION['hasil_cari_kasir']); 
    echo "<script>alert('Pesanan berhasil dibatalkan!'); window.location='index.php';</script>";
}

// LOGIK: TOMBOL AKSI LAINNYA
if (isset($_GET['act']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $act = $_GET['act'];
    
    if ($act == 'accept') {
        mysqli_query($conn, "UPDATE reservasi SET status='Paid' WHERE id='$id'");
    } elseif ($act == 'reject') {
        mysqli_query($conn, "UPDATE reservasi SET status='Batal' WHERE id='$id'");
    } elseif ($act == 'selesai') {
        mysqli_query($conn, "UPDATE reservasi SET status='Selesai' WHERE id='$id'");
    }
    header("Location: index.php");
}

// LOGIK: PENCARIAN KODE
if (isset($_POST['cari_kode'])) {
    $kode = $_POST['kode'];
    $q = mysqli_query($conn, "SELECT * FROM reservasi WHERE kode_booking='$kode'");
    $_SESSION['hasil_cari_kasir'] = mysqli_fetch_assoc($q);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 font-sans pb-20">

    <nav class="bg-gray-800 text-white p-4 flex justify-between items-center sticky top-0 z-50 shadow-md">
        <div class="font-bold text-xl flex items-center gap-2">
            <span>🛠️</span> Admin Panel
        </div>
        <div class="flex gap-4 text-sm">
            <a href="index.php" class="text-yellow-400 font-bold border-b-2 border-yellow-400">Dashboard</a>
            <a href="logout.php" class="bg-red-600 px-4 py-1 rounded hover:bg-red-700 transition">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        
        <div id="realtime-content">
            <div class="text-center py-10 text-gray-500">Memuat Data Realtime...</div>
        </div>

        <div class="mt-10 border-t-2 border-red-200 pt-6 text-center">
            <form action="" method="POST" onsubmit="return confirm('PERINGATAN KERAS!\n\nAnda akan menghapus SEMUA riwayat pesanan.\n\nApakah Anda yakin?')">
                <button type="submit" name="reset_data" class="bg-red-100 text-red-600 border border-red-400 px-6 py-2 rounded hover:bg-red-600 hover:text-white transition font-bold text-sm">
                    🗑️ HAPUS / RESET SEMUA DATA PESANAN
                </button>
            </form>
        </div>

    </div>

    <script>
        function loadData() {
            if (!document.activeElement.tagName.match(/INPUT|TEXTAREA/)) {
                $('#realtime-content').load('load_data.php');
            }
        }

        $(document).ready(function() {
            loadData(); 
            setInterval(loadData, 3000);
        });
    </script>

</body>
</html>