<?php
session_start();
include 'includes/db.php';

date_default_timezone_set('Asia/Jakarta');

// ==========================================
// 1. PROSES SUBMIT PESANAN (DARI TOMBOL KONFIRMASI)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['metode'])) {
    
    // Cek Session Data
    if (!isset($_SESSION['info_pesanan']) || !isset($_SESSION['keranjang'])) {
        header("Location: index.php");
        exit;
    }

    $info = $_SESSION['info_pesanan'];
    $keranjang = $_SESSION['keranjang'];
    $total = $_SESSION['total_final'];
    $metode = $_POST['metode'];

    // Generate Kode Unik
    $kode_booking = "PM-" . date("dm") . "-" . strtoupper(substr(md5(time()), 0, 4));
    
    $waktu_sekarang = date("Y-m-d H:i:s");
    
    // === SETTING TIMER: 5 MENIT UNTUK SEMUA METODE ===
    $menit_tambahan = 5; 
    $expired_at = date("Y-m-d H:i:s", strtotime("+$menit_tambahan minutes"));

    // Validasi Jumlah Orang (Agar tidak 0)
    $pax_final = (isset($info['jumlah_orang']) && $info['jumlah_orang'] > 0) ? $info['jumlah_orang'] : 1;

    // Insert Data Reservasi
    $sql = "INSERT INTO reservasi (kode_booking, nama_pelanggan, no_hp, email, area, no_meja, tanggal_booking, jam_booking, jumlah_orang, total_bayar, metode_bayar, status, created_at, expired_at) 
            VALUES ('$kode_booking', '{$info['nama']}', '{$info['hp']}', '{$info['email']}', '{$_SESSION['area']}', '{$info['meja']}', '{$info['tanggal']}', '{$info['jam']}', '$pax_final', '$total', '$metode', 'Pending', '$waktu_sekarang', '$expired_at')";

    if (mysqli_query($conn, $sql)) {
        $reservasi_id = mysqli_insert_id($conn);
        
        // Insert Detail Menu
        foreach ($keranjang as $item) {
            $sql_detail = "INSERT INTO reservasi_detail (id_reservasi, id_menu, qty) 
                           VALUES ('$reservasi_id', '{$item['id']}', '{$item['qty']}')";
            mysqli_query($conn, $sql_detail);
        }

        // Reset Session agar keranjang kosong
        unset($_SESSION['keranjang']);
        unset($_SESSION['info_pesanan']);
        unset($_SESSION['total_final']);

        // Redirect ke halaman ini sendiri dengan kode booking (PENTING: Mencegah resubmit form)
        header("Location: pembayaran.php?kode=$kode_booking");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
        exit;
    }
}

// ==========================================
// 2. LOGIKA HALAMAN PEMBAYARAN (TAMPILAN)
// ==========================================

// Cek Kode Booking di URL
if (!isset($_GET['kode'])) {
    header("Location: index.php");
    exit;
}

$kode = $_GET['kode'];
$query = mysqli_query($conn, "SELECT * FROM reservasi WHERE kode_booking = '$kode'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Pesanan tidak ditemukan.";
    exit;
}

