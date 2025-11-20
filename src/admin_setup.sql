-- =============================================
-- KIENANSHOP - Admin Setup SQL
-- Chạy file này để tạo bảng admin và cấu hình
-- =============================================

-- 1. Tạo bảng contacts nếu chưa có
CREATE TABLE IF NOT EXISTS contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('pending', 'replied', 'closed') DEFAULT 'pending',
    admin_reply TEXT NULL,
    replied_at TIMESTAMP NULL,
    replied_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tạo bảng admin
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    fullname VARCHAR(100),
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status TINYINT DEFAULT 1
);

-- 3. Thêm admin mặc định
-- Username: admin
-- Password: admin123
INSERT INTO admins (username, password, email, fullname, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@kienanshop.vn', 'Administrator', 'super_admin')
ON DUPLICATE KEY UPDATE username=username;

-- 4. Tạo bảng activity logs
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- 5. Tạo bảng settings
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 6. Thêm settings mặc định
INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES
('site_name', 'KIENANSHOP', 'text'),
('site_email', 'kienanshop@gmail.com', 'email'),
('site_phone', '0912431719', 'text'),
('site_address', 'TP. Hồ Chí Minh, Việt Nam', 'text'),
('free_shipping_threshold', '500000', 'number'),
('maintenance_mode', '0', 'boolean')
ON DUPLICATE KEY UPDATE setting_key=setting_key;

-- Hoàn tất!
-- Bây giờ bạn có thể đăng nhập admin tại: /admin/login.php
-- Username: admin
-- Password: admin123
