<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

// Lấy thống kê
$stats = [];

// Tổng đơn hàng
$result = $conn->query("SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as revenue FROM orders");
if ($result) {
    $order_stats = $result->fetch_assoc();
    $stats['orders'] = [
        'total' => $order_stats['count'],
        'revenue' => $order_stats['revenue']
    ];
} else {
    $stats['orders'] = ['total' => 0, 'revenue' => 0];
}

// Tổng sản phẩm
$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE status = 1");
$stats['products'] = $result ? $result->fetch_assoc()['total'] : 0;

// Tổng khách hàng
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['users'] = $result ? $result->fetch_assoc()['total'] : 0;

// Tin nhắn chưa đọc
$result = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'new'");
if ($result) {
    $stats['messages'] = $result->fetch_assoc()['total'];
} else {
    $stats['messages'] = 0;
}

// Đơn hàng gần đây
$recent_orders_query = $conn->query("
    SELECT o.*, u.fullname, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$recent_orders = $recent_orders_query ? $recent_orders_query->fetch_all(MYSQLI_ASSOC) : [];

// Sản phẩm sắp hết hàng (chỉ hiển thị stock <= 5)
$low_stock_query = $conn->query("
    SELECT p.*, c.name as category_name,
           CASE 
               WHEN stock = 0 THEN 'out'
               WHEN stock <= 3 THEN 'critical'
               WHEN stock <= 5 THEN 'low'
               ELSE 'warning'
           END as stock_level
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE stock <= 5 AND status = 1
    ORDER BY stock ASC 
    LIMIT 8
");
$low_stock = $low_stock_query ? $low_stock_query->fetch_all(MYSQLI_ASSOC) : [];

// Đếm số sản phẩm sắp hết (chỉ đếm <= 5)
$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock <= 5 AND stock > 0 AND status = 1");
$low_stock_count = $result ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock = 0 AND status = 1");
$out_of_stock_count = $result ? $result->fetch_assoc()['total'] : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin KIENANSHOP</title>
    <link rel="icon" type="image/jpeg" href="../assets/images/logo.jpeg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin-premium.css">
    <link rel="stylesheet" href="assets/css/admin-hover-effects.css">
    <link rel="stylesheet" href="assets/css/admin-dark-mode.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
                <p>Tổng quan hệ thống</p>
            </div>
            
            <!-- Critical Stock Alert Banner -->
            <?php if ($out_of_stock_count > 0): ?>
            <div class="critical-stock-banner">
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="content">
                    <div class="title">
                        <i class="fas fa-bell"></i> Cảnh báo: Có <?php echo $out_of_stock_count; ?> sản phẩm đã hết hàng!
                    </div>
                    <div class="description">
                        Vui lòng nhập thêm hàng ngay để tránh mất đơn hàng. 
                        <?php if ($low_stock_count > 0): ?>
                            Ngoài ra còn <?php echo $low_stock_count; ?> sản phẩm sắp hết.
                        <?php endif; ?>
                    </div>
                </div>
                <a href="products.php?filter=out_of_stock" class="btn-restock">
                    <i class="fas fa-plus-circle"></i> Nhập hàng ngay
                </a>
            </div>
            <?php elseif ($low_stock_count > 3): ?>
            <div class="critical-stock-banner" style="background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(245, 158, 11, 0.1) 100%); border-left-color: #f59e0b;">
                <div class="icon" style="color: #f59e0b;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="content">
                    <div class="title" style="color: #92400e;">
                        <i class="fas fa-info-circle"></i> Thông báo: Có <?php echo $low_stock_count; ?> sản phẩm còn ≤ 5 cái
                    </div>
                    <div class="description" style="color: #78350f;">
                        Nên nhập thêm hàng ngay để đảm bảo đủ cung cấp cho khách hàng.
                    </div>
                </div>
                <a href="products.php?filter=low_stock" class="btn-restock" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="fas fa-box"></i> Nhập hàng
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['orders']['total']); ?></h3>
                        <p>Tổng đơn hàng</p>
                        <span class="stat-revenue"><?php echo number_format($stats['orders']['revenue']); ?>đ</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['products']); ?></h3>
                        <p>Sản phẩm</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['users']); ?></h3>
                        <p>Khách hàng</p>
                    </div>
                </div>
                
                <div class="stat-card <?php echo ($low_stock_count > 0 || $out_of_stock_count > 0) ? 'glow-pulse' : ''; ?>" 
                     style="<?php echo ($low_stock_count > 0 || $out_of_stock_count > 0) ? 'border: 2px solid rgba(245, 158, 11, 0.3);' : ''; ?>">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($low_stock_count + $out_of_stock_count); ?></h3>
                        <p>Cần nhập hàng</p>
                        <?php if ($out_of_stock_count > 0): ?>
                            <span class="stat-revenue" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                <i class="fas fa-times-circle"></i> <?php echo $out_of_stock_count; ?> hết hàng
                            </span>
                        <?php elseif ($low_stock_count > 0): ?>
                            <span class="stat-revenue" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                <i class="fas fa-box"></i> <?php echo $low_stock_count; ?> sắp hết
                            </span>
                        <?php else: ?>
                            <span class="stat-revenue">
                                <i class="fas fa-check-circle"></i> Đủ hàng
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <!-- Recent Orders -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-shopping-bag"></i> Đơn hàng gần đây</h2>
                        <a href="orders.php" class="btn-link">Xem tất cả <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($order['fullname']); ?></td>
                                    <td><?php echo number_format($order['total']); ?>đ</td>
                                    <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Low Stock Products -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>
                            <i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i> 
                            Sản phẩm sắp hết
                        </h2>
                        <a href="products.php?filter=low_stock" class="btn-link">
                            Xem tất cả <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <?php if (count($low_stock) > 0): ?>
                        <!-- Stock Summary -->
                        <div style="padding: 15px 20px; background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(245, 158, 11, 0.08) 100%); border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; gap: 15px; justify-content: center; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 10px; padding: 8px 16px; background: rgba(255,255,255,0.7); border-radius: 10px;">
                                <i class="fas fa-exclamation-circle" style="font-size: 20px; color: #ef4444;"></i>
                                <div>
                                    <div style="font-size: 20px; font-weight: 900; color: #ef4444;"><?php echo $out_of_stock_count; ?></div>
                                    <div style="font-size: 11px; color: #991b1b; font-weight: 700; text-transform: uppercase;">Hết hàng</div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; padding: 8px 16px; background: rgba(255,255,255,0.7); border-radius: 10px;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 20px; color: #f59e0b;"></i>
                                <div>
                                    <div style="font-size: 20px; font-weight: 900; color: #f59e0b;"><?php echo $low_stock_count; ?></div>
                                    <div style="font-size: 11px; color: #92400e; font-weight: 700; text-transform: uppercase;">≤ 5 sản phẩm</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-list">
                            <?php foreach ($low_stock as $product): ?>
                            <div class="product-item" style="border-left: 4px solid <?php 
                                echo $product['stock_level'] == 'out' ? '#ef4444' : 
                                    ($product['stock_level'] == 'critical' ? '#f59e0b' : 
                                    ($product['stock_level'] == 'low' ? '#fbbf24' : '#fde68a')); 
                            ?>;">
                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     onerror="this.src='../assets/images/no-image.png'">
                                <div class="product-info" style="flex: 1;">
                                    <h4 style="margin-bottom: 5px;"><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                        <?php if ($product['stock'] == 0): ?>
                                            <span class="stock-status stock-out">
                                                <i class="fas fa-times-circle"></i> Hết hàng
                                            </span>
                                        <?php elseif ($product['stock'] <= 3): ?>
                                            <span class="stock-status stock-out" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);">
                                                <i class="fas fa-exclamation-circle"></i> Chỉ còn <?php echo $product['stock']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="stock-status stock-low">
                                                <i class="fas fa-exclamation-triangle"></i> Còn <?php echo $product['stock']; ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($product['category_name'])): ?>
                                            <span style="font-size: 12px; color: #6b7280; font-weight: 600;">
                                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <a href="product_edit.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-icon btn-edit" 
                                       data-tooltip="Nhập thêm hàng">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                    <a href="products.php?view=<?php echo $product['id']; ?>" 
                                       class="btn-icon btn-view" 
                                       data-tooltip="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (($low_stock_count + $out_of_stock_count) > 8): ?>
                        <div style="padding: 15px 20px; text-align: center; border-top: 1px solid rgba(0,0,0,0.05);">
                            <a href="products.php?filter=low_stock" class="btn-gradient-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 10px 20px; font-size: 14px;">
                                <i class="fas fa-boxes"></i>
                                Xem thêm <?php echo (($low_stock_count + $out_of_stock_count) - 8); ?> sản phẩm
                            </a>
                        </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="empty-state-premium" style="padding: 50px 30px;">
                            <div class="empty-state-icon-large" style="font-size: 80px;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="empty-state-title-large" style="font-size: 22px;">Tất cả sản phẩm đều đủ hàng!</h3>
                            <p class="empty-state-description-large" style="font-size: 14px;">
                                Không có sản phẩm nào sắp hết hàng. Tồn kho đang ở mức an toàn.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/admin-premium.js"></script>
    <script src="assets/js/admin-dark-mode.js"></script>
</body>
</html>
