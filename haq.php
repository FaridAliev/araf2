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

<center><img width="300" src="img/koin/4.png"></center>
<div style="color: orange;"><b>Hakkımızda: ARAF ile Zirveye Uçuşa Hazır Mısınız?</b></div><br/>

Durun, nefes alın ve gözlerinizi kapatın. Şimdi, dijital finansın sınırlarını paramparça ettiğimiz, hayallerimizin ötesine geçtiğimiz ve ARAF ile zirveye uçtuğumuz bir dünya hayal edin. İşte burası, sadece bir başlangıç. ARAF, sıradanlığa meydan okuyan, imkansızı mümkün kılan ve sizi zirveye taşıyacak bir güçtür!<br/><br/>

<div style="color: orange;"><b>Misyonumuz: Gücü İçinizde Hissedin!</b></div><br/>

Artık başkalarının çizdiği sınırlara mahkum değilsiniz! ARAF, size kendi ekonomik evreninizi yaratma, yönetme ve genişletme gücü veriyor. İçinizdeki potansiyeli keşfedin, finansal özgürlüğün kanatlarıyla yükselin!<br/><br/>

<div style="color: orange;"><b>Vizyonumuz: Yıldızlara Dokunun!</b></div><br/>

ARAF, sadece bir dijital para birimi değil, bir yaşam tarzı, bir felsefe, bir yükseliş destanıdır! Amacımız, ARAF'ı küresel bir finansal devrimin öncüsü yapmak. İnsanların ARAF ile birbirleriyle bağ kurduğu, hayallerini paylaştığı ve birlikte yıldızlara dokunduğu bir dünya inşa etmek.<br/><br/>

<div style="color: orange;"><b>Değerlerimiz: Bizi Ayakta Tutanlar!</b></div><br/>

Bağımsızlık: Zincirleri kırın, kendi kaderinizi yazın!<br/>
Topluluk: Birlikte büyüyelim, birlikte zirveye ulaşalım!<br/>
Güven: Şeffaflık, dürüstlük ve güvenilirlik, ARAF'ın DNA'sında var!<br/>
Erişilebilirlik: Finansal özgürlük herkesin hakkı! ARAF, kapıları sonuna kadar açıyor.<br/>
Yenilik: Sürekli gelişim, sürekli değişim! ARAF, geleceği şekillendiriyor.<br/><br/>

<div style="color: orange;"><b>ARAF'ın Farkı: Neden Başka Bir Seçenek Yok?</b></div><br/>

Kendi Kütlesi: Özgün, benzersiz ve güçlü! ARAF, kendi evrenini yaratıyor.<br/>
Topluluk Ekonomisi: Birlikte kazanalım, birlikte zenginleşelim!<br/>
Güvenli ve Kolay: Herkes için erişilebilir, herkes için güvenli!<br/>
E-ticaret: ARAF ile alışverişin keyfini çıkarın, yeni bir ekonomi yaratın!<br/>
API: Geliştiriciler, ARAF ile hayal gücünüzün sınırlarını zorlayın!<br/><br/>


<div style="color: orange;"><b>Gelecek Hedeflerimiz: Birlikte Efsane Yazalım!</b></div><br/>

ARAF'ı küresel bir finansal efsane haline getireceğiz!<br/>
ARAF ekosistemini genişleteceğiz, yeni dünyalar keşfedeceğiz!<br/>
Topluluğumuzu büyüteceğiz, milyonlara ilham vereceğiz!<br/>
Değer istikrarını sağlayacağız, güvenin sembolü olacağız!<br/><br/>

<div style="color: orange;"><b>Bize Katılın: Bu Yükselişin Parçası Olun!</b></div><br/>

ARAF topluluğuna katılarak, finansal özgürlüğün öncüsü olun, bu büyük maceranın bir parçası olun! Birlikte, hayallerimizi gerçeğe dönüştüreceğiz. ARAF ile zirveye uçuş zamanı!
</div>



  <div class="section">
    <h2>AF Hakkında</h2>
    <p>ARAF (AF) token, [AF token'ın açıklaması]...  1 AF = 0.026 USD</p>
  </div>

</div>
</body>
</html>