# 📢 Hướng Dẫn Hệ Thống Thông Báo Ưu Đãi & Sự Kiện

## 🎯 Tổng Quan

Hệ thống thông báo cho phép admin gửi thông báo về ưu đãi, sự kiện, khuyến mãi đến người dùng. Người dùng chỉ có thể **xem** thông báo, không thể trả lời hay tương tác.

## 👨‍💼 Dành Cho Admin

### Quản Lý Thông Báo

1. Đăng nhập vào trang admin
2. Vào menu **"Thông báo"** trên sidebar
3. Xem thống kê:
   - Tổng số thông báo đã gửi
   - Số thông báo chưa đọc
   - Số thông báo đã đọc
4. Xem danh sách thông báo đã gửi với thông tin:
   - Loại thông báo
   - Tiêu đề và nội dung
   - Người nhận
   - Trạng thái (đã đọc/chưa đọc)
   - Thời gian gửi
5. Xóa thông báo nếu cần

### Gửi Thông Báo Mới

1. Từ trang **"Thông báo"**, nhấn **"Gửi thông báo mới"**
   
   HOẶC
   
   Vào menu **"Gửi thông báo"** trên sidebar

2. Chọn loại thông báo:
   - **Ưu đãi** 🎁: Khuyến mãi, giảm giá
   - **Hệ thống** ℹ️: Thông báo hệ thống, bảo trì
   - **Sản phẩm** 📦: Sản phẩm mới, hàng về
   - **Đơn hàng** 🛒: Thông báo về đơn hàng

3. Nhập thông tin:
   - **Tiêu đề**: Ngắn gọn, thu hút
   - **Nội dung**: Mô tả chi tiết
   - **Link**: (Tùy chọn) Link đến trang sản phẩm, danh mục...
   - **Gửi đến**: Chọn tất cả hoặc người dùng cụ thể

4. Nhấn **"Gửi Thông Báo"**

### Ví Dụ Thông Báo

**Ưu đãi:**
- Tiêu đề: "🎉 Flash Sale 50% - Chỉ hôm nay!"
- Nội dung: "Giảm giá 50% tất cả sản phẩm điện thoại. Nhanh tay đặt hàng!"
- Link: `products.php?category=dien-thoai`

**Sản phẩm mới:**
- Tiêu đề: "📱 iPhone 15 Pro Max vừa về hàng!"
- Nội dung: "Sản phẩm hot nhất năm đã có tại KIENANSHOP. Đặt hàng ngay!"
- Link: `product-detail.php?id=123`

## 👤 Dành Cho Người Dùng

### Xem Thông Báo

1. Đăng nhập vào tài khoản
2. Nhấn vào biểu tượng 🔔 trên header
3. Xem danh sách thông báo từ admin
4. Nhấn **"Đã đọc"** để đánh dấu đã xem

### Lưu Ý

- Người dùng **chỉ xem được** thông báo, không thể trả lời
- Thông báo chưa đọc sẽ có dấu hiệu đặc biệt (màu nền khác)
- Số thông báo chưa đọc hiển thị trên icon 🔔

## 🔧 Kỹ Thuật

### Phân Biệt Loại Thông Báo

Hệ thống sử dụng cột `user_type` và `type` để phân biệt:

- `user_type = 'admin'`: Thông báo từ admin
- `user_type = 'user'`: Thông báo liên hệ của user
- `type IN ('promotion', 'system', 'order', 'product')`: Các loại thông báo hiển thị

### File Liên Quan

- `admin/notifications.php`: Quản lý thông báo (admin)
- `admin/send_notification.php`: Gửi thông báo (admin)
- `notifications.php`: Xem thông báo (user)
- `get_user_notifications.php`: API lấy thông báo
- `assets/js/notification-checker.js`: Kiểm tra thông báo mới

### Cấu Trúc Bảng

```sql
CREATE TABLE thongbao (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    user_type ENUM('user', 'admin') DEFAULT 'user',
    type VARCHAR(50),
    title VARCHAR(255),
    message TEXT,
    link VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## ✅ Checklist Triển Khai

- [x] Tạo trang quản lý thông báo cho admin
- [x] Tạo trang gửi thông báo cho admin
- [x] Thêm menu vào sidebar admin với badge số chưa đọc
- [x] Lọc thông báo chỉ từ admin
- [x] Loại bỏ nút trả lời/tương tác
- [x] Hiển thị đúng số thông báo chưa đọc
- [x] Thống kê thông báo (tổng, đã đọc, chưa đọc)
- [x] Tính năng xóa thông báo
- [x] Tạo hướng dẫn sử dụng

## 🎨 Giao Diện

- Thông báo chưa đọc: Nền màu hồng nhạt, viền trái màu hồng
- Icon theo loại: Ưu đãi (vàng), Hệ thống (tím), Sản phẩm (xanh), Đơn hàng (xanh lá)
- Nút "Đã đọc" để đánh dấu
- Hiển thị thời gian tương đối (vừa xong, 5 phút trước...)

---

**Lưu ý:** Hệ thống này chỉ dành cho thông báo một chiều từ admin đến user. Nếu cần trao đổi hai chiều, sử dụng hệ thống tin nhắn/liên hệ riêng.
