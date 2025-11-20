<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

requireAdmin();

// Lấy filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where = "WHERE payment_method = 'online'";

if ($filter == 'pending') {
    $where .= " AND payment_status = 'pending'";
} elseif ($filter == 'completed') {
    $where .= " AND payment_status = 'completed'";
}

if (!empty($search)) {
    $where .= " AND (order_code LIKE '%$search%' OR fullname LIKE '%$search%')";
}

// Lấy danh sách đơn hàng thanh toán online
$query = "SELECT * FROM orders $where ORDER BY created_at DESC";
$result = $conn->query($query);

// Thống kê
$stats_query = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN payment_status = 'completed' THEN total ELSE 0 END) as total_revenue
    FROM orders 
    WHERE payment_method = 'online'
");
$stats = $stats_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán trực tuyến - Admin</title>
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
                    <h1><i class="fas fa-credit-card"></i> Thanh toán trực tuyến</h1>
                    <p>Quản lý các đơn hàng thanh toán qua chuyển khoản</p>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['total']); ?></h3>
                        <p>Tổng đơn hàng</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['pending']); ?></h3>
                        <p>Chờ xác nhận</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['completed']); ?></h3>
                        <p>Đã xác nhận</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['total_revenue']); ?>đ</h3>
                        <p>Doanh thu</p>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="filters-bar" style="margin-bottom: 20px;">
                <div class="filter-tabs">
                    <a href="?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
                        <i class="fas fa-list"></i> Tất cả
                    </a>
                    <a href="?filter=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">
                        <i class="fas fa-clock"></i> Chờ xác nhận
                    </a>
                    <a href="?filter=completed" class="filter-tab <?php echo $filter == 'completed' ? 'active' : ''; ?>">
                        <i class="fas fa-check"></i> Đã xác nhận
                    </a>
                </div>
                
                <form method="GET" style="display: flex; gap: 10px;">
                    <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                    <input type="text" name="search" placeholder="Tìm mã đơn hàng..." value="<?php echo htmlspecialchars($search); ?>"
                           style="padding: 10px 15px; border: 2px solid #e5e7eb; border-radius: 10px; width: 300px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </form>
            </div>
            
            <!-- Orders Table -->
            <div class="dashboard-card">
                <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Khách hàng</th>
                                <th>Số tiền</th>
                                <th>Trạng thái thanh toán</th>
                                <th>Trạng thái đơn</th>
                                <th>Thời gian</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong style="color: #667eea;"><?php echo htmlspecialchars($order['order_code'] ?? 'DH' . $order['id']); ?></strong>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($order['fullname']); ?></strong><br>
                                        <small style="color: #6b7280;"><?php echo htmlspecialchars($order['phone']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: #ec4899;"><?php echo number_format($order['total']); ?>đ</strong>
                                </td>
                                <td>
                                    <?php if ($order['payment_status'] == 'pending'): ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Chờ xác nhận
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Đã xác nhận
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_map = [
                                        'pending' => ['Chờ xử lý', 'badge-pending'],
                                        'processing' => ['Đang xử lý', 'badge-processing'],
                                        'shipping' => ['Đang giao', 'badge-shipping'],
                                        'completed' => ['Hoàn thành', 'badge-completed'],
                                        'cancelled' => ['Đã hủy', 'badge-cancelled']
                                    ];
                                    $status_info = $status_map[$order['status']] ?? ['Không xác định', 'badge-pending'];
                                    ?>
                                    <span class="badge <?php echo $status_info[1]; ?>">
                                        <?php echo $status_info[0]; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn-icon btn-primary" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($order['payment_status'] == 'pending'): ?>
                                        <button onclick="confirmPayment(<?php echo $order['id']; ?>)" class="btn-icon btn-success" title="Xác nhận thanh toán">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-credit-card"></i>
                    <h3>Chưa có đơn hàng thanh toán online</h3>
                    <p>Các đơn hàng thanh toán qua chuyển khoản sẽ hiển thị ở đây</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function confirmPayment(orderId) {
            if (confirm('Xác nhận đã nhận được thanh toán cho đơn hàng này?')) {
                fetch('api/confirm_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Đã xác nhận thanh toán thành công!');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra!');
                });
            }
        }
    </script>
</body>
</html>
