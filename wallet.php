<?php
session_start();

// Kullanıcı oturumu yoksa get.php'ye yönlendir
if (!isset($_SESSION['username'])) {
    header("Location: get.php");
    exit();
}

include 'config.php'; // Veritabanı bağlantısı

$username = $_SESSION['username'];
$user_id = $_SESSION['id'];

// Cüzdan adresini oturumdan al
$wallet_address = isset($_SESSION['wallet_address']) ? $_SESSION['wallet_address'] : '';

// Kullanıcının bilgilerini veritabanından al
$sql = "SELECT balance, mining_balance, is_mining, api_key FROM users WHERE id='$user_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $balance = $row['balance'];
    $mining_balance = $row['mining_balance'];
    $is_mining = $row['is_mining'];
    $api_key = $row['api_key'];
} else {
    $balance = 0; // Varsayılan bakiye
    $mining_balance = 0; // Varsayılan madencilik bakiyesi
    $is_mining = 0; // Varsayılan madencilik durumu
    $api_key = ""; // Varsayılan API anahtarı
}

// ARAF Fiyatını Al
$araf_price = getArafPrice($conn);

// Madencilik Hızını Al
$admin_settings_query = "SELECT mining_speed FROM admin_settings WHERE id=1";
$admin_settings_result = mysqli_query($conn, $admin_settings_query);
$mining_speed = mysqli_fetch_assoc($admin_settings_result)['mining_speed'];

// İşlem kaydı ekleyen fonksiyon
function logTransaction($conn, $user_id, $type, $details) {
    $type = mysqli_real_escape_string($conn, $type);
    $details = mysqli_real_escape_string($conn, $details);
    $sql = "INSERT INTO transactions (user_id, date, type, details) VALUES ('$user_id', NOW(), '$type', '$details')";
    mysqli_query($conn, $sql);
}

// API Anahtarı Oluşturma Fonksiyonu
function generateApiKey() {
    return bin2hex(random_bytes(32)); // 64 karakterlik rastgele bir anahtar oluştur
}

// API Anahtarı Oluşturma/Değiştirme İşlemi (AJAX ile)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'generateApiKey') {
    // Yeni API anahtarı oluştur
    $new_api_key = generateApiKey();
    $hashed_api_key = hash('sha256', $new_api_key); // API anahtarını hashle

    // API anahtarını veritabanına kaydet/güncelle
    $update_query = "UPDATE users SET api_key='$hashed_api_key' WHERE id='$user_id'";

    if (mysqli_query($conn, $update_query)) {
        // Oturum değişkenini güncelle
        $_SESSION['api_key'] = $new_api_key;
        // Başarılı yanıt gönder
        echo json_encode(['status' => 'success', 'api_key' => $new_api_key]);
    } else {
        // Hata yanıtı gönder
        echo json_encode(['status' => 'error', 'message' => 'API anahtarı oluşturulurken/değiştirilirken bir hata oluştu.']);
    }
    exit; // İşlemi sonlandır
}

// İşlem ekleme fonksiyonu (JavaScript'ten çağrılacak)
if (isset($_POST['action']) && $_POST['action'] == 'addTransaction') {
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);
    $date = date("Y-m-d H:i:s");

    $insert_query = "INSERT INTO transactions (user_id, date, type, details) VALUES ('$user_id', '$date', '$type', '$details')";
    mysqli_query($conn, $insert_query);
    exit(); // İşlem tamamlandı, çık
}

// Bakiye arttırma
if (isset($_POST['action']) && $_POST['action'] == 'addBalance') {
    $amount = 10; // Sabit değer, isteğe göre değiştirilebilir
    $new_balance = $balance + $amount;

    $update_query = "UPDATE users SET balance='$new_balance' WHERE id='$user_id'";
    if (mysqli_query($conn, $update_query)) {
        $balance = $new_balance; // Bakiyeyi güncelle
        echo "success"; // Başarı mesajı
    } else {
        echo "error"; // Hata mesajı
    }
    exit();
}

