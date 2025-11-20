-- Thêm cột stock_status vào bảng products
ALTER TABLE products 
ADD COLUMN stock_status ENUM('in_stock', 'out_of_stock') DEFAULT 'in_stock' AFTER stock;

-- Cập nhật stock_status dựa trên số lượng tồn kho hiện tại
UPDATE products 
SET stock_status = CASE 
    WHEN stock > 0 THEN 'in_stock'
    ELSE 'out_of_stock'
END;
