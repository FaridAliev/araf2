<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ARAF Cüzdan - Ana Sayfa</title>
<style>
body {
  font-family: sans-serif;
  background-color: #1e212d;
  color: #fff;
}
.container {
  max-width: 960px;
  margin: 0 auto;
  padding: 20px;
}
.header {
  background-color: #282c34;
  padding: 20px;
  text-align: center;
}
.section {
  background-color: #333;
  padding: 20px;
  margin-bottom: 20px;
  border-radius: 5px;
}
.feature {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}
.feature-icon {
  width: 50px;
  height: 50px;
  background-color: #ffa500;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-right: 10px;
  color: white;
  font-size: 24px;
}
.button {
  background-color: #ffa500;
  color: #fff;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1>ARAF (AF) Cüzdan</h1>
    <p>Güvenli ve Kullanışlı Kripto Para Cüzdanınız</p>
    <?php
        include 'config.php'; // Veritabanı bağlantısı
        $araf_price = getArafPrice($conn); // ARAF fiyatını al
        mysqli_close($conn); // Veritabanı bağlantısını kapat
    ?>
    <p>1 AF = <?php echo htmlspecialchars(number_format($araf_price, 5)); ?> USD</p>
  </div>

  <div class="section">
    <h2>ARAF Cüzdan ile Neler Yapabilirsiniz?</h2>
    <div class="feature">
      <div class="feature-icon">💰</div>
      <p>Kolayca AF tokenlerinizi güvenli bir şekilde saklayın ve yönetin.</p>
    </div>
    <div class="feature">
      <div class="feature-icon">💸</div>
      <p>Diğer kullanıcılara hızlı ve güvenli bir şekilde AF token gönderin.</p>
    </div>
    <div class="feature">
      <div class="feature-icon">📈</div>
      <p>Cüzdanınızdaki AF token bakiyenizi gerçek zamanlı olarak takip edin.</p>
    </div>
    <div class="feature">
      <div class="feature-icon">⚙️</div>
      <p>Kullanışlı hesap makinesi ile AF ve USD arasında dönüşüm yapın.</p>
    </div>
    <div class="feature">
      <div class="feature-icon">⛏️</div>
      <p>(Simüle edilmiş) Madencilik yaparak AF token kazanın.</p>
    </div>
<br/>
    <a href="get.php" class="button">Cüzdana Başlayın</a> </div>

  <div class="section">
    <h2>AF Hakkında</h2>
    <p>ARAF (AF) token, [AF token'ın açıklaması]...  1 AF = 0.026 USD</p>
  </div>

</div>
</body>
</html>