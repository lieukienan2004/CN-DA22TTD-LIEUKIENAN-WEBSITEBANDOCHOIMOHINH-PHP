<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

// Xử lý thêm/sửa/xóa danh mục
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $icon = $conn->real_escape_string($_POST['icon']);
        $conn->query("INSERT INTO categories (name, icon) VALUES ('$name', '$icon')");
        logActivity($conn, 'add_category', "Thêm danh mục: $name");
        header('Location: categories.php?success=added');
        exit;
    }
    
    if (isset($_POST['edit'])) {
        $id = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $icon = $conn->real_escape_string($_POST['icon']);
        $conn->query("UPDATE categories SET name='$name', icon='$icon' WHERE id=$id");
        logActivity($conn, 'edit_category', "Sửa danh mục ID: $id");
        header('Location: categories.php?success=updated');
        exit;
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE id=$id");
    logActivity($conn, 'delete_category', "Xóa danh mục ID: $id");
    header('Location: categories.php?success=deleted');
    exit;
}

$categories = $conn->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.id
")->fetch_all(MYSQLI_ASSOC);

$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_category = $conn->query("SELECT * FROM categories WHERE id=$edit_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Danh mục - Admin</title>
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
                    <h1><i class="fas fa-tags"></i> Quản lý Danh mục</h1>
                    <p>Quản lý danh mục sản phẩm</p>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                if ($_GET['success'] == 'added') echo 'Thêm danh mục thành công!';
                if ($_GET['success'] == 'updated') echo 'Cập nhật danh mục thành công!';
                if ($_GET['success'] == 'deleted') echo 'Xóa danh mục thành công!';
                ?>
            </div>
            <?php endif; ?>
            
            <div class="dashboard-grid" style="grid-template-columns: 1fr 2fr;">
                <!-- Form thêm/sửa -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><?php echo $edit_category ? 'Sửa danh mục' : 'Thêm danh mục mới'; ?></h2>
                    </div>
                    <div style="padding: 25px;">
                        <form method="POST">
                            <?php if ($edit_category): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label>Tên danh mục</label>
                                <input type="text" name="name" class="form-control" required 
                                       value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Icon (Font Awesome)</label>
                                <input type="text" name="icon" class="form-control" required 
                                       value="<?php echo $edit_category ? htmlspecialchars($edit_category['icon']) : 'fas fa-box'; ?>"
                                       placeholder="fas fa-box">
                                <small>Ví dụ: fas fa-robot, fas fa-car, fas fa-plane</small>
                            </div>
                            
                            <div class="form-actions">
                                <?php if ($edit_category): ?>
                                <button type="submit" name="edit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Cập nhật
                                </button>
                                <a href="categories.php" class="btn btn-secondary">Hủy</a>
                                <?php else: ?>
                                <button type="submit" name="add" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Thêm danh mục
                                </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Danh sách danh mục -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Danh sách danh mục</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Icon</th>
                                    <th>Tên danh mục</th>
                                    <th>Số sản phẩm</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><strong>#<?php echo $cat['id']; ?></strong></td>
                                    <td>
                                        <div class="category-icon-preview">
                                            <i class="<?php echo $cat['icon']; ?>"></i>
                                        </div>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                                    <td><?php echo $cat['product_count']; ?> sản phẩm</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?edit=<?php echo $cat['id']; ?>" class="btn-icon">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $cat['id']; ?>" 
                                               class="btn-icon btn-danger"
                                               onclick="return confirm('Xóa danh mục này? Tất cả sản phẩm trong danh mục sẽ bị ảnh hưởng!')">
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
    </div>
    
    <style>
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #ec4899;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #6b7280;
            font-size: 12px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
        
        .category-icon-preview {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
    </style>
</body>
</html>
