-- Tạo bảng thongbao để lưu thông báo cho người dùng
CREATE TABLE IF NOT EXISTS `thongbao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'order, product, promotion, system',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm một số thông báo mẫu (thay user_id = 1 bằng ID user thực tế của bạn)
INSERT INTO `thongbao` (`user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 'order', 'Đơn hàng đã được xác nhận', 'Đơn hàng #123 của bạn đã được xác nhận và đang được chuẩn bị.', 'orders.php?id=123', 0, NOW()),
(1, 'promotion', 'Khuyến mãi đặc biệt', 'Giảm giá 20% cho tất cả sản phẩm Gundam trong tuần này!', 'products.php?category=1', 0, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'product', 'Sản phẩm mới', 'Mô hình Gundam RX-78-2 Ver 2.0 đã có hàng!', 'product-detail.php?id=1', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'system', 'Chào mừng bạn đến với KIENANSHOP', 'Cảm ơn bạn đã đăng ký tài khoản. Chúc bạn có trải nghiệm mua sắm tuyệt vời!', NULL, 1, DATE_SUB(NOW(), INTERVAL 3 DAY));
