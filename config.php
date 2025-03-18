<?php
$servername = "localhost"; // Veritabanı sunucusu
$username = "root"; // Veritabanı kullanıcı adı
$password = "ferid123456"; // Veritabanı şifresi
$dbname = "araf"; // Veritabanı adı

// Veritabanı bağlantısını oluştur
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Bağlantıyı kontrol et
if (!$conn) {
    die("Veritabanı bağlantısı başarısız: " . mysqli_connect_error());
}

// ARAF fiyatını veritabanından çeken fonksiyon
function getArafPrice($conn) {
    $price_query = "SELECT price FROM af_price WHERE id=1";
    $price_result = mysqli_query($conn, $price_query);
    if ($price_result && mysqli_num_rows($price_result) > 0) {
        $af_price = mysqli_fetch_assoc($price_result)['price'];
        return $af_price;
    } else {
        return 0.026; // Varsayılan fiyat (eğer veritabanında bulunamazsa)
    }
}
?>