// Koin gönderme
if (isset($_POST['action']) && $_POST['action'] == 'sendCoin') {
    $recipient_address = mysqli_real_escape_string($conn, $_POST['address']);
    $amount = floatval($_POST['amount']);

    // Alıcı adresi sistemde kayıtlı mı kontrol et
    $recipient_query = "SELECT id, balance FROM users WHERE wallet_address='$recipient_address'";
    $recipient_result = mysqli_query($conn, $recipient_query);

    // Kendi adresine göndermeyi engelle
    if ($recipient_address == $wallet_address) {
        echo "own_address"; // Kendi adresine gönderme hatası
        exit();
    }

    if (mysqli_num_rows($recipient_result) == 1) {
        $recipient_row = mysqli_fetch_assoc($recipient_result);
        $recipient_id = $recipient_row['id'];
        $recipient_balance = $recipient_row['balance'];

        // Yetersiz bakiye kontrolü
        if ($amount > $balance) {
            echo "insufficient"; // Yetersiz bakiye mesajı
            exit();
        }

        // Gönderenin bakiyesini güncelle
        $sender_new_balance = $balance - $amount;
        $sender_update_query = "UPDATE users SET balance='$sender_new_balance' WHERE id='$user_id'";

        // Alıcının bakiyesini güncelle
        $recipient_new_balance = $recipient_balance + $amount;
        $recipient_update_query = "UPDATE users SET balance='$recipient_new_balance' WHERE id='$recipient_id'";

        // İşlemleri gerçekleştir
        if (mysqli_query($conn, $sender_update_query) && mysqli_query($conn, $recipient_update_query)) {
            $balance = $sender_new_balance; // Gönderenin bakiyesini güncelle

            // İşlem kayıtları eklenebilir (önerilir)

            echo "success"; // Başarı mesajı
        } else {
            echo "error"; // Hata mesajı
        }
    } else {
        echo "invalid_address"; // Geçersiz adres mesajı
    }
    exit();
}

// Madencilik İşlemi
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'toggleMining') {
        $is_mining = $_POST['is_mining'] == 'true' ? 1 : 0; // JavaScript'ten gelen değeri al

        // Madencilik durumunu veritabanında güncelle
        $update_query = "UPDATE users SET is_mining='$is_mining' WHERE id='$user_id'";
        mysqli_query($conn, $update_query);

        echo "success"; // Başarı mesajı
        exit();
    } elseif ($_POST['action'] == 'transferMiningBalance') {
        // Madencilik bakiyesini esas bakiyeye aktar
        $new_balance = $balance + $mining_balance;
        $update_query = "UPDATE users SET balance='$new_balance', mining_balance=0 WHERE id='$user_id'";

        if (mysqli_query($conn, $update_query)) {
            $balance = $new_balance; // Bakiyeyi güncelle

            // Madencilik bakiyesini sıfırla
            $mining_balance = 0;

            // JavaScript'e yeni değerleri gönder
            $response = array(
                'status' => 'success',
                'balance' => number_format($balance, 2),
                'mining_balance' => number_format($mining_balance, 2)
            );

            echo json_encode($response);
        } else {
            echo "error"; // Hata mesajı
        }
        exit();
    }
    elseif ($_POST['action'] == 'updateMiningBalance') {
        $newMiningBalance = $_POST['miningBalance'];
        $update_query = "UPDATE users SET mining_balance='$newMiningBalance' WHERE id='$user_id'";
        if(mysqli_query($conn, $update_query)) {
            $mining_balance = $newMiningBalance;
            echo "success";
        } else {
            echo "error";
        }
        exit();
    }
}

// İşlem geçmişini al
$transactions_query = "SELECT date, type, details FROM transactions WHERE user_id='$user_id' ORDER BY date DESC LIMIT 10";
$transactions_result = mysqli_query($conn, $transactions_query);

mysqli_close($conn); // Veritabanı bağlantısını kapat
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ARAF Cüzdan</title>
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
.button {
  background-color: #ffa500;
  color: #fff;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
.modal {
  display: none;
  position: fixed;
  z-index: 1;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.4);
}
.modal-content {
  background-color: #333;
  margin: 15% auto;
  padding: 20px;
  border: 1px solid #888;
  width: 500px; /* Genişliği arttırdık */
}
.modal-content iframe {
  width: 100%;
  height: 300px; /* iframe yüksekliğini belirledik */
  border: 1px solid #888;
}

@font-face {
  font-family: 'araf1'; /* Font ailesi adı (isteğe bağlı) */
  src: url('font/araf1-Regular.woff2') format('woff2'),
       url('font/araf1-Regular.woff') format('woff');
  font-weight: normal;
  font-style: normal;
}



.close {
  float: right;
  color: #fff;
  font-size: 20px;
  cursor: pointer;
}

