<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Sửa Mô tả Sản phẩm</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h2 { color: #333; border-bottom: 3px solid #ec4899; padding-bottom: 10px; }
        .success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .error { background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .info { background: #dbeafe; color: #1e40af; padding: 15px; border-radius: 8px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #f3f4f6; font-weight: 600; }
        .btn { display: inline-block; padding: 10px 20px; background: #ec4899; color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; }
        .btn:hover { background: #db2777; }
        pre { background: #f9fafb; padding: 15px; border-radius: 8px; overflow-x: auto; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h2>🔧 Sửa Mô tả Sản phẩm Bị Lỗi</h2>";

// Bước 1: Tìm các sản phẩm có mô tả lỗi
echo "<h3>Bước 1: Tìm sản phẩm có mô tả lỗi</h3>";

$sql = "SELECT id, name, description, LENGTH(description) as desc_length 
        FROM products 
        WHERE description LIKE '%WWWWWWWWWW%' 
           OR description LIKE '%YYYYYYYYYY%'
           OR LENGTH(description) > 5000";
$result = $conn->query($sql);

$broken_products = [];
if ($result->num_rows > 0) {
    echo "<div class='error'>⚠️ Tìm thấy " . $result->num_rows . " sản phẩm có mô tả lỗi:</div>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Tên sản phẩm</th><th>Độ dài mô tả</th><th>Preview</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $broken_products[] = $row['id'];
        echo "<tr>";
        echo "<td><strong>#" . $row['id'] . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . $row['desc_length'] . " ký tự</td>";
        echo "<td>" . htmlspecialchars(substr($row['description'], 0, 50)) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='success'>✓ Không tìm thấy sản phẩm có mô tả lỗi rõ ràng.</div>";
}

// Bước 2: Danh sách mô tả mẫu đúng
echo "<h3>Bước 2: Cập nhật mô tả</h3>";

$correct_descriptions = [
    'FIFA 365' => "Gói thẻ hình FIFA 365 2025 Adrenalyn từ PANINI - Thương hiệu đến từ nước Ý.

Panini FIFA 365 Adrenalyn XL - đã trở lại. Tuyệt vời hơn bao giờ hết. Cú bạn là người mới chơi Adrenalyn XL - hay đã là một fan cuồng nhiệt, bộ sưu tập mới này sẽ không làm bạn thất vọng!

Điểm nổi bật:
- Bộ sưu tập có hơn 400 thẻ để bạn sưu tầm
- Mỗi Fans' Favourites đều có các phiên bản:
  + Thẻ Thường
  + Thẻ Epic biết: bao gồm phiên bản ĐÁNH SỐ (được sản xuất giới hạn) và phiên bản CHỮ KÝ của cầu thủ

Bộ sản phẩm gồm:
- Mỗi gói bao gồm ngẫu nhiên 6 thẻ hình cầu thủ
- Số lượng khi mua nguyên bộ là 24 sản phẩm",

    'Adrenalyn' => "Gói thẻ hình FIFA 365 2025 Adrenalyn từ PANINI - Thương hiệu đến từ nước Ý.

Panini FIFA 365 Adrenalyn XL - đã trở lại. Tuyệt vời hơn bao giờ hết. Cú bạn là người mới chơi Adrenalyn XL - hay đã là một fan cuồng nhiệt, bộ sưu tập mới này sẽ không làm bạn thất vọng!

Điểm nổi bật:
- Bộ sưu tập có hơn 400 thẻ để bạn sưu tầm
- Mỗi Fans' Favourites đều có các phiên bản:
  + Thẻ Thường
  + Thẻ Epic biết: bao gồm phiên bản ĐÁNH SỐ (được sản xuất giới hạn) và phiên bản CHỮ KÝ của cầu thủ

Bộ sản phẩm gồm:
- Mỗi gói bao gồm ngẫu nhiên 6 thẻ hình cầu thủ
- Số lượng khi mua nguyên bộ là 24 sản phẩm",

    'PANINI' => "Gói thẻ hình FIFA 365 2025 Adrenalyn từ PANINI - Thương hiệu đến từ nước Ý.

Panini FIFA 365 Adrenalyn XL - đã trở lại. Tuyệt vời hơn bao giờ hết. Cú bạn là người mới chơi Adrenalyn XL - hay đã là một fan cuồng nhiệt, bộ sưu tập mới này sẽ không làm bạn thất vọng!

Điểm nổi bật:
- Bộ sưu tập có hơn 400 thẻ để bạn sưu tầm
- Mỗi Fans' Favourites đều có các phiên bản:
  + Thẻ Thường
  + Thẻ Epic biết: bao gồm phiên bản ĐÁNH SỐ (được sản xuất giới hạn) và phiên bản CHỮ KÝ của cầu thủ

Bộ sản phẩm gồm:
- Mỗi gói bao gồm ngẫu nhiên 6 thẻ hình cầu thủ
- Số lượng khi mua nguyên bộ là 24 sản phẩm"
];

$updated_count = 0;

// Sửa các sản phẩm có mô tả lỗi
if (!empty($broken_products)) {
    foreach ($broken_products as $product_id) {
        $product = $conn->query("SELECT name FROM products WHERE id = $product_id")->fetch_assoc();
        $product_name = $product['name'];
        
        // Tìm mô tả phù hợp
        $new_description = null;
        foreach ($correct_descriptions as $keyword => $desc) {
            if (stripos($product_name, $keyword) !== false) {
                $new_description = $desc;
                break;
            }
        }
        
        if ($new_description) {
            $stmt = $conn->prepare("UPDATE products SET description = ? WHERE id = ?");
            $stmt->bind_param("si", $new_description, $product_id);
            
            if ($stmt->execute()) {
                echo "<div class='success'>✓ Đã cập nhật sản phẩm #$product_id: " . htmlspecialchars($product_name) . "</div>";
                $updated_count++;
            } else {
                echo "<div class='error'>✗ Lỗi cập nhật sản phẩm #$product_id: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='info'>ℹ️ Sản phẩm #$product_id không khớp với mẫu mô tả: " . htmlspecialchars($product_name) . "</div>";
        }
    }
}

// Bước 3: Kiểm tra lại
echo "<h3>Bước 3: Kiểm tra kết quả</h3>";

if ($updated_count > 0) {
    echo "<div class='success'>✓ Đã cập nhật thành công $updated_count sản phẩm!</div>";
    
    // Hiển thị các sản phẩm đã sửa
    $ids = implode(',', $broken_products);
    $result = $conn->query("SELECT id, name, description FROM products WHERE id IN ($ids)");
    
    echo "<h4>Mô tả sau khi sửa:</h4>";
    while ($row = $result->fetch_assoc()) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
        echo "<h4>ID: " . $row['id'] . " - " . htmlspecialchars($row['name']) . "</h4>";
        echo "<pre>" . htmlspecialchars($row['description']) . "</pre>";
        echo "</div>";
    }
} else {
    echo "<div class='info'>ℹ️ Không có sản phẩm nào được cập nhật.</div>";
}

// Bước 4: Liệt kê tất cả sản phẩm
echo "<h3>Bước 4: Danh sách tất cả sản phẩm</h3>";
$all_products = $conn->query("SELECT id, name, LEFT(description, 100) as desc_preview, LENGTH(description) as desc_length FROM products ORDER BY id");

echo "<table>";
echo "<tr><th>ID</th><th>Tên</th><th>Độ dài mô tả</th><th>Preview</th></tr>";
while ($row = $all_products->fetch_assoc()) {
    $color = $row['desc_length'] > 1000 ? 'color: red;' : '';
    echo "<tr>";
    echo "<td><strong>#" . $row['id'] . "</strong></td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td style='$color'>" . $row['desc_length'] . "</td>";
    echo "<td>" . htmlspecialchars($row['desc_preview']) . "...</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<a href='admin/products.php' class='btn'>← Quay lại Quản lý Sản phẩm</a>";
echo "<a href='products.php' class='btn'>Xem Trang Sản phẩm</a>";

echo "</div></body></html>";
?>
