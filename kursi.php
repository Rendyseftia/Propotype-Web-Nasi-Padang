<?php
session_start();
include 'includes/db.php';

// 1. SET ZONA WAKTU SAMARINDA (WITA)
date_default_timezone_set('Asia/Makassar');

if (isset($_GET['pilih'])) {
    $_SESSION['area'] = $_GET['pilih'];
}

$area = isset($_SESSION['area']) ? $_SESSION['area'] : 'Indoor';
$tanggal_pilih = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// 2. LOGIKA BARU UNTUK JAM DEFAULT (FIX BUG 23:00)
// Ambil jam sekarang (Format 00-24)
$jam_sekarang_angka = (int) date('H');

if (isset($_GET['jam'])) {
    // Jika user memilih lewat dropdown
    $jam_pilih = $_GET['jam'];
} else {
    // Jika user baru masuk halaman:
    // Cek apakah jam sekarang ada di rentang buka (10:00 - 21:00)
    if ($jam_sekarang_angka >= 10 && $jam_sekarang_angka <= 21) {
        $jam_pilih = date('H:00'); // Pakai jam sekarang
    } else {
        $jam_pilih = '10:00'; // Jika toko tutup/kepagian, paksa default jam 10:00
    }
}

$kapasitas_meja = [
    1 => 4, 2 => 4, 3 => 12, 4 => 10, 
    5 => 2, 6 => 2, 7 => 8, 8 => 8, 
    9 => 4, 10 => 4, 11 => 6, 12 => 6  
];

$query = "SELECT no_meja FROM reservasi 
          WHERE tanggal_booking = '$tanggal_pilih' 
          AND jam_booking = '$jam_pilih' 
          AND area = '$area'
          AND status IN ('Pending', 'Paid')";

