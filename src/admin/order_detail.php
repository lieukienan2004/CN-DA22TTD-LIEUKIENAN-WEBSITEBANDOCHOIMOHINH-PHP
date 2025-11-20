<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Lấy thông tin đơn hàng
$order = $conn->query("
    SELECT o.*, u.fullname as user_fullname, u.email as user_email, u.phone as user_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = $order_id
")->fetch_assoc();

if (!$order) {
    header('Location: orders.php?error=not_found');
    exit;
}

// Lấy chi tiết sản phẩm trong đơn hàng
$items = $conn->query("
    SELECT oi.*, p.name, p.image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
")->fetch_all(MYSQLI_ASSOC);

// Xử lý cập nhật trạng thái
if (isset($_POST['update_status'])) {
    $new_status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE orders SET status = '$new_status' WHERE id = $order_id");
    logActivity($conn, 'update_order_status', "Cập nhật trạng thái đơn hàng #$order_id thành $new_status");
    header("Location: order_detail.php?id=$order_id&success=updated");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết Đơn hàng #<?php echo $order_id; ?> - Admin</title>
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
                    <h1><i class="fas fa-receipt"></i> Chi tiết Đơn hàng #<?php echo $order_id; ?></h1>
                    <p>Thông tin chi tiết đơn hàng</p>
                </div>
                <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Cập nhật thành công!
            </div>
            <?php endif; ?>
            
            <div class="order-detail-grid">
                <!-- Thông tin đơn hàng -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> Thông tin đơn hàng</h3>
                    </div>
                    <div class="order-info">
                        <div class="info-row">
                            <span class="label"><i class="fas fa-hashtag"></i> Mã đơn hàng:</span>
                            <span class="value"><strong>#<?php echo $order['id']; ?></strong></span>
                        </div>
                        <div class="info-row">
                            <span class="label"><i class="fas fa-calendar"></i> Ngày đặt:</span>
                            <span class="value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label"><i class="fas fa-info-circle"></i> Trạng thái:</span>
                            <span class="value">
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
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="label"><i class="fas fa-credit-card"></i> Thanh toán:</span>
                            <span class="value">
                                <?php 
                                    if (isset($order['payment_method'])) {
                                        echo $order['payment_method'] == 'cod' ? 'COD' : 'Chuyển khoản';
                                    } else {
                                        echo 'COD';
                                    }
                                ?>
                            </span>
                        </div>
                        <div class="info-row" style="background: linear-gradient(135deg, #fef3f8 0%, #fce7f3 100%); border: 2px solid #fbcfe8;">
                            <span class="label"><i class="fas fa-dollar-sign"></i> Tổng tiền:</span>
                            <span class="value"><strong style="color: #ec4899; font-size: 22px;"><?php echo number_format($order['total']); ?>đ</strong></span>
                        </div>
                    </div>
                    
                    <!-- Form cập nhật trạng thái -->
                    <form method="POST" class="status-update-form">
                        <label><i class="fas fa-edit"></i> Cập nhật trạng thái đơn hàng:</label>
                        <select name="status" class="form-control" style="margin-bottom: 16px;">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>⏳ Chờ xử lý</option>
                            <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>✓ Đã xác nhận</option>
                            <option value="shipping" <?php echo $order['status'] == 'shipping' ? 'selected' : ''; ?>>🚚 Đang giao hàng</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>✓ Hoàn thành</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>✗ Đã hủy</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-save"></i> Cập nhật trạng thái
                        </button>
                    </form>
                </div>
                
                <!-- Thông tin khách hàng -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> Thông tin khách hàng</h3>
                    </div>
                    <div class="order-info">
                        <div class="info-row">
                            <span class="label">Họ tên:</span>
                            <span class="value"><strong><?php echo htmlspecialchars($order['fullname'] ?? 'N/A'); ?></strong></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Email:</span>
                            <span class="value"><?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Điện thoại:</span>
                            <span class="value"><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Địa chỉ:</span>
                            <span class="value"><?php echo htmlspecialchars($order['address'] ?? 'N/A'); ?></span>
                        </div>
                        <?php if (isset($order['note']) && $order['note']): ?>
                        <div class="info-row">
                            <span class="label">Ghi chú:</span>
                            <span class="value"><?php echo nl2br(htmlspecialchars($order['note'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sản phẩm trong đơn hàng -->
            <div class="dashboard-card" style="margin-top: 24px;">
                <div class="card-header">
                    <h3><i class="fas fa-shopping-bag"></i> Sản phẩm đã đặt</h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <?php if ($item['image']): ?>
                                        <img src="../<?php echo $item['image']; ?>" alt="" class="product-thumb">
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['price']); ?>đ</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><strong><?php echo number_format($item['price'] * $item['quantity']); ?>đ</strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right;"><strong>Tổng cộng:</strong></td>
                                <td><strong style="color: #ec4899; font-size: 18px;"><?php echo number_format($order['total']); ?>đ</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .order-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 24px;
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
        
        .order-info {
            padding: 24px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            margin-bottom: 8px;
            background: #f9fafb;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .info-row:hover {
            background: #f3f4f6;
            transform: translateX(4px);
        }
        
        .info-row .label {
            color: #6b7280;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-row .label i {
            color: #ec4899;
            font-size: 16px;
        }
        
        .info-row .value {
            color: #1f2937;
            font-weight: 600;
            text-align: right;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .product-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
            border: 3px solid #f3f4f6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.1);
        }
        
        .status-update-form {
            margin-top: 20px;
            padding: 24px;
            background: linear-gradient(135deg, #fef3f8 0%, #fce7f3 100%);
            border-radius: 12px;
            border: 2px solid #fbcfe8;
        }
        
        .status-update-form label {
            display: block;
            margin-bottom: 12px;
            color: #374151;
            font-weight: 700;
            font-size: 15px;
        }
        
        .data-table tfoot tr {
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
            font-size: 16px;
        }
        
        .data-table tfoot td {
            padding: 20px !important;
            border-top: 3px solid #ec4899;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 2px solid #6ee7b7;
            font-weight: 600;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f3f4f6;
        }
        
        .page-header h1 {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #1f2937;
            font-size: 28px;
            font-weight: 800;
        }
        
        .page-header h1 i {
            color: #ec4899;
        }
        
        .badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border: 2px solid #fcd34d;
        }
        
        .badge-confirmed {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border: 2px solid #93c5fd;
        }
        
        .badge-shipping {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4338ca;
            border: 2px solid #a5b4fc;
        }
        
        .badge-completed {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border: 2px solid #6ee7b7;
        }
        
        .badge-cancelled {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border: 2px solid #fca5a5;
        }
        
        @media (max-width: 768px) {
            .order-detail-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
        }
    </style>
</body>
</html>
