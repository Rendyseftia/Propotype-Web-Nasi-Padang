<?php
session_start();
include 'includes/db.php';

// Logika untuk menyimpan data post dari halaman menu ke session
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $keranjang = [];
    $total_final = 0;

    if (isset($_POST['qty'])) {
        foreach ($_POST['qty'] as $id => $qty) {
            if ($qty > 0) {
                $nama = $_POST['nama_menu'][$id];
                $harga = $_POST['harga'][$id];
                $subtotal = $qty * $harga;
                
                $keranjang[] = [
                    'id' => $id,
                    'nama' => $nama,
                    'harga' => $harga,
                    'qty' => $qty,
                    'subtotal' => $subtotal
                ];
                $total_final += $subtotal;
            }
        }
    }
    $_SESSION['keranjang'] = $keranjang;
    $_SESSION['total_final'] = $total_final;
}

// Cek apakah data pesanan ada
$info = isset($_SESSION['info_pesanan']) ? $_SESSION['info_pesanan'] : null;
$items = isset($_SESSION['keranjang']) ? $_SESSION['keranjang'] : [];
$total = isset($_SESSION['total_final']) ? $_SESSION['total_final'] : 0;

// Jika data kosong, lempar balik ke menu
if (empty($items) || empty($info)) {
    header("Location: menu.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-stone-50 font-sans pb-20">

    <nav class="bg-white shadow-sm p-4 mb-8">
        <div class="container mx-auto flex justify-between items-center">
            <a href="menu.php" class="text-gray-600 hover:text-red-800">← Kembali</a>
            <h1 class="font-serif font-bold text-xl text-red-800">Cek Pesanan</h1>
            <div class="text-yellow-500 font-bold">Langkah 4 / 4</div>
        </div>
    </nav>

    <div class="container mx-auto px-4 max-w-3xl">
        <div class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-yellow-500 relative">
            
            <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 bg-yellow-500 text-white px-6 py-1 rounded-full font-bold shadow-md">
                STRUK PESANAN
            </div>

            <div class="mt-4 mb-6 border-b pb-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Nama Pemesan</p>
                        <p class="font-bold text-gray-800"><?= htmlspecialchars($info['nama']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-500">Waktu & Tempat</p>
                        <p class="font-bold text-gray-800">
                            <?= date('d M Y', strtotime($info['tanggal'])) ?>, <?= $info['jam'] ?>
                        </p>
                        <p class="text-red-600 font-bold"><?= $_SESSION['area'] ?> - Meja <?= $info['meja'] ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Kontak</p>
                        <p class="font-bold text-gray-800"><?= htmlspecialchars($info['hp']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-500">Email</p>
                        <p class="font-bold text-gray-800"><?= htmlspecialchars($info['email']) ?></p>
                    </div>
                </div>
            </div>

            <table class="w-full mb-6 text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="text-left py-2 px-2">Menu</th>
                        <th class="text-center py-2 px-2">Qty</th>
                        <th class="text-right py-2 px-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr class="border-b border-dashed">
                        <td class="py-3 px-2">
                            <div class="font-bold text-gray-800"><?= $item['nama'] ?></div>
                            <div class="text-xs text-gray-500">@ Rp <?= number_format($item['harga']) ?></div>
                        </td>
                        <td class="text-center py-3 px-2">x<?= $item['qty'] ?></td>
                        <td class="text-right py-3 px-2 font-bold">Rp <?= number_format($item['subtotal']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="pt-4 text-right font-bold text-lg">TOTAL BAYAR</td>
                        <td class="pt-4 text-right font-bold text-2xl text-red-800">Rp <?= number_format($total) ?></td>
                    </tr>
                </tfoot>
            </table>

            <div class="bg-red-50 p-4 rounded-lg border border-red-200 mb-8">
                <h3 class="font-bold text-red-800 mb-2">⚠️ Perhatian</h3>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    <li>Pastikan data pesanan sudah benar.</li>
                    <li>Setelah klik tombol bayar, Anda memiliki waktu terbatas untuk menyelesaikan pembayaran.</li>
                    <li>Pesanan tidak dapat dibatalkan manual setelah konfirmasi.</li>
                </ul>
            </div>

            <h3 class="font-bold text-center mb-4 text-gray-800">Pilih Metode Pembayaran</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <form action="pembayaran.php" method="POST" onsubmit="return confirmOrder(event, 'Cash')">
                    <input type="hidden" name="metode" value="Cash">
                    <button type="submit" class="w-full p-4 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:bg-green-50 transition flex items-center gap-4 group cursor-pointer">
                        <div class="bg-green-100 p-3 rounded-full text-2xl">💵</div>
                        <div class="text-left">
                            <div class="font-bold text-gray-800 group-hover:text-green-700">Bayar di Kasir (Cash)</div>
                            <div class="text-xs text-gray-500">Batas waktu kedatangan 15 Menit</div>
                        </div>
                    </button>
                </form>

                <form action="pembayaran.php" method="POST" onsubmit="return confirmOrder(event, 'Transfer')">
                    <input type="hidden" name="metode" value="Transfer">
                    <button type="submit" class="w-full p-4 border-2 border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition flex items-center gap-4 group cursor-pointer">
                        <div class="bg-blue-100 p-3 rounded-full text-2xl">🏦</div>
                        <div class="text-left">
                            <div class="font-bold text-gray-800 group-hover:text-blue-700">Transfer Bank</div>
                            <div class="text-xs text-gray-500">Batas waktu upload 10 Menit</div>
                        </div>
                    </button>
                </form>

            </div>
            </div>
    </div>

    <script>
        function confirmOrder(e, method) {
            e.preventDefault(); // Mencegah form submit langsung
            const form = e.target;
            
            Swal.fire({
                title: 'Konfirmasi Pesanan?',
                text: `Anda memilih metode ${method}. Timer akan berjalan setelah ini.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#B91C1C', // Warna Merah
                cancelButtonColor: '#6B7280', // Warna Abu
                confirmButtonText: 'Ya, Proses Sekarang',
                cancelButtonText: 'Cek Lagi'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Lanjut submit jika user klik Ya
                }
            });
        }
    </script>
</body>
</html>