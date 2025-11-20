# Hướng Dẫn Sửa Thông Báo Liên Hệ

## Vấn đề đã sửa
Khi bấm "Đi tới trang" từ thông báo liên hệ, giờ đây sẽ:
- Chuyển đến trang `view_my_contact.php`
- Tự động highlight (làm nổi bật) tin nhắn liên hệ đó
- Scroll đến đúng vị trí tin nhắn

## Các file đã sửa

### 1. admin/contact_reply.php
- Sửa link thông báo từ `contact.php?view=` thành `view_my_contact.php?highlight=`
- Thay đổi type từ `system` thành `contact`
- Thêm hỗ trợ cột `contact_id` (nếu có)

### 2. notifications.php
- Đơn giản hóa logic xử lý link
- Sử dụng link trực tiếp từ database
- Loại bỏ các debug log không cần thiết

### 3. create_test_contact.php
- Cập nhật link test để có parameter `highlight`

### 4. view_my_contact.php
- Đã có sẵn chức năng highlight và scroll
- Không cần sửa gì thêm

## Cách test

### Bước 1: Tạo dữ liệu test
```
http://localhost/create_test_contact.php
```

### Bước 2: Xem thông báo
```
http://localhost/notifications.php
```

### Bước 3: Bấm "Xem" và "Đi tới trang"
- Sẽ chuyển đến `view_my_contact.php`
- Tin nhắn sẽ được highlight với viền màu tím
- Trang sẽ tự động scroll đến tin nhắn đó

## Tùy chọn: Thêm cột contact_id

Để quản lý tốt hơn, bạn có thể chạy SQL:
```sql
-- File: add_contact_id_to_thongbao.sql
ALTER TABLE `thongbao` 
ADD COLUMN `contact_id` INT NULL DEFAULT NULL AFTER `link`,
ADD KEY `contact_id` (`contact_id`);

UPDATE `thongbao` 
SET `contact_id` = CAST(SUBSTRING_INDEX(link, '=', -1) AS UNSIGNED)
WHERE `type` = 'contact' 
AND `link` LIKE 'view_my_contact.php?highlight=%';
```

**Lưu ý:** Cột này là tùy chọn. Hệ thống vẫn hoạt động tốt mà không cần cột này.

## Kết quả

✅ Thông báo liên hệ giờ đây hoạt động đúng
✅ Highlight tin nhắn khi click từ thông báo
✅ Scroll tự động đến tin nhắn
✅ Trải nghiệm người dùng tốt hơn
