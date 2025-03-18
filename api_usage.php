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

// Kullanıcının bilgilerini veritabanından al
$sql = "SELECT api_key FROM users WHERE id='$user_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $api_key = $row['api_key'];
} else {
    $api_key = ""; // Varsayılan API anahtarı
}

mysqli_close($conn); // Veritabanı bağlantısını kapat
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Kullanım Kılavuzu</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #1e212d;
            color: #fff;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #282c34;
            padding: 20px;
            border-radius: 5px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            margin-top: 20px;
        }
        p {
            line-height: 1.6;
        }
        .code {
            background-color: #333;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .api-key {
            font-weight: bold;
            color: #ffa500;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>API Kullanım Kılavuzu</h1>

        <h2>Giriş</h2>
        <p>
            Bu kılavuz, ARAF API'sini nasıl kullanacağınızı anlamanıza yardımcı olacaktır. API'yi kullanarak bakiye sorgulayabilir ve coin gönderebilirsiniz.
        </p>

        <h2>Kimlik Doğrulama</h2>
        <p>
            API'ye erişmek için bir API anahtarı kullanmanız gerekmektedir. API anahtarınız aşağıda verilmiştir:
        </p>
        <div class="code">
            <span class="api-key"><?php echo htmlspecialchars($api_key); ?></span>
        </div>

        <h2>Bakiye Sorgulama</h2>
        <p>
            Bakiye sorgulamak için aşağıdaki örneği kullanabilirsiniz:
        </p>
        <h3>İstek</h3>
        <div class="code">
            POST /api.php<br>
            Content-Type: application/x-www-form-urlencoded<br><br>
            action=get_balance&api_key=<span class="api-key"><?php echo htmlspecialchars($api_key); ?></span>
        </div>
        <h3>Yanıt</h3>
        <div class="code">
        {<br>
        "status": "success",<br>
        "data": {<br>
        "balance": "100.00"<br>
        }<br>
        }
        </div>

        <h2>Coin Gönderme</h2>
        <p>
            Coin göndermek için aşağıdaki örneği kullanabilirsiniz:
        </p>
        <h3>İstek</h3>
        <div class="code">
            POST /api.php<br>
            Content-Type: application/x-www-form-urlencoded<br><br>
            action=send_coin&api_key=<span class="api-key"><?php echo htmlspecialchars($api_key); ?></span>&address=0x1234567890123456789012345678901234567890&amount=10
        </div>
        <h3>Yanıt</h3>
        <div class="code">
            {<br>
            "status": "success",<br>
            "data": {<br>
            "message": "Coin başarıyla gönderildi."<br>
            }<br>
            }
        </div>

        <h2>Hata Kodları</h2>
        <p>
            API isteklerinizde hatalar alırsanız aşağıdaki hata kodlarını kontrol edebilirsiniz:
        </p>
        <ul>
            <li>Geçersiz API anahtarı: "Geçersiz API anahtarı."</li>
            <li>Yetersiz bakiye: "Yetersiz bakiye."</li>
            <li>Geçersiz cüzdan adresi: "Geçersiz cüzdan adresi."</li>
            <li>Geçersiz işlem: "Geçersiz işlem."</li>
        </ul>
    </div>
</body>
</html>