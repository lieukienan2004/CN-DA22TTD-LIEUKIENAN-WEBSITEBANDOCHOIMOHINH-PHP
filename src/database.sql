-- Tạo database
CREATE DATABASE IF NOT EXISTS toy_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE toy_store;

-- Bảng danh mục
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fas fa-cube',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng sản phẩm
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    discount INT DEFAULT 0,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Bảng người dùng
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng đơn hàng
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    fullname VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Bảng chi tiết đơn hàng
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Bảng tin nhắn liên hệ
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(50) DEFAULT 'general',
    message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng danh sách yêu thích
CREATE TABLE wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
);

-- Bảng reset mật khẩu
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    used TINYINT DEFAULT 0,
    INDEX idx_token (token),
    INDEX idx_email (email)
);

-- Thêm dữ liệu mẫu cho danh mục
INSERT INTO categories (name, icon) VALUES
('Gundam', 'fas fa-robot'),
('Xe Mô Hình', 'fas fa-car'),
('Máy Bay', 'fas fa-plane'),
('Tàu Chiến', 'fas fa-ship'),
('Panini', 'fas fa-user-astronaut'),
('Lego', 'fas fa-cubes');

-- Thêm dữ liệu mẫu cho sản phẩm
INSERT INTO products (category_id, name, description, price, discount, image, stock) VALUES
(1, 'RG 1/144 RX-93 Nu Gundam', 'Mô hình Gundam tỉ lệ 1/144 với chi tiết cao cấp, khớp linh hoạt và nhiều phụ kiện đi kèm.', 850000, 15, 'https://images.unsplash.com/photo-1606041011872-596597976b25?w=500', 25),
(1, 'MG 1/100 Strike Freedom Gundam', 'Master Grade Strike Freedom với khung xương kim loại, hiệu ứng ánh sáng LED.', 1200000, 10, 'https://images.unsplash.com/photo-1606041011872-596597976b25?w=500', 15),
(2, 'Lamborghini Aventador 1:18', 'Mô hình xe Lamborghini tỉ lệ 1:18 với chi tiết nội thất hoàn hảo, cửa mở được.', 650000, 20, 'https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=500', 30),
(2, 'Ferrari F40 1:24', 'Mô hình Ferrari F40 huyền thoại với sơn đỏ bóng, bánh xe hợp kim.', 450000, 0, 'https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=500', 40),
(3, 'F-16 Fighting Falcon 1:72', 'Mô hình máy bay chiến đấu F-16 với chi tiết vũ khí và decal đầy đủ.', 380000, 5, 'https://images.unsplash.com/photo-1540962351504-03099e0a754b?w=500', 20),
(3, 'Boeing 747 1:200', 'Mô hình máy bay dân dụng Boeing 747 với livery hãng hàng không nổi tiếng.', 550000, 0, 'https://images.unsplash.com/photo-1540962351504-03099e0a754b?w=500', 18),
(4, 'USS Missouri BB-63 1:700', 'Mô hình tàu chiến USS Missouri với chi tiết vũ khí và radar đầy đủ.', 720000, 12, 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=500', 12),
(5, 'Iron Man Mark 85 Figure', 'Mô hình nhân vật Iron Man Mark 85 cao 30cm với khớp linh hoạt và phụ kiện.', 980000, 25, 'https://images.unsplash.com/photo-1608889476561-6242cfdbf622?w=500', 22),
(6, '123', 'Mô hình nhân vật Iron Man Mark 85 cao 30cm với khớp linh hoạt và phụ kiện.', 980000, 25, 'https://images.unsplash.com/photo-1608889476561-6242cfdbf622?w=500', 22);
