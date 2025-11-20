-- Script để sửa mô tả sản phẩm
-- Chuyển đổi \\r\\n thành xuống dòng thực

UPDATE products 
SET description = REPLACE(REPLACE(REPLACE(description, '\\r\\n', CHAR(10)), '\\r', CHAR(10)), '\\n', CHAR(10))
WHERE description LIKE '%\\r\\n%' OR description LIKE '%\\n%' OR description LIKE '%\\r%';

-- Kiểm tra kết quả
SELECT id, name, description FROM products LIMIT 5;
