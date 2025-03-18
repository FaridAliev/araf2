<?php
session_start();

// Admin oturumu yoksa adlogin.php'ye yönlendir
if (!isset($_SESSION['admin_username'])) {
    header("Location: adlogin.php");
    exit();
}

include 'config.php'; // Veritabanı bağlantısı

$message = ""; // İşlem mesajları için değişken
$transactions = []; // İşlem geçmişi verilerini saklamak için dizi
$show_all_transactions = false; // Tüm işlemleri gösterme durumu

// AF Fiyatını Güncelleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_price'])) {
    $new_price = $_POST['af_price'];

    // Veritabanında AF fiyatını güncelle
    $update_query = "UPDATE af_price SET price='$new_price' WHERE id=1";
    if (mysqli_query($conn, $update_query)) {
        $message = "AF fiyatı başarıyla güncellendi.";
    } else {
        $message = "AF fiyatı güncellenirken bir hata oluştu: " . mysqli_error($conn);
    }
}

// Madencilik Hızını Güncelleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_mining_speed'])) {
    $new_mining_speed = $_POST['mining_speed'];

    // Veritabanında madencilik hızını güncelle
    $update_query = "UPDATE admin_settings SET mining_speed='$new_mining_speed' WHERE id=1";
    if (mysqli_query($conn, $update_query)) {
        $message = "Madencilik hızı başarıyla güncellendi.";
    } else {
        $message = "Madencilik hızı güncellenirken bir hata oluştu: " . mysqli_error($conn);
    }
}

// Kullanıcı Bakiyesini Düzenleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_balance'])) {
    $user_id = $_POST['user_id'];
    $balance_change = $_POST['balance_change'];
    $change_type = $_POST['change_type'];

    // Kullanıcının mevcut bakiyesini al
    $get_balance_query = "SELECT balance FROM users WHERE id='$user_id'";
    $get_balance_result = mysqli_query($conn, $get_balance_query);

    if ($get_balance_result && mysqli_num_rows($get_balance_result) == 1) {
        $user_data = mysqli_fetch_assoc($get_balance_result);
        $current_balance = $user_data['balance'];

        // İşlem tipine göre bakiyeyi güncelle
        if ($change_type == 'add') {
            $new_balance = $current_balance + $balance_change;
        } elseif ($change_type == 'subtract') {
            $new_balance = $current_balance - $balance_change;
        } elseif ($change_type == 'set') {
            $new_balance = $balance_change;
        } else {
            $message = "Geçersiz işlem tipi.";
        }

        // Bakiyeyi veritabanında güncelle
        if (isset($new_balance)) {
            $update_balance_query = "UPDATE users SET balance='$new_balance' WHERE id='$user_id'";
            if (mysqli_query($conn, $update_balance_query)) {
                $message = "Kullanıcı bakiyesi başarıyla güncellendi.";
            } else {
                $message = "Kullanıcı bakiyesi güncellenirken bir hata oluştu: " . mysqli_error($conn);
            }
        }
    } else {
        $message = "Kullanıcı bulunamadı.";
    }
}

// Kullanıcı Silme
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];

    // Kullanıcıyı veritabanından sil
    $delete_query = "DELETE FROM users WHERE id='$user_id'";
    if (mysqli_query($conn, $delete_query)) {
        $message = "Kullanıcı başarıyla silindi.";
    } else {
        $message = "Kullanıcı silinirken bir hata oluştu: " . mysqli_error($conn);
    }
}

// İşlem Geçmişini Arama
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_transactions'])) {
    $search_term = mysqli_real_escape_string($conn, $_POST['search_term']);

    // Kullanıcıyı bul
    $user_query = "SELECT id FROM users WHERE username LIKE '%$search_term%' OR email LIKE '%$search_term%' OR wallet_address LIKE '%$search_term%'";
    $user_result = mysqli_query($conn, $user_query);

    if (mysqli_num_rows($user_result) > 0) {
        $user = mysqli_fetch_assoc($user_result);
        $user_id = $user['id'];

        // İşlem geçmişini al
        $transactions_query = "SELECT date, type, details FROM transactions WHERE user_id='$user_id' ORDER BY date DESC";
        $transactions_result = mysqli_query($conn, $transactions_query);

        if (mysqli_num_rows($transactions_result) > 0) {
            while ($transaction = mysqli_fetch_assoc($transactions_result)) {
                $transactions[] = $transaction;
            }
        } else {
            $message = "Bu kullanıcı için işlem geçmişi bulunamadı.";
        }
    } else {
        $message = "Kullanıcı bulunamadı.";
    }
}

// Tüm İşlemleri Gösterme
if (isset($_POST['show_all'])) {
    $show_all_transactions = true;
    $all_transactions_query = "SELECT users.username, transactions.date, transactions.type, transactions.details FROM transactions INNER JOIN users ON transactions.user_id = users.id ORDER BY transactions.date DESC LIMIT 50";
    $all_transactions_result = mysqli_query($conn, $all_transactions_query);

    if (mysqli_num_rows($all_transactions_result) > 0) {
        while ($transaction = mysqli_fetch_assoc($all_transactions_result)) {
            $transactions[] = $transaction;
        }
    } else {
        $message = "İşlem geçmişi bulunamadı.";
    }
}

// Kullanıcıları ve AF Fiyatını Veritabanından Çekme
$users_query = "SELECT * FROM users";
$users_result = mysqli_query($conn, $users_query);

$price_query = "SELECT price FROM af_price WHERE id=1";
$price_result = mysqli_query($conn, $price_query);
$af_price = mysqli_fetch_assoc($price_result)['price'];

