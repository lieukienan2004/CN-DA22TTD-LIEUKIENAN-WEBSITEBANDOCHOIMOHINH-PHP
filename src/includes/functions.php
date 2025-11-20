<?php
// Lấy sản phẩm mới nhất
function getLatestProducts($conn, $limit = 8) {
    $sql = "SELECT * FROM products WHERE status = 1 ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Lấy danh mục
function getCategories($conn) {
    $sql = "SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id 
            ORDER BY c.name";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Lấy chi tiết sản phẩm
function getProductById($conn, $id) {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Thêm vào giỏ hàng
function addToCart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

// Lấy số lượng sản phẩm trong giỏ
function getCartCount() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}

// Tính tổng giá trị giỏ hàng
function getCartTotal($conn) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = getProductById($conn, $product_id);
        if ($product) {
            $price = $product['price'] * (1 - $product['discount']/100);
            $total += $price * $quantity;
        }
    }
    return $total;
}

// Xác thực đăng nhập
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Kiểm tra trạng thái tài khoản
    global $conn;
    if ($conn) {
        $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Nếu tài khoản bị khóa, đăng xuất
            if (isset($user['status']) && $user['status'] == 0) {
                session_destroy();
                return false;
            }
        }
    }
    
    return true;
}

// Làm sạch input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
