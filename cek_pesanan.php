<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode = mysqli_real_escape_string($conn, $_POST['kode_booking']);
    
    $query = mysqli_query($conn, "SELECT * FROM reservasi WHERE kode_booking = '$kode'");
    $cek = mysqli_fetch_assoc($query);

    if ($cek) {
        header("Location: pembayaran.php?kode=$kode");
        exit;
    } else {
        $error = "Kode Booking tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Pesanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-stone-50 font-sans h-screen flex flex-col">

    <nav class="fixed w-full z-40 top-0 bg-red-800 shadow-md">
        <div class="container mx-auto px-6 py-3 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-serif font-bold text-yellow-400 flex items-center gap-2">
                🌶️ Padang Pagi Malam
            </a>
            <a href="index.php" class="text-white hover:text-yellow-300 font-bold text-sm">Kembali ke Home</a>
        </div>
    </nav>

    <div class="flex-grow flex items-center justify-center px-4">
        <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full border-t-4 border-yellow-500">
            <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center">Cek Status Pesanan</h1>
            <p class="text-gray-500 text-center mb-6 text-sm">Masukkan Kode Booking yang Anda dapatkan.</p>

            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-center text-sm">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Kode Booking</label>
                    <input type="text" name="kode_booking" placeholder="Contoh: PM-2011-X9" required
                           class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-red-500 uppercase tracking-wider text-center text-lg font-bold">
                </div>
                <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white font-bold py-3 px-4 rounded-lg transition duration-300 shadow-lg transform hover:scale-105 flex justify-center items-center gap-2">
                    <span>🔍</span> Cari Pesanan Saya
                </button>
            </form>

            <div class="mt-6 text-center border-t pt-4">
                <p class="text-xs text-gray-400 mb-2">Lupa kode booking Anda?</p>
                <a href="https://wa.me/6282154956553?text=Halo%20Admin,%20saya%20lupa%20kode%20booking%20saya.%20Mohon%20bantuannya." target="_blank" class="inline-flex items-center gap-2 text-green-600 font-bold hover:text-green-800 hover:underline text-sm transition">
                    <span>📱</span> Hubungi Admin via WhatsApp
                </a>
            </div>
        </div>
    </div>

</body>
</html>