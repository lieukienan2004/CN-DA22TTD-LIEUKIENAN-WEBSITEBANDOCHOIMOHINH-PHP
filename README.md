# 🎮 KIENANSHOP - Website Bán Đồ Chơi Mô Hình

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Website thương mại điện tử chuyên bán đồ chơi mô hình cao cấp (Gundam, Xe mô hình, Máy bay, Tàu chiến, Panini, Lego) được phát triển bằng PHP thuần và MySQL.

## 📋 Mục Lục

- [Tính Năng](#-tính-năng)
- [Công Nghệ Sử Dụng](#-công-nghệ-sử-dụng)
- [Yêu Cầu Hệ Thống](#-yêu-cầu-hệ-thống)
- [Cài Đặt](#-cài-đặt)
- [Cấu Hình](#-cấu-hình)
- [Sử Dụng](#-sử-dụng)
- [Cấu Trúc Thư Mục](#-cấu-trúc-thư-mục)
- [Tác Giả](#-tác-giả)
- [Giấy Phép](#-giấy-phép)

## ✨ Tính Năng

### Người Dùng
- 🔐 **Đăng ký/Đăng nhập** - Hệ thống xác thực người dùng an toàn
- 🛒 **Giỏ hàng** - Thêm, xóa, cập nhật số lượng sản phẩm
- ❤️ **Danh sách yêu thích** - Lưu sản phẩm ưa thích
- 🔍 **Tìm kiếm thông minh** - Tìm kiếm sản phẩm với gợi ý tự động
- 📦 **Quản lý đơn hàng** - Theo dõi trạng thái đơn hàng
- 💬 **Hệ thống chat** - Liên hệ với admin
- 🔔 **Thông báo** - Nhận thông báo về đơn hàng và ưu đãi
- ⭐ **Đánh giá sản phẩm** - Viết review và xếp hạng
- 👤 **Quản lý tài khoản** - Cập nhật thông tin cá nhân, avatar
- 🎨 **Giao diện đẹp** - Responsive, hiệu ứng hoa anh đào

### Quản Trị Viên
- 📊 **Dashboard** - Thống kê doanh thu, đơn hàng
- 📦 **Quản lý sản phẩm** - CRUD sản phẩm, danh mục
- 🏷️ **Quản lý giảm giá** - Tạo mã giảm giá, khuyến mãi
- 📋 **Quản lý đơn hàng** - Xử lý, cập nhật trạng thái đơn hàng
- 👥 **Quản lý người dùng** - Xem, chỉnh sửa thông tin khách hàng
- 💬 **Quản lý liên hệ** - Trả lời tin nhắn khách hàng
- 📢 **Gửi thông báo** - Thông báo ưu đãi cho người dùng
- 📈 **Báo cáo** - Xuất báo cáo doanh thu, sản phẩm bán chạy

## 🛠 Công Nghệ Sử Dụng

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Icons:** Font Awesome 6.0
- **Fonts:** Google Fonts (Inter)

## 💻 Yêu Cầu Hệ Thống

- PHP >= 7.4
- MySQL >= 5.7
- Apache/Nginx Web Server
- XAMPP/WAMP/LAMP (khuyến nghị cho môi trường phát triển)

## 📥 Cài Đặt

### 1. Clone Repository

```bash
git clone https://github.com/lieukienan2004/CN-DA22TTD-LIEUKIENAN-WEBSITEBANDOCHOIMOHINH-PHP.git
cd CN-DA22TTD-LIEUKIENAN-WEBSITEBANDOCHOIMOHINH-PHP
```

### 2. Cấu Hình Database

```bash
# Tạo database mới
mysql -u root -p
CREATE DATABASE bandochoi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Import database
mysql -u root -p bandochoi_db < bandochoi/database.sql
```

### 3. Cấu Hình Kết Nối

Chỉnh sửa file `bandochoi/config/database.php`:

```php
<?php
$host = 'localhost';
$dbname = 'bandochoi_db';
$username = 'root';
$password = ''; // Mật khẩu MySQL của bạn
```

### 4. Cấu Hình Web Server

**Với XAMPP:**
- Copy thư mục `bandochoi` vào `C:/xampp/htdocs/`
- Truy cập: `http://localhost/bandochoi/`

**Với Apache:**
```apache
<VirtualHost *:80>
    ServerName kienanshop.local
    DocumentRoot "C:/path/to/bandochoi"
    <Directory "C:/path/to/bandochoi">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 5. Tạo Tài Khoản Admin

Truy cập: `http://localhost/bandochoi/create_admin_simple.php`

Hoặc chạy SQL:
```sql
INSERT INTO admins (username, password, email, full_name) 
VALUES ('admin', '$2y$10$...', 'admin@kienanshop.com', 'Administrator');
```

## ⚙️ Cấu Hình

### Upload Files
Đảm bảo thư mục có quyền ghi:
```bash
chmod 755 bandochoi/uploads/
chmod 755 bandochoi/uploads/products/
chmod 755 bandochoi/uploads/avatars/
```

### Email Configuration
Chỉnh sửa `bandochoi/config/email.php` để cấu hình gửi email:
```php
$mail_config = [
    'host' => 'smtp.gmail.com',
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password',
    'port' => 587
];
```

## 🚀 Sử Dụng

### Truy Cập Website
- **Trang chủ:** `http://localhost/bandochoi/`
- **Đăng nhập:** `http://localhost/bandochoi/login.php`
- **Admin:** `http://localhost/bandochoi/admin/`

### Tài Khoản Mặc Định
```
Admin:
Username: admin
Password: admin123

User Test:
Email: test@example.com
Password: 123456
```

## 📁 Cấu Trúc Thư Mục

```
bandochoi/
├── admin/              # Trang quản trị
│   ├── dashboard.php
│   ├── products.php
│   ├── orders.php
│   └── ...
├── api/                # API endpoints
├── assets/             # Tài nguyên tĩnh
│   ├── css/
│   ├── js/
│   └── images/
├── config/             # File cấu hình
│   └── database.php
├── includes/           # File include chung
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── uploads/            # File upload
│   ├── products/
│   └── avatars/
├── index.php           # Trang chủ
├── products.php        # Danh sách sản phẩm
├── product-detail.php  # Chi tiết sản phẩm
├── cart.php            # Giỏ hàng
├── checkout.php        # Thanh toán
└── database.sql        # Database schema
```

## 📚 Tài Liệu Hướng Dẫn

Xem thêm các file hướng dẫn chi tiết:
- [HUONG_DAN_ADMIN.md](bandochoi/HUONG_DAN_ADMIN.md) - Hướng dẫn sử dụng trang quản trị
- [HUONG_DAN_GIAO_DIEN_ADMIN.md](bandochoi/HUONG_DAN_GIAO_DIEN_ADMIN.md) - Hướng dẫn giao diện admin
- [HUONG_DAN_TON_KHO.md](bandochoi/HUONG_DAN_TON_KHO.md) - Hướng dẫn quản lý tồn kho
- [HUONG_DAN_THONG_BAO_UU_DAI.md](bandochoi/HUONG_DAN_THONG_BAO_UU_DAI.md) - Hướng dẫn gửi thông báo

## 🐛 Xử Lý Lỗi

### Lỗi kết nối database
```bash
# Kiểm tra MySQL đang chạy
# Kiểm tra thông tin kết nối trong config/database.php
```

### Lỗi upload file
```bash
# Kiểm tra quyền thư mục uploads
chmod -R 755 uploads/
```

### Lỗi session
```php
// Thêm vào đầu file PHP
ini_set('session.gc_maxlifetime', 3600);
session_start();
```

## 🤝 Đóng Góp

Mọi đóng góp đều được chào đón! Vui lòng:
1. Fork repository
2. Tạo branch mới (`git checkout -b feature/AmazingFeature`)
3. Commit thay đổi (`git commit -m 'Add some AmazingFeature'`)
4. Push lên branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## 👨‍💻 Tác Giả

**Liêu Kiến An**
- MSSV: DA22TTD
- Email: lieukienan2004@gmail.com
- GitHub: [@lieukienan2004](https://github.com/lieukienan2004)

## 📄 Giấy Phép

Dự án này được phát hành dưới giấy phép MIT. Xem file [LICENSE](LICENSE) để biết thêm chi tiết.

## 🙏 Lời Cảm Ơn

- Font Awesome cho bộ icon tuyệt vời
- Google Fonts cho font chữ đẹp
- Cộng đồng PHP và MySQL

---
**Made with ❤️ by Liễu Kiện An**
 