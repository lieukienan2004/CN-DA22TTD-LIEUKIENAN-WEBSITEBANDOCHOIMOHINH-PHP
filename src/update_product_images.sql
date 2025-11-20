-- Script cập nhật ảnh sản phẩm sang thư mục assets/images/
-- Chạy script này sau khi đã upload ảnh vào thư mục assets/images/

-- Cập nhật ảnh cho các sản phẩm Gundam (category_id = 1)
UPDATE products SET image = 'assets/images/gundam-1.jpg' WHERE id = 1;
UPDATE products SET image = 'assets/images/gundam-2.jpg' WHERE id = 2;
UPDATE products SET image = 'assets/images/gundam-3.jpg' WHERE id = 10;

-- Cập nhật ảnh cho các sản phẩm Xe mô hình (category_id = 2)
UPDATE products SET image = 'assets/images/car-lamborghini.jpg' WHERE id = 3;
UPDATE products SET image = 'assets/images/car-ferrari.jpg' WHERE id = 4;
UPDATE products SET image = 'assets/images/car-porsche.jpg' WHERE id = 11;

-- Cập nhật ảnh cho các sản phẩm Máy bay (category_id = 3)
UPDATE products SET image = 'assets/images/plane-f16.jpg' WHERE id = 5;
UPDATE products SET image = 'assets/images/plane-boeing.jpg' WHERE id = 6;

-- Cập nhật ảnh cho các sản phẩm Tàu chiến (category_id = 4)
UPDATE products SET image = 'assets/images/ship-missouri.jpg' WHERE id = 7;

-- Cập nhật ảnh cho các sản phẩm Nhân vật (category_id = 5)
UPDATE products SET image = 'assets/images/figure-ironman.jpg' WHERE id = 8;
UPDATE products SET image = 'assets/images/figure-spiderman.jpg' WHERE id = 12;

-- Cập nhật ảnh cho các sản phẩm LEGO (category_id = 6)
UPDATE products SET image = 'assets/images/lego-bugatti.jpg' WHERE id = 9;

-- Kiểm tra kết quả
SELECT id, name, image FROM products ORDER BY id;