</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1>ARAF (AF) Cüzdan</h1>
	 <div style="font-family: 'araf1', sans-serif; font-size: 40pt; color: orange;">A</div>
    <p><font style="font-family: 'araf1', sans-serif; font-size: 15pt; color: orange;">A</font> 1 = <?php echo htmlspecialchars(number_format($araf_price, 5)); ?> USD</p>
    <p>Hoş Geldin, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
  </div>

  <div class="section">
    <h2>Özel Cüzdan Adresiniz:</h2>
    <p><?php echo htmlspecialchars($wallet_address); ?></p>
  </div>

  <div class="section">
    <h2>Balans: <span id="balance"><?php echo htmlspecialchars(number_format($balance, 2)); ?></span>  <font style="font-family: 'araf1', sans-serif; font-size: 20pt; color: orange;">A</font></h2>
    <button class="button" onclick="showBalanceModal()">Balans Arttır</button>
  </div>

  <div class="section">
    <h2>Koin Gönder</h2>
    <input type="text" id="sendAddress" placeholder="Alıcı Adresi">
    <input type="number" id="sendAmount" placeholder="Miktar">
    <button class="button" onclick="sendCoin()">Gönder</button>
  </div>

  <div class="section">
    <h2>İşlem Tarihi</h2>
    <button class="button" onclick="showTransactionModal()">İşlemleri Göster</button>
  </div>

   <div class="section">
    <h2>Madencilik Bakiyesi: <span id="miningBalance"><?php echo htmlspecialchars(number_format($mining_balance, 2)); ?></span> AF</h2>
    <button id="miningButton" class="button" onclick="toggleMining()"><?php echo $is_mining ? "Madenciliği Durdur" : "Madenciliğe Başla"; ?></button>
    <p>Madencilik Hızı: <?php echo htmlspecialchars(number_format($mining_speed, 4)); ?> AF/sn</p>
  </div>

  <div class="section">
    <h2>API Anahtarı</h2>
    <?php if (empty($api_key)): ?>
        <button class="button" onclick="generateApiKey()">API Oluştur</button>
    <?php else: ?>
        <p id="apiKeyText">API Anahtarınız: <?php echo htmlspecialchars($api_key); ?></p>
        <button class="button" onclick="generateApiKey()">API Değiştir</button>
		
    <?php endif; ?>
	<button class="button" onclick="window.location.href='api_usage.php'">API kullanım bilgileri</button>
  </div>

  <div class="section">
    <button class="button" onclick="showCalculatorModal()">Hesap Makinesi</button>
  </div>

  <div class="section">
      <a href="logout.php" class="button">Çıkış Yap</a>
  </div>

  <div id="balanceModal" class="modal">
    <div class="modal-content">
      <span onclick="closeModal('balanceModal')">×</span>
      <h2>Balans Arttır</h2>
      <p>Kredi kartı bilgileri girin (Simüle Edilmiştir)</p>
      <input type="text" placeholder="Kart Numarası">
      <button class="button" onclick="addBalance()">Arttır</button>
    </div>
  </div>

  <div id="calculatorModal" class="modal">
    <div class="modal-content">
      <span onclick="closeModal('calculatorModal')">×</span>
      <h2>Hesap Makinesi</h2>
      <input type="number" id="afAmount" placeholder="AF Miktarı"> = <span id="usdAmount">0</span> USD<br>
      <input type="number" id="usdAmount2" placeholder="USD Miktarı"> = <span id="afAmount2">0</span> AF
      <script>
        let afAmountInput = document.getElementById("afAmount");
        let usdAmountSpan = document.getElementById("usdAmount");
        let usdAmountInput2 = document.getElementById("usdAmount2");
        let afAmountSpan2 = document.getElementById("afAmount2");

        const arafPrice = <?php echo htmlspecialchars($araf_price); ?>; // PHP'den JavaScript'e aktar

        afAmountInput.addEventListener("input", () => {
          usdAmountSpan.textContent = (afAmountInput.value * arafPrice).toFixed(5);
        });

        usdAmountInput2.addEventListener("input", () => {
          afAmountSpan2.textContent = (usdAmountInput2.value / arafPrice).toFixed(5);
        });
      </script>
    </div>
  </div>

  <div id="transactionModal" class="modal">
    <div class="modal-content">
      <span onclick="closeModal('transactionModal')">×</span>
      <h2>İşlem Geçmişi</h2>
      <iframe id="transactionIframe" srcdoc=""></iframe> </div>
  </div>

</div>

<script>
let balance = <?php echo htmlspecialchars(number_format($balance, 2)); ?>; // PHP'den gelen bakiyeyi JavaScript'e aktar
let apiKey = "<?php echo htmlspecialchars($api_key); ?>"; // API key değerini JavaScript tarafına aktar
let miningBalance = <?php echo htmlspecialchars(number_format($mining_balance, 2)); ?>;
let isMining = <?php echo htmlspecialchars($is_mining); ?>;
const miningButton = document.getElementById("miningButton");
const apiKeyText = document.getElementById("apiKeyText");
const miningSpeed = <?php echo htmlspecialchars($mining_speed); ?>; // PHP'den JavaScript'e aktar

let miningInterval;
let transactions = [];

function showBalanceModal() {
  document.getElementById("balanceModal").style.display = "block";
}
function showCalculatorModal() {
  document.getElementById("calculatorModal").style.display = "block";
}

