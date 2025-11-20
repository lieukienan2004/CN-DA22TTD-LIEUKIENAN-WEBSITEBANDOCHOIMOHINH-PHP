<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

requireAdmin();

// Xử lý xóa coupon
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM coupons WHERE id = $id");
    header('Location: coupons.php?msg=deleted');
    exit;
}

// Xử lý thêm/sửa coupon
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $code = strtoupper(trim($_POST['code']));
    $discount_type = $_POST['discount_type'];
    $discount_value = floatval($_POST['discount_value']);
    $min_order_value = floatval($_POST['min_order_value']);
    $max_discount = !empty($_POST['max_discount']) ? floatval($_POST['max_discount']) : null;
    $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $status = isset($_POST['status']) ? 1 : 0;
    
    if ($id > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE coupons SET code=?, discount_type=?, discount_value=?, min_order_value=?, max_discount=?, usage_limit=?, end_date=?, status=? WHERE id=?");
        $stmt->bind_param("ssdddisii", $code, $discount_type, $discount_value, $min_order_value, $max_discount, $usage_limit, $end_date, $status, $id);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order_value, max_discount, usage_limit, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdddiis", $code, $discount_type, $discount_value, $min_order_value, $max_discount, $usage_limit, $end_date, $status);
    }
    
    if ($stmt->execute()) {
        header('Location: coupons.php?msg=success');
        exit;
    }
}

