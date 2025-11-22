<?php
session_start();
include 'includes/db.php';

// LOGIKA PENYIMPANAN SESSION DARI HALAMAN KURSI
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi agar jumlah orang tidak kosong/nol (Default 1)
    $pax = isset($_POST['jumlah_orang']) && !empty($_POST['jumlah_orang']) ? $_POST['jumlah_orang'] : 1;

    $_SESSION['info_pesanan'] = [
        'tanggal' => $_POST['tanggal'],
        'jam'     => $_POST['jam'],
        'meja'    => $_POST['selected_meja'],
        'nama'    => $_POST['nama'],
        'hp'      => $_POST['hp'],
        'email'   => $_POST['email'],
        'jumlah_orang' => $pax // Data Penting!
    ];
}

// Jika session kosong, lempar kembali
if (!isset($_SESSION['info_pesanan'])) {
    header("Location: kursi.php");
    exit;
}

$info = $_SESSION['info_pesanan'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Menu - Padang Pagi Malam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-stone-50 font-sans pb-32">

    <nav class="bg-white shadow-sm p-4 sticky top-0 z-40">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" onclick="return confirm('Yakin ingin membatalkan pesanan dan kembali ke Home? Data akan hilang.')" class="flex items-center gap-2 text-gray-500 hover:text-red-700 transition text-sm font-bold bg-gray-100 px-3 py-2 rounded-lg hover:bg-red-50">
                <span>🏠</span> <span class="hidden md:inline">Batal / Home</span>
            </a>
            
            <div class="text-center text-xs md:text-sm text-gray-500 leading-tight">
                Hai, <b class="text-red-800"><?= htmlspecialchars($info['nama']) ?></b><br>
                Meja <?= $info['meja'] ?> (<?= $info['jam'] ?>)
            </div>
            
            <div class="text-yellow-500 font-bold text-sm md:text-base">Langkah 3 / 4</div>
        </div>
    </nav>

    <form action="konfirmasi.php" method="POST" id="form-menu">
        <div class="container mx-auto px-4 mt-8">
            
            <h2 class="text-2xl font-serif font-bold text-red-800 mb-6 border-l-4 border-yellow-500 pl-4 flex items-center gap-2">
                🍛 Makanan Berat
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                <?php
                $query = mysqli_query($conn, "SELECT * FROM menu WHERE kategori='Makanan'");
                while($m = mysqli_fetch_assoc($query)):
                ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden flex flex-row h-32 relative transform transition hover:scale-105 border border-gray-100">
                    <img src="assets/img/<?= $m['gambar'] ?>" onerror="this.src='https://placehold.co/150?text=Makanan'" class="w-32 h-full object-cover">
                    <div class="p-4 flex flex-col justify-between w-full">
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm md:text-base"><?= $m['nama_menu'] ?></h4>
                            <p class="text-red-700 font-bold">Rp <?= number_format($m['harga']) ?></p>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" onclick="updateQty(<?= $m['id'] ?>, -1, <?= $m['harga'] ?>)" class="w-8 h-8 bg-gray-200 rounded-full font-bold text-gray-600 hover:bg-gray-300 transition">-</button>
                            <input type="text" readonly name="qty[<?= $m['id'] ?>]" id="qty-<?= $m['id'] ?>" value="0" class="w-8 text-center font-bold text-gray-700 outline-none">
                            <button type="button" onclick="updateQty(<?= $m['id'] ?>, 1, <?= $m['harga'] ?>)" class="w-8 h-8 bg-red-700 rounded-full font-bold text-white hover:bg-red-800 transition">+</button>
                            
                            <input type="hidden" name="harga[<?= $m['id'] ?>]" value="<?= $m['harga'] ?>">
                            <input type="hidden" name="nama_menu[<?= $m['id'] ?>]" value="<?= $m['nama_menu'] ?>">
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <h2 class="text-2xl font-serif font-bold text-red-800 mb-6 border-l-4 border-yellow-500 pl-4 flex items-center gap-2">
                🥗 Menu Ringan / Pelengkap
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                <?php
                $query = mysqli_query($conn, "SELECT * FROM menu WHERE kategori='Menu Ringan'");
                while($m = mysqli_fetch_assoc($query)):
                ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden flex flex-row h-32 relative transform transition hover:scale-105 border border-gray-100">
                    <img src="assets/img/<?= $m['gambar'] ?>" onerror="this.src='https://placehold.co/150?text=Sayur'" class="w-32 h-full object-cover">
                    <div class="p-4 flex flex-col justify-between w-full">
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm md:text-base"><?= $m['nama_menu'] ?></h4>
                            <p class="text-red-700 font-bold">Rp <?= number_format($m['harga']) ?></p>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" onclick="updateQty(<?= $m['id'] ?>, -1, <?= $m['harga'] ?>)" class="w-8 h-8 bg-gray-200 rounded-full font-bold text-gray-600 hover:bg-gray-300 transition">-</button>
                            <input type="text" readonly name="qty[<?= $m['id'] ?>]" id="qty-<?= $m['id'] ?>" value="0" class="w-8 text-center font-bold text-gray-700 outline-none">
                            <button type="button" onclick="updateQty(<?= $m['id'] ?>, 1, <?= $m['harga'] ?>)" class="w-8 h-8 bg-red-700 rounded-full font-bold text-white hover:bg-red-800 transition">+</button>
                            
                            <input type="hidden" name="harga[<?= $m['id'] ?>]" value="<?= $m['harga'] ?>">
                            <input type="hidden" name="nama_menu[<?= $m['id'] ?>]" value="<?= $m['nama_menu'] ?>">
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <h2 class="text-2xl font-serif font-bold text-red-800 mb-6 border-l-4 border-yellow-500 pl-4 flex items-center gap-2">
                🍹 Minuman Segar
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $query = mysqli_query($conn, "SELECT * FROM menu WHERE kategori='Minuman'");
                while($m = mysqli_fetch_assoc($query)):
                ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden flex flex-row h-32 relative transform transition hover:scale-105 border border-gray-100">
                    <img src="assets/img/<?= $m['gambar'] ?>" onerror="this.src='https://placehold.co/150?text=Minum'" class="w-32 h-full object-cover">
                    <div class="p-4 flex flex-col justify-between w-full">
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm md:text-base"><?= $m['nama_menu'] ?></h4>
                            <p class="text-red-700 font-bold">Rp <?= number_format($m['harga']) ?></p>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" onclick="updateQty(<?= $m['id'] ?>, -1, <?= $m['harga'] ?>)" class="w-8 h-8 bg-gray-200 rounded-full font-bold text-gray-600 hover:bg-gray-300 transition">-</button>
                            <input type="text" readonly name="qty[<?= $m['id'] ?>]" id="qty-<?= $m['id'] ?>" value="0" class="w-8 text-center font-bold text-gray-700 outline-none">
                            <button type="button" onclick="updateQty(<?= $m['id'] ?>, 1, <?= $m['harga'] ?>)" class="w-8 h-8 bg-red-700 rounded-full font-bold text-white hover:bg-red-800 transition">+</button>
                            
                            <input type="hidden" name="harga[<?= $m['id'] ?>]" value="<?= $m['harga'] ?>">
                            <input type="hidden" name="nama_menu[<?= $m['id'] ?>]" value="<?= $m['nama_menu'] ?>">
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

        </div>

        <div class="fixed bottom-0 w-full bg-white shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] p-4 z-50 border-t">
            <div class="container mx-auto flex justify-between items-center">
                <div>
                    <p class="text-gray-500 text-xs">Estimasi Total</p>
                    <p class="text-2xl font-bold text-red-800" id="total-display">Rp 0</p>
                    <input type="hidden" name="total_harga" id="input-total" value="0">
                </div>
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white font-bold py-3 px-8 rounded-full shadow-lg flex items-center gap-2 transition transform hover:scale-105">
                    Konfirmasi Pesanan ➜
                </button>
            </div>
        </div>
    </form>

    <script>
        let grandTotal = 0;

        // Fungsi Tambah/Kurang Qty
        function updateQty(id, change, harga) {
            const input = document.getElementById('qty-' + id);
            let currentVal = parseInt(input.value);
            let newVal = currentVal + change;

            if (newVal < 0) newVal = 0;
            
            if (newVal !== currentVal) {
                input.value = newVal;
                grandTotal += (change * harga);
                document.getElementById('total-display').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
                document.getElementById('input-total').value = grandTotal;
            }
        }

        // Validasi Minimum Order saat Submit
        document.getElementById('form-menu').addEventListener('submit', function(e) {
            const total = parseInt(document.getElementById('input-total').value);
            
            
            const minimumCharge = 100000; 

            if (total < minimumCharge) {
                e.preventDefault(); 
                Swal.fire({
                    icon: 'error',
                    title: 'Pesanan Belum Cukup',
                    text: 'Mohon maaf, Minimum Charge pemesanan adalah Rp 100.000',
                    confirmButtonColor: '#B91C1C',
                    confirmButtonText: 'Oke, Saya Tambah Menu'
                });
            }
        });
    </script>
</body>
</html>