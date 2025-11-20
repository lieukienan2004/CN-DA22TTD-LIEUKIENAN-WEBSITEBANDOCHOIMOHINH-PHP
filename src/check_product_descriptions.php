<?php
require_once 'config/database.php';

$conn = Database::getInstance()->getConnection();

// Lấy tất cả sản phẩm và mô tả
$sql = "SELECT id, name, description, LENGTH(description) as desc_length FROM products ORDER BY id";
$result = $conn->query($sql);

echo "<h2>Kiểm tra mô tả sản phẩm</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Tên</th><th>Độ dài mô tả</th><th>Mô tả (100 ký tự đầu)</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . $row['desc_length'] . "</td>";
    echo "<td>" . htmlspecialchars(substr($row['description'], 0, 100)) . "...</td>";
    echo "</tr>";
}

echo "</table>";

// Tìm sản phẩm có tên chứa "FIFA" hoặc "PANINI"
echo "<h3>Sản phẩm FIFA/PANINI:</h3>";
$sql = "SELECT id, name, description FROM products WHERE name LIKE '%FIFA%' OR name LIKE '%PANINI%' OR name LIKE '%Adrenalyn%'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<h4>ID: " . $row['id'] . " - " . htmlspecialchars($row['name']) . "</h4>";
        echo "<pre>" . htmlspecialchars($row['description']) . "</pre>";
    }
} else {
    echo "<p>Không tìm thấy sản phẩm FIFA/PANINI</p>";
}
?>
