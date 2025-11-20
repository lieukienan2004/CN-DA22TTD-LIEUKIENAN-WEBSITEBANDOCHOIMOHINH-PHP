# Hướng Dẫn Hệ Thống Liên Hệ & Thông Báo

## Tổng Quan
Hệ thống liên hệ cho phép khách hàng gửi tin nhắn và admin nhận thông báo tự động để trả lời nhanh chóng.

## Luồng Hoạt Động

### 1. Khách Hàng Gửi Tin Nhắn
- Khách hàng điền form liên hệ trên website
- Thông tin bao gồm: Họ tên, Email, Số điện thoại, Chủ đề, Nội dung
- Khi gửi thành công, tin nhắn được lưu vào database

### 2. Thông Báo Tự Động Cho Admin
- **Ngay lập tức** khi có tin nhắn mới, hệ thống tự động:
  - Tạo thông báo cho TẤT CẢ admin trong hệ thống
  - Hiển thị badge số lượng tin nhắn chưa đọc
  - Thông báo xuất hiện ở:
    - Chuông thông báo trên header admin
    - Trang quản lý tin nhắn (admin/contacts.php)

### 3. Admin Xem & Trả Lời
- Admin click vào thông báo → Chuyển đến trang trả lời
- Trang trả lời hiển thị:
  - Thông tin người gửi (tên, email, số điện thoại)
  - Nội dung tin nhắn gốc
  - Lịch sử các lần trả lời (nếu có)
  - Form để gửi trả lời mới

### 4. Gửi Trả Lời
Admin có 2 cách trả lời:
- **Trả lời trong hệ thống**: Lưu vào database, tạo thông báo cho khách (nếu có tài khoản)
- **Trả lời qua Email**: Click nút "Gửi qua Email" để mở ứng dụng email

## Các File Liên Quan

### Frontend (Khách hàng)
- `submit_contact.php` - Xử lý gửi tin nhắn + tạo thông báo cho admin
- `contact.html` - Form liên hệ

### Backend (Admin)
- `admin/contacts.php` - Danh sách tin nhắn
- `admin/contact_reply.php` - Trang trả lời tin nhắn
- `admin/get_notifications.php` - API lấy thông báo

### Database
- `contact_messages` - Lưu tin nhắn liên hệ
- `traloithongbao` - Lưu lịch sử trả lời
- `thongbao` - Lưu thông báo cho user/admin

## Cài Đặt

### 1. Tạo Bảng Database
```sql
-- Chạy file SQL này để tạo bảng traloithongbao
mysql -u root -p kienanshop < setup_contact_replies_table.sql
```

### 2. Kiểm Tra Bảng user_notifications
```sql
-- Đảm bảo bảng này đã tồn tại
mysql -u root -p kienanshop < setup_user_notifications.sql
```

## Tính Năng

### Thống Kê
- Tổng số tin nhắn
- Số tin nhắn chưa đọc
- Số tin nhắn đã xử lý

### Lọc & Tìm Kiếm
- Lọc theo trạng thái: Tất cả / Chưa đọc / Đã đọc / Đã trả lời
- Hiển thị thời gian gửi
- Badge màu sắc theo trạng thái

### Trạng Thái Tin Nhắn
- **new** (Chưa đọc) - Màu hồng, nổi bật
- **read** (Đã đọc) - Màu xanh dương
- **replied** (Đã trả lời) - Màu xanh lá

### Thông Báo Realtime
- Chuông thông báo trên header
- Badge số lượng tin nhắn mới
- Dropdown hiển thị tin nhắn gần nhất
- Click vào thông báo → Chuyển đến trang trả lời

## Sử Dụng

### Xem Tin Nhắn Mới
1. Đăng nhập admin
2. Click vào icon chuông trên header
3. Xem danh sách thông báo
4. Click vào tin nhắn để xem chi tiết

### Trả Lời Tin Nhắn
1. Vào **Quản lý Tin nhắn** từ sidebar
2. Click nút **"Trả lời"** trên tin nhắn
3. Nhập nội dung trả lời
4. Click **"Gửi trả lời"**

### Đánh Dấu Đã Đọc
- Click nút **"Đánh dấu đã đọc"** trên từng tin nhắn
- Hoặc tự động đánh dấu khi mở trang trả lời

### Xóa Tin Nhắn
- Click nút **"Xóa"** trên tin nhắn
- Xác nhận xóa
- Lưu ý: Xóa tin nhắn sẽ xóa cả lịch sử trả lời

## Lưu Ý

### Bảo Mật
- Chỉ admin mới xem được tin nhắn
- Validate dữ liệu đầu vào
- Escape HTML để tránh XSS

### Hiệu Suất
- Giới hạn 50 tin nhắn mới nhất
- Index trên các cột thường xuyên query
- Cache thông báo nếu cần

### Email
- Nút "Gửi qua Email" mở ứng dụng email mặc định
- Có thể tích hợp SMTP để gửi email tự động

## Mở Rộng

### Tính Năng Có Thể Thêm
- [ ] Gửi email tự động khi trả lời
- [ ] Template trả lời nhanh
- [ ] Phân loại tin nhắn theo chủ đề
- [ ] Gán tin nhắn cho admin cụ thể
- [ ] Đánh giá độ hài lòng sau khi trả lời
- [ ] Export tin nhắn ra Excel
- [ ] Tìm kiếm nâng cao
- [ ] Lọc theo khoảng thời gian

### Tích Hợp
- Tích hợp với hệ thống chat realtime
- Webhook gửi thông báo qua Telegram/Slack
- API để mobile app có thể truy cập

## Troubleshooting

### Không Nhận Được Thông Báo
1. Kiểm tra bảng `user_notifications` đã tồn tại chưa
2. Kiểm tra role của user có phải là 'admin' không
3. Xem log lỗi trong `submit_contact.php`

### Không Gửi Được Tin Nhắn
1. Kiểm tra kết nối database
2. Kiểm tra bảng `contact_messages` đã tồn tại
3. Kiểm tra validation trong form

### Lỗi Khi Trả Lời
1. Kiểm tra bảng `contact_replies` đã được tạo
2. Kiểm tra foreign key constraints
3. Kiểm tra session admin

## Liên Hệ Hỗ Trợ
Nếu gặp vấn đề, vui lòng kiểm tra:
- File log của server
- Console browser (F12)
- Database structure
