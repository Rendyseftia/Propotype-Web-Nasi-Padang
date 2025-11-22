<?php
session_start();
include '../includes/db.php';

// 1. WAJIB: SET ZONA WAKTU AGAR SINKRON DENGAN CLIENT
date_default_timezone_set('Asia/Makassar');

$hasil_cari = null;
if (isset($_SESSION['hasil_cari_kasir'])) {
    $hasil_cari = $_SESSION['hasil_cari_kasir'];
}

$today = date('Y-m-d');

// 2. QUERY MONITOR: Pastikan mengambil status 'Pending' DAN 'Paid' untuk tanggal HARI INI
$q_monitor = mysqli_query($conn, "SELECT * FROM reservasi WHERE tanggal_booking = '$today' AND status IN ('Pending', 'Paid')");
$meja_active = [];
while($row = mysqli_fetch_assoc($q_monitor)) {
    // Key format: Area-NoMeja (Contoh: Indoor-1)
    $key = $row['area'] . '-' . $row['no_meja'];
    $meja_active[$key] = $row;
}

// LOGIKA KEUANGAN (Hanya Paid + Selesai)
$q_cash = mysqli_query($conn, "SELECT SUM(total_bayar) AS uang FROM reservasi WHERE status IN ('Paid', 'Selesai') AND metode_bayar = 'Cash'");
$d_cash = mysqli_fetch_assoc($q_cash);
$total_cash = $d_cash['uang'] ? $d_cash['uang'] : 0;

$q_tf = mysqli_query($conn, "SELECT SUM(total_bayar) AS uang FROM reservasi WHERE status IN ('Paid', 'Selesai') AND metode_bayar = 'Transfer'");
$d_tf = mysqli_fetch_assoc($q_tf);
$total_tf = $d_tf['uang'] ? $d_tf['uang'] : 0;

