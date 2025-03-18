<?php
session_start();

// Oturumu sonlandır
session_destroy();

// Admin giriş sayfasına yönlendir
header("Location: adlogin.php");
exit();
?>