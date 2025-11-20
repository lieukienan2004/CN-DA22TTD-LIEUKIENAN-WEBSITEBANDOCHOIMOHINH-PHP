-- Thêm cột is_new vào bảng products
ALTER TABLE products ADD COLUMN is_new TINYINT DEFAULT 0 AFTER status;

-- Kiểm tra cấu trúc bảng
DESCRIBE products;

-- Cập nhật một số sản phẩm mẫu làm "NEW" (tùy chọn)
-- UPDATE products SET is_new = 1 WHERE id IN (1, 2, 3);