$grand_total = $total_cash + $total_tf;
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500 flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Pendapatan Cash</p>
            <h3 class="text-2xl font-bold text-green-600 mt-1">Rp <?= number_format($total_cash) ?></h3>
        </div>
        <div class="text-3xl bg-green-100 p-3 rounded-full">💵</div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500 flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Pendapatan Transfer</p>
            <h3 class="text-2xl font-bold text-blue-600 mt-1">Rp <?= number_format($total_tf) ?></h3>
        </div>
        <div class="text-3xl bg-blue-100 p-3 rounded-full">💳</div>
    </div>

    <div class="bg-gray-800 p-6 rounded-xl shadow-lg border-l-4 border-yellow-400 flex items-center justify-between text-white">
        <div>
            <p class="text-gray-300 text-xs font-bold uppercase tracking-wider">Total Omzet (All)</p>
            <h3 class="text-3xl font-bold text-yellow-400 mt-1">Rp <?= number_format($grand_total) ?></h3>
        </div>
        <div class="text-3xl bg-gray-700 p-3 rounded-full">💰</div>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-purple-600 mb-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        <span>📡</span> Live Monitor Meja (<?= date('d M Y') ?>)
    </h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div>
            <h3 class="font-bold text-center mb-4 bg-gray-200 p-2 rounded">INDOOR AREA</h3>
            <div class="grid grid-cols-3 md:grid-cols-4 gap-3">
                <?php for($i=1; $i<=12; $i++): 
                    $key = 'Indoor-'.$i;
                    $data = isset($meja_active[$key]) ? $meja_active[$key] : null;
                    // Logic Warna: Paid = Hijau, Pending = Kuning
                    $bgClass = 'bg-gray-100'; // Default Kosong
                    $borderClass = 'border-gray-300';
                    
                    if ($data) {
                        $borderClass = 'border-transparent';
                        if ($data['status'] == 'Paid') {
                            $bgClass = 'bg-green-600'; // SUDAH BAYAR
                        } else {
                            $bgClass = 'bg-yellow-500'; // BELUM BAYAR
                        }
                    }
                ?>
                <div class="<?= $bgClass ?> border-2 <?= $borderClass ?> rounded-lg p-2 h-32 relative flex flex-col justify-between transition shadow-sm text-xs">
                    <div class="flex justify-between items-start">
                        <span class="font-bold <?= $data ? 'text-white' : 'text-gray-500' ?>">#<?= $i ?></span>
                        <?php if($data): ?>
                            <span class="bg-white px-1 rounded font-bold text-gray-800 scale-75 origin-right"><?= $data['status'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($data): ?>
                        <div class="text-white leading-tight mt-1">
                            <p class="font-bold truncate mb-1"><?= $data['nama_pelanggan'] ?></p>
                            <div class="flex flex-col gap-0.5 bg-black/20 p-1 rounded">
                                <span>🕒 <?= date('H:i', strtotime($data['jam_booking'])) ?></span>
                                <span>👥 <?= $data['jumlah_orang'] ?> Pax</span>
                            </div>
                        </div>
                        <a href="index.php?act=selesai&id=<?= $data['id'] ?>" onclick="return confirm('Pelanggan sudah pulang? Kosongkan meja ini?')" 
                            class="absolute bottom-1 right-1 bg-white text-red-600 px-2 py-0.5 rounded hover:bg-red-100 font-bold shadow scale-90 origin-bottom-right cursor-pointer">
                            Clear
                        </a>
                    <?php else: ?>
                        <div class="flex items-center justify-center h-full opacity-20">
                            <span class="text-2xl">🪑</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <div>
            <h3 class="font-bold text-center mb-4 bg-gray-200 p-2 rounded">OUTDOOR AREA</h3>
            <div class="grid grid-cols-3 md:grid-cols-4 gap-3">
                <?php for($i=1; $i<=12; $i++): 
                    $key = 'Outdoor-'.$i;
                    $data = isset($meja_active[$key]) ? $meja_active[$key] : null;
                    // Logic Warna
                    $bgClass = 'bg-gray-100';
                    $borderClass = 'border-gray-300';
                    
                    if ($data) {
                        $borderClass = 'border-transparent';
                        if ($data['status'] == 'Paid') {
                            $bgClass = 'bg-green-600';
                        } else {
                            $bgClass = 'bg-yellow-500';
                        }
                    }
                ?>
                <div class="<?= $bgClass ?> border-2 <?= $borderClass ?> rounded-lg p-2 h-32 relative flex flex-col justify-between transition shadow-sm text-xs">
                    <div class="flex justify-between items-start">
                        <span class="font-bold <?= $data ? 'text-white' : 'text-gray-500' ?>">#<?= $i ?></span>
                        <?php if($data): ?>
                            <span class="bg-white px-1 rounded font-bold text-gray-800 scale-75 origin-right"><?= $data['status'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($data): ?>
                        <div class="text-white leading-tight mt-1">
                            <p class="font-bold truncate mb-1"><?= $data['nama_pelanggan'] ?></p>
                             <div class="flex flex-col gap-0.5 bg-black/20 p-1 rounded">
                                <span>🕒 <?= date('H:i', strtotime($data['jam_booking'])) ?></span>
                                <span>👥 <?= $data['jumlah_orang'] ?> Pax</span>
                            </div>
                        </div>
                        <a href="index.php?act=selesai&id=<?= $data['id'] ?>" onclick="return confirm('Pelanggan sudah pulang? Kosongkan meja ini?')" 
                            class="absolute bottom-1 right-1 bg-white text-red-600 px-2 py-0.5 rounded hover:bg-red-100 font-bold shadow scale-90 origin-bottom-right cursor-pointer">
                            Clear
                        </a>
                    <?php else: ?>
                        <div class="flex items-center justify-center h-full opacity-20">
                            <span class="text-2xl">🌳</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-green-500">
        <h2 class="text-xl font-bold text-gray-800 mb-4">💵 Kasir (Input Kode)</h2>
        <form action="" method="POST" class="flex gap-2 mb-4">
            <input type="text" name="kode" placeholder="PM-..." class="flex-1 border p-2 rounded uppercase" required>
            <button type="submit" name="cari_kode" class="bg-green-600 text-white px-4 py-2 rounded font-bold hover:bg-green-700">Cek</button>
        </form>
        <?php if($hasil_cari): ?>
            <div class="bg-gray-50 p-4 rounded border">
                <div class="flex justify-between">
                    <div>
                        <p><b>Nama:</b> <?= $hasil_cari['nama_pelanggan'] ?></p>
                        <p><b>Total:</b> Rp <?= number_format($hasil_cari['total_bayar']) ?></p>
                    </div>
                    <div class="text-right text-sm">
                         <p>📅 <?= date('d/m', strtotime($hasil_cari['tanggal_booking'])) ?></p>
                         <p>🕒 <?= date('H:i', strtotime($hasil_cari['jam_booking'])) ?></p>
                         <p>👥 <?= $hasil_cari['jumlah_orang'] ?> Org</p>
                    </div>
                </div>
                <p class="mt-2"><b>Status:</b> <span class="font-bold text-blue-600"><?= $hasil_cari['status'] ?></span></p>
                
                <?php if($hasil_cari['status'] == 'Pending' && $hasil_cari['metode_bayar'] == 'Cash'): ?>
                    <form action="" method="POST" class="mt-4 grid grid-cols-2 gap-2">
                        <input type="hidden" name="id_reservasi" value="<?= $hasil_cari['id'] ?>">
                        <button type="submit" name="confirm_cash" onclick="return confirm('Terima Pembayaran Lunas?')" class="bg-green-600 text-white py-2 rounded font-bold hover:bg-green-700">✓ TERIMA</button>
                        <button type="submit" name="reject_cash" onclick="return confirm('Batalkan Pesanan Ini?')" class="bg-red-600 text-white py-2 rounded font-bold hover:bg-red-700">✗ BATALKAN</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-blue-500">
        <h2 class="text-xl font-bold text-gray-800 mb-4">🏦 Verifikasi Transfer</h2>
        <div class="overflow-x-auto max-h-80 overflow-y-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 sticky top-0"><tr><th class="p-2">Info Pesanan</th><th class="p-2">Bukti</th><th class="p-2">Aksi</th></tr></thead>
                <tbody>
                    <?php
                    $q_tf = mysqli_query($conn, "SELECT * FROM reservasi WHERE metode_bayar='Transfer' AND status='Pending' AND bukti_bayar IS NOT NULL");
                    if(mysqli_num_rows($q_tf) > 0): while($row = mysqli_fetch_assoc($q_tf)): ?>
                    <tr class="border-b">
                        <td class="p-2">
                            <div class="font-bold text-blue-800"><?= $row['kode_booking'] ?></div>
                            <div><?= $row['nama_pelanggan'] ?></div>
                            <div class="text-xs text-gray-500 mt-1">
                                📅 <?= date('d/m', strtotime($row['tanggal_booking'])) ?> | 🕒 <?= date('H:i', strtotime($row['jam_booking'])) ?><br>
                                👥 <?= $row['jumlah_orang'] ?> Orang | Rp <?= number_format($row['total_bayar']) ?>
                            </div>
                        </td>
                        <td class="p-2"><a href="../assets/uploads/<?= $row['bukti_bayar'] ?>" target="_blank"><img src="../assets/uploads/<?= $row['bukti_bayar'] ?>" class="w-12 h-12 object-cover rounded hover:scale-150"></a></td>
                        <td class="p-2 flex flex-col gap-1">
                            <a href="index.php?act=accept&id=<?= $row['id'] ?>" onclick="return confirm('Terima Pembayaran?')" class="bg-green-500 text-white px-2 py-1 rounded text-center hover:bg-green-600">✓</a>
                            <a href="index.php?act=reject&id=<?= $row['id'] ?>" onclick="return confirm('Tolak Pesanan?')" class="bg-red-500 text-white px-2 py-1 rounded text-center hover:bg-red-600">✗</a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?><tr><td colspan="3" class="p-4 text-center text-gray-400">Kosong.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-gray-600">
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
        <h2 class="text-xl font-bold text-gray-800">📋 Riwayat Pesanan</h2>
        <input type="text" id="searchHistory" placeholder="Cari Nama / Kode..." class="border p-2 rounded w-full md:w-1/3">
    </div>
    <div class="overflow-x-auto max-h-96 overflow-y-auto">
        <table class="w-full text-sm text-left border" id="historyTable">
            <thead class="bg-gray-100 sticky top-0">
                <tr>
                    <th class="p-3 border">Tgl Pesan</th>
                    <th class="p-3 border">Kode</th>
                    <th class="p-3 border">Nama</th>
                    <th class="p-3 border">Detail Booking</th>
                    <th class="p-3 border">Total</th>
                    <th class="p-3 border">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q_all = mysqli_query($conn, "SELECT * FROM reservasi ORDER BY created_at DESC LIMIT 100");
                while($d = mysqli_fetch_assoc($q_all)):
                    $statusColor = ($d['status'] == 'Paid' || $d['status'] == 'Selesai') ? 'text-green-600' : (($d['status'] == 'Batal') ? 'text-red-600' : 'text-yellow-600');
                ?>
                <tr class="hover:bg-gray-50 border-b">
                    <td class="p-3 border"><?= date('d/m H:i', strtotime($d['created_at'])) ?></td>
                    <td class="p-3 border font-mono font-bold"><?= $d['kode_booking'] ?></td>
                    <td class="p-3 border"><?= $d['nama_pelanggan'] ?></td>
                    <td class="p-3 border text-xs">
                        <div class="font-bold">📅 <?= date('d M Y', strtotime($d['tanggal_booking'])) ?></div>
                        <div>🕒 <?= date('H:i', strtotime($d['jam_booking'])) ?> WIB</div>
                        <div>👥 <?= $d['jumlah_orang'] ?> Orang (<?= $d['area'] ?>-<?= $d['no_meja'] ?>)</div>
                    </td>
                    <td class="p-3 border">Rp <?= number_format($d['total_bayar']) ?></td>
                    <td class="p-3 border font-bold <?= $statusColor ?>"><?= $d['status'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('searchHistory').addEventListener('keyup', function() {
        let searchValue = this.value.toLowerCase();
        let tableRows = document.querySelectorAll('#historyTable tbody tr');
        tableRows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(searchValue) ? '' : 'none';
        });
    });
</script>