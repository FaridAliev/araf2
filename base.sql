-- Kullanıcılar tablosu
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0,
    wallet_address VARCHAR(255) UNIQUE
);

-- İşlemler tablosu
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATETIME NOT NULL,
    type VARCHAR(50) NOT NULL,
    details VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Admin tablosu (admin.php için)
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- İlk admin kullanıcısını ekle (güçlü bir şifre kullanın!)
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$YOUR_STRONG_PASSWORD_HASH');

-- AF Fiyatı tablosu (admin.php için)
CREATE TABLE af_price (
    id INT AUTO_INCREMENT PRIMARY KEY,
    price DECIMAL(10, 5) NOT NULL
);

-- İlk AF fiyatını ekle
INSERT INTO af_price (price) VALUES (0.026);