$result = mysqli_query($conn, $query);
$meja_terisi = [];
while ($row = mysqli_fetch_assoc($result)) {
    $meja_terisi[] = $row['no_meja'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Kursi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-stone-50 font-sans">

    <nav class="bg-white shadow-sm p-4 mb-8 sticky top-0 z-40">
        <div class="container mx-auto flex justify-between items-center">
            <a href="area.php" class="text-gray-600 hover:text-red-800">← Kembali</a>
            <h1 class="font-serif font-bold text-xl text-red-800">Pilih Waktu & Kursi (<?= $area ?>)</h1>
            <div class="text-yellow-500 font-bold">Langkah 2 / 4</div>
        </div>
    </nav>

    <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8 pb-10">
        
        <div class="col-span-1 order-2 md:order-1">
            <div class="bg-white p-6 rounded-xl shadow-lg sticky top-24">
                <h3 class="font-bold text-lg mb-4 text-gray-800 border-b pb-2">1. Atur Waktu</h3>
                
                <form action="" method="GET" class="mb-6">
                    <?php if(isset($_GET['pilih'])): ?>
                        <input type="hidden" name="pilih" value="<?= $_GET['pilih'] ?>">
                    <?php endif; ?>

                    <div class="mb-4">
                        <label class="block text-sm text-gray-500 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="<?= $tanggal_pilih ?>" onchange="this.form.submit()" 
                               class="w-full border p-2 rounded focus:ring-red-500 focus:border-red-500 cursor-pointer hover:bg-gray-50" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-500 mb-1">Jam Makan</label>
                        <select name="jam" onchange="this.form.submit()" class="w-full border p-2 rounded focus:ring-red-500 cursor-pointer hover:bg-gray-50" required>
                            <?php
                            $start = 10; 
                            $end = 21;   
                            for ($i = $start; $i <= $end; $i++) {
                                $time = sprintf("%02d:00", $i);
                                $selected = ($time == $jam_pilih) ? 'selected' : '';
                                echo "<option value='$time' $selected>$time</option>";
                            }
                            ?>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Jam Operasional: 10:00 - 21:00 WITA</p>
                    </div>
                </form>

                <h3 class="font-bold text-lg mb-4 text-gray-800 border-b pb-2">2. Data Pemesan</h3>
                
                <form action="menu.php" method="POST" id="form-data">
                    <input type="hidden" name="tanggal" value="<?= $tanggal_pilih ?>">
                    <input type="hidden" name="jam" value="<?= $jam_pilih ?>">
                    <input type="hidden" name="selected_meja" id="input_meja" required>

                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" class="w-full border p-2 rounded" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Jumlah Orang (Pax)</label>
                        <input type="number" id="jumlah_orang" name="jumlah_orang" 
                               min="1" 
                               disabled
                               oninput="cekMaksimal(this)"
                               class="w-full border p-2 rounded bg-gray-100 cursor-not-allowed focus:ring-red-500 transition" 
                               placeholder="Pilih meja dulu..." required>
                        <p class="text-xs text-red-500 mt-1 font-bold" id="info-kapasitas">*Silakan pilih meja di peta sebelah kanan</p>
                    </div>

                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">No WhatsApp</label>
                        <input type="number" name="hp" class="w-full border p-2 rounded" placeholder="0812..." required>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" class="w-full border p-2 rounded" placeholder="email@contoh.com" required>
                    </div>

                    <button type="submit" id="btn-lanjut" disabled class="w-full bg-gray-300 text-white font-bold py-3 rounded-lg mt-4 transition cursor-not-allowed flex justify-center items-center gap-2">
                        <span>Pilih Menu Makanan</span>
                        <span>→</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-span-1 md:col-span-2 order-1 md:order-2">
            <div class="bg-white p-8 rounded-xl shadow-lg h-full border-t-4 border-red-800">
                <h3 class="font-bold text-lg mb-6 text-center text-gray-800 font-serif">Denah Meja <?= $area ?> (<?= $jam_pilih ?>)</h3>
                
                <div class="flex justify-center gap-4 mb-8 text-xs md:text-sm flex-wrap">
                    <div class="flex items-center gap-2"><div class="w-4 h-4 bg-green-50 border border-green-500 rounded"></div> Kosong</div>
                    <div class="flex items-center gap-2"><div class="w-4 h-4 bg-red-100 border border-red-500 rounded"></div> Terisi</div>
                    <div class="flex items-center gap-2"><div class="w-4 h-4 bg-yellow-400 border border-yellow-600 rounded"></div> Pilihanmu</div>
                </div>

                <div class="grid grid-cols-3 md:grid-cols-4 gap-4 md:gap-6">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <?php 
                        $kapasitas = $kapasitas_meja[$i];
                        $is_booked = in_array($i, $meja_terisi);
                        $class_booked = "bg-red-50 border-red-300 text-red-400 cursor-not-allowed opacity-60";
                        $class_avail  = "bg-green-50 border-green-500 text-green-700 hover:bg-green-100 cursor-pointer hover:shadow-md transform hover:-translate-y-1";
                        ?>
                        
                        <div onclick="<?= $is_booked ? '' : "pilihMeja($i, $kapasitas)" ?>" 
                             id="meja-<?= $i ?>"
                             class="relative h-24 border-2 rounded-xl flex flex-col items-center justify-center transition-all duration-300 <?= $is_booked ? $class_booked : $class_avail ?>">
                            
                            <span class="text-2xl mb-1"><?= $is_booked ? '❌' : '🪑' ?></span>
                            <span class="font-bold text-sm">Meja <?= $i ?></span>
                            
                            <?php if($is_booked): ?>
                                <span class="text-[10px] mt-1 font-bold uppercase">Booked</span>
                            <?php else: ?>
                                <span class="text-[10px] mt-1 text-gray-500 font-bold bg-white px-2 rounded-full border border-gray-200">
                                    Max <?= $kapasitas ?> Org
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cekMaksimal(input) {
            const max = parseInt(input.getAttribute('max'));
            if (input.value > max) {
                input.value = max; 
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: `Maksimal ${max} Orang untuk meja ini!`,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        }

        function pilihMeja(nomor, kapasitas) {
            document.querySelectorAll('[id^="meja-"]').forEach(el => {
                if (!el.classList.contains('bg-red-50')) {
                    el.classList.remove('bg-yellow-400', 'border-yellow-600', 'text-white');
                    el.classList.add('bg-green-50', 'border-green-500', 'text-green-700');
                }
            });

            const element = document.getElementById('meja-' + nomor);
            element.classList.remove('bg-green-50', 'border-green-500', 'text-green-700');
            element.classList.add('bg-yellow-400', 'border-yellow-600', 'text-white');

            document.getElementById('input_meja').value = nomor;
            
            const inputOrang = document.getElementById('jumlah_orang');
            inputOrang.disabled = false;
            inputOrang.classList.remove('bg-gray-100', 'cursor-not-allowed');
            inputOrang.classList.add('bg-white');
            inputOrang.value = ''; 
            inputOrang.max = kapasitas; 
            inputOrang.placeholder = `Max ${kapasitas} Orang...`; 

            document.getElementById('info-kapasitas').innerText = `*Meja ${nomor} hanya muat ${kapasitas} orang`;
            document.getElementById('info-kapasitas').classList.remove('text-red-500');
            document.getElementById('info-kapasitas').classList.add('text-green-600');

            const btn = document.getElementById('btn-lanjut');
            btn.disabled = false;
            btn.classList.remove('bg-gray-300', 'cursor-not-allowed');
            btn.classList.add('bg-red-700', 'hover:bg-red-800');
        }
    </script>
</body>
</html>