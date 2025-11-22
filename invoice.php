<?php
session_start();
include 'includes/db.php';

if (!isset($_GET['kode'])) {
    header("Location: index.php");
    exit;
}

$kode = $_GET['kode'];
$query = mysqli_query($conn, "SELECT * FROM reservasi WHERE kode_booking = '$kode' AND status = 'Paid'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Invoice tidak tersedia atau pesanan belum lunas.");
}

$id_reservasi = $data['id'];
$query_detail = mysqli_query($conn, "SELECT rd.*, m.nama_menu, m.harga 
                                     FROM reservasi_detail rd 
                                     JOIN menu m ON rd.id_menu = m.id 
                                     WHERE rd.id_reservasi = '$id_reservasi'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= $kode ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #555; }
        .invoice-box { background: white; width: 100%; max-width: 600px; margin: 20px auto; padding: 30px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

    <div class="text-center no-print pt-4">
        <button onclick="downloadPDF()" class="bg-red-700 text-white px-6 py-2 rounded font-bold shadow">Download PDF</button>
        <a href="index.php" class="text-white ml-4 underline">Kembali ke Home</a>
    </div>

    <div id="element-to-print" class="invoice-box">
        <div class="text-center border-b-4 border-double border-black pb-4 mb-4">
            <h1 class="text-3xl font-bold uppercase">Padang Pagi Malam</h1>
            <p class="text-sm">Jl. Ks Tubun. Gg. 12, Samarinda Kota</p>
            <p class="text-sm">WhatsApp: 0821-5495-6553</p>
        </div>

        <div class="flex justify-between mb-6 text-sm">
            <div>
                <p>Kode Booking: <b><?= $data['kode_booking'] ?></b></p>
                <p>Nama: <?= $data['nama_pelanggan'] ?></p>
                <p>No HP: <?= $data['no_hp'] ?></p>
            </div>
            <div class="text-right">
                <p>Tanggal: <?= date('d/m/Y', strtotime($data['tanggal_booking'])) ?></p>
                <p>Jam: <?= date('H:i', strtotime($data['jam_booking'])) ?></p>
                <p>Meja: <?= $data['area'] ?> - No <?= $data['no_meja'] ?></p>
            </div>
        </div>

        <table class="w-full text-sm mb-6">
            <thead class="border-b border-black">
                <tr>
                    <th class="text-left py-2">Menu</th>
                    <th class="text-center py-2">Qty</th>
                    <th class="text-right py-2">Harga</th>
                    <th class="text-right py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($query_detail)): ?>
                <tr>
                    <td class="py-1"><?= $row['nama_menu'] ?></td>
                    <td class="text-center py-1"><?= $row['qty'] ?></td>
                    <td class="text-right py-1"><?= number_format($row['harga']) ?></td>
                    <td class="text-right py-1"><?= number_format($row['qty'] * $row['harga']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot class="border-t border-black font-bold">
                <tr>
                    <td colspan="3" class="pt-2 text-right">TOTAL</td>
                    <td class="pt-2 text-right">Rp <?= number_format($data['total_bayar']) ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">METODE BAYAR</td>
                    <td class="text-right uppercase"><?= $data['metode_bayar'] ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">STATUS</td>
                    <td class="text-right">LUNAS (PAID)</td>
                </tr>
            </tfoot>
        </table>

        <div class="text-center mt-8 border-t border-dashed border-gray-400 pt-4 text-xs text-gray-500">
            <p>Terima kasih atas kunjungan Anda.</p>
            <p>Simpan struk ini sebagai bukti reservasi.</p>
        </div>
    </div>

    <script>
        function downloadPDF() {
            var element = document.getElementById('element-to-print');
            var opt = {
                margin:       0.5,
                filename:     'Invoice-PadangPagiMalam-<?= $kode ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>