<?php
require_once 'config/database.php';

$conn = Database::getInstance()->getConnection();

echo "<h2>Sửa mô tả sản phẩm bị lỗi</h2>";

// Bước 1: Kiểm tra sản phẩm có mô tả lỗi
echo "<h3>1. Kiểm tra sản phẩm có mô tả lỗi (chứa WWWW...):</h3>";
$sql = "SELECT id, name, LEFT(description, 100) as desc_preview, LENGTH(description) as desc_length
        FROM products 
        WHERE description LIKE '%WWWWWWWWWW%'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Tên</th><th>Độ dài</th><th>Preview</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . $row['desc_length'] . "</td>";
        echo "<td>" . htmlspecialchars($row['desc_preview']) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Không tìm thấy sản phẩm có mô tả lỗi.</p>";
}

// Bước 2: Sửa mô tả
echo "<h3>2. Sửa mô tả sản phẩm FIFA/PANINI:</h3>";

$new_description = "Gói thẻ hình FIFA 365 2025 Adrenalyn từ PANINI - Thương hiệu đến từ nước Ý. 

Panini FIFA 365 Adrenalyn XL - đã trở lại. Tuyệt vời hơn bao giờ hết. Cú bạn là người mới chơi Adrenalyn XL - hay đã là một fan cuồng nhiệt, bộ sưu tập mới này sẽ không làm bạn thất vọng!

Điểm nổi bật:
- Bộ sưu tập có hơn 400 thẻ để bạn sưu tầm
- Mỗi Fans' Favourites đều có các phiên bản:
  + Thẻ Thường
  + Thẻ Epic biết: bao gồm phiên bản ĐÁNH SỐ (được sản xuất giới hạn) và phiên bản CHỮ KÝ của cầu thủ

Bộ sản phẩm gồm:
- Mỗi gói bao gồm ngẫu nhiên 6 thẻ hình cầu thủ.
- Số lượng khi mua nguyên bộ là 24 sản phẩm";

$sql = "UPDATE products 
        SET description = ?
        WHERE name LIKE '%FIFA 365%' OR name LIKE '%Adrenalyn%' OR name LIKE '%PANINI%' OR description LIKE '%WWWWWWWWWW%'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $new_description);

if ($stmt->execute()) {
    echo "<p style='color: green;'>✓ Đã cập nhật " . $stmt->affected_rows . " sản phẩm</p>";
} else {
    echo "<p style='color: red;'>✗ Lỗi: " . $conn->error . "</p>";
}

// Bước 3: Kiểm tra lại
echo "<h3>3. Kiểm tra lại sau khi sửa:</h3>";
$sql = "SELECT id, name, description 
        FROM products 
        WHERE name LIKE '%FIFA%' OR name LIKE '%PANINI%' OR name LIKE '%Adrenalyn%'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<h4>ID: " . $row['id'] . " - " . htmlspecialchars($row['name']) . "</h4>";
        echo "<pre style='white-space: pre-wrap;'>" . htmlspecialchars($row['description']) . "</pre>";
        echo "</div>";
    }
} else {
    echo "<p>Không tìm thấy sản phẩm.</p>";
}

echo "<hr>";
echo "<p><a href='products.php'>← Quay lại trang sản phẩm</a></p>";
?>