// Madencilik Hızını Çekme
$admin_settings_query = "SELECT mining_speed FROM admin_settings WHERE id=1";
$admin_settings_result = mysqli_query($conn, $admin_settings_query);
$mining_speed = mysqli_fetch_assoc($admin_settings_result)['mining_speed'];

mysqli_close($conn); // Veritabanı bağlantısını kapat
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ARAF Admin Paneli</title>
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
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
.error, .success {
    color: white;
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 5px;
}
.error {
    background-color: #ff4d4d;
}
.success {
    background-color: #4caf50;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #555;
}
th {
    background-color: #444;
}
.balance-form {
    display: flex;
    align-items: center;
}
.balance-form input[type="number"] {
    width: 100px;
    margin-right: 10px;
}
/* Modal stilleri (cüzdan.php'deki gibi) */
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
  width: 80%; /* Genişliği arttırdık */
  max-width: 800px; /* Maksimum genişlik */
}
.modal-content iframe {
  width: 100%;
  height: 400px; /* iframe yüksekliğini belirledik */
  border: 1px solid #888;
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
    <h1>ARAF Admin Paneli</h1>
    <p>Hoş Geldin, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
  </div>

  <?php if ($message): ?>
      <div class="<?php echo strpos($message, 'başarıyla') !== false ? 'success' : 'error'; ?>"><?php echo $message; ?></div>
  <?php endif; ?>

  <div class="section">
    <h2>AF Fiyatını Güncelle</h2>
    <form method="post">
      <label for="af_price">Yeni AF Fiyatı (USD):</label>
      <input type="number" name="af_price" id="af_price" step="0.00001" value="<?php echo htmlspecialchars($af_price); ?>">
      <button class="button" type="submit" name="update_price">Güncelle</button>
    </form>
  </div>

  <div class="section">
    <h2>Madencilik Hızını Güncelle</h2>
    <form method="post">
      <label for="mining_speed">Yeni Madencilik Hızı (AF/sn):</label>
      <input type="number" name="mining_speed" id="mining_speed" step="0.0001" value="<?php echo htmlspecialchars($mining_speed); ?>">
      <button class="button" type="submit" name="update_mining_speed">Güncelle</button>
    </form>
  </div>

  <div class="section">
    <h2>Kullanıcı Hesapları</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Kullanıcı Adı</th>
          <th>E-posta</th>
          <th>Bakiye</th>
          <th>Madencilik Bakiyesi</th>
          <th>Madencilik Yapıyor mu?</th>
          <th>İşlemler</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if (mysqli_num_rows($users_result) > 0) {
            while ($row = mysqli_fetch_assoc($users_result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['balance']) . " AF</td>";
                echo "<td>" . htmlspecialchars($row['mining_balance']) . " AF</td>";
                echo "<td>" . ($row['is_mining'] ? "Evet" : "Hayır") . "</td>";
                echo "<td>";
                echo "<form method='post' class='balance-form'>";
                echo "<input type='hidden' name='user_id' value='" . htmlspecialchars($row['id']) . "'>";
                echo "<select name='change_type'>";
                echo "<option value='add'>Ekle</option>";
                echo "<option value='subtract'>Çıkar</option>";
                echo "<option value='set'>Ayarla</option>";
                echo "</select>";
                echo "<input type='number' name='balance_change' step='0.01' placeholder='Miktar'>";
                echo "<button class='button' type='submit' name='update_balance'>Yap</button>    ";
				echo "<a href='admin.php?delete_user=" . htmlspecialchars($row['id']) . "' class='button' onclick='return confirm(\"Bu kullanıcıyı silmek istediğinizden emin misiniz?\")'>Sil</a>";
                echo "</form>";

                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>Kullanıcı bulunamadı.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <div class="section">
    <h2>İşlem Geçmişi</h2>
    <form method="post">
      <input type="text" name="search_term" placeholder="Kullanıcı adı, e-posta veya cüzdan adresi">
      <button class="button" type="submit" name="search_transactions">Ara</button>
      <button class="button" type="submit" name="show_all">TAMAMI</button>
    </form>

    <?php if (!empty($transactions)): ?>
      <button class="button" onclick="showTransactionModal()">İşlemleri Göster</button>
    <?php endif; ?>
  </div>

  <div class="section">
    <a href="adlogout.php" class="button">Çıkış Yap</a>
  </div>

  <div id="transactionModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('transactionModal')">×</span>
      <h2>İşlem Geçmişi</h2>
      <iframe id="transactionIframe" srcdoc=""></iframe>
    </div>
  </div>

</div>

<script>
// Modal açma/kapama fonksiyonları (cüzdan.php'deki gibi)
function showTransactionModal() {
    const iframe = document.getElementById("transactionIframe");
    let html = "<table class='transaction-table'><thead><tr><th>Kullanıcı Adı</th><th>Tarih</th><th>İşlem Tipi</th><th>Detaylar</th></tr></thead><tbody>";

    <?php
    if (!empty($transactions)) {
        foreach ($transactions as $transaction) {
            echo "html += '<tr>";
            if ($show_all_transactions) {
                echo "<td>" . htmlspecialchars($transaction['username']) . "</td>";
            }
            echo "<td>" . htmlspecialchars($transaction['date']) . "</td>";
            echo "<td>" . htmlspecialchars($transaction['type']) . "</td>";
            echo "<td>" . htmlspecialchars($transaction['details']) . "</td>";
            echo "</tr>';";
        }
    } else {
        echo "html += '<tr><td colspan=\"4\">İşlem geçmişi bulunmamaktadır.</td></tr>';";
    }
    ?>

    html += "</tbody></table>";
    iframe.srcdoc = html;
    document.getElementById("transactionModal").style.display = "block";
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = "none";
}
</script>

</body>
</html>