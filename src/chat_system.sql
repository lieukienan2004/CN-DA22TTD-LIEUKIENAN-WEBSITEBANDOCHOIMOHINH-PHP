-- Bảng phiên chat
CREATE TABLE IF NOT EXISTS chat_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(100) UNIQUE NOT NULL,
    user_name VARCHAR(100),
    user_email VARCHAR(100),
    status ENUM('active', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX(session_id),
    INDEX(status)
);

-- Bảng tin nhắn
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(100) NOT NULL,
    sender_type ENUM('user', 'admin') NOT NULL,
    sender_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    message_type ENUM('text', 'product_link', 'image') DEFAULT 'text',
    product_id INT DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(session_id),
    INDEX(created_at)
);

-- Bảng admin đang online
CREATE TABLE IF NOT EXISTS admin_online (
    admin_id INT PRIMARY KEY,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);
