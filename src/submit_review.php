<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
$user_id = $_SESSION['user_id'];

if ($product_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Kiểm tra xem đã đánh giá chưa
$check_review = $conn->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ?");
$check_review->bind_param("ii", $user_id, $product_id);
$check_review->execute();

if ($check_review->get_result()->num_rows > 0) {
    // Update existing review
    $stmt = $conn->prepare("UPDATE product_reviews SET rating = ?, comment = ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("isii", $rating, $comment, $user_id, $product_id);
} else {
    // Insert new review
    $stmt = $conn->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn đã đánh giá!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại.']);
}
