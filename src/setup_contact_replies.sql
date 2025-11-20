-- Tạo bảng lưu câu trả lời của admin cho tin nhắn liên hệ
CREATE TABLE IF NOT EXISTS `contact_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_message_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `reply_message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `contact_message_id` (`contact_message_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm cột để lưu email đã gửi
ALTER TABLE `contact_messages` 
ADD COLUMN `replied_at` timestamp NULL DEFAULT NULL AFTER `status`,
ADD COLUMN `replied_by` int(11) NULL DEFAULT NULL AFTER `replied_at`;
