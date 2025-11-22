<?php
$host = "localhost"; 
$user = "root";     
$pass = "";         
$db   = "db_padang_malam"; 
$port = 3307;
$conn = mysqli_connect($host, $user, $pass, $db, $port);
if (!$conn) {
    die("Gagal Konek Database: " . mysqli_connect_error());
}
date_default_timezone_set('Asia/Jakarta');
$now = date('Y-m-d H:i:s');

$query_cleanup = "UPDATE reservasi 
                  SET status = 'Batal' 
                  WHERE status = 'Pending' AND '$now' > expired_at";
mysqli_query($conn, $query_cleanup);
?>