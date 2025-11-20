# 🎯 HƯỚNG DẪN SỬ DỤNG ADMIN PANEL

## 📋 Mục lục
1. [Cài đặt](#cài-đặt)
2. [Đăng nhập](#đăng-nhập)
3. [Các tính năng](#các-tính-năng)
4. [Hướng dẫn sử dụng](#hướng-dẫn-sử-dụng)

---

## 🚀 Cài đặt

### Bước 1: Chạy SQL Setup
1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Chọn database `kienan123`
3. Click tab "SQL"
4. Copy toàn bộ nội dung file `admin_setup.sql`
5. Paste vào và click "Go"

### Bước 2: Tạo tài khoản Admin
1. Truy cập: `http://localhost/bccnan/admin/create_admin.php`
2. Nếu thành công, sẽ thấy thông báo màu xanh
3. **XÓA file `create_admin.php` sau khi tạo xong** (bảo mật)

---

## 🔐 Đăng nhập

**URL:** `http://localhost/bccnan/admin/login.php`

**Thông tin đăng nhập mặc định:**
- Username: `admin`
- Password: `admin123`

⚠️ **Lưu ý:** Nên đổi mật khẩu sau lần đăng nhập đầu tiên!

---

## ✨ Các tính năng

### 📊 Dashboard
- Thống kê tổng quan (đơn hàng, sản phẩm, khách hàng, tin nhắn)
- Đơn hàng gần đây
- Sản phẩm sắp hết hàng
- Biểu đồ doanh thu

### 📦 Quản lý Sản phẩm
- ✅ Xem danh sách sản phẩm
- ✅ Thêm sản phẩm mới
- ✅ Sửa thông tin sản phẩm
- ✅ Xóa sản phẩm
- ✅ Tìm kiếm và lọc theo danh mục
- ✅ Quản lý tồn kho

### 🏷️ Quản lý Danh mục
- Thêm/Sửa/Xóa danh mục
- Sắp xếp thứ tự hiển thị
- Quản lý icon danh mục

### 🛒 Quản lý Đơn hàng
- Xem danh sách đơn hàng
- Chi tiết đơn hàng
- Cập nhật trạng thái
- In hóa đơn
- Thống kê doanh thu

### 👥 Quản lý Khách hàng
- Danh sách khách hàng
- Xem lịch sử mua hàng
- Khóa/Mở khóa tài khoản
- Thống kê khách hàng

### 📧 Quản lý Tin nhắn
- Xem tin nhắn liên hệ
- Trả lời tin nhắn
- Đánh dấu đã đọc/chưa đọc
- Xóa tin nhắn

### ⚙️ Cài đặt
- Thông tin website
- Cấu hình email
- Cài đặt thanh toán
- Quản lý admin

---

## 📖 Hướng dẫn sử dụng

### 1. Thêm sản phẩm mới

**Bước 1:** Click menu "Sản phẩm" → "Thêm sản phẩm mới"

**Bước 2:** Điền thông tin:
- Tên sản phẩm (bắt buộc)
- Mô tả
- Danh mục (bắt buộc)
- Giá (VNĐ)
- Giảm giá (%)
- Tồn kho
- URL ảnh

**Bước 3:** Click "Lưu sản phẩm"

**Lưu ý về ảnh:**
- Đặt ảnh vào thư mục `assets/images/`
- Nhập đường dẫn: `assets/images/ten-anh.jpg`
- Kích thước khuyến nghị: 800x800px
- Định dạng: JPG, PNG

### 2. Sửa sản phẩm

**Bước 1:** Vào "Sản phẩm" → Click icon "Sửa" (bút chì)

**Bước 2:** Cập nhật thông tin cần thay đổi

**Bước 3:** Click "Cập nhật"

### 3. Xóa sản phẩm

**Bước 1:** Vào "Sản phẩm" → Click icon "Xóa" (thùng rác)

**Bước 2:** Xác nhận xóa

⚠️ **Cảnh báo:** Không thể khôi phục sau khi xóa!

### 4. Tìm kiếm sản phẩm

**Cách 1:** Dùng ô tìm kiếm
- Nhập tên sản phẩm
- Click "Lọc"

**Cách 2:** Lọc theo danh mục
- Chọn danh mục từ dropdown
- Tự động lọc

### 5. Quản lý đơn hàng

**Xem đơn hàng:**
- Click menu "Đơn hàng"
- Xem danh sách tất cả đơn

**Cập nhật trạng thái:**
- Click vào đơn hàng
- Chọn trạng thái mới:
  - `pending`: Chờ xử lý
  - `processing`: Đang xử lý
  - `completed`: Hoàn thành
  - `cancelled`: Đã hủy

### 6. Trả lời tin nhắn

**Bước 1:** Click menu "Tin nhắn"

**Bước 2:** Click vào tin nhắn cần trả lời

**Bước 3:** Nhập nội dung trả lời

**Bước 4:** Click "Gửi trả lời"

→ Email tự động gửi đến khách hàng

---

## 🎨 Giao diện

### Màu sắc chủ đạo
- **Primary:** Hồng (#ec4899)
- **Success:** Xanh lá (#10b981)
- **Warning:** Vàng (#f59e0b)
- **Danger:** Đỏ (#ef4444)

### Responsive
- ✅ Desktop (1920px+)
- ✅ Laptop (1366px+)
- ✅ Tablet (768px+)
- ✅ Mobile (375px+)

---

## 🔒 Bảo mật

### Quyền truy cập
- **Super Admin:** Toàn quyền
- **Admin:** Quản lý sản phẩm, đơn hàng
- **Moderator:** Chỉ xem và trả lời tin nhắn

### Khuyến nghị
1. ✅ Đổi mật khẩu mặc định
2. ✅ Xóa file `create_admin.php`
3. ✅ Không chia sẻ thông tin đăng nhập
4. ✅ Đăng xuất sau khi sử dụng
5. ✅ Backup database định kỳ

---

## 🐛 Xử lý lỗi

### Lỗi đăng nhập
**Triệu chứng:** "Sai mật khẩu"

**Giải pháp:**
1. Chạy lại `create_admin.php`
2. Kiểm tra database có bảng `admins` chưa
3. Xóa cache trình duyệt

### Lỗi không hiển thị ảnh
**Triệu chứng:** Ảnh bị vỡ

**Giải pháp:**
1. Kiểm tra đường dẫn ảnh đúng chưa
2. Đảm bảo file ảnh tồn tại
3. Kiểm tra quyền truy cập thư mục

### Lỗi database
**Triệu chứng:** "Table doesn't exist"

**Giải pháp:**
1. Chạy lại `admin_setup.sql`
2. Kiểm tra `config/database.php`
3. Đảm bảo MySQL đang chạy

---

## 📞 Hỗ trợ

Nếu gặp vấn đề, hãy kiểm tra:
1. Console trình duyệt (F12)
2. Error log PHP
3. MySQL error log

---

## 🎉 Hoàn tất!

Admin panel đã sẵn sàng sử dụng. Chúc bạn quản lý website hiệu quả!

**Các file quan trọng:**
- `/admin/login.php` - Đăng nhập
- `/admin/index.php` - Dashboard
- `/admin/products.php` - Quản lý sản phẩm
- `/admin/orders.php` - Quản lý đơn hàng
- `/admin/contacts.php` - Quản lý tin nhắn

**Database:**
- Bảng `admins` - Tài khoản admin
- Bảng `admin_logs` - Lịch sử hoạt động
- Bảng `site_settings` - Cài đặt website
