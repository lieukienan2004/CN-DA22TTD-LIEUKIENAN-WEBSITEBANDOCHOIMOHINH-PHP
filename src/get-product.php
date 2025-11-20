<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

$product = getProductById($conn, $product_id);

if (!$product) {
    echo json_encode(['error' => 'Product not found']);
    exit;
}

echo json_encode($product);
?>
