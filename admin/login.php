<?php
session_start();
include '../includes/db.php';

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau Password Salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-xl shadow-2xl w-96">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Admin Login 🔒</h1>
        
        <?php if(isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-700 transition">
                MASUK DASHBOARD
            </button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="../index.php" class="text-sm text-gray-500 hover:underline">← Kembali ke Website</a>
        </div>
    </div>

</body>
</html>