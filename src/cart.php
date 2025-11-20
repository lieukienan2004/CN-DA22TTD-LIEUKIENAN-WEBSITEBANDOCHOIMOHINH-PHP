<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            if ($quantity > 0) {
                // Kiểm tra tồn kho
                $product = getProductById($conn, $product_id);
                if ($product) {
                    $stock_status = isset($product['stock_status']) ? $product['stock_status'] : 'in_stock';
                    
                    if ($stock_status == 'out_of_stock') {
                        $_SESSION['error_message'] = 'Sản phẩm "' . $product['name'] . '" đã hết hàng';
                        unset($_SESSION['cart'][$product_id]);
                    } elseif ($quantity > $product['stock']) {
                        $_SESSION['error_message'] = 'Sản phẩm "' . $product['name'] . '" chỉ còn ' . $product['stock'] . ' sản phẩm';
                        $_SESSION['cart'][$product_id] = $product['stock'];
                    } else {
                        $_SESSION['cart'][$product_id] = intval($quantity);
                    }
                }
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }
    
    if (isset($_POST['remove_item'])) {
        $product_id = intval($_POST['remove_item']);
        unset($_SESSION['cart'][$product_id]);
    }
}

$cart_items = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = getProductById($conn, $product_id);
        if ($product) {
            $product['quantity'] = $quantity;
            $cart_items[] = $product;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - KIENANSHOP</title>
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/premium-ui.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container" style="padding: 40px 20px;">
        <h1 style="margin-bottom: 30px;">Giỏ Hàng</h1>
        
        <?php if (isset($_SESSION['error_message'])): ?>
        <div style="background: #fee; border: 1px solid #fcc; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
        <div style="text-align: center; padding: 60px 0;">
            <i class="fas fa-shopping-cart" style="font-size: 80px; color: #d1d5db; margin-bottom: 20px;"></i>
            <h2>Giỏ hàng trống</h2>
            <p style="margin: 20px 0;">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
            <a href="products.php" class="btn btn-primary">Tiếp tục mua sắm</a>
        </div>
        <?php else: ?>
        <form method="POST">
            <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <thead style="background: var(--light-gray);">
                    <tr>
                        <th style="padding: 15px; text-align: left;">Sản phẩm</th>
                        <th style="padding: 15px;">Đơn giá</th>
                        <th style="padding: 15px;">Số lượng</th>
                        <th style="padding: 15px;">Thành tiền</th>
                        <th style="padding: 15px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($cart_items as $item): 
                        $stock_status = isset($item['stock_status']) ? $item['stock_status'] : 'in_stock';
                        $is_out_of_stock = ($stock_status == 'out_of_stock');
                        
                        $price = $item['price'] * (1 - $item['discount']/100);
                        $subtotal = $price * $item['quantity'];
                        
                        if (!$is_out_of_stock) {
                            $total += $subtotal;
                        }
                    ?>
                    <tr style="border-bottom: 1px solid var(--border-color); <?php echo $is_out_of_stock ? 'opacity: 0.5;' : ''; ?>">
                        <td style="padding: 15px;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                                <div>
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <?php if ($is_out_of_stock): ?>
                                    <span style="color: #c33; font-size: 14px;">
                                        <i class="fas fa-exclamation-circle"></i> Hết hàng
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php echo number_format($price); ?>đ
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php if ($is_out_of_stock): ?>
                            <input type="number" value="0" disabled
                                   style="width: 60px; padding: 5px; border: 1px solid var(--border-color); border-radius: 5px; text-align: center;">
                            <?php else: ?>
                            <input type="number" name="quantity[<?php echo $item['id']; ?>]" 
                                   value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>"
                                   style="width: 60px; padding: 5px; border: 1px solid var(--border-color); border-radius: 5px; text-align: center;">
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: center; font-weight: bold; color: var(--danger-color);">
                            <?php echo $is_out_of_stock ? '-' : number_format($subtotal) . 'đ'; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <button type="submit" name="remove_item" value="<?php echo $item['id']; ?>"
                                    style="background: none; border: none; color: var(--danger-color); cursor: pointer; font-size: 18px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center;">
                <button type="submit" name="update_cart" class="btn btn-secondary" style="width: auto;">
                    <i class="fas fa-sync"></i> Cập nhật giỏ hàng
                </button>
                
                <div style="text-align: right;">
                    <p style="font-size: 24px; margin-bottom: 15px;">
                        Tổng cộng: <span style="color: var(--danger-color); font-weight: bold;"><?php echo number_format($total); ?>đ</span>
                    </p>
                    <a href="checkout.php" class="btn btn-primary" style="width: auto; padding: 15px 40px;">
                        <i class="fas fa-credit-card"></i> Thanh toán
                    </a>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
