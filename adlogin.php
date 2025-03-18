<?php
session_start();

// Oturumda admin varsa admin.php'ye yönlendir
if (isset($_SESSION['admin_username'])) {
    header("Location: admin.php");
    exit();
}

$error = ""; // Hata mesajları için değişken

// Giriş işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'config.php'; // Veritabanı bağlantısı

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Admin kullanıcısını veritabanında kontrol et
    $sql = "SELECT id, username, password FROM admins WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_username'] = $username; // Oturumu başlat
            $_SESSION['admin_id'] = $row['id']; // Admin ID'sini de oturuma ekle
            header("Location: admin.php"); // Admin paneline yönlendir
            exit();
        } else {
            $error = "Yanlış şifre.";
        }
    } else {
        $error = "Bu kullanıcı adı bulunamadı.";
    }

    mysqli_close($conn); // Veritabanı bağlantısını kapat
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ARAF Admin - Giriş</title>
<style>
body {
  font-family: sans-serif;
  background-color: #1e212d;
  color: #fff;
}
.container {
  max-width: 400px;
  margin: 100px auto;
  padding: 20px;
  background-color: #282c34;
  border-radius: 5px;
}
.form-group {
  margin-bottom: 15px;
}
label {
  display: block;
  margin-bottom: 5px;
}
input[type="text"],
input[type="password"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #555;
  border-radius: 3px;
  background-color: #333;
  color: #fff;
}
.button {
  background-color: #ffa500;
  color: #fff;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  width: 100%;
}
.error {
    color: red;
    margin-bottom: 10px;
}
</style>
</head>
<body>
<div class="container">
  <h1>ARAF Admin Paneli - Giriş</h1>

  <?php if ($error): ?>
      <div class="error"><?php echo $error; ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="form-group">
      <label for="username">Kullanıcı Adı:</label>
      <input type="text" id="username" name="username" placeholder="Kullanıcı Adınız">
    </div>
    <div class="form-group">
      <label for="password">Şifre:</label>
      <input type="password" id="password" name="password" placeholder="Şifreniz">
    </div>
    <button class="button" type="submit">Giriş</button>
  </form>
</div>
</body>
</html>