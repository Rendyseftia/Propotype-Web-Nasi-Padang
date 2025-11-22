<?php 
include 'includes/db.php'; 

// ==========================================
// TAMBAHAN: LOGIKA CEK MEJA FULL (REAL + RANDOM)
// ==========================================
date_default_timezone_set('Asia/Jakarta'); // Pastikan zona waktu benar
$today = date('Y-m-d');
$full_tables = [];

// 1. AMBIL DARI DATABASE (RESERVASI WEB - REAL)
$q_reserved = mysqli_query($conn, "SELECT area, no_meja FROM reservasi WHERE tanggal_booking = '$today' AND status IN ('Pending', 'Paid')");
while ($row = mysqli_fetch_assoc($q_reserved)) {
    $full_tables[] = "🔴 " . $row['area'] . "-" . $row['no_meja'] . " (Booked)";
}

// 2. GENERATE RANDOM (WALK-IN CUSTOMER - DUMMY)
// Membuat seolah-olah ada 3 meja lain yang penuh oleh pelanggan datang langsung
$area_options = ['Indoor', 'Outdoor'];
for ($i = 0; $i < 3; $i++) {
    $rand_area = $area_options[rand(0, 1)];
    $rand_no = rand(1, 12);
    
    // Cek sederhana agar tidak duplikat persis dengan tulisan database
    $cek_str = $rand_area . "-" . $rand_no;
    $is_duplicate = false;
    foreach($full_tables as $ft) {
        if (strpos($ft, $cek_str) !== false) $is_duplicate = true;
    }

    if (!$is_duplicate) {
        $full_tables[] = "🟠 " . $rand_area . "-" . $rand_no . " (Walk-in)";
    }
}

