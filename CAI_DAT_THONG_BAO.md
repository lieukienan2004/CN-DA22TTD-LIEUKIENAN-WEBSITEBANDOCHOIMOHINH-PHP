# Hướng Dẫn Cài Đặt Hệ Thống Thông Báo

## Bước 1: Chạy File SQL

### 1.1. Tạo bảng thongbao (nếu chưa có)
```bash
mysql -u root -p kienan123 < setup_user_notifications.sql
```

### 1.2. Sửa lỗi Foreign Key Constraint
```bash
mysql -u root -p kienan123 < fix_thongbao_constraint.sql
```

### 1.3. Tạo bảng traloithongbao
```bash
mysql -u root -p kienan123 < setup_contact_replies_table.sql
```

## Bước 2: Kiểm Tra Cấu Trúc Bảng

### Bảng `thongbao`
```sql
CREATE TABLE IF NOT EXISTS `thongbao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_type` ENUM('user', 'admin') DEFAULT 'user',
  `type` varchar(50) NOT NULL COMMENT 'order, product, promotion, system, contact',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `user_type` (`user_type`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Giải thích:**
- `user_id`: ID của user hoặc admin
- `user_type`: Phân biệt 'user' (khách hàng) hoặc 'admin' (quản trị viên)
- `type`: Loại thông báo (order, product, promotion, system, contact)
- `is_read`: Đã đọc (1) hay chưa đọc (0)

### Bảng `traloithongbao`
```sql
CREATE TABLE IF NOT EXISTS traloithongbao (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contact_id INT NOT NULL,
    admin_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contact_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Bước 3: Test Hệ Thống

### Test 1: Gửi Tin Nhắn Liên Hệ
1. Vào trang liên hệ trên website
2. Điền form và gửi tin nhắn
3. Kiểm tra database:
```sql
SELECT * FROM contact_messages ORDER BY id DESC LIMIT 1;
SELECT * FROM thongbao WHERE type = 'contact' ORDER BY id DESC LIMIT 1;
```

### Test 2: Xem Thông Báo Admin
1. Đăng nhập admin
2. Click vào icon chuông trên header
3. Kiểm tra có thông báo tin nhắn mới không

### Test 3: Trả Lời Tin Nhắn
1. Vào **Quản lý Tin nhắn** (admin/contacts.php)
2. Click nút **"Trả lời"** trên tin nhắn
3. Nhập nội dung và gửi
4. Kiểm tra database:
```sql
SELECT * FROM traloithongbao ORDER BY id DESC LIMIT 1;
```

## Bước 4: Xử Lý Lỗi Thường Gặp

### Lỗi: Table 'thongbao' doesn't exist
**Giải pháp:**
```bash
mysql -u root -p kienan123 < setup_user_notifications.sql
```

### Lỗi: Foreign key constraint fails
**Giải pháp:**
```bash
mysql -u root -p kienan123 < fix_thongbao_constraint.sql
```

### Lỗi: Unknown column 'user_type'
**Giải pháp:**
```sql
ALTER TABLE thongbao ADD COLUMN user_type ENUM('user', 'admin') DEFAULT 'user' AFTER user_id;
```

### Lỗi: Cannot add foreign key constraint (traloithongbao)
**Giải pháp:** Kiểm tra bảng `contact_messages` đã tồn tại chưa
```sql
SHOW TABLES LIKE 'contact_messages';
```

## Bước 5: Kiểm Tra Hoạt Động

### Query Kiểm Tra Thông Báo Admin
```sql
-- Xem tất cả thông báo của admin
SELECT * FROM thongbao WHERE user_type = 'admin' ORDER BY created_at DESC;

-- Đếm thông báo chưa đọc của admin
SELECT COUNT(*) as unread FROM thongbao WHERE user_type = 'admin' AND is_read = 0;

-- Xem tin nhắn liên hệ mới
SELECT * FROM contact_messages WHERE status = 'new' ORDER BY created_at DESC;
```

### Query Kiểm Tra Lịch Sử Trả Lời
```sql
-- Xem tất cả trả lời
SELECT 
    tr.*,
    cm.name as customer_name,
    cm.subject,
    a.fullname as admin_name
FROM traloithongbao tr
LEFT JOIN contact_messages cm ON tr.contact_id = cm.id
LEFT JOIN admins a ON tr.admin_id = a.id
ORDER BY tr.created_at DESC;
```

## Luồng Hoạt Động Hoàn Chỉnh

### 1. Khách Hàng Gửi Tin Nhắn
```
Khách hàng điền form → submit_contact.php
↓
Lưu vào contact_messages
↓
Tạo thông báo cho TẤT CẢ admin trong bảng thongbao
(user_id = admin_id, user_type = 'admin', type = 'contact')
```

### 2. Admin Nhận Thông Báo
```
Admin đăng nhập → Xem chuông thông báo
↓
admin/get_notifications.php lấy tin nhắn mới từ contact_messages
↓
Hiển thị badge số lượng tin nhắn chưa đọc
```

### 3. Admin Trả Lời
```
Admin click "Trả lời" → admin/contact_reply.php
↓
Admin nhập nội dung → Submit form
↓
Lưu vào traloithongbao
↓
Cập nhật status = 'replied' trong contact_messages
↓
Nếu khách hàng có tài khoản → Tạo thông báo trong bảng thongbao
(user_id = customer_id, user_type = 'user', type = 'system')
```

## Tính Năng Bổ Sung

### Gửi Email Tự Động (Tùy chọn)
Có thể thêm code gửi email trong `admin/contact_reply.php`:
```php
// Sau khi lưu trả lời vào database
$to = $contact['email'];
$subject = "Phản hồi từ KIENANSHOP - " . $contact['subject'];
$message = $reply_message;
$headers = "From: noreply@kienanshop.vn\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

mail($to, $subject, $message, $headers);
```

### Thông Báo Realtime (Tùy chọn)
Có thể tích hợp WebSocket hoặc polling để cập nhật thông báo realtime:
```javascript
// Polling mỗi 30 giây
setInterval(function() {
    fetch('admin/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            updateNotificationBadge(data.count);
        });
}, 30000);
```

## Bảo Trì

### Xóa Thông Báo Cũ (Chạy định kỳ)
```sql
-- Xóa thông báo đã đọc cũ hơn 30 ngày
DELETE FROM thongbao 
WHERE is_read = 1 
AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### Backup Dữ Liệu
```bash
# Backup bảng thông báo
mysqldump -u root -p kienan123 thongbao > backup_thongbao.sql

# Backup bảng tin nhắn
mysqldump -u root -p kienan123 contact_messages traloithongbao > backup_contacts.sql
```

## Liên Hệ Hỗ Trợ
Nếu gặp vấn đề, kiểm tra:
1. Log lỗi PHP: `error_log` hoặc console browser
2. Log MySQL: `/var/log/mysql/error.log`
3. Cấu trúc bảng: `DESCRIBE thongbao;`
