<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = getProductById($conn, $product_id);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Lấy reviews
$reviews_query = $conn->prepare("
    SELECT r.*, u.fullname, u.avatar 
    FROM product_reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$reviews_query->bind_param("i", $product_id);
$reviews_query->execute();
$reviews = $reviews_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Tính rating trung bình
$avg_rating = 0;
$total_reviews = count($reviews);
if ($total_reviews > 0) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $avg_rating = round($sum / $total_reviews, 1);
}

// Kiểm tra user đã đăng nhập chưa
$can_review = isLoggedIn();

// Kiểm tra user đã đánh giá chưa
if ($can_review) {
    $check_existing = $conn->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ?");
    $check_existing->bind_param("ii", $_SESSION['user_id'], $product_id);
    $check_existing->execute();
    $has_reviewed = $check_existing->get_result()->num_rows > 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kiểm tra đăng nhập trước khi thêm vào giỏ
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php?message=Vui lòng đăng nhập để mua hàng');
        exit;
    }
    
    // Kiểm tra còn hàng không
    $stock_status = isset($product['stock_status']) ? $product['stock_status'] : 'in_stock';
    if ($stock_status == 'out_of_stock') {
        $_SESSION['error_message'] = 'Sản phẩm đã hết hàng';
        header('Location: product-detail.php?id=' . $product_id);
        exit;
    }
    
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Kiểm tra số lượng yêu cầu có vượt quá tồn kho không
    if ($quantity > $product['stock']) {
        $_SESSION['error_message'] = 'Số lượng yêu cầu vượt quá tồn kho';
        header('Location: product-detail.php?id=' . $product_id);
        exit;
    }
    
    addToCart($product_id, $quantity);
    
    // Nếu bấm "Mua ngay" thì chuyển đến checkout, còn không thì đến cart
    if (isset($_POST['buy_now'])) {
        header('Location: checkout.php');
    } else {
        header('Location: cart.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - KIENANSHOP</title>
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/product-detail.css">
    <link rel="stylesheet" href="assets/css/premium-ui.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
            <i class="fas fa-chevron-right"></i>
            <a href="products.php">Sản phẩm</a>
            <i class="fas fa-chevron-right"></i>
            <a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>
    </div>
    
    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="container" style="margin-top: 20px;">
        <div style="background: #fee; border: 1px solid #fcc; color: #c33; padding: 15px; border-radius: 8px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="product-detail-container">
        <div class="container">
            <div class="product-detail-grid">
                <!-- Product Images -->
                <div class="product-images">
                    <div class="main-image">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             id="mainImage">
                        <?php if ($product['discount'] > 0): ?>
                        <div class="discount-badge">
                            <span>-<?php echo $product['discount']; ?>%</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="product-info-detail">
                    <div class="product-header">
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        <div class="product-meta">
                            <span class="category-tag">
                                <i class="fas fa-tag"></i>
                                <a href="products.php?category=<?php echo $product['category_id']; ?>">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </a>
                            </span>
                            <?php 
                            $stock_status = isset($product['stock_status']) ? $product['stock_status'] : 'in_stock';
                            ?>
                            <span class="stock-status <?php echo $stock_status == 'in_stock' ? 'in-stock' : 'out-stock'; ?>">
                                <i class="fas fa-circle"></i>
                                <?php echo $stock_status == 'in_stock' ? 'Còn hàng' : 'Hết hàng'; ?>
                            </span>
                        </div>
                        
                        <!-- Rating -->
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                            <div class="product-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?php echo $i <= $avg_rating ? 'fas' : 'far'; ?> fa-star" style="color: #fbbf24;"></i>
                                <?php endfor; ?>
                            </div>
                            <span style="color: var(--text-light);">
                                <?php echo $avg_rating; ?>/5 (<?php echo $total_reviews; ?> đánh giá)
                            </span>
                        </div>
                    </div>
                    
                    <!-- Price -->
                    <div class="price-section">
                        <?php if ($product['discount'] > 0): ?>
                        <div class="price-group">
                            <span class="original-price"><?php echo number_format($product['price']); ?>đ</span>
                            <span class="discount-percent">-<?php echo $product['discount']; ?>%</span>
                        </div>
                        <div class="current-price"><?php echo number_format($product['price'] * (1 - $product['discount']/100)); ?>đ</div>
                        <div class="save-amount">Tiết kiệm: <?php echo number_format($product['price'] * $product['discount']/100); ?>đ</div>
                        <?php else: ?>
                        <div class="current-price"><?php echo number_format($product['price']); ?>đ</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Description -->
                    <div class="description-section">
                        <h3><i class="fas fa-info-circle"></i> Mô tả sản phẩm</h3>
                        <p><?php 
                        $description = $product['description'];
                        // Loại bỏ slashes nếu có
                        $description = stripslashes($description);
                        // Chuyển đổi các ký tự escaped thành ký tự thực
                        $description = str_replace(['\\r\\n', '\\r', '\\n'], "\n", $description);
                        echo nl2br(htmlspecialchars($description)); 
                        ?></p>
                    </div>
                    
                    <!-- Features -->
                    <div class="features-section">
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <strong>Bảo hành chính hãng</strong>
                                <span>Đổi trả trong 7 ngày</span>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-truck"></i>
                            <div>
                                <strong>Giao hàng toàn quốc</strong>
                                <span>Miễn phí với đơn > 500k</span>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-headset"></i>
                            <div>
                                <strong>Hỗ trợ 24/7</strong>
                                <span>Hotline: 0912431719</span>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-certificate"></i>
                            <div>
                                <strong>Sản phẩm chính hãng</strong>
                                <span>100% authentic</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add to Cart Form -->
                    <form method="POST" class="add-to-cart-form">
                        <div class="quantity-selector">
                            <label>Số lượng:</label>
                            <div class="quantity-input">
                                <button type="button" class="qty-btn" onclick="decreaseQty()">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly>
                                <button type="button" class="qty-btn" onclick="increaseQty()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <span class="stock-info">Còn <?php echo $product['stock']; ?> sản phẩm</span>
                        </div>
                        
                        <div class="action-buttons">
                            <?php 
                            $stock_status = isset($product['stock_status']) ? $product['stock_status'] : 'in_stock';
                            $is_out_of_stock = ($stock_status == 'out_of_stock');
                            
                            if ($is_out_of_stock): 
                            ?>
                            <button type="button" class="btn btn-add-cart" disabled style="opacity: 0.6; cursor: not-allowed;">
                                <i class="fas fa-ban"></i>
                                <span>Hết hàng</span>
                            </button>
                            <?php else: ?>
                            <button type="submit" name="add_to_cart" class="btn btn-add-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Thêm vào giỏ hàng</span>
                            </button>
                            <button type="submit" name="buy_now" class="btn btn-buy-now">
                                <i class="fas fa-bolt"></i>
                                <span>Mua ngay</span>
                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Product Details Tabs -->
            <div class="product-tabs">
                <div class="tab-headers">
                    <button class="tab-btn active" onclick="openTab(event, 'specifications')">
                        <i class="fas fa-list"></i> Thông số kỹ thuật
                    </button>
                    <button class="tab-btn" onclick="openTab(event, 'shipping')">
                        <i class="fas fa-shipping-fast"></i> Vận chuyển
                    </button>
                    <button class="tab-btn" onclick="openTab(event, 'warranty')">
                        <i class="fas fa-shield-alt"></i> Bảo hành
                    </button>
                </div>
                
                <div id="specifications" class="tab-content active">
                    <h3>Thông số kỹ thuật</h3>
                    <table class="specs-table">
                        <tr>
                            <td>Tên sản phẩm</td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                        </tr>
                        <tr>
                            <td>Danh mục</td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        </tr>
                        <tr>
                            <td>Tình trạng</td>
                            <td><?php echo $product['stock'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?></td>
                        </tr>
                        <tr>
                            <td>Xuất xứ</td>
                            <td>Nhật Bản</td>
                        </tr>
                        <tr>
                            <td>Chất liệu</td>
                            <td>Nhựa ABS cao cấp</td>
                        </tr>
                    </table>
                </div>
                
                <div id="shipping" class="tab-content">
                    <h3>Chính sách vận chuyển</h3>
                    <ul class="policy-list">
                        <li><i class="fas fa-check"></i> Giao hàng toàn quốc</li>
                        <li><i class="fas fa-check"></i> Miễn phí vận chuyển cho đơn hàng từ 500.000đ</li>
                        <li><i class="fas fa-check"></i> Thời gian giao hàng: 2-5 ngày làm việc</li>
                        <li><i class="fas fa-check"></i> Kiểm tra hàng trước khi thanh toán</li>
                        <li><i class="fas fa-check"></i> Đóng gói cẩn thận, chắc chắn</li>
                    </ul>
                </div>
                
                <div id="warranty" class="tab-content">
                    <h3>Chính sách bảo hành</h3>
                    <ul class="policy-list">
                        <li><i class="fas fa-check"></i> Bảo hành chính hãng 12 tháng</li>
                        <li><i class="fas fa-check"></i> Đổi trả trong 7 ngày nếu có lỗi từ nhà sản xuất</li>
                        <li><i class="fas fa-check"></i> Hỗ trợ sửa chữa, bảo dưỡng trọn đời</li>
                        <li><i class="fas fa-check"></i> Cam kết sản phẩm chính hãng 100%</li>
                    </ul>
                </div>
            </div>
            
            <!-- Reviews Section -->
            <div class="reviews-section" style="margin-top: 40px; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                <h3 style="margin-bottom: 20px; font-size: 24px;">
                    <i class="fas fa-star" style="color: #fbbf24;"></i> 
                    Đánh giá sản phẩm (<?php echo $total_reviews; ?>)
                </h3>
                
                <?php if (!isLoggedIn()): ?>
                <div style="background: #fef3c7; padding: 20px; border-radius: 10px; margin-bottom: 30px; text-align: center;">
                    <i class="fas fa-info-circle" style="color: #f59e0b; font-size: 24px; margin-bottom: 10px;"></i>
                    <p style="margin: 0;">Vui lòng <a href="login.php" style="color: var(--primary-color); font-weight: 600;">đăng nhập</a> để đánh giá sản phẩm</p>
                </div>
                <?php elseif (isset($has_reviewed) && $has_reviewed): ?>
                <div style="background: #d1fae5; padding: 20px; border-radius: 10px; margin-bottom: 30px; text-align: center;">
                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 24px; margin-bottom: 10px;"></i>
                    <p style="margin: 0;">Bạn đã đánh giá sản phẩm này rồi</p>
                </div>
                <?php else: ?>
                <div class="review-form" style="background: var(--light-gray); padding: 20px; border-radius: 10px; margin-bottom: 30px;">
                    <h4 style="margin-bottom: 15px;">Viết đánh giá của bạn</h4>
                    <form id="reviewForm">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Đánh giá của bạn:</label>
                            <div class="star-rating" style="font-size: 30px;">
                                <i class="far fa-star" data-rating="1"></i>
                                <i class="far fa-star" data-rating="2"></i>
                                <i class="far fa-star" data-rating="3"></i>
                                <i class="far fa-star" data-rating="4"></i>
                                <i class="far fa-star" data-rating="5"></i>
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" required>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Nhận xét:</label>
                            <textarea name="comment" rows="4" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..."
                                      style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px;"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: auto;">
                            <i class="fas fa-paper-plane"></i> Gửi đánh giá
                        </button>
                    </form>
                </div>
                <?php endif; ?>
                
                <!-- Reviews List -->
                <div class="reviews-list">
                    <?php if (empty($reviews)): ?>
                    <p style="text-align: center; color: var(--text-light); padding: 40px 0;">
                        <i class="fas fa-comment-slash" style="font-size: 50px; display: block; margin-bottom: 15px;"></i>
                        Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá sản phẩm này!
                    </p>
                    <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-item" style="border-bottom: 1px solid var(--border-color); padding: 20px 0;">
                        <div style="display: flex; gap: 15px;">
                            <div style="flex-shrink: 0;">
                                <?php if ($review['avatar']): ?>
                                <img src="<?php echo htmlspecialchars($review['avatar']); ?>" alt="Avatar" 
                                     style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--gradient-1); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                    <?php echo strtoupper(substr($review['fullname'], 0, 1)); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                    <div>
                                        <strong><?php echo htmlspecialchars($review['fullname']); ?></strong>
                                        <div class="review-rating" style="margin-top: 5px;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="<?php echo $i <= $review['rating'] ? 'fas' : 'far'; ?> fa-star" style="color: #fbbf24; font-size: 14px;"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <span style="color: var(--text-light); font-size: 14px;">
                                        <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                                    </span>
                                </div>
                                <?php if ($review['comment']): ?>
                                <p style="color: var(--text-color); line-height: 1.6;">
                                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        function increaseQty() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.max);
            const current = parseInt(input.value);
            if (current < max) {
                input.value = current + 1;
            }
        }
        
        function decreaseQty() {
            const input = document.getElementById('quantity');
            const min = parseInt(input.min);
            const current = parseInt(input.value);
            if (current > min) {
                input.value = current - 1;
            }
        }

        function openTab(evt, tabName) {
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            const tabBtns = document.getElementsByClassName('tab-btn');
            for (let i = 0; i < tabBtns.length; i++) {
                tabBtns[i].classList.remove('active');
            }
            
            document.getElementById(tabName).classList.add('active');
            evt.currentTarget.classList.add('active');
        }
        
        // Star Rating
        document.querySelectorAll('.star-rating i').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                document.getElementById('ratingInput').value = rating;
                
                document.querySelectorAll('.star-rating i').forEach((s, index) => {
                    if (index < rating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                        s.style.color = '#fbbf24';
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                        s.style.color = '#d1d5db';
                    }
                });
            });
            
            star.addEventListener('mouseenter', function() {
                const rating = this.dataset.rating;
                document.querySelectorAll('.star-rating i').forEach((s, index) => {
                    if (index < rating) {
                        s.style.color = '#fbbf24';
                    } else {
                        s.style.color = '#d1d5db';
                    }
                });
            });
        });
        
        document.querySelector('.star-rating').addEventListener('mouseleave', function() {
            const currentRating = document.getElementById('ratingInput').value || 0;
            document.querySelectorAll('.star-rating i').forEach((s, index) => {
                if (index < currentRating) {
                    s.style.color = '#fbbf24';
                } else {
                    s.style.color = '#d1d5db';
                }
            });
        });
        
        // Submit Review
        document.getElementById('reviewForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const rating = document.getElementById('ratingInput').value;
            if (!rating) {
                alert('Vui lòng chọn số sao đánh giá');
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('submit_review.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            });
        });
        
        // Image Zoom
        const mainImage = document.getElementById('mainImage');
        if (mainImage) {
            mainImage.style.cursor = 'zoom-in';
            mainImage.addEventListener('click', function() {
                const modal = document.createElement('div');
                modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; display: flex; align-items: center; justify-content: center; cursor: zoom-out;';
                
                const img = document.createElement('img');
                img.src = this.src;
                img.style.cssText = 'max-width: 90%; max-height: 90%; object-fit: contain;';
                
                modal.appendChild(img);
                document.body.appendChild(modal);
                
                modal.addEventListener('click', function() {
                    document.body.removeChild(modal);
                });
            });
        }
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>
