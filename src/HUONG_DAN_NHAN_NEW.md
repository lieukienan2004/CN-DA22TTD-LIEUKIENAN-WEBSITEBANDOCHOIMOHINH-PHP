# Hướng Dẫn Sử Dụng Nhãn "NEW" Cho Sản Phẩm

## Bước 1: Thêm cột is_new vào database

Truy cập: `http://localhost/bccnan/add_is_new_column.php`

Script sẽ tự động thêm cột `is_new` vào bảng `products`.

## Bước 2: Đánh dấu sản phẩm là "NEW"

### Khi thêm sản phẩm mới:
1. Vào **Admin > Quản lý Sản phẩm > Thêm sản phẩm mới**
2. Điền thông tin sản phẩm
3. Tích vào checkbox **"Đánh dấu là sản phẩm mới (hiển thị nhãn NEW)"**
4. Lưu sản phẩm

### Khi sửa sản phẩm:
1. Vào **Admin > Quản lý Sản phẩm**
2. Click nút **Sửa** ở sản phẩm muốn đánh dấu
3. Tích vào checkbox **"Đánh dấu là sản phẩm mới (hiển thị nhãn NEW)"**
4. Cập nhật

## Bước 3: Kiểm tra kết quả

Sản phẩm được đánh dấu `is_new = 1` sẽ hiển thị nhãn **NEW** màu đỏ ở góc trên bên trái:

- Trang chủ (index.php)
- Trang sản phẩm (products.php)
- Trang chi tiết sản phẩm

## Giao diện nhãn NEW:

```
┌─────────────────┐
│ NEW  [-20%]     │  <- NEW (đỏ, góc trái) + Giảm giá (cam, góc phải)
│                 │
│   [Hình ảnh]    │
│                 │
└─────────────────┘
```

## Đặc điểm nhãn NEW:

- **Vị trí**: Góc trên bên trái
- **Màu sắc**: Đỏ (#dc2626)
- **Font**: Chữ in hoa, đậm
- **Hiệu ứng**: Shadow mềm mại

## Lưu ý:

- Nhãn NEW và nhãn giảm giá có thể hiển thị cùng lúc
- Nhãn NEW ở bên trái, nhãn giảm giá ở bên phải
- Có thể bật/tắt nhãn NEW bất cứ lúc nào trong admin
