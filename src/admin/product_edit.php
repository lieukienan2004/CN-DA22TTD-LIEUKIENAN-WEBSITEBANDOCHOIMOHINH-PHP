<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit;
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $discount = intval($_POST['discount']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    $status = isset($_POST['status']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $stock_status = $_POST['stock_status'];
    $image = trim($_POST['image']);
    
    $stmt = $conn->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, discount=?, image=?, stock=?, stock_status=?, status=?, is_new=? WHERE id=?");
    $stmt->bind_param("issdisisiii", $category_id, $name, $description, $price, $discount, $image, $stock, $stock_status, $status, $is_new, $id);
    
    if ($stmt->execute()) {
        logActivity($conn, 'update_product', "Cập nhật sản phẩm: $name");
        header('Location: products.php?success=updated');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Sản phẩm - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <div class="page-header">
                <div>
                    <h1><i class="fas fa-edit"></i> Sửa Sản phẩm</h1>
                    <p>Cập nhật thông tin sản phẩm</p>
                </div>
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
            
            <form method="POST" class="product-form">
                <div class="form-grid">
                    <div class="form-section">
                        <h3>Thông tin cơ bản</h3>
                        
                        <div class="form-group">
                            <label>Tên sản phẩm <span class="required">*</span></label>
                            <input type="text" name="name" required class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea name="description" rows="5" class="form-control" id="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
                            <small>Nhập hoặc paste mô tả sản phẩm. Hệ thống sẽ tự động làm sạch định dạng.</small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Danh mục <span class="required">*</span></label>
                                <select name="category_id" required class="form-control">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Giá (VNĐ) <span class="required">*</span></label>
                                <input type="number" name="price" required class="form-control" min="0" step="1000" value="<?php echo $product['price']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Giảm giá (%)</label>
                                <input type="number" name="discount" class="form-control" min="0" max="100" value="<?php echo $product['discount']; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Tồn kho <span class="required">*</span></label>
                                <input type="number" name="stock" required class="form-control" min="0" value="<?php echo $product['stock']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Trạng thái tồn kho <span class="required">*</span></label>
                            <select name="stock_status" required class="form-control">
                                <option value="in_stock" <?php echo $product['stock_status'] == 'in_stock' ? 'selected' : ''; ?>>Còn hàng</option>
                                <option value="out_of_stock" <?php echo $product['stock_status'] == 'out_of_stock' ? 'selected' : ''; ?>>Hết hàng</option>
                            </select>
                            <small>Trạng thái này sẽ hiển thị trên website</small>
                        </div>
                        
                        <div class="form-group">
                            <label>URL Ảnh <span class="required">*</span></label>
                            <input type="text" name="image" required class="form-control" value="<?php echo htmlspecialchars($product['image']); ?>">
                            <small>Đường dẫn ảnh hiện tại</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="status" <?php echo $product['status'] ? 'checked' : ''; ?>>
                                <span>Hiển thị sản phẩm</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_new" <?php echo (isset($product['is_new']) && $product['is_new']) ? 'checked' : ''; ?>>
                                <span>Đánh dấu là sản phẩm mới (hiển thị nhãn NEW)</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Xử lý paste event để làm sạch text
        document.addEventListener('DOMContentLoaded', function() {
            const descriptionField = document.getElementById('description');
            
            if (descriptionField) {
                descriptionField.addEventListener('paste', function(e) {
                    e.preventDefault();
                    
                    // Lấy text từ clipboard
                    const text = (e.clipboardData || window.clipboardData).getData('text');
                    
                    // Làm sạch text: loại bỏ ký tự đặc biệt, giữ lại xuống dòng
                    const cleanText = text
                        .replace(/\r\n/g, '\n')  // Chuẩn hóa line breaks
                        .replace(/\r/g, '\n')
                        .replace(/[\u200B-\u200D\uFEFF]/g, '')  // Loại bỏ zero-width characters
                        .replace(/[^\S\n]+/g, ' ')  // Chuẩn hóa spaces
                        .trim();
                    
                    // Insert text vào vị trí cursor
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    const currentValue = this.value;
                    
                    this.value = currentValue.substring(0, start) + cleanText + currentValue.substring(end);
                    
                    // Đặt cursor về vị trí sau text vừa paste
                    const newPosition = start + cleanText.length;
                    this.setSelectionRange(newPosition, newPosition);
                });
            }
        });
    </script>
    
    <style>
        .product-form {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-section h3 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1f2937;
            padding-bottom: 10px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }
        
        .required {
            color: #ef4444;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.1);
        }
        
        textarea.form-control {
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f3f4f6;
        }
        
        small {
            display: block;
            margin-top: 5px;
            color: #6b7280;
            font-size: 12px;
        }
    </style>
</body>
</html>
