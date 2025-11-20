<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? cleanInput($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$query_safe = $conn->real_escape_string($query);

$sql = "SELECT DISTINCT name, id, image, price, discount 
        FROM products 
        WHERE status = 1 
        AND (name LIKE '%" . $query_safe . "%' OR description LIKE '%" . $query_safe . "%')
        LIMIT 5";

$result = $conn->query($sql);
$suggestions = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $final_price = $row['price'] * (1 - $row['discount']/100);
        $suggestions[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'image' => $row['image'],
            'price' => number_format($final_price) . 'đ'
        ];
    }
}

echo json_encode($suggestions);
