<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

// Cập nhật trạng thái đơn hàng
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE orders SET status='$status' WHERE id=$order_id");
    logActivity($conn, 'update_order_status', "Cập nhật trạng thái đơn hàng #$order_id thành $status");
    header("Location: orders.php?success=updated");
    exit;
}

// Lấy danh sách đơn hàng
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where = "WHERE 1=1";
if ($status_filter) {
    $where .= " AND o.status = '$status_filter'";
}

$orders = $conn->query("
    SELECT o.*, u.email, u.phone as user_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    $where
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Đơn hàng - Admin</title>
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
                    <h1><i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng</h1>
                    <p>Xem và xử lý đơn hàng</p>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Cập nhật đơn hàng thành công!
            </div>
            <?php endif; ?>
            
            <!-- Filter -->
            <div class="filters-bar">
                <div class="filter-tabs">
                    <a href="orders.php" class="filter-tab <?php echo !$status_filter ? 'active' : ''; ?>">
                        Tất cả
                    </a>
                    <a href="?status=pending" class="filter-tab <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">
                        Chờ xử lý
                    </a>
                    <a href="?status=processing" class="filter-tab <?php echo $status_filter == 'processing' ? 'active' : ''; ?>">
                        Đang xử lý
                    </a>
                    <a href="?status=completed" class="filter-tab <?php echo $status_filter == 'completed' ? 'active' : ''; ?>">
                        Hoàn thành
                    </a>
                    <a href="?status=cancelled" class="filter-tab <?php echo $status_filter == 'cancelled' ? 'active' : ''; ?>">
                        Đã hủy
                    </a>
                </div>
            </div>
            
            <!-- Orders Table -->
            <div class="dashboard-card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Điện thoại</th>
                                <th>Địa chỉ</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($order['fullname']); ?></strong>
                                        <?php if ($order['email']): ?>
                                        <br><small><?php echo htmlspecialchars($order['email']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($order['phone']); ?></td>
                                <td style="max-width: 200px;">
                                    <small><?php echo htmlspecialchars($order['address']); ?></small>
                                </td>
                                <td><strong><?php echo number_format($order['total']); ?>đ</strong></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="status-select status-<?php echo $order['status']; ?>">
                                            <option value="pending" <?php echo $order['status']=='pending'?'selected':''; ?>>⏳ Chờ xử lý</option>
                                            <option value="processing" <?php echo $order['status']=='processing'?'selected':''; ?>>⚙️ Đang xử lý</option>
                                            <option value="shipping" <?php echo $order['status']=='shipping'?'selected':''; ?>>🚚 Đang giao</option>
                                            <option value="completed" <?php echo $order['status']=='completed'?'selected':''; ?>>✅ Hoàn thành</option>
                                            <option value="cancelled" <?php echo $order['status']=='cancelled'?'selected':''; ?>>❌ Đã hủy</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
        .filter-tabs {
            display: flex;
            gap: 10px;
        }
        
        .filter-tab {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            color: #6b7280;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .filter-tab:hover {
            background: #f3f4f6;
        }
        
        .filter-tab.active {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: white;
        }
        
        .status-select {
            padding: 8px 16px;
            border-radius: 20px;
            border: 2px solid;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: none;
        }
        
        .status-select:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .status-pending { 
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border-color: #fcd34d;
        }
        
        .status-processing { 
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border-color: #93c5fd;
        }
        
        .status-shipping { 
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4338ca;
            border-color: #a5b4fc;
        }
        
        .status-completed { 
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border-color: #6ee7b7;
        }
        
        .status-cancelled { 
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-color: #fca5a5;
        }
        
        .status-confirmed { 
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border-color: #93c5fd;
        }
    </style>
</body>
</html>
