<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn Hàng Của Tôi - KIENANSHOP</title>
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
    <link rel="stylesheet" href="assets/css/footer.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .account-container {
            padding: 60px 20px;
            background: var(--light-gray);
            min-height: 70vh;
        }
        
        .account-header {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .account-header h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .account-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        
        .account-sidebar {
            background: white;
            padding: 25px;
            border-radius: 20px;
            height: fit-content;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .account-menu {
            list-style: none;
        }
        
        .account-menu li {
            margin-bottom: 10px;
        }
        
        .account-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .account-menu a:hover,
        .account-menu a.active {
            background: var(--gradient-1);
            color: white;
        }
        
        .account-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .order-list {
            margin-top: 20px;
        }
        
        .order-item {
            padding: 25px;
            border: 2px solid var(--border-color);
            border-radius: 15px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .order-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .order-id {
            font-weight: 700;
            font-size: 18px;
            color: var(--primary-color);
        }
        
        .order-status {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }
        
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .account-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="account-container">
        <div class="container">
            <div class="account-header">
                <h1><i class="fas fa-shopping-bag"></i> Đơn Hàng Của Tôi</h1>
                <p style="color: var(--text-light);">Quản lý và theo dõi đơn hàng của bạn</p>
            </div>
            
            <div class="account-grid">
                <div class="account-sidebar">
                    <ul class="account-menu">
                        <li><a href="account.php"><i class="fas fa-user"></i> Thông tin tài khoản</a></li>
                        <li><a href="orders.php" class="active"><i class="fas fa-shopping-bag"></i> Đơn hàng của tôi</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                    </ul>
                </div>
                
                <div class="account-content">
                    <h2 style="margin-bottom: 25px;">Tất Cả Đơn Hàng (<?php echo count($orders); ?>)</h2>
                    
                    <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>Bạn chưa có đơn hàng nào</h3>
                        <p style="margin: 15px 0;">Hãy khám phá và mua sắm những sản phẩm yêu thích của bạn!</p>
                        <a href="products.php" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-shopping-cart"></i> Mua sắm ngay
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="order-list">
                        <?php foreach ($orders as $order): 
                            // Lấy chi tiết sản phẩm trong đơn hàng
                            $stmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                            $stmt->bind_param("i", $order['id']);
                            $stmt->execute();
                            $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        ?>
                        <div class="order-item">
                            <div class="order-header">
                                <div>
                                    <span class="order-id">#<?php echo $order['id']; ?></span>
                                    <p style="color: var(--text-light); font-size: 14px; margin-top: 5px;">
                                        <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </p>
                                </div>
                                <span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php 
                                    $status_text = [
                                        'pending' => 'Đang xử lý',
                                        'completed' => 'Hoàn thành',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                    echo $status_text[$order['status']] ?? 'Đang xử lý';
                                    ?>
                                </span>
                            </div>
                            
                            <!-- Danh sách sản phẩm -->
                            <div style="background: var(--light-gray); padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                                <?php foreach ($order_items as $index => $item): ?>
                                <div style="display: flex; gap: 15px; <?php echo $index < count($order_items) - 1 ? 'margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);' : ''; ?>">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    <div style="flex: 1;">
                                        <strong style="font-size: 16px;"><?php echo htmlspecialchars($item['name']); ?></strong>
                                        <p style="color: var(--text-light); font-size: 14px; margin-top: 5px;">
                                            Số lượng: <strong><?php echo $item['quantity']; ?></strong> × <?php echo number_format($item['price']); ?>đ
                                        </p>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-weight: 700; color: var(--primary-color); font-size: 18px;">
                                            <?php echo number_format($item['price'] * $item['quantity']); ?>đ
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Thông tin giao hàng và tổng tiền -->
                            <div style="display: grid; grid-template-columns: 1fr auto; gap: 20px; align-items: end;">
                                <div style="background: #fef3c7; padding: 15px; border-radius: 10px;">
                                    <p style="font-size: 13px; color: #92400e; margin-bottom: 5px;">
                                        <i class="fas fa-user"></i> <strong>Người nhận:</strong> <?php echo htmlspecialchars($order['fullname']); ?>
                                    </p>
                                    <p style="font-size: 13px; color: #92400e; margin-bottom: 5px;">
                                        <i class="fas fa-phone"></i> <strong>SĐT:</strong> <?php echo htmlspecialchars($order['phone']); ?>
                                    </p>
                                    <p style="font-size: 13px; color: #92400e;">
                                        <i class="fas fa-map-marker-alt"></i> <strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <p style="font-size: 14px; color: var(--text-light); margin-bottom: 5px;">Tổng thanh toán</p>
                                    <p style="font-weight: 800; color: var(--danger-color); font-size: 24px;">
                                        <?php echo number_format($order['total']); ?>đ
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
