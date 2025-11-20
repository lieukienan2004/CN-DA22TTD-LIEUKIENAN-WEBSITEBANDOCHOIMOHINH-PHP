-- Thêm cột contact_id vào bảng thongbao để dễ quản lý thông báo liên hệ
ALTER TABLE `thongbao` 
ADD COLUMN `contact_id` INT NULL DEFAULT NULL AFTER `link`,
ADD KEY `contact_id` (`contact_id`);

-- Cập nhật các thông báo hiện có từ link
UPDATE `thongbao` 
SET `contact_id` = CAST(SUBSTRING_INDEX(link, '=', -1) AS UNSIGNED)
WHERE `type` = 'contact' 
AND `link` LIKE 'view_my_contact.php?highlight=%';
