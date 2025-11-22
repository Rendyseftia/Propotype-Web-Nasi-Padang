<nav class="fixed w-full z-40 top-0 transition-all duration-300 bg-red-800 shadow-md">
    <div class="container mx-auto px-6 py-3 flex justify-between items-center">
        <a href="index.php" class="text-2xl font-serif font-bold text-yellow-400 flex items-center gap-2">
            🌶️ Padang Pagi Malam
        </a>

        <div class="hidden md:flex space-x-8 text-white font-sans text-sm">
            <a href="index.php" class="hover:text-yellow-300 transition">Home</a>
            <button onclick="openModal('modal-about')" class="hover:text-yellow-300 transition">About Us</button>
            <button onclick="openModal('modal-contact')" class="hover:text-yellow-300 transition">Contact</button>
            <button onclick="openModal('modal-location')" class="hover:text-yellow-300 transition">Location</button>
        </div>

        <a href="cek_pesanan.php" class="bg-yellow-500 text-red-900 px-4 py-2 rounded-full font-bold hover:bg-yellow-400 transition text-sm shadow-lg border-2 border-transparent hover:border-white">
            🔍 Cek Pesanan
        </a>
    </div>
</nav>