// Gabungkan array jadi string panjang untuk running text
$info_full = implode("     |     ", $full_tables);
if (empty($full_tables)) {
    $info_full = "🟢 Saat ini Meja Masih Banyak Tersedia! Segera Reservasi!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padang Pagi Malam - Reservasi Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Tambahan CSS agar transisi modal lebih halus */
        .modal-transition {
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        
        /* TAMBAHAN: Animasi Marquee (Teks Berjalan) */
        .animate-marquee {
            animation: marquee 25s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>
<body class="bg-stone-50 font-sans antialiased">

    <?php include 'includes/navbar.php'; ?>

    <header class="relative h-screen flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1565557623262-b51c2513a641?q=80&w=1920&auto=format&fit=crop" 
                 class="w-full h-full object-cover brightness-50" 
                 alt="Nasi Padang Header">
        </div>

        <div class="relative z-10 text-center px-4 mt-16">
            <h1 class="text-5xl md:text-7xl font-serif text-white font-bold mb-4 drop-shadow-lg">
                Warisan Rasa <span class="text-yellow-500">Minang</span>
            </h1>
            <p class="text-gray-200 text-lg mb-8 max-w-2xl mx-auto leading-relaxed">
                Nikmati kelezatan rempah asli Sumatra dalam suasana modern. 
                Booking meja Anda sekarang tanpa perlu antri.
            </p>
            <a href="area.php" class="inline-block bg-red-700 hover:bg-red-600 text-white font-bold text-xl px-8 py-4 rounded-full shadow-xl transform hover:scale-105 transition duration-300 border-2 border-red-500 hover:border-white">
                RESERVASI SEKARANG ➜
            </a>
        </div>

        <div class="absolute bottom-0 w-full bg-black/80 text-white py-3 overflow-hidden border-t border-yellow-600 backdrop-blur-md z-20">
            <div class="flex items-center">
                <div class="bg-red-700 px-4 py-1 font-bold text-xs md:text-sm uppercase tracking-wider z-30 shadow-lg ml-2 rounded shrink-0">
                    LIVE STATUS:
                </div>
                <div class="whitespace-nowrap animate-marquee pl-4 font-mono text-sm text-yellow-300">
                    <?= $info_full ?>     |     <?= $info_full ?>     |     <?= $info_full ?>
                </div>
            </div>
        </div>
    </header>

    <section class="py-12 bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div class="p-6 group hover:bg-red-50 rounded-xl transition">
                <div class="text-4xl mb-4 transform group-hover:scale-125 transition">🌶️</div>
                <h3 class="font-bold text-xl mb-2 text-red-800">Rempah Asli</h3>
                <p class="text-gray-500 text-sm">Bumbu didatangkan langsung dari Sumatera Barat.</p>
            </div>
            <div class="p-6 border-l-0 md:border-l border-r-0 md:border-r border-gray-100 group hover:bg-red-50 rounded-xl transition">
                <div class="text-4xl mb-4 transform group-hover:scale-125 transition">⚡</div>
                <h3 class="font-bold text-xl mb-2 text-red-800">Penyajian Cepat</h3>
                <p class="text-gray-500 text-sm">Sistem hidang modern, makanan siap dalam 5 menit.</p>
            </div>
            <div class="p-6 group hover:bg-red-50 rounded-xl transition">
                <div class="text-4xl mb-4 transform group-hover:scale-125 transition">❄️</div>
                <h3 class="font-bold text-xl mb-2 text-red-800">Nyaman & Luas</h3>
                <p class="text-gray-500 text-sm">Pilihan ruang Indoor AC atau Outdoor Smoking Area.</p>
            </div>
        </div>
    </section>

    <section id="menu-section" class="py-16 container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-serif font-bold text-red-800">Menu Terfavorit</h2>
            <div class="w-24 h-1 bg-yellow-500 mx-auto mt-2 rounded-full"></div>
            <p class="text-gray-500 mt-4">Pilihan pelanggan minggu ini</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php
            $query = mysqli_query($conn, "SELECT * FROM menu LIMIT 3");
            while($menu = mysqli_fetch_assoc($query)) {
            ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:-translate-y-2 transition duration-300 group cursor-pointer border border-gray-100">
                <div class="h-56 overflow-hidden relative">
                    <span class="absolute top-4 right-4 bg-yellow-500 text-white text-xs font-bold px-3 py-1 rounded-full z-10 shadow">RECOMMENDED</span>
                    <img src="assets/img/<?= $menu['gambar'] ?>" 
                         onerror="this.src='https://placehold.co/600x400?text=Menu+Foto'" 
                         class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2 font-serif"><?= $menu['nama_menu'] ?></h3>
                    <div class="flex justify-between items-center">
                        <p class="text-red-700 font-bold text-lg">Rp <?= number_format($menu['harga']) ?></p>
                        <div class="flex text-yellow-400 text-xs">★★★★★ (5.0)</div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        
        <div class="text-center mt-10">
            <a href="area.php" class="text-red-800 font-bold hover:underline text-lg">Lihat Menu Lengkap & Pesan ➜</a>
        </div>
    </section>

    <section class="flex flex-col md:flex-row h-96 w-full">
        <div class="w-full md:w-1/2 bg-cover bg-center relative group cursor-pointer" 
             style="background-image: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=800');">
            <div class="absolute inset-0 bg-black/50 group-hover:bg-black/30 transition duration-500 flex items-center justify-center">
                <h3 class="text-white text-3xl font-serif font-bold border-b-2 border-transparent group-hover:border-yellow-400 transition">INDOOR SPACE</h3>
            </div>
        </div>
        <div class="w-full md:w-1/2 bg-cover bg-center relative group cursor-pointer" 
             style="background-image: url('https://images.unsplash.com/photo-1600093463592-8e36ae95ef56?q=80&w=800');">
            <div class="absolute inset-0 bg-black/50 group-hover:bg-black/30 transition duration-500 flex items-center justify-center">
                <h3 class="text-white text-3xl font-serif font-bold border-b-2 border-transparent group-hover:border-yellow-400 transition">SMOKING AREA</h3>
            </div>
        </div>
    </section>

    <footer class="bg-green-900 text-white py-10">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-2xl font-serif font-bold mb-4 text-yellow-400">Padang Pagi Malam</h2>
            <p class="mb-4 text-gray-300">Jl. Ks Tubun. Gg. 12, Samarinda Kota</p>
            <div class="flex justify-center gap-4 mb-8">
                <a href="https://instagram.com/rendyseftiaa_" target="_blank" class="hover:text-yellow-400 transition flex items-center gap-2">
                    <span>📸</span> Instagram
                </a>
                <a href="https://wa.me/6282154956553?text=Hallo%20Admin%20Padang%20Pagi%20Malam!" target="_blank" class="hover:text-yellow-400 transition flex items-center gap-2">
                    <span>📱</span> WhatsApp
                </a>
            </div>
            <p class="text-xs text-gray-400">© 2025 Tugas Web Programming.</p>
        </div>
    </footer>

    <div id="modal-about" onclick="closeModal('modal-about')" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex justify-center items-center p-4 modal-transition opacity-0">
        <div onclick="event.stopPropagation()" class="bg-white p-8 rounded-2xl max-w-lg w-full relative shadow-2xl transform scale-95 transition-transform duration-300">
            <button onclick="closeModal('modal-about')" class="absolute top-4 right-4 text-gray-400 hover:text-red-600 text-2xl font-bold">×</button>
            
            <h2 class="text-3xl font-serif font-bold text-red-800 mb-4">Tentang Kami</h2>
            
            <div class="mb-6 rounded-xl overflow-hidden shadow-md border-2 border-gray-100">
                <img src="assets/img/kelompok.jpg" onerror="this.src='https://placehold.co/600x350?text=FOTO+KELOMPOK'" class="w-full h-64 object-cover hover:scale-105 transition duration-700">
            </div>

            <p class="text-gray-600 leading-relaxed mb-6 text-justify text-sm">
            "Kami, sebagai kelompok dalam mata pelajaran Pemrograman Web Dasar, telah berusaha semaksimal mungkin dalam membuat dan menyelesaikan proyek website ini. 
             Meski masih terdapat kekurangan, kami berkomitmen untuk terus belajar dan memperbaiki kemampuan kami dalam hal desain, struktur kode, dan fungsionalitas web. 
             Pembuatan website ini menjadi pengalaman berharga bagi kami karena mengajarkan kerja sama, ketelitian, dan cara menerapkan konsep dasar web secara nyata. 
             Kami berharap hasil yang kami sajikan dapat menjadi langkah awal untuk pengembangan web yang lebih baik di masa mendatang." 
                <b>Tugas Akhir Mata Kuliah Dasar Pemrograman Web.</b>
            </p>
            <blockquote class="border-l-4 border-yellow-500 pl-4 italic text-gray-700 bg-yellow-50 p-4 rounded-lg text-xs">
                "Q.S. Al-Insyirah: 6"
"Sesungguhnya bersama kesulitan ada kemudahan."
            </blockquote>
        </div>
    </div>

    <div id="modal-contact" onclick="closeModal('modal-contact')" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex justify-center items-center p-4 modal-transition opacity-0">
        <div onclick="event.stopPropagation()" class="bg-white p-8 rounded-2xl max-w-md w-full relative transform scale-95 transition-transform duration-300">
            <button onclick="closeModal('modal-contact')" class="absolute top-4 right-4 text-gray-400 hover:text-red-600 text-2xl font-bold">×</button>
            <h2 class="text-2xl font-bold text-red-800 mb-6 text-center">Hubungi Admin</h2>
            <div class="space-y-4">
                <a href="https://wa.me/6282154956553?text=Hallo%20Admin%20Padang%20Pagi%20Malam!" target="_blank" class="flex items-center gap-4 p-4 bg-green-50 rounded-xl hover:bg-green-100 transition group">
                    <span class="text-3xl group-hover:scale-110 transition">📱</span>
                    <div>
                        <p class="text-xs text-gray-500 font-bold">WhatsApp (Chat Only)</p>
                        <p class="text-green-700 font-bold">+62 821-5495-6553</p>
                    </div>
                </a>
                <a href="mailto:2411102441104@umkt.ac.id?subject=Tanya%20Reservasi&body=Hallo%20Admin%20Padang%20Pagi%20Malam!" class="flex items-center gap-4 p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition group">
                    <span class="text-3xl group-hover:scale-110 transition">📧</span>
                    <div>
                        <p class="text-xs text-gray-500 font-bold">Email Official</p>
                        <p class="text-blue-700 font-bold">2411102441104@umkt.ac.id</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div id="modal-location" onclick="closeModal('modal-location')" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex justify-center items-center p-4 modal-transition opacity-0">
        <div onclick="event.stopPropagation()" class="bg-white p-2 rounded-2xl max-w-2xl w-full relative transform scale-95 transition-transform duration-300">
            <button onclick="closeModal('modal-location')" class="absolute -top-10 right-0 text-white hover:text-red-500 text-3xl font-bold">×</button>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d997.4183643846337!2d117.1416024695476!3d-0.48824839996917935!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2df67f16c5d2d275%3A0x267bd9fa2a60b480!2sGg.%2012%20No.52%2C%20RW.Rt.14Kel%2C%20Dadi%20Mulya%2C%20Kec.%20Samarinda%20Ulu%2C%20Kota%20Samarinda%2C%20Kalimantan%20Timur%2075123!5e0!3m2!1sen!2sid!4v1763567207081!5m2!1sen!2sid" 
                    width="100%" height="400" style="border:0; border-radius: 1rem;" allowfullscreen="" loading="lazy"></iframe>
            <div class="p-4 text-center">
                <h3 class="font-bold text-red-800">Padang Pagi Malam Pusat</h3>
                <p class="text-gray-600 text-sm">Jl. KS Tubun. Gg 12, Samarinda Kota </p>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            if(modal) {
                modal.classList.remove('hidden');
                // Delay sedikit agar animasi opacity berjalan
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    modal.children[0].classList.remove('scale-95');
                    modal.children[0].classList.add('scale-100');
                }, 10);
            } else {
                console.error("Modal dengan ID " + id + " tidak ditemukan!");
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if(modal) {
                modal.classList.add('opacity-0');
                modal.children[0].classList.remove('scale-100');
                modal.children[0].classList.add('scale-95');
                // Tunggu animasi selesai baru hidden
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        }
    </script>

</body>
</html>