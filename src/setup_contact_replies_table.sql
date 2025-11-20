-- Tạo bảng lưu trữ các câu trả lời cho tin nhắn liên hệ
CREATE TABLE IF NOT EXISTS traloithongbao (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contact_id INT NOT NULL,
    admin_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contact_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_contact_id (contact_id),
    INDEX idx_admin_id (admin_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm cột replied_at vào bảng contact_messages nếu chưa có
ALTER TABLE contact_messages 
ADD COLUMN IF NOT EXISTS replied_at TIMESTAMP NULL DEFAULT NULL;

-- Cập nhật replied_at cho các tin nhắn đã trả lời
UPDATE contact_messages 
SET replied_at = (
    SELECT MIN(created_at) 
    FROM traloithongbao 
    WHERE traloithongbao.contact_id = contact_messages.id
)
WHERE status = 'replied';