// SKENARIO: JIKA STATUS BATAL (TAMPILAN MERAH + WA)
if ($data['status'] == 'Batal') {
    $pesan_wa = "Halo Admin, saya ingin konfirmasi pembatalan pesanan dengan kode: " . $kode . ". Mohon bantuannya.";
    $link_wa = "https://wa.me/6282154956553?text=" . urlencode($pesan_wa);

    echo "<!DOCTYPE html><html lang='id'><head><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-stone-50 flex items-center justify-center h-screen font-sans p-4'>";
    echo "<div class='bg-white p-8 rounded-2xl shadow-xl max-w-md w-full text-center border-t-8 border-red-600'>";
    echo "<div class='text-6xl mb-4'>🚫</div>";
    echo "<h1 class='text-2xl font-bold text-red-700 mb-2'>Pesanan Dibatalkan</h1>";
    echo "<p class='text-gray-600 mb-6'>Pesanan Anda telah dibatalkan oleh sistem (waktu habis) atau oleh Admin/Kasir.</p>";
    echo "<div class='bg-red-50 border border-red-200 p-4 rounded-lg text-sm text-red-800 mb-6'>";
    echo "Jika ini kesalahan, silakan hubungi Admin segera.";
    echo "</div>";
    echo "<a href='$link_wa' target='_blank' class='block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg mb-3 flex items-center justify-center gap-2'><span>📱</span> Hubungi Admin via WhatsApp</a>";
    echo "<a href='index.php' class='block w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 rounded-lg'>Kembali ke Home</a>";
    echo "</div></body></html>";
    exit; // Stop script di sini agar tampilan bawah tidak muncul
}

// LOGIKA TIMER & AUTO CANCEL
$sekarang = time();
$expired = strtotime($data['expired_at']);
$sisa_waktu = $expired - $sekarang;

// Jika waktu habis saat halaman dibuka -> Ubah status jadi Batal
if ($sisa_waktu <= 0 && $data['status'] == 'Pending') {
    mysqli_query($conn, "UPDATE reservasi SET status = 'Batal' WHERE kode_booking = '$kode'");
    header("Refresh:0"); // Refresh halaman agar masuk ke tampilan Batal di atas
    exit;
}

