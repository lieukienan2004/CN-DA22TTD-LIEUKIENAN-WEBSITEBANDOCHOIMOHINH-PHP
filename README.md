# KIENANSHOP - Website Bán Đồ Chơi Mô Hình

Website bán hàng chuyên nghiệp được xây dựng bằng PHP, MySQL với giao diện hiện đại và responsive.

## Tính năng

- ✅ Trang chủ với sản phẩm nổi bật
- ✅ Danh sách sản phẩm theo danh mục
- ✅ Chi tiết sản phẩm
- ✅ Giỏ hàng
- ✅ Thanh toán đơn hàng
- ✅ Tìm kiếm sản phẩm
- ✅ Giao diện responsive (mobile-friendly)

## Cài đặt

### 1. Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Apache/Nginx web server

### 2. Cài đặt database
```sql
-- Import file database.sql vào MySQL
mysql -u root -p < database.sql
```

### 3. Cấu hình
Mở file `config/database.php` và cập nhật thông tin kết nối:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'toy_store');
```

### 4. Chạy website
- Copy toàn bộ file vào thư mục web root (htdocs/www)
- Truy cập: http://localhost/

## Cấu trúc thư mục

```
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── index.php
├── products.php
├── product-detail.php
├── cart.php
├── checkout.php
├── database.sql
└── README.md
```

## Hướng dẫn sử dụng

1. **Xem sản phẩm**: Truy cập trang chủ hoặc trang sản phẩm
2. **Thêm vào giỏ**: Click "Xem chi tiết" và chọn "Thêm vào giỏ"
3. **Thanh toán**: Vào giỏ hàng và click "Thanh toán"
4. **Hoàn tất**: Điền thông tin và hoàn tất đơn hàng

## Tùy chỉnh

### Thay đổi màu sắc
Mở `assets/css/style.css` và chỉnh sửa biến CSS:
```css
:root {
    --primary-color: #2563eb;
    --secondary-color: #1e40af;
    --danger-color: #ef4444;
}
```

### Thêm sản phẩm
Thêm dữ liệu vào bảng `products` trong database hoặc tạo trang admin.

## Hỗ trợ

Nếu có vấn đề, vui lòng tạo issue hoặc liên hệ.
