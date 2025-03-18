<?php
session_start(); // Oturum başlat

// Oturumda kullanıcı varsa wallet.php'ye yönlendir
if (isset($_SESSION['username'])) {
    header("Location: wallet.php");
    exit();
}

$error = ""; // Hata mesajları için değişken

// Kayıt işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    include 'config.php'; // Veritabanı bağlantısı

    $username = mysqli_real_escape_string($conn, $_POST['reg-username']); // Güvenlik için
    $email = mysqli_real_escape_string($conn, $_POST['reg-email']); // Güvenlik için
    $password = password_hash($_POST['reg-password'], PASSWORD_DEFAULT); // Şifreyi güvenli bir şekilde hashle

    // Cüzdan adresini oluştur (örneğin, md5 hash'i kullanarak)
    $wallet_address = '0x' . md5(uniqid(rand(), true)); // Daha güvenli bir yöntem de kullanılabilir

    // Kullanıcı adı veya e-posta zaten var mı kontrol et
    $check_query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $error = "Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor.";
    } else {
        // Kullanıcıyı veritabanına ekle
        $sql = "INSERT INTO users (username, email, password, balance, wallet_address) VALUES ('$username', '$email', '$password', 0, '$wallet_address')";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['username'] = $username; // Oturumu başlat
            $_SESSION['id'] = mysqli_insert_id($conn); // Son eklenen kullanıcının ID'sini al
            $_SESSION['wallet_address'] = $wallet_address; // Cüzdan adresini de oturuma ekle
            header("Location: wallet.php"); // Cüzdan sayfasına yönlendir
            exit();
        } else {
            $error = "Kayıt sırasında bir hata oluştu: " . mysqli_error($conn);
        }
    }

    mysqli_close($conn); // Veritabanı bağlantısını kapat
}

// Giriş işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    include 'config.php'; // Veritabanı bağlantısı

    $username = mysqli_real_escape_string($conn, $_POST['username']); // Güvenlik için
    $password = $_POST['password'];

    // Kullanıcıyı veritabanında kontrol et
    $sql = "SELECT id, username, password, wallet_address FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username; // Oturumu başlat
            $_SESSION['id'] = $row['id']; // Kullanıcı id'sini de oturuma ekle
            $_SESSION['wallet_address'] = $row['wallet_address']; // Cüzdan adresini de oturuma ekle
            header("Location: wallet.php"); // Cüzdan sayfasına yönlendir
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
<title>ARAF Cüzdan - Giriş/Kayıt</title>
<style>
body {
  font-family: sans-serif;
  background-color: #1e212d;
  color: #fff;
}
.container {
  max-width: 600px;
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
input[type="password"],
input[type="email"] {
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
.form-container {
  display: block; /* Başlangıçta üyelik formu gösterilir */
}
.hide {
  display: none;
}

.error {
    color: red;
    margin-bottom: 10px;
}

</style>
</head>
<body>
<div class="container">
  <h1>ARAF (AF) Cüzdan</h1>

  <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div id="login-form" class="form-container">
    <h2>Giriş Yap</h2>
    <form method="post">
        <div class="form-group">
          <label for="username">Kullanıcı Adı:</label>
          <input type="text" id="username" name="username" placeholder="Kullanıcı Adınız">
        </div>
        <div class="form-group">
          <label for="password">Şifre:</label>
          <input type="password" id="password" name="password" placeholder="Şifreniz">
        </div>
        <button class="button" type="submit" name="login">Giriş</button>
        <p>Üyeliğiniz yok mu? <button type="button" class="button" onclick="showRegistrationForm()">Kayıt Ol</button></p>
    </form>
  </div>

  <div id="registration-form" class="form-container hide">
    <h2>Kayıt Ol</h2>
    <form method="post">
        <div class="form-group">
          <label for="reg-username">Kullanıcı Adı:</label>
          <input type="text" id="reg-username" name="reg-username" placeholder="Kullanıcı Adınız">
        </div>
        <div class="form-group">
          <label for="reg-email">Email:</label>
          <input type="email" id="reg-email" name="reg-email" placeholder="Email Adresiniz">
        </div>
        <div class="form-group">
          <label for="reg-password">Şifre:</label>
          <input type="password" id="reg-password" name="reg-password" placeholder="Şifreniz">
        </div>
        <button class="button" type="submit" name="register">Kayıt Ol</button>
        <p>Zaten üye misiniz? <button type="button" class="button" onclick="showLoginForm()">Giriş Yap</button></p>
    </form>
  </div>

</div>

<script>
function showRegistrationForm() {
  document.getElementById("login-form").classList.add("hide");
  document.getElementById("registration-form").classList.remove("hide");
}

function showLoginForm() {
  document.getElementById("registration-form").classList.add("hide");
  document.getElementById("login-form").classList.remove("hide");
}
</script>

</body>
</html>