// LOGIKA UPLOAD BUKTI TRANSFER
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti'])) {
    $nama_file = "bukti_" . $kode . "_" . time() . ".jpg";
    $tmp_file = $_FILES['bukti']['tmp_name'];
    $path = "assets/uploads/" . $nama_file;

    if (!is_dir("assets/uploads")) mkdir("assets/uploads");

    if (move_uploaded_file($tmp_file, $path)) {
        mysqli_query($conn, "UPDATE reservasi SET bukti_bayar = '$nama_file' WHERE kode_booking = '$kode'");
        echo "<script>alert('Bukti berhasil diupload! Menunggu konfirmasi admin.'); window.location.href='pembayaran.php?kode=$kode';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Padang Pagi Malam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-stone-50 font-sans">

    <?php if ($data['status'] == 'Pending'): ?>
    <div class="sticky top-0 z-50 bg-red-700 text-white p-4 text-center shadow-lg">
        <p class="text-sm uppercase tracking-widest mb-1">Sisa Waktu Pembayaran</p>
        <div class="text-3xl font-mono font-bold" id="timer">00:00</div>
    </div>
    <?php endif; ?>

    <div class="container mx-auto px-4 py-8 max-w-2xl">
        
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            <div class="bg-gray-800 p-6 text-center">
                <p class="text-gray-400 text-sm mb-1">Kode Booking Anda</p>
                <h1 class="text-4xl font-bold text-yellow-400 tracking-wider"><?= $data['kode_booking'] ?></h1>
            </div>

            <div class="p-8">
                <?php if($data['status'] == 'Paid'): ?>
                    
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-4xl">✅</span>
                        </div>
                        <h2 class="text-2xl font-bold text-green-700 mb-2">Pembayaran Lunas!</h2>
                        <p class="text-gray-600">Terima kasih. Silakan download invoice Anda.</p>
                        
                        <a href="invoice.php?kode=<?= $kode ?>" class="inline-block mt-6 bg-red-700 text-white px-6 py-3 rounded-lg font-bold hover:bg-red-800 transition shadow-lg">
                            📄 Download Invoice PDF
                        </a>
                    </div>

                <?php elseif(!empty($data['bukti_bayar']) && $data['metode_bayar'] == 'Transfer'): ?>

                    <div class="text-center py-8">
                        <div class="text-4xl mb-4">⏳</div>
                        <h2 class="text-xl font-bold text-blue-700 mb-2">Menunggu Konfirmasi</h2>
                        <p class="text-gray-600">Bukti transfer sudah diterima.<br>Admin sedang mengecek pembayaran Anda.</p>
                        <button onclick="location.reload()" class="mt-6 bg-blue-100 text-blue-700 px-4 py-2 rounded font-bold">Cek Status</button>
                    </div>

                <?php else: ?>

                    <div class="mb-8 text-center">
                        <p class="text-gray-600">Total Tagihan</p>
                        <p class="text-3xl font-bold text-red-800">Rp <?= number_format($data['total_bayar']) ?></p>
                    </div>

                    <?php if($data['metode_bayar'] == 'Cash'): ?>
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                            <h3 class="font-bold text-yellow-800 mb-2">Instruksi Cash:</h3>
                            <ol class="list-decimal list-inside text-sm text-yellow-900 space-y-2">
                                <li>Segera menuju Kasir.</li>
                                <li>Tunjukkan <b>Kode Booking</b> di atas.</li>
                                <li>Lakukan pembayaran sebelum waktu habis.</li>
                            </ol>
                        </div>
                        <div class="text-center text-sm text-gray-400 mb-6">Halaman ini akan otomatis terupdate saat kasir mengkonfirmasi.</div>

                    <?php else: ?>
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mb-6">
                            <h3 class="font-bold text-blue-800 mb-4 text-center">Transfer Bank BCA</h3>
                            <div class="flex justify-between items-center border-b border-blue-200 pb-4 mb-4">
                                <span class="text-gray-600">No. Rekening</span>
                                <span class="font-mono font-bold text-xl">123-456-7890</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Atas Nama</span>
                                <span class="font-bold">Padang Pagi Malam</span>
                            </div>
                        </div>

                        <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <label class="block text-sm font-bold text-gray-700">Upload Bukti Transfer</label>
                            <input type="file" name="bukti" accept="image/*" required class="w-full border p-2 rounded bg-gray-50">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg transition">
                                Kirim Bukti Transfer
                            </button>
                        </form>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 border-t text-center">
                <button onclick="confirmHome('<?= $data['kode_booking'] ?>')" class="text-gray-500 hover:text-red-700 font-bold text-sm flex items-center justify-center gap-2 w-full transition">
                    <span>🏠</span> Kembali ke Home / Lihat Menu
                </button>
            </div>
        </div>
    </div>

    <script>
        // 1. LOGIKA TIMER & AUTO REFRESH
        <?php if ($data['status'] == 'Pending'): ?>
        let timeLeft = <?= $sisa_waktu ?>;
        const timerDisplay = document.getElementById('timer');
        
        // Countdown Function
        const countdown = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerDisplay.innerHTML = "WAKTU HABIS";
                
                // Alert Waktu Habis
                Swal.fire({
                    icon: 'error',
                    title: 'Waktu Habis',
                    text: 'Maaf, waktu pembayaran telah habis. Pesanan dibatalkan.',
                    confirmButtonColor: '#B91C1C'
                }).then(() => {
                    location.reload();
                });
            } else {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerDisplay.innerHTML = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                timeLeft--;
            }
        }, 1000);

        // Auto Refresh Halaman setiap 10 Detik (Untuk Cek Status Lunas dari Kasir/Admin)
        setInterval(() => {
            location.reload();
        }, 10000);
        <?php endif; ?>


        // 2. LOGIKA TOMBOL HOME (SWEETALERT WARNING)
        function confirmHome(kode) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan Penting!',
                html: `Salin kode ini: <b class="text-2xl text-red-700 bg-red-100 px-2 rounded">${kode}</b><br><br>Jika hilang atau lupa, silakan konfirmasi kepada admin.`,
                showCancelButton: true,
                confirmButtonColor: '#B91C1C',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Sudah Salin, Ke Home',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'index.php';
                }
            });
        }
    </script>

</body>
</html>