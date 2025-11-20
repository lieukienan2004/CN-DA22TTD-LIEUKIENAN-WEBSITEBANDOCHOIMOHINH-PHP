-- Xóa foreign key constraint để có thể lưu thông báo cho cả users và admins
ALTER TABLE thongbao DROP FOREIGN KEY thongbao_ibfk_1;

-- Thêm index cho user_id (không có constraint)
ALTER TABLE thongbao ADD INDEX idx_user_id (user_id);

-- Thêm cột user_type để phân biệt user và admin
ALTER TABLE thongbao ADD COLUMN user_type ENUM('user', 'admin') DEFAULT 'user' AFTER user_id;
