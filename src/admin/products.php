<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id = $id");
    logActivity($conn, 'delete_product', "Xóa sản phẩm ID: $id");
    header('Location: products.php?success=deleted');
    exit;
}

// Lấy danh sách sản phẩm
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;

$where = "WHERE 1=1";
if ($search) {
    $search = $conn->real_escape_string($search);
    $where .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}
if ($category) {
    $where .= " AND p.category_id = $category";
}

$products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $where
    ORDER BY p.id DESC
")->fetch_all(MYSQLI_ASSOC);

$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm - Admin</title>
    <link rel="icon" type="image/jpeg" href="../assets/images/logo.jpeg">
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
                    <h1><i class="fas fa-box"></i> Quản lý Sản phẩm</h1>
                    <p>Thêm, sửa, xóa sản phẩm</p>
                </div>
                <a href="product_add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm sản phẩm mới
                </a>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                if ($_GET['success'] == 'added') echo 'Thêm sản phẩm thành công!';
                if ($_GET['success'] == 'updated') echo 'Cập nhật sản phẩm thành công!';
                if ($_GET['success'] == 'deleted') echo 'Xóa sản phẩm thành công!';
                ?>
            </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="filters-bar">
                <form method="GET" class="filter-form">
                    <div class="search-group">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <select name="category" onchange="this.form.submit()">
                        <option value="0">Tất cả danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Lọc
                    </button>
                </form>
            </div>
            
            <!-- Products Table -->
            <div class="dashboard-card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Giảm giá</th>
                                <th>Tồn kho</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><strong>#<?php echo $product['id']; ?></strong></td>
                                <td>
                                    <img src="../<?php echo $product['image']; ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                <td><?php echo number_format($product['price']); ?>đ</td>
                                <td>
                                    <?php if ($product['discount'] > 0): ?>
                                    <span class="badge" style="background: #fef3c7; color: #92400e;">-<?php echo $product['discount']; ?>%</span>
                                    <?php else: ?>
                                    <span style="color: #9ca3af;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="<?php echo $product['stock'] < 10 ? 'text-danger' : ''; ?>">
                                        <?php echo $product['stock']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $stock_status = isset($product['stock_status']) ? $product['stock_status'] : 'in_stock';
                                    $status_class = $stock_status == 'in_stock' ? 'badge-completed' : 'badge-cancelled';
                                    $status_text = $stock_status == 'in_stock' ? 'Còn hàng' : 'Hết hàng';
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                    <br>
                                    <small style="color: #6b7280;">
                                        <?php echo $product['status'] ? 'Hiển thị' : 'Ẩn'; ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="product_edit.php?id=<?php echo $product['id']; ?>" class="btn-icon" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $product['id']; ?>" 
                                           class="btn-icon btn-danger" 
                                           title="Xóa"
                                           onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .btn-primary {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(236, 72, 153, 0.3);
        }
        
        .filters-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .search-group {
            flex: 1;
            position: relative;
        }
        
        .search-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .search-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
        }
        
        .filter-form select {
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            min-width: 200px;
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-danger {
            background: #fee2e2 !important;
            color: #dc2626 !important;
        }
        
        .btn-danger:hover {
            background: #dc2626 !important;
            color: white !important;
        }
        
        .text-danger {
            color: #dc2626;
            font-weight: 700;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
    </style>
</body>
</html>
