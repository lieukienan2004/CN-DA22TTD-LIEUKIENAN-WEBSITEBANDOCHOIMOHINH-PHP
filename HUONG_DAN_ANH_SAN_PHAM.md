# Hướng Dẫn Quản Lý Ảnh Sản Phẩm

## Cấu trúc thư mục
```
assets/
  └── images/
      ├── logo.png (đã có)
      ├── gundam-1.jpg
      ├── gundam-2.jpg
      ├── gundam-3.jpg
      ├── car-lamborghini.jpg
      ├── car-ferrari.jpg
      ├── car-porsche.jpg
      ├── plane-f16.jpg
      ├── plane-boeing.jpg
      ├── ship-missouri.jpg
      ├── figure-ironman.jpg
      ├── figure-spiderman.jpg
      └── lego-bugatti.jpg
```

## Danh sách ảnh cần upload

### 1. Gundam (3 ảnh)
- `gundam-1.jpg` - RG 1/144 RX-93 Nu Gundam
- `gundam-2.jpg` - MG 1/100 Strike Freedom Gundam
- `gundam-3.jpg` - PG 1/60 Unicorn Gundam

### 2. Xe mô hình (3 ảnh)
- `car-lamborghini.jpg` - Lamborghini Aventador 1:18
- `car-ferrari.jpg` - Ferrari F40 1:24
- `car-porsche.jpg` - Porsche 911 GT3 RS 1:12

### 3. Máy bay (2 ảnh)
- `plane-f16.jpg` - F-16 Fighting Falcon 1:72
- `plane-boeing.jpg` - Boeing 747 1:200

### 4. Tàu chiến (1 ảnh)
- `ship-missouri.jpg` - USS Missouri BB-63 1:700

### 5. Nhân vật (2 ảnh)
- `figure-ironman.jpg` - Iron Man Mark 85 Figure
- `figure-spiderman.jpg` - Spider-Man Advanced Suit

### 6. LEGO (1 ảnh)
- `lego-bugatti.jpg` - LEGO Technic Bugatti Chiron

## Yêu cầu kỹ thuật

### Kích thước ảnh
- **Khuyến nghị**: 800x800px đến 1200x1200px
- **Tỉ lệ**: 1:1 (vuông) hoặc 4:3
- **Định dạng**: JPG hoặc PNG
- **Dung lượng**: Tối đa 500KB mỗi ảnh

### Chất lượng
- Ảnh rõ nét, không bị mờ
- Nền trắng hoặc nền đơn giản
- Sản phẩm nằm ở trung tâm
- Ánh sáng đều, không bị tối hoặc quá sáng

## Cách thực hiện

### Bước 1: Chuẩn bị ảnh
1. Tải ảnh sản phẩm về máy
2. Đổi tên theo quy tắc trên
3. Tối ưu kích thước và dung lượng nếu cần

### Bước 2: Upload ảnh
1. Mở thư mục `assets/images/`
2. Copy tất cả ảnh vào thư mục này
3. Đảm bảo tên file chính xác

### Bước 3: Cập nhật database
1. Mở phpMyAdmin hoặc MySQL client
2. Chọn database của bạn
3. Chạy file `update_product_images.sql`

```sql
-- Hoặc chạy từng lệnh:
UPDATE products SET image = 'assets/images/gundam-1.jpg' WHERE id = 1;
-- ... (các lệnh khác)
```

### Bước 4: Kiểm tra
1. Mở website
2. Vào trang sản phẩm
3. Kiểm tra xem ảnh đã hiển thị đúng chưa

## Thêm sản phẩm mới

Khi thêm sản phẩm mới:

1. **Upload ảnh** vào `assets/images/` với tên có ý nghĩa
   - Ví dụ: `gundam-wing-zero.jpg`

2. **Thêm sản phẩm** vào database:
```sql
INSERT INTO products (category_id, name, description, price, discount, image, stock) 
VALUES (1, 'Wing Zero Gundam', 'Mô hình Wing Zero...', 950000, 10, 'assets/images/gundam-wing-zero.jpg', 20);
```

## Nguồn ảnh miễn phí

Nếu cần tải ảnh mẫu:
- **Unsplash**: https://unsplash.com/
- **Pexels**: https://www.pexels.com/
- **Pixabay**: https://pixabay.com/

Tìm kiếm với từ khóa:
- "gundam model"
- "car model"
- "airplane model"
- "action figure"
- "lego"

## Lưu ý quan trọng

⚠️ **Backup trước khi cập nhật**
```sql
-- Backup bảng products
CREATE TABLE products_backup AS SELECT * FROM products;
```

⚠️ **Kiểm tra đường dẫn**
- Đảm bảo đường dẫn là `assets/images/` (không có `/` ở đầu)
- Tên file phải khớp chính xác (phân biệt hoa thường trên Linux)

⚠️ **Quyền truy cập**
- Thư mục `assets/images/` phải có quyền đọc (chmod 755)
- File ảnh phải có quyền đọc (chmod 644)

## Troubleshooting

### Ảnh không hiển thị?
1. Kiểm tra đường dẫn file có đúng không
2. Kiểm tra tên file có chính xác không
3. Kiểm tra quyền truy cập thư mục
4. Xem Console trong trình duyệt (F12) để kiểm tra lỗi

### Ảnh bị vỡ hoặc méo?
1. Kiểm tra kích thước ảnh gốc
2. Đảm bảo tỉ lệ ảnh phù hợp
3. Kiểm tra CSS của `.product-image img`

### Ảnh tải chậm?
1. Giảm dung lượng ảnh (nén ảnh)
2. Sử dụng định dạng WebP nếu có thể
3. Cân nhắc sử dụng CDN