function showTransactionModal() {
  const iframe = document.getElementById("transactionIframe");
  let html = "<table class='transaction-table'><thead><tr><th>Tarih</th><th>İşlem Tipi</th><th>Detaylar</th></tr></thead><tbody>";

  <?php
  if (mysqli_num_rows($transactions_result) > 0) {
      while ($transaction = mysqli_fetch_assoc($transactions_result)) {
          echo "html += '<tr><td>" . htmlspecialchars($transaction['date']) . "</td><td>" . htmlspecialchars($transaction['type']) . "</td><td>" . htmlspecialchars($transaction['details']) . "</td></tr>';";
      }
  } else {
      echo "html += '<tr><td colspan=\"3\">İşlem geçmişi bulunmamaktadır.</td></tr>';";
  }
  ?>

  html += "</tbody></table>";
  iframe.srcdoc = html;
  document.getElementById("transactionModal").style.display = "block";
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = "none";
}

function addBalance() {
  // PHP'ye AJAX isteği gönder
  fetch('wallet.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: 'action=addBalance'
  })
  .then(response => response.text())
  .then(data => {
    if (data === "success") {
      balance += 10; // JavaScript tarafında da bakiyeyi güncelle
      document.getElementById("balance").textContent = balance;
      closeModal('balanceModal');
      addTransaction("Balans Arttırma", "+10 AF");
    } else {
      alert("Bakiye arttırma sırasında bir hata oluştu.");
    }
  });
}

function sendCoin() {
  let address = document.getElementById("sendAddress").value;
  let amount = document.getElementById("sendAmount").value;

  // PHP'ye AJAX isteği gönder
  fetch('wallet.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `action=sendCoin&address=${address}&amount=${amount}`
  })
  .then(response => response.text())
  .then(data => {
    if (data === "success") {
      balance -= amount;
      document.getElementById("balance").textContent = balance;
      addTransaction("Koin Gönderme", `-${amount} AF, ${address}`);
    } else if (data === "insufficient") {
      alert("Yetersiz Bakiye");
    } else if (data === "invalid_address") {
      alert("Geçersiz Cüzdan Adresi");
    }  else if (data === "own_address") {
      alert("Kendi cüzdan adresinize para gönderemezsiniz.");
    } else {
      alert("Koin gönderme sırasında bir hata oluştu.");
    }
  });
}

async function generateApiKey() {
    const response = await fetch('wallet.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'action=generateApiKey'
    });

    const data = await response.json();

    if (data.status === 'success') {
        apiKey = data.api_key;
        apiKeyText.textContent = 'API Anahtarınız: ' + apiKey;
         // 0 saniye sonra sayfayı yenile
        setTimeout(function() {
            location.reload();
        }, 0);
    } else {
        alert(data.message);
    }
}

function addTransaction(type, details) {
  const date = new Date().toLocaleString();
  transactions.push({
    date: date,
    type: type,
    details: details
  });

   // PHP'ye AJAX isteği gönder
   fetch('wallet.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=addTransaction&type=${type}&details=${details}`
      })
      .then(response => response.text())
      .then(data => {
        // İşlem başarılı veya başarısız olsa da burada bir şey yapmanıza gerek yok
      });
}

function toggleMining() {
  isMining = !isMining; // Madencilik durumunu tersine çevir

  // PHP'ye AJAX isteği gönder
  fetch('wallet.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `action=toggleMining&is_mining=${isMining}`
  })
  .then(response => response.text())
  .then(data => {
    if (data === "success") {
      if (isMining) {
        miningButton.textContent = "Madenciliği Durdur";
        startMining();
      } else {
        miningButton.textContent = "Madenciliğe Başla";
        stopMining();
      }
    } else {
      alert("Madencilik durumu güncellenirken bir hata oluştu.");
      isMining = !isMining; // Hata durumunda durumu geri al
    }
  });
}

function startMining() {
  miningInterval = setInterval(() => {
    miningBalance += parseFloat(miningSpeed);

    // Veritabanına güncel madencilik bakiyesini göndermek için AJAX kullan
    fetch('wallet.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=updateMiningBalance&miningBalance=${miningBalance}`
    }).then(response => response.text())
      .then(data => {
        if(data === "success") {
            document.getElementById("miningBalance").textContent = miningBalance.toFixed(2);
        } else {
            alert("Madencilik bakiyesi güncellenirken bir hata oluştu!");
            stopMining();
        }
    });
  }, 1000);
}

function stopMining() {
  clearInterval(miningInterval);

  // Madencilik bakiyesini esas bakiyeye aktarmak için PHP'ye AJAX isteği gönder
  fetch('wallet.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: 'action=transferMiningBalance'
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === "success") {
        balance = parseFloat(data.balance);
        miningBalance = parseFloat(data.mining_balance);
        document.getElementById("balance").textContent = balance.toFixed(2);
        document.getElementById("miningBalance").textContent = miningBalance.toFixed(2);
    } else {
      alert("Madencilik bakiyesi aktarılırken bir hata oluştu.");
    }
  });
}

</script>

</body>
</html>