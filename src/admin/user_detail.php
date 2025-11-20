<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Lấy thông tin khách hàng
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

if (!$user) {
    header('Location: users.php?error=not_found');
    exit;
}

// Lấy danh sách đơn hàng
$orders = $conn->query("
    SELECT * FROM orders 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Thống kê
$stats = [
    'total_orders' => count($orders),
    'total_spent' => array_sum(array_column($orders, 'total')),
    'completed_orders' => count(array_filter($orders, fn($o) => $o['status'] == 'completed')),
    'pending_orders' => count(array_filter($orders, fn($o) => $o['status'] == 'pending'))
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết Khách hàng - Admin</title>
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
                    <h1><i class="fas fa-user-circle"></i> Chi tiết Khách hàng</h1>
                    <p>Thông tin chi tiết và lịch sử mua hàng</p>
                </div>
                <a href="users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
            
            <!-- Thống kê -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>Tổng đơn hàng</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['total_spent']); ?>đ</h3>
                        <p>Tổng chi tiêu</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['completed_orders']; ?></h3>
                        <p>Đơn hoàn thành</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['pending_orders']; ?></h3>
                        <p>Đơn chờ xử lý</p>
                    </div>
                </div>
            </div>
            
            <!-- Thông tin khách hàng -->
            <div class="dashboard-card" style="margin-top: 24px;">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i> Thông tin cá nhân</h3>
                </div>
                <div class="user-detail-info">
                    <div class="user-avatar-section">
                        <?php if ($user['avatar'] && file_exists('../' . $user['avatar'])): ?>
                        <img src="../<?php echo $user['avatar']; ?>" alt="" class="user-avatar-large">
                        <?php else: ?>
                        <div class="user-avatar-large-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                        <?php endif; ?>
                        <div class="user-status">
                            <span class="badge <?php echo $user['status'] ? 'badge-completed' : 'badge-cancelled'; ?>">
                                <?php echo $user['status'] ? 'Hoạt động' : 'Bị khóa'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="user-info-grid">
                        <div class="info-item">
                            <i class="fas fa-user"></i>
                            <div>
                                <span class="label">Họ tên</span>
                                <strong><?php echo htmlspecialchars($user['fullname']); ?></strong>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <span class="label">Email</span>
                                <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <span class="label">Điện thoại</span>
                                <strong><?php echo htmlspecialchars($user['phone'] ?: 'Chưa cập nhật'); ?></strong>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <span class="label">Địa chỉ</span>
                                <strong><?php echo htmlspecialchars($user['address'] ?: 'Chưa cập nhật'); ?></strong>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <span class="label">Ngày đăng ký</span>
                                <strong><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></strong>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-hashtag"></i>
                            <div>
                                <span class="label">ID Khách hàng</span>
                                <strong>#<?php echo $user['id']; ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lịch sử đơn hàng -->
            <div class="dashboard-card" style="margin-top: 24px;">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Lịch sử đơn hàng</h3>
                </div>
                <div class="table-responsive">
                    <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>Chưa có đơn hàng</h3>
                        <p>Khách hàng chưa đặt đơn hàng nào</p>
                    </div>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td><strong><?php echo number_format($order['total']); ?>đ</strong></td>
                                <td>
                                    <span class="badge badge-<?php echo $order['status']; ?>">
                                        <?php 
                                            $statuses = [
                                                'pending' => 'Chờ xử lý',
                                                'confirmed' => 'Đã xác nhận',
                                                'shipping' => 'Đang giao',
                                                'completed' => 'Hoàn thành',
                                                'cancelled' => 'Đã hủy'
                                            ];
                                            echo $statuses[$order['status']] ?? $order['status'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
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
        
        .card-header {
            padding: 24px 24px 20px 24px;
            border-bottom: 2px solid #f3f4f6;
            margin-bottom: 0;
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
            border-radius: 12px 12px 0 0;
        }
        
        .card-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }
        
        .card-header i {
            color: #ec4899;
            font-size: 20px;
        }
        
        .user-detail-info {
            padding: 32px;
            display: flex;
            gap: 32px;
        }
        
        .user-avatar-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }
        
        .user-avatar-large {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ec4899;
            box-shadow: 0 8px 24px rgba(236, 72, 153, 0.3);
        }
        
        .user-avatar-large-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 60px;
            box-shadow: 0 8px 24px rgba(236, 72, 153, 0.3);
        }
        
        .user-info-grid {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
            transition: all 0.3s;
        }
        
        .info-item:hover {
            background: #f3f4f6;
            transform: translateX(4px);
        }
        
        .info-item i {
            font-size: 24px;
            color: #ec4899;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 10px;
        }
        
        .info-item div {
            flex: 1;
        }
        
        .info-item .label {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .info-item strong {
            display: block;
            font-size: 15px;
            color: #1f2937;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 20px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        
        @media (max-width: 768px) {
            .user-detail-info {
                flex-direction: column;
            }
            
            .user-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
