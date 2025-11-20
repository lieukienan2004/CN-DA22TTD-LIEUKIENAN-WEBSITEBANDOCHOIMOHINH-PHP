-- Kiểm tra các sản phẩm có mô tả bị lỗi (chứa nhiều ký tự W lặp lại)
SELECT id, name, LEFT(description, 50) as desc_preview, LENGTH(description) as desc_length
FROM products 
WHERE description LIKE '%WWWWWWWWWW%';

-- Sửa mô tả cho sản phẩm FIFA 365 2025 Adrenalyn (nếu tồn tại)
UPDATE products 
SET description = 'Gói thẻ hình FIFA 365 2025 Adrenalyn từ PANINI - Thương hiệu đến từ nước Ý. 

Panini FIFA 365 Adrenalyn XL - đã trở lại. Tuyệt vời hơn bao giờ hết. Cú bạn là người mới chơi Adrenalyn XL - hay đã là một fan cuồng nhiệt, bộ sưu tập mới này sẽ không làm bạn thất vọng!

Điểm nổi bật:
- Bộ sưu tập có hơn 400 thẻ để bạn sưu tầm
- Mỗi Fans\' Favourites đều có các phiên bản:
  + Thẻ Thường
  + Thẻ Epic biết: bao gồm phiên bản ĐÁNH SỐ (được sản xuất giới hạn) và phiên bản CHỮ KÝ của cầu thủ

Bộ sản phẩm gồm:
- Mỗi gói bao gồm ngẫu nhiên 6 thẻ hình cầu thủ.
- Số lượng khi mua nguyên bộ là 24 sản phẩm'
WHERE name LIKE '%FIFA 365%' OR name LIKE '%Adrenalyn%' OR name LIKE '%PANINI%';

-- Kiểm tra lại sau khi sửa
SELECT id, name, description 
FROM products 
WHERE name LIKE '%FIFA%' OR name LIKE '%PANINI%' OR name LIKE '%Adrenalyn%';