// Lấy danh sách coupons
$coupons = $conn->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Lấy coupon để edit
$edit_coupon = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_coupon = $conn->query("SELECT * FROM coupons WHERE id = $edit_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Mã Giảm Giá - Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin-dark-mode.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .coupon-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .coupon-header h1 {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 0;
        }
        
        .coupon-header .btn-add {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
        }
        
        .coupon-header .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 72, 153, 0.4);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .coupon-form-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .coupon-form-card h2 {
            margin: 0 0 25px 0;
            color: #1f2937;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }
        
        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .form-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .form-actions {
            grid-column: 1 / -1;
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            font-size: 14px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 72, 153, 0.4);
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        .coupons-table-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .coupons-table-card h2 {
            margin: 0 0 20px 0;
            color: #1f2937;
            font-size: 22px;
        }
        
        .table-wrapper {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        }
        
        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        table td {
            padding: 15px;
            border-bottom: 1px solid #f3f4f6;
            color: #6b7280;
            font-size: 14px;
        }
        
        table tbody tr {
            transition: all 0.2s ease;
        }
        
        table tbody tr:hover {
            background: #f9fafb;
        }
        
        .coupon-code {
            font-weight: 700;
            color: #ec4899;
            font-size: 15px;
            font-family: 'Courier New', monospace;
            background: rgba(236, 72, 153, 0.1);
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }
        
        .btn-icon.btn-edit {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .btn-icon.btn-edit:hover {
            background: #3b82f6;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-icon.btn-delete {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .btn-icon.btn-delete:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
        }
        
        .expired-text {
            color: #ef4444;
            font-weight: 600;
            font-size: 12px;
        }
        
        .usage-progress {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .usage-bar {
            flex: 1;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .usage-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="coupon-header">
            <h1>
                <i class="fas fa-ticket-alt" style="color: #ec4899;"></i>
                Quản Lý Mã Giảm Giá
            </h1>
            <?php if (!$edit_coupon): ?>
            <a href="#form" class="btn-add">
                <i class="fas fa-plus"></i>
                Thêm mã mới
            </a>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php 
            if ($_GET['msg'] == 'success') echo 'Thao tác thành công!';
            if ($_GET['msg'] == 'deleted') echo 'Đã xóa mã giảm giá!';
            ?>
        </div>
        <?php endif; ?>
        
        <!-- Form thêm/sửa -->
        <div class="coupon-form-card" id="form">
            <h2>
                <i class="fas fa-<?php echo $edit_coupon ? 'edit' : 'plus-circle'; ?>"></i>
                <?php echo $edit_coupon ? 'Sửa Mã Giảm Giá' : 'Thêm Mã Giảm Giá Mới'; ?>
            </h2>
            <form method="POST" class="form-grid">
                <?php if ($edit_coupon): ?>
                <input type="hidden" name="id" value="<?php echo $edit_coupon['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Mã giảm giá *</label>
                    <input type="text" name="code" value="<?php echo $edit_coupon['code'] ?? ''; ?>" required 
                           style="text-transform: uppercase;" placeholder="VD: SUMMER2025">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-percentage"></i> Loại giảm giá *</label>
                    <select name="discount_type" required>
                        <option value="percent" <?php echo ($edit_coupon['discount_type'] ?? '') == 'percent' ? 'selected' : ''; ?>>Phần trăm (%)</option>
                        <option value="fixed" <?php echo ($edit_coupon['discount_type'] ?? '') == 'fixed' ? 'selected' : ''; ?>>Số tiền cố định (đ)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-dollar-sign"></i> Giá trị giảm *</label>
                    <input type="number" name="discount_value" value="<?php echo $edit_coupon['discount_value'] ?? ''; ?>" 
                           step="0.01" required placeholder="VD: 10 hoặc 50000">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-shopping-cart"></i> Đơn hàng tối thiểu (đ)</label>
                    <input type="number" name="min_order_value" value="<?php echo $edit_coupon['min_order_value'] ?? 0; ?>" 
                           step="1000" placeholder="0">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-arrow-down"></i> Giảm tối đa (đ)</label>
                    <input type="number" name="max_discount" value="<?php echo $edit_coupon['max_discount'] ?? ''; ?>" 
                           step="1000" placeholder="Không giới hạn">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-users"></i> Số lần sử dụng tối đa</label>
                    <input type="number" name="usage_limit" value="<?php echo $edit_coupon['usage_limit'] ?? ''; ?>" 
                           placeholder="Không giới hạn">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Ngày hết hạn</label>
                    <input type="datetime-local" name="end_date" 
                           value="<?php echo $edit_coupon ? date('Y-m-d\TH:i', strtotime($edit_coupon['end_date'])) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label style="margin-bottom: 0;">&nbsp;</label>
                    <div class="form-checkbox">
                        <input type="checkbox" name="status" id="status" <?php echo ($edit_coupon['status'] ?? 1) ? 'checked' : ''; ?>>
                        <label for="status" style="margin: 0; cursor: pointer;">
                            <i class="fas fa-check-circle"></i> Kích hoạt mã giảm giá
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $edit_coupon ? 'Cập nhật' : 'Thêm mới'; ?>
                    </button>
                    <?php if ($edit_coupon): ?>
                    <a href="coupons.php" class="btn btn-secondary" style="text-decoration: none;">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Danh sách coupons -->
        <div class="coupons-table-card">
            <h2>
                <i class="fas fa-list"></i>
                Danh Sách Mã Giảm Giá (<?php echo count($coupons); ?>)
            </h2>
            <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Loại</th>
                        <th>Giá trị</th>
                        <th>ĐH tối thiểu</th>
                        <th>Giảm tối đa</th>
                        <th>Đã dùng/Giới hạn</th>
                        <th>Hết hạn</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): 
                        $is_expired = $coupon['end_date'] && strtotime($coupon['end_date']) < time();
                        $usage_percent = $coupon['usage_limit'] ? ($coupon['used_count'] / $coupon['usage_limit'] * 100) : 0;
                    ?>
                    <tr>
                        <td><span class="coupon-code"><?php echo $coupon['code']; ?></span></td>
                        <td>
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-<?php echo $coupon['discount_type'] == 'percent' ? 'percentage' : 'dollar-sign'; ?>" 
                                   style="color: #6b7280;"></i>
                                <?php echo $coupon['discount_type'] == 'percent' ? 'Phần trăm' : 'Cố định'; ?>
                            </span>
                        </td>
                        <td>
                            <strong style="color: #ec4899;">
                            <?php 
                            echo $coupon['discount_type'] == 'percent' 
                                ? $coupon['discount_value'] . '%' 
                                : number_format($coupon['discount_value']) . 'đ';
                            ?>
                            </strong>
                        </td>
                        <td><?php echo number_format($coupon['min_order_value']); ?>đ</td>
                        <td><?php echo $coupon['max_discount'] ? number_format($coupon['max_discount']) . 'đ' : '<span style="color: #9ca3af;">-</span>'; ?></td>
                        <td>
                            <div class="usage-progress">
                                <span style="font-weight: 600; min-width: 60px;">
                                    <?php echo $coupon['used_count']; ?> / 
                                    <?php echo $coupon['usage_limit'] ?? '∞'; ?>
                                </span>
                                <?php if ($coupon['usage_limit']): ?>
                                <div class="usage-bar">
                                    <div class="usage-bar-fill" style="width: <?php echo min($usage_percent, 100); ?>%;"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php 
                            if ($coupon['end_date']) {
                                echo '<div style="display: flex; flex-direction: column; gap: 4px;">';
                                echo '<span>' . date('d/m/Y', strtotime($coupon['end_date'])) . '</span>';
                                echo '<span style="font-size: 12px; color: #9ca3af;">' . date('H:i', strtotime($coupon['end_date'])) . '</span>';
                                if ($is_expired) {
                                    echo '<span class="expired-text"><i class="fas fa-exclamation-circle"></i> Hết hạn</span>';
                                }
                                echo '</div>';
                            } else {
                                echo '<span style="color: #9ca3af;">Không giới hạn</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($is_expired): ?>
                                <span class="badge badge-danger">
                                    <i class="fas fa-times-circle"></i> Hết hạn
                                </span>
                            <?php elseif (!$coupon['status']): ?>
                                <span class="badge badge-warning">
                                    <i class="fas fa-pause-circle"></i> Tắt
                                </span>
                            <?php else: ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> Hoạt động
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="?edit=<?php echo $coupon['id']; ?>#form" class="btn-icon btn-edit" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteCoupon(<?php echo $coupon['id']; ?>, '<?php echo $coupon['code']; ?>')" 
                                        class="btn-icon btn-delete" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    
    <script src="assets/js/admin-dark-mode.js"></script>
    <script>
        function deleteCoupon(id, code) {
            if (confirm('Bạn có chắc muốn xóa mã "' + code + '"?')) {
                window.location.href = '?delete=' + id;
            }
        }
        
        // Auto hide alert after 3 seconds
        setTimeout(function() {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.style.animation = 'slideUp 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            }
        }, 3000);
    </script>
    
    <style>
        @keyframes slideUp {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
    </style>
</body>
</html>
