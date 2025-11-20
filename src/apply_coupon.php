<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$coupon_code = isset($_POST['coupon_code']) ? strtoupper(trim($_POST['coupon_code'])) : '';
$cart_total = isset($_POST['cart_total']) ? floatval($_POST['cart_total']) : 0;

if (empty($coupon_code)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá']);
    exit;
}

// Lấy thông tin coupon
$stmt = $conn->prepare("
    SELECT * FROM coupons 
    WHERE code = ? 
    AND status = 1 
    AND (start_date IS NULL OR start_date <= NOW())
    AND (end_date IS NULL OR end_date >= NOW())
    AND (usage_limit IS NULL OR used_count < usage_limit)
");
$stmt->bind_param("s", $coupon_code);
$stmt->execute();
$coupon = $stmt->get_result()->fetch_assoc();

if (!$coupon) {
    echo json_encode(['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn']);
    exit;
}

// Kiểm tra giá trị đơn hàng tối thiểu
if ($cart_total < $coupon['min_order_value']) {
    echo json_encode([
        'success' => false, 
        'message' => 'Đơn hàng tối thiểu ' . number_format($coupon['min_order_value']) . 'đ để sử dụng mã này'
    ]);
    exit;
}

// Tính discount
if ($coupon['discount_type'] == 'percent') {
    $discount = $cart_total * ($coupon['discount_value'] / 100);
    if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
        $discount = $coupon['max_discount'];
    }
} else {
    $discount = $coupon['discount_value'];
}

$discount = min($discount, $cart_total); // Không được giảm quá tổng đơn hàng

// Lưu vào session
$_SESSION['applied_coupon'] = [
    'code' => $coupon_code,
    'discount' => $discount,
    'coupon_id' => $coupon['id']
];

echo json_encode([
    'success' => true, 
    'message' => 'Áp dụng mã giảm giá thành công!',
    'discount' => $discount,
    'discount_formatted' => number_format($discount) . 'đ',
    'new_total' => $cart_total - $discount,
    'new_total_formatted' => number_format($cart_total - $discount) . 'đ'
]);
