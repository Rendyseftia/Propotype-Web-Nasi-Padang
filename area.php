<?php 
session_start(); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Area</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-stone-900 font-sans">

    <nav class="absolute top-0 w-full z-50 p-6 flex justify-between items-center text-white">
        <a href="index.php" class="flex items-center gap-2 hover:text-yellow-400 transition">
            <span class="text-xl">←</span> Kembali ke Home
        </a>
        <div class="text-yellow-500 font-serif text-xl font-bold">Langkah 1 / 4</div>
    </nav>

    <div class="flex flex-col md:flex-row h-screen w-full">

        <a href="kursi.php?pilih=Indoor" class="group relative w-full md:w-1/2 h-1/2 md:h-full overflow-hidden cursor-pointer">
            <div class="absolute inset-0 bg-cover bg-center transition duration-700 transform group-hover:scale-110" 
                 style="background-image: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1000');">
            </div>
            <div class="absolute inset-0 bg-black/60 group-hover:bg-black/40 transition duration-500"></div>
            <div class="absolute inset-0 flex flex-col justify-center items-center text-center p-4">
                <h2 class="text-4xl md:text-6xl font-serif font-bold text-white mb-2 group-hover:text-yellow-400 transition">INDOOR</h2>
                <p class="text-gray-300 text-lg tracking-widest uppercase group-hover:text-white">Ruangan AC</p>
            </div>
        </a>

        <a href="kursi.php?pilih=Outdoor" class="group relative w-full md:w-1/2 h-1/2 md:h-full overflow-hidden cursor-pointer border-t-4 md:border-t-0 md:border-l-4 border-yellow-600">
            <div class="absolute inset-0 bg-cover bg-center transition duration-700 transform group-hover:scale-110" 
                 style="background-image: url('https://images.unsplash.com/photo-1600093463592-8e36ae95ef56?q=80&w=1000');">
            </div>
            <div class="absolute inset-0 bg-black/60 group-hover:bg-black/40 transition duration-500"></div>
            <div class="absolute inset-0 flex flex-col justify-center items-center text-center p-4">
                <h2 class="text-4xl md:text-6xl font-serif font-bold text-white mb-2 group-hover:text-yellow-400 transition">OUTDOOR</h2>
                <p class="text-gray-300 text-lg tracking-widest uppercase group-hover:text-white">Smoking Area</p>
            </div>
        </a>

    </div>

</body>
</html>