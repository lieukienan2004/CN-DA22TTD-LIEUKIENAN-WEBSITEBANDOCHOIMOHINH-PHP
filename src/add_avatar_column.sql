-- Thêm cột avatar vào bảng users nếu chưa có
-- Chạy file SQL này nếu bạn đã có database từ trước

ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL;
