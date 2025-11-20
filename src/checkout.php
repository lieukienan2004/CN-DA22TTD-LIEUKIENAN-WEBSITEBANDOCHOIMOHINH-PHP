<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php?message=Vui lòng đăng nhập để thanh toán');
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Kiểm tra tồn kho trước khi thanh toán
$out_of_stock_items = [];
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $product = getProductById($conn, $product_id);
    if ($product) {
        $stock_status = isset($product['stock_status']) ? $product['stock_status'] : 'in_stock';
        
        if ($stock_status == 'out_of_stock') {
            $out_of_stock_items[] = $product['name'];
            unset($_SESSION['cart'][$product_id]);
        } elseif ($quantity > $product['stock']) {
            $_SESSION['cart'][$product_id] = $product['stock'];
        }
    }
}

if (!empty($out_of_stock_items)) {
    $_SESSION['error_message'] = 'Một số sản phẩm đã hết hàng: ' . implode(', ', $out_of_stock_items);
    header('Location: cart.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (empty($fullname) || empty($phone) || empty($address)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } else {
        $conn->begin_transaction();
        
        try {
            $total = getCartTotal($conn);
            $user_id = isLoggedIn() ? $_SESSION['user_id'] : NULL;
            $payment_method = $_POST['payment_method'] ?? 'cod';
            
            // Tạo mã đơn hàng
            $order_code = isset($_POST['order_code']) && !empty($_POST['order_code']) 
                ? $_POST['order_code'] 
                : 'DH' . time() . rand(100, 999);
            
            // Xử lý coupon
            $coupon_code = null;
            $discount_amount = 0;
            if (isset($_SESSION['applied_coupon'])) {
                $coupon_code = $_SESSION['applied_coupon']['code'];
                $discount_amount = $_SESSION['applied_coupon']['discount'];
            }
            
            $sql = "INSERT INTO orders (order_code, user_id, fullname, phone, address, total, coupon_code, discount_amount, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisssdsds", $order_code, $user_id, $fullname, $phone, $address, $total, $coupon_code, $discount_amount, $payment_method);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Tạo thông báo cho tất cả admin
            try {
                $admin_query = $conn->query("SELECT id FROM admins");
                if ($admin_query) {
                    while ($admin = $admin_query->fetch_assoc()) {
                        $notif_title = "Đơn hàng mới #" . $order_id;
                        $notif_message = "Từ " . htmlspecialchars($fullname) . " - " . number_format($total) . "đ";
                        $notif_link = "order_detail.php?id=" . $order_id;
                        
                        $notif_stmt = $conn->prepare("INSERT INTO thongbao (user_id, user_type, type, title, message, link) VALUES (?, 'admin', 'order', ?, ?, ?)");
                        if ($notif_stmt) {
                            $notif_stmt->bind_param("isss", $admin['id'], $notif_title, $notif_message, $notif_link);
                            $notif_stmt->execute();
                        }
                    }
                }
            } catch (Exception $e) {
                // Nếu có lỗi tạo thông báo, vẫn tiếp tục đặt hàng
                error_log("Lỗi tạo thông báo admin: " . $e->getMessage());
            }
            
            // Cập nhật coupon usage
            if ($coupon_code && isset($_SESSION['applied_coupon']['coupon_id'])) {
                $coupon_id = $_SESSION['applied_coupon']['coupon_id'];
                
                // Tăng used_count
                $conn->query("UPDATE coupons SET used_count = used_count + 1 WHERE id = $coupon_id");
                
                // Lưu lịch sử sử dụng
                $stmt = $conn->prepare("INSERT INTO coupon_usage (coupon_id, user_id, order_id, discount_amount) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $coupon_id, $user_id, $order_id, $discount_amount);
                $stmt->execute();
            }
            
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $product = getProductById($conn, $product_id);
                
                // Kiểm tra lại tồn kho
                $stock_status = isset($product['stock_status']) ? $product['stock_status'] : 'in_stock';
                if ($stock_status == 'out_of_stock') {
                    throw new Exception('Sản phẩm "' . $product['name'] . '" đã hết hàng');
                }
                
                if ($quantity > $product['stock']) {
                    throw new Exception('Sản phẩm "' . $product['name'] . '" chỉ còn ' . $product['stock'] . ' sản phẩm');
                }
                
                $price = $product['price'] * (1 - $product['discount']/100);
                
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
                $stmt->execute();
                
                // Trừ tồn kho
                $new_stock = $product['stock'] - $quantity;
                $update_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                $update_stock->bind_param("ii", $new_stock, $product_id);
                $update_stock->execute();
            }
            
            $conn->commit();
            unset($_SESSION['cart']);
            unset($_SESSION['applied_coupon']);
            $success = true;
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - KIENANSHOP</title>
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/premium-ui.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .checkout-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 60px 20px;
        }
        
        .checkout-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .checkout-header {
            text-align: center;
            margin-bottom: 50px;
            color: white;
        }
        
        .checkout-header h1 {
            font-size: 42px;
            font-weight: 900;
            margin: 0 0 15px 0;
            text-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .checkout-header p {
            font-size: 18px;
            opacity: 0.95;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
            align-items: start;
        }
        
        .checkout-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            animation: slideUp 0.6s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card-title {
            font-size: 24px;
            font-weight: 800;
            color: #1f2937;
            margin: 0 0 30px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .card-title i {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 28px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            font-weight: 700;
            color: #374151;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn-checkout {
            width: 100%;
            padding: 18px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 18px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-checkout:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
        }
        
        .btn-checkout:active {
            transform: translateY(-1px);
        }
        
        .order-item {
            display: flex;
            gap: 15px;
            padding: 20px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-radius: 16px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .order-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.15);
        }
        
        .order-item-image {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .order-item-quantity {
            color: #6b7280;
            font-size: 14px;
            font-weight: 600;
        }
        
        .order-item-price {
            font-weight: 800;
            color: #667eea;
            font-size: 18px;
        }
        
        .coupon-box {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(251, 191, 36, 0.1) 100%);
            border: 2px dashed #f59e0b;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .coupon-input-group {
            display: flex;
            gap: 12px;
        }
        
        .coupon-input {
            flex: 1;
            padding: 14px 18px;
            border: 2px solid #fbbf24;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .btn-apply-coupon {
            padding: 14px 28px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .btn-apply-coupon:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
        }
        
        .order-summary {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-radius: 16px;
            padding: 25px;
            margin-top: 25px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 16px;
        }
        
        .summary-row.discount {
            color: #10b981;
            font-weight: 700;
        }
        
        .summary-row.total {
            border-top: 3px solid #667eea;
            margin-top: 15px;
            padding-top: 20px;
            font-size: 24px;
            font-weight: 900;
            color: #667eea;
        }
        
        .success-animation {
            text-align: center;
            padding: 80px 40px;
        }
        
        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease;
            box-shadow: 0 20px 60px rgba(16, 185, 129, 0.3);
        }
        
        .success-icon i {
            font-size: 60px;
            color: white;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .success-title {
            font-size: 36px;
            font-weight: 900;
            color: #1f2937;
            margin-bottom: 15px;
        }
        
        .success-message {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 40px;
        }
        
        .success-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-success {
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary-success {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary-success {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-secondary-success:hover {
            background: #667eea;
            color: white;
        }
        
        .error-alert {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .error-alert i {
            font-size: 24px;
        }
        
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .payment-option {
            cursor: pointer;
        }
        
        .payment-option input[type="radio"] {
            display: none;
        }
        
        .payment-card {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border: 3px solid #e5e7eb;
            border-radius: 16px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .payment-option:hover .payment-card {
            border-color: #667eea;
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.15);
        }
        
        .payment-option input[type="radio"]:checked + .payment-card {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
        }
        
        .payment-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            flex-shrink: 0;
        }
        
        .payment-icon.cod {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .payment-icon.online {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        
        .payment-details {
            flex: 1;
        }
        
        .payment-title {
            font-size: 18px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .payment-desc {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }
        
        .payment-check {
            font-size: 28px;
            color: #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .payment-option input[type="radio"]:checked + .payment-card .payment-check {
            color: #667eea;
            animation: checkBounce 0.5s ease;
        }
        
        @keyframes checkBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        /* Payment Modal */
        .payment-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            animation: fadeIn 0.3s ease;
        }
        
        .payment-modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payment-modal-content {
            background: white;
            border-radius: 24px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.4s ease;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .payment-modal-close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 32px;
            font-weight: bold;
            color: #9ca3af;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-modal-close:hover {
            color: #ef4444;
            transform: rotate(90deg);
        }
        
        .payment-modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 24px 24px 0 0;
        }
        
        .payment-modal-header i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .payment-modal-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 900;
        }
        
        .payment-modal-body {
            padding: 40px;
        }
        
        .payment-qr {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .qr-image {
            width: 280px;
            height: 280px;
            border: 4px solid #667eea;
            border-radius: 16px;
            padding: 15px;
            background: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }
        
        .qr-note {
            margin-top: 15px;
            font-weight: 700;
            color: #667eea;
            font-size: 16px;
        }
        
        .payment-info {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .info-row {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-row.highlight {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(251, 191, 36, 0.1) 100%);
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            border: 2px solid #fbbf24;
        }
        
        .info-row i {
            font-size: 24px;
            color: #667eea;
            width: 30px;
            flex-shrink: 0;
        }
        
        .info-row.highlight i {
            color: #f59e0b;
        }
        
        .info-label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 18px;
            font-weight: 800;
            color: #1f2937;
        }
        
        .info-value.amount {
            font-size: 28px;
            color: #f59e0b;
        }
        
        .btn-copy {
            margin-top: 10px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-copy::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-copy:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-copy:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-copy:active {
            transform: translateY(0);
        }
        
        .btn-copy.copied {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-copy.copied i {
            animation: checkBounce 0.5s ease;
        }
        
        .btn-copy i {
            font-size: 16px;
            z-index: 1;
        }
        
        .btn-copy span {
            z-index: 1;
        }
        
        .payment-note {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
        }
        
        .payment-note i {
            font-size: 24px;
            color: #3b82f6;
            flex-shrink: 0;
        }
        
        .payment-note p {
            margin: 0;
            color: #1f2937;
            line-height: 1.6;
            font-size: 14px;
        }
        
        .btn-confirm-payment {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 18px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        }
        
        .btn-confirm-payment:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Copy Toast Notification */
        .copy-toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 16px 24px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(16, 185, 129, 0.4);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 15px;
            z-index: 10001;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .copy-toast.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .copy-toast i {
            font-size: 24px;
            animation: checkPulse 0.6s ease;
        }
        
        @keyframes checkPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }
        
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            
            .checkout-header h1 {
                font-size: 32px;
            }
            
            .checkout-card {
                padding: 25px;
            }
            
            .payment-card {
                flex-direction: column;
                text-align: center;
            }
            
            .payment-icon {
                width: 80px;
                height: 80px;
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="checkout-container">
        <div class="checkout-wrapper">
            <div class="checkout-header">
                <h1><i class="fas fa-shopping-cart"></i> Thanh Toán</h1>
                <p>Hoàn tất đơn hàng của bạn trong vài bước đơn giản</p>
            </div>
            
            <?php if ($success): ?>
            <div class="checkout-card success-animation">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h2 class="success-title">Đặt hàng thành công!</h2>
                <p class="success-message">Cảm ơn bạn đã mua hàng. Chúng tôi sẽ liên hệ với bạn sớm nhất.</p>
                <div class="success-buttons">
                    <a href="account.php" class="btn-success btn-primary-success">
                        <i class="fas fa-shopping-bag"></i> Xem đơn hàng
                    </a>
                    <a href="index.php" class="btn-success btn-secondary-success">
                        <i class="fas fa-home"></i> Về trang chủ
                    </a>
                </div>
            </div>
            <?php else: ?>
            
            <?php if ($error): ?>
            <div class="error-alert">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
            <?php endif; ?>
        
            <div class="checkout-grid">
                <div class="checkout-card">
                    <form method="POST">
                        <h3 class="card-title">
                            <i class="fas fa-shipping-fast"></i>
                            Thông tin giao hàng
                        </h3>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user"></i> Họ và tên *
                            </label>
                            <input type="text" name="fullname" required class="form-input" placeholder="Nhập họ và tên của bạn">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-phone"></i> Số điện thoại *
                            </label>
                            <input type="tel" name="phone" required class="form-input" placeholder="Nhập số điện thoại">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" name="email" class="form-input" placeholder="Nhập email (không bắt buộc)">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Địa chỉ giao hàng *
                            </label>
                            <textarea name="address" required class="form-input form-textarea" placeholder="Nhập địa chỉ chi tiết"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-credit-card"></i> Phương thức thanh toán *
                            </label>
                            <div class="payment-methods">
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="cod" checked>
                                    <div class="payment-card">
                                        <div class="payment-icon cod">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="payment-details">
                                            <div class="payment-title">Thanh toán khi nhận hàng</div>
                                            <div class="payment-desc">Thanh toán bằng tiền mặt khi nhận hàng (COD)</div>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="online">
                                    <div class="payment-card">
                                        <div class="payment-icon online">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <div class="payment-details">
                                            <div class="payment-title">Thanh toán trực tuyến</div>
                                            <div class="payment-desc">Thanh toán qua thẻ ATM, Visa, MasterCard, Momo</div>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-checkout" id="btnCheckout">
                            <i class="fas fa-check-circle"></i>
                            Hoàn tất đặt hàng
                        </button>
                    </form>
                </div>
            
                <div>
                    <div class="checkout-card">
                        <h3 class="card-title">
                            <i class="fas fa-receipt"></i>
                            Đơn hàng của bạn
                        </h3>
                    
                    <?php
                    $cart_items = [];
                    foreach ($_SESSION['cart'] as $product_id => $quantity) {
                        $product = getProductById($conn, $product_id);
                        if ($product) {
                            $product['quantity'] = $quantity;
                            $cart_items[] = $product;
                        }
                    }
                    
                    $total = 0;
                    foreach ($cart_items as $item):
                        $price = $item['price'] * (1 - $item['discount']/100);
                        $subtotal = $price * $item['quantity'];
                        $total += $subtotal;
                    ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-item-image">
                            <div class="order-item-details">
                                <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="order-item-quantity">Số lượng: x<?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="order-item-price">
                                <?php echo number_format($subtotal); ?>đ
                            </div>
                        </div>
                        <?php endforeach; ?>
                    
                        <?php 
                        $discount = 0;
                        if (isset($_SESSION['applied_coupon'])) {
                            $discount = $_SESSION['applied_coupon']['discount'];
                        }
                        $final_total = $total - $discount;
                        ?>
                        
                        <div class="coupon-box">
                            <div style="margin-bottom: 12px; font-weight: 700; color: #d97706; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-tag"></i>
                                Mã giảm giá
                            </div>
                            <div class="coupon-input-group">
                                <input type="text" id="couponCode" placeholder="Nhập mã giảm giá" class="coupon-input">
                                <button type="button" onclick="applyCoupon()" class="btn-apply-coupon">
                                    <i class="fas fa-check"></i> Áp dụng
                                </button>
                            </div>
                            <div id="couponMessage" style="margin-top: 12px; font-size: 14px; font-weight: 600;"></div>
                        </div>
                        
                        <div class="order-summary">
                            <div class="summary-row">
                                <span>Tạm tính:</span>
                                <span><?php echo number_format($total); ?>đ</span>
                            </div>
                            
                            <?php if ($discount > 0): ?>
                            <div class="summary-row discount">
                                <span><i class="fas fa-tag"></i> Giảm giá (<?php echo $_SESSION['applied_coupon']['code']; ?>):</span>
                                <span>-<?php echo number_format($discount); ?>đ</span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="summary-row total">
                                <span>Tổng cộng:</span>
                                <span id="finalTotal"><?php echo number_format($final_total); ?>đ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Thanh toán trực tuyến -->
    <div id="paymentModal" class="payment-modal">
        <div class="payment-modal-content">
            <span class="payment-modal-close">&times;</span>
            <div class="payment-modal-header">
                <i class="fas fa-credit-card"></i>
                <h2>Thanh toán chuyển khoản</h2>
            </div>
            <div class="payment-modal-body">
                <div class="payment-qr">
                    <img src="assets/images/qr.jpeg" alt="QR Code" class="qr-image">
                    <p class="qr-note">Quét mã QR để thanh toán</p>
                </div>
                <div class="payment-info">
                    <div class="info-row">
                        <i class="fas fa-university"></i>
                        <div>
                            <div class="info-label">Ngân hàng</div>
                            <div class="info-value">Vietcombank (VCB)</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <i class="fas fa-credit-card"></i>
                        <div>
                            <div class="info-label">Số tài khoản</div>
                            <div class="info-value">1030074487</div>
                            <button class="btn-copy" onclick="copyAccountNumber(this, '1030074487')">
                                <i class="fas fa-copy"></i>
                                <span>Sao chép</span>
                            </button>
                        </div>
                    </div>
                    <div class="info-row">
                        <i class="fas fa-user"></i>
                        <div>
                            <div class="info-label">Chủ tài khoản</div>
                            <div class="info-value">KIENANSHOP</div>
                        </div>
                    </div>
                    <div class="info-row highlight">
                        <i class="fas fa-money-bill-wave"></i>
                        <div>
                            <div class="info-label">Số tiền cần chuyển</div>
                            <div class="info-value amount" id="paymentAmount">0đ</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <i class="fas fa-comment-alt"></i>
                        <div>
                            <div class="info-label">Nội dung chuyển khoản</div>
                            <div class="info-value" id="paymentContent">DH [Mã đơn hàng]</div>
                            <button class="btn-copy" onclick="copyContent(this)">
                                <i class="fas fa-copy"></i>
                                <span>Sao chép</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="payment-note">
                    <i class="fas fa-info-circle"></i>
                    <p>Sau khi chuyển khoản thành công, đơn hàng của bạn sẽ được xử lý trong vòng 5-10 phút. Vui lòng chuyển đúng số tiền và nội dung để được xác nhận nhanh nhất.</p>
                </div>
                <button class="btn-confirm-payment" onclick="confirmPayment()">
                    <i class="fas fa-check"></i> Tôi đã chuyển khoản
                </button>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        function applyCoupon() {
            const couponCode = document.getElementById('couponCode').value.trim();
            const cartTotal = <?php echo $total; ?>;
            
            if (!couponCode) {
                showCouponMessage('Vui lòng nhập mã giảm giá', 'error');
                return;
            }
            
            fetch('apply_coupon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'coupon_code=' + encodeURIComponent(couponCode) + '&cart_total=' + cartTotal
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showCouponMessage(data.message, 'success');
                    document.getElementById('finalTotal').textContent = data.new_total_formatted;
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showCouponMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showCouponMessage('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
            });
        }
        
        function showCouponMessage(message, type) {
            const messageDiv = document.getElementById('couponMessage');
            messageDiv.textContent = message;
            messageDiv.style.color = type === 'success' ? '#10b981' : '#ef4444';
        }
        
        // Payment Modal
        const paymentModal = document.getElementById('paymentModal');
        const modalClose = document.querySelector('.payment-modal-close');
        const checkoutForm = document.querySelector('form');
        const finalTotal = document.getElementById('finalTotal').textContent;
        
        // Tạo mã đơn hàng tạm thời
        function generateOrderCode() {
            const timestamp = Date.now();
            const random = Math.floor(Math.random() * 1000);
            return 'DH' + timestamp.toString().slice(-6) + random.toString().padStart(3, '0');
        }
        
        let orderCode = generateOrderCode();
        
        // Xử lý submit form
        checkoutForm.addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (paymentMethod === 'online') {
                e.preventDefault();
                
                // Tạo mã đơn hàng mới mỗi lần mở modal
                orderCode = generateOrderCode();
                
                // Hiển thị modal thanh toán với mã đơn hàng
                document.getElementById('paymentAmount').textContent = finalTotal;
                document.getElementById('paymentContent').textContent = orderCode;
                
                // Lưu mã đơn hàng vào hidden input để submit sau
                let orderCodeInput = document.getElementById('orderCodeInput');
                if (!orderCodeInput) {
                    orderCodeInput = document.createElement('input');
                    orderCodeInput.type = 'hidden';
                    orderCodeInput.name = 'order_code';
                    orderCodeInput.id = 'orderCodeInput';
                    checkoutForm.appendChild(orderCodeInput);
                }
                orderCodeInput.value = orderCode;
                
                paymentModal.classList.add('show');
            }
            // Nếu là COD thì submit bình thường
        });
        
        // Đóng modal
        modalClose.onclick = function() {
            paymentModal.classList.remove('show');
        }
        
        window.onclick = function(event) {
            if (event.target == paymentModal) {
                paymentModal.classList.remove('show');
            }
        }
        
        // Copy to clipboard with animation
        function copyAccountNumber(button, text) {
            navigator.clipboard.writeText(text).then(function() {
                // Thay đổi button thành trạng thái "Đã sao chép"
                const icon = button.querySelector('i');
                const span = button.querySelector('span');
                const originalIcon = icon.className;
                const originalText = span.textContent;
                
                button.classList.add('copied');
                icon.className = 'fas fa-check';
                span.textContent = 'Đã sao chép!';
                
                // Hiển thị toast notification
                showCopyToast('Đã sao chép số tài khoản: ' + text);
                
                // Reset sau 2 giây
                setTimeout(() => {
                    button.classList.remove('copied');
                    icon.className = originalIcon;
                    span.textContent = originalText;
                }, 2000);
            }).catch(function(err) {
                console.error('Lỗi sao chép:', err);
                alert('Không thể sao chép. Vui lòng thử lại!');
            });
        }
        
        function copyContent(button) {
            const content = document.getElementById('paymentContent').textContent;
            navigator.clipboard.writeText(content).then(function() {
                const icon = button.querySelector('i');
                const span = button.querySelector('span');
                const originalIcon = icon.className;
                const originalText = span.textContent;
                
                button.classList.add('copied');
                icon.className = 'fas fa-check';
                span.textContent = 'Đã sao chép!';
                
                showCopyToast('Đã sao chép nội dung: ' + content);
                
                setTimeout(() => {
                    button.classList.remove('copied');
                    icon.className = originalIcon;
                    span.textContent = originalText;
                }, 2000);
            }).catch(function(err) {
                console.error('Lỗi sao chép:', err);
                alert('Không thể sao chép. Vui lòng thử lại!');
            });
        }
        
        // Toast notification
        function showCopyToast(message) {
            // Xóa toast cũ nếu có
            const oldToast = document.querySelector('.copy-toast');
            if (oldToast) oldToast.remove();
            
            // Tạo toast mới
            const toast = document.createElement('div');
            toast.className = 'copy-toast';
            toast.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(toast);
            
            // Animation
            setTimeout(() => toast.classList.add('show'), 10);
            
            // Tự động ẩn sau 3 giây
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Xác nhận đã chuyển khoản
        function confirmPayment() {
            if (confirm('Bạn đã chuyển khoản thành công?')) {
                // Submit form
                checkoutForm.submit();
            }
        }
    </script>
</body>
</html>
