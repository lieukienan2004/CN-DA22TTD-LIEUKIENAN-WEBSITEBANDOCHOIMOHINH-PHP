-- Xóa sản phẩm cũ (nếu muốn reset)
-- TRUNCATE TABLE products;

-- Thêm sản phẩm mẫu
INSERT INTO products (category_id, name, description, price, discount, image, stock) VALUES
(1, 'RG 1/144 RX-93 Nu Gundam', 'Mô hình Gundam tỉ lệ 1/144 với chi tiết cao cấp, khớp linh hoạt và nhiều phụ kiện đi kèm.', 850000, 15, 'https://images.unsplash.com/photo-1606041011872-596597976b25?w=500', 25),
(1, 'MG 1/100 Strike Freedom Gundam', 'Master Grade Strike Freedom với khung xương kim loại, hiệu ứng ánh sáng LED.', 1200000, 10, 'https://images.unsplash.com/photo-1606041011872-596597976b25?w=500', 15),
(2, 'Lamborghini Aventador 1:18', 'Mô hình xe Lamborghini tỉ lệ 1:18 với chi tiết nội thất hoàn hảo, cửa mở được.', 650000, 20, 'https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=500', 30),
(2, 'Ferrari F40 1:24', 'Mô hình Ferrari F40 huyền thoại với sơn đỏ bóng, bánh xe hợp kim.', 450000, 0, 'https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=500', 40),
(3, 'F-16 Fighting Falcon 1:72', 'Mô hình máy bay chiến đấu F-16 với chi tiết vũ khí và decal đầy đủ.', 380000, 5, 'https://images.unsplash.com/photo-1540962351504-03099e0a754b?w=500', 20),
(3, 'Boeing 747 1:200', 'Mô hình máy bay dân dụng Boeing 747 với livery hãng hàng không nổi tiếng.', 550000, 0, 'https://images.unsplash.com/photo-1540962351504-03099e0a754b?w=500', 18),
(4, 'USS Missouri BB-63 1:700', 'Mô hình tàu chiến USS Missouri với chi tiết vũ khí và radar đầy đủ.', 720000, 12, 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=500', 12),
(5, 'Iron Man Mark 85 Figure', 'Mô hình nhân vật Iron Man Mark 85 cao 30cm với khớp linh hoạt và phụ kiện.', 980000, 25, 'https://images.unsplash.com/photo-1608889476561-6242cfdbf622?w=500', 22),
(6, 'LEGO Technic Bugatti Chiron', 'Bộ LEGO Technic Bugatti Chiron với 3599 chi tiết, động cơ W16 mô phỏng.', 1500000, 10, 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?w=500', 10),
(1, 'PG 1/60 Unicorn Gundam', 'Perfect Grade Unicorn Gundam với LED đầy đủ, khung xương kim loại cao cấp.', 2500000, 5, 'https://images.unsplash.com/photo-1606041011872-596597976b25?w=500', 8),
(2, 'Porsche 911 GT3 RS 1:12', 'Mô hình Porsche 911 GT3 RS tỉ lệ lớn với chi tiết động cơ boxer.', 1800000, 15, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=500', 12),
(5, 'Spider-Man Advanced Suit', 'Mô hình Spider-Man Advanced Suit cao 35cm với nhiều tư thế.', 1200000, 20, 'https://images.unsplash.com/photo-1608889825103-eb5ed706fc64?w=500', 15);
