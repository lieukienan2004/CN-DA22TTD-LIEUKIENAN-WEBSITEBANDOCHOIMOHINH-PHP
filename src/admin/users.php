<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

// Thêm cột status nếu chưa có
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN status TINYINT(1) DEFAULT 1 AFTER phone");
}

// Xử lý khóa/mở khóa tài khoản
if (isset($_GET['toggle_status'])) {
    $user_id = intval($_GET['toggle_status']);
    $conn->query("UPDATE users SET status = 1 - status WHERE id = $user_id");
    logActivity($conn, 'toggle_user_status', "Thay đổi trạng thái user ID: $user_id");
    header('Location: users.php?success=updated');
    exit;
}

// Lấy danh sách khách hàng
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = "WHERE 1=1";
if ($search) {
    $where .= " AND (u.fullname LIKE '%$search%' OR u.email LIKE '%$search%' OR u.phone LIKE '%$search%')";
}

$users = $conn->query("
    SELECT u.*, 
           COUNT(DISTINCT o.id) as order_count,
           COALESCE(SUM(o.total), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Khách hàng - Admin</title>
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
                    <h1><i class="fas fa-users"></i> Quản lý Khách hàng</h1>
                    <p>Xem thông tin và quản lý khách hàng</p>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Cập nhật thành công!
            </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="stats-grid" style="margin-bottom: 24px;">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo count($users); ?></h3>
                        <p>Tổng khách hàng</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo count(array_filter($users, fn($u) => $u['status'] == 1)); ?></h3>
                        <p>Đang hoạt động</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo array_sum(array_column($users, 'order_count')); ?></h3>
                        <p>Tổng đơn hàng</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format(array_sum(array_column($users, 'total_spent'))); ?>đ</h3>
                        <p>Tổng doanh thu</p>
                    </div>
                </div>
            </div>
            
            <!-- Search -->
            <div class="filters-bar">
                <form method="GET" class="filter-form">
                    <div class="search-group">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Tìm kiếm khách hàng..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <?php if ($search): ?>
                    <a href="users.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Users Table -->
            <div class="dashboard-card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Điện thoại</th>
                                <th>Số đơn hàng</th>
                                <th>Tổng chi tiêu</th>
                                <th>Ngày đăng ký</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong>#<?php echo $user['id']; ?></strong></td>
                                <td>
                                    <div class="user-info">
                                        <?php if ($user['avatar'] && file_exists('../' . $user['avatar'])): ?>
                                        <img src="../<?php echo $user['avatar']; ?>" alt="" class="user-avatar">
                                        <?php else: ?>
                                        <div class="user-avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($user['fullname']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                                <td><?php echo $user['order_count']; ?> đơn</td>
                                <td><strong><?php echo number_format($user['total_spent']); ?>đ</strong></td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['status'] ? 'badge-completed' : 'badge-cancelled'; ?>">
                                        <?php echo $user['status'] ? 'Hoạt động' : 'Bị khóa'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="user_detail.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?toggle_status=<?php echo $user['id']; ?>" 
                                           class="btn-icon <?php echo $user['status'] ? 'btn-danger' : 'btn-success'; ?>" 
                                           title="<?php echo $user['status'] ? 'Khóa tài khoản' : 'Mở khóa'; ?>"
                                           onclick="return confirm('<?php echo $user['status'] ? 'Khóa' : 'Mở khóa'; ?> tài khoản này?')">
                                            <i class="fas fa-<?php echo $user['status'] ? 'lock' : 'unlock'; ?>"></i>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }
        
        .stat-details h3 {
            font-size: 28px;
            font-weight: 800;
            color: #1f2937;
            margin: 0 0 4px 0;
        }
        
        .stat-details p {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #f3f4f6;
        }
        
        .user-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        
        .btn-success {
            background: #d1fae5 !important;
            color: #065f46 !important;
        }
        
        .btn-success:hover {
            background: #10b981 !important;
            color: white !important;
        }
        
        .btn-outline {
            background: white;
            border: 2px solid #e5e7eb;
            color: #6b7280;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-outline:hover {
            border-color: #ec4899;
            color: #ec4899;
            background: #fdf2f8;
        }
        
        .filter-form {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .search-group {
            position: relative;
            flex: 1;
            max-width: 400px;
        }
        
        .search-group i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
        }
        
        .search-group input {
            width: 100%;
            padding: 12px 16px 12px 48px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .search-group input:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }
        
        .data-table tbody tr:hover {
            background: #fdf2f8;
        }
    </style>
</body>
</html>
