<?php
include 'config.php';

// Aktif madencilik yapan kullanıcıları bul
$mining_users_query = "SELECT id, balance, last_mining_time FROM users WHERE is_mining=1";
$mining_users_result = mysqli_query($conn, $mining_users_query);

if (mysqli_num_rows($mining_users_result) > 0) {
    while ($user = mysqli_fetch_assoc($mining_users_result)) {
        $user_id = $user['id'];
        $balance = $user['balance'];
        $last_mining_time = strtotime($user['last_mining_time']);
        $now = time();
        $time_diff = $now - $last_mining_time; // Son madencilikten bu yana geçen süre (saniye)

        // 2 saniyede bir 0.0001 AF kazan
        $mining_rate = 0.0001;
        $earned_amount = ($time_diff / 2) * $mining_rate; // Geçen süreye göre kazanılan miktar

        // Bakiyeyi güncelle
        $new_balance = $balance + $earned_amount;
        $update_balance_query = "UPDATE users SET balance='$new_balance', last_mining_time=NOW() WHERE id='$user_id'";
        mysqli_query($conn, $update_balance_query);
    }
}

mysqli_close($conn);
?>