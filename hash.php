<?php
$password = 'kamikadze123456'; // Buraya kendi belirlediğin güçlü şifreyi yaz
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "Oluşturulan Hash: " . $hashedPassword;
?>