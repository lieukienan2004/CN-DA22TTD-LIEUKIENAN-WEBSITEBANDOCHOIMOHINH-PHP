<?php
require_once 'config/database.php';

// Lấy ID sản phẩm từ URL hoặc mặc định
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 34;

$sql = "SELECT id, name, stock, stock_status FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if ($product) {
    echo "<h2>Thông tin sản phẩm #" . $product['id'] . "</h2>";
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th style='padding: 10px; background: #f0f0f0;'>Trường</th><th style='padding: 10px; background: #f0f0f0;'>Giá trị</th></tr>";
    echo "<tr><td style='padding: 10px;'>ID</td><td style='padding: 10px;'>" . $product['id'] . "</td></tr>";
    echo "<tr><td style='padding: 10px;'>Tên</td><td style='padding: 10px;'>" . htmlspecialchars($product['name']) . "</td></tr>";
    echo "<tr><td style='padding: 10px;'>Số lượng (stock)</td><td style='padding: 10px;'>" . $product['stock'] . "</td></tr>";
    
    $status_color = $product['stock_status'] == 'in_stock' ? 'green' : 'red';
    $status_text = $product['stock_status'] == 'in_stock' ? 'Còn hàng' : 'Hết hàng';
    echo "<tr><td style='padding: 10px;'>Trạng thái (stock_status)</td><td style='padding: 10px; color: {$status_color}; font-weight: bold;'>" . $product['stock_status'] . " ({$status_text})</td></tr>";
    echo "</table>";
    
    // Kiểm tra logic
    echo "<h3>Kiểm tra logic:</h3>";
    $stock_status = isset($product['stock_status']) ? $product['stock_status'] : 'in_stock';
    $is_out_of_stock = ($stock_status == 'out_of_stock');
    
    echo "<p>stock_status = '" . $stock_status . "'</p>";
    echo "<p>is_out_of_stock = " . ($is_out_of_stock ? 'TRUE (Hết hàng)' : 'FALSE (Còn hàng)') . "</p>";
    
    // Form cập nhật
    echo "<hr>";
    echo "<h3>Cập nhật trạng thái:</h3>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='product_id' value='" . $product['id'] . "'>";
    echo "<select name='new_status'>";
    echo "<option value='in_stock' " . ($product['stock_status'] == 'in_stock' ? 'selected' : '') . ">Còn hàng</option>";
    echo "<option value='out_of_stock' " . ($product['stock_status'] == 'out_of_stock' ? 'selected' : '') . ">Hết hàng</option>";
    echo "</select>";
    echo " <button type='submit' name='update'>Cập nhật</button>";
    echo "</form>";
} else {
    echo "<p style='color: red;'>Không tìm thấy sản phẩm #" . $product_id . "</p>";
}

// Xử lý cập nhật
if (isset($_POST['update'])) {
    $product_id = intval($_POST['product_id']);
    $new_status = $_POST['new_status'];
    
    $update_sql = "UPDATE products SET stock_status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $product_id);
    
    if ($update_stmt->execute()) {
        echo "<p style='color: green; margin-top: 20px;'>✅ Đã cập nhật thành công! <a href='check_product_status.php?id=" . $product_id . "'>Tải lại</a></p>";
    } else {
        echo "<p style='color: red; margin-top: 20px;'>❌ Lỗi: " . $conn->error . "</p>";
    }
}

$conn->close();
?>
