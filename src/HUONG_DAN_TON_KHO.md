# Hướng dẫn Quản lý Trạng thái Tồn kho

## Tính năng mới: Quản lý trạng thái Còn hàng / Hết hàng

### 1. Cài đặt Database

Chạy file SQL để thêm cột `stock_status` vào bảng products:

```bash
mysql -u root -p toy_store < add_stock_status.sql
```

Hoặc chạy trực tiếp trong phpMyAdmin/MySQL:

```sql
ALTER TABLE products 
ADD COLUMN stock_status ENUM('in_stock', 'out_of_stock') DEFAULT 'in_stock' AFTER stock;

UPDATE products 
SET stock_status = CASE 
    WHEN stock > 0 THEN 'in_stock'
    ELSE 'out_of_stock'
END;
```

### 2. Chức năng

#### Trang Admin (admin/products.php)
- Hiển thị cột "Trạng thái" với badge màu:
  - **Xanh lá**: Còn hàng
  - **Đỏ**: Hết hàng
- Có thể chỉnh sửa trạng thái khi thêm/sửa sản phẩm

#### Thêm sản phẩm (admin/product_add.php)
- Thêm dropdown "Trạng thái tồn kho" với 2 lựa chọn:
  - Còn hàng
  - Hết hàng
- Mặc định là "Còn hàng"

#### Sửa sản phẩm (admin/product_edit.php)
- Có thể thay đổi trạng thái tồn kho
- Trạng thái hiện tại được chọn sẵn

#### Trang sản phẩm (products.php)
- Sản phẩm hết hàng hiển thị overlay màu đen với chữ "HẾT HÀNG" màu đỏ
- Nút "Xem Chi Tiết" bị vô hiệu hóa cho sản phẩm hết hàng

#### Chi tiết sản phẩm (product-detail.php)
- Hiển thị badge "Hết hàng" màu đỏ
- Nút "Thêm vào giỏ hàng" và "Mua ngay" bị vô hiệu hóa
- Hiển thị icon cấm thay vì icon giỏ hàng

### 3. Cách sử dụng

1. **Đăng nhập Admin**
2. **Vào "Quản lý Sản phẩm"**
3. **Chọn sản phẩm cần chỉnh sửa**
4. **Thay đổi "Trạng thái tồn kho"**:
   - Chọn "Còn hàng" nếu sản phẩm còn bán
   - Chọn "Hết hàng" nếu sản phẩm tạm hết
5. **Lưu thay đổi**

### 4. Lưu ý

- Trạng thái "Hết hàng" KHÔNG phụ thuộc vào số lượng tồn kho
- Admin có thể đặt "Hết hàng" ngay cả khi còn số lượng trong kho
- Điều này hữu ích khi:
  - Sản phẩm đang chờ nhập hàng
  - Tạm ngưng bán sản phẩm
  - Sản phẩm có vấn đề chất lượng

### 5. Hiển thị trên Website

**Trang danh sách sản phẩm:**
- Overlay đen mờ với chữ "HẾT HÀNG" màu đỏ nổi bật
- Nút bị vô hiệu hóa

**Trang chi tiết:**
- Badge "Hết hàng" màu đỏ
- Không thể thêm vào giỏ hàng
- Không thể mua ngay

### 6. File đã thay đổi

- `add_stock_status.sql` - Script SQL thêm cột mới
- `admin/products.php` - Hiển thị trạng thái trong bảng
- `admin/product_add.php` - Thêm dropdown trạng thái
- `admin/product_edit.php` - Chỉnh sửa trạng thái
- `products.php` - Hiển thị overlay hết hàng
- `product-detail.php` - Vô hiệu hóa nút mua hàng
- `assets/css/style.css` - CSS cho overlay hết hàng

---

**Hoàn thành!** Bây giờ bạn có thể quản lý trạng thái tồn kho một cách linh hoạt.
