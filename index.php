<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$products = getLatestProducts($conn, 8);
$categories = getCategories($conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KIENANSHOP - Cửa Hàng Mô Hình - Đồ Chơi Cao Cấp</title>
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/color-enhancements.css?v=1.0">
    <link rel="stylesheet" href="assets/css/footer.css?v=2.0">
    <link rel="stylesheet" href="assets/css/modal.css?v=2.0">
    <link rel="stylesheet" href="assets/css/dark-mode.css?v=1.0">
    <link rel="stylesheet" href="assets/css/search.css?v=1.0">
    <link rel="stylesheet" href="assets/css/search-fix.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/premium-ui.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/premium-modern.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Snowflake Animation */
        .sakura-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
            overflow: hidden;
        }

        .sakura {
            position: absolute;
            top: -20px;
            font-size: 25px;
            opacity: 0.9;
            animation: sakuraFall linear infinite;
            filter: drop-shadow(0 2px 5px rgba(255, 105, 180, 0.4));
        }

        .sakura::before {
            content: '🌸';
        }

        @keyframes sakuraFall {
            0% {
                transform: translateY(0) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            20% {
                transform: translateY(20vh) translateX(10px) rotate(72deg);
            }
            40% {
                transform: translateY(40vh) translateX(-15px) rotate(144deg);
            }
            60% {
                transform: translateY(60vh) translateX(20px) rotate(216deg);
            }
            80% {
                transform: translateY(80vh) translateX(-10px) rotate(288deg);
            }
            90% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(105vh) translateX(5px) rotate(360deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Snowflake Container -->
    <div class="sakura-container" id="sakuraContainer"></div>
    
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Banner Slider Section -->
    <section class="hero-banner">
        <div class="container">
            <div class="banner-slider">
                <!-- Previous Button -->
                <button class="slider-btn prev-btn" onclick="changeBanner(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <!-- Banner Wrapper -->
                <a href="products.php?sale=1" id="bannerLink" class="banner-wrapper" style="text-decoration: none; display: block;">
                    <div class="banner-image">
                        <img id="bannerImage" src="assets/images/salene.jpeg" alt="Banner Sale" class="banner-img"> 
                    </div>
                </a>
                
                <!-- Next Button -->
                <button class="slider-btn next-btn" onclick="changeBanner(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <!-- Dots Indicator -->
                <div class="banner-dots" id="bannerDots"></div>
            </div>
        </div>
    </section>

    <script>
        // Banner slider configuration with links
        const bannerData = [
            {
                image: 'assets/images/salene.jpeg',
                link: 'products.php?sale=1',
                alt: 'Sale Hot - Giảm đến 35%'
            },
            {
                image: 'assets/images/sale2ne.jpeg',
                link: 'products.php?category=1',  // Gundam category
                alt: 'Gundam - Hàng mới về'
            },
            {
                image: 'assets/images/sale3ne.jpeg',
                link: 'products.php?category=5',  // Panini category
                alt: 'Panini - Hàng mới về'
            }
        ];
        
        let currentBannerIndex = 0;
        let bannerInterval;
        
        // Initialize dots
        function initBannerDots() {
            const dotsContainer = document.getElementById('bannerDots');
            dotsContainer.innerHTML = '';
            
            bannerData.forEach((_, index) => {
                const dot = document.createElement('span');
                dot.className = 'banner-dot' + (index === 0 ? ' active' : '');
                dot.onclick = () => goToBanner(index);
                dotsContainer.appendChild(dot);
            });
        }
        
        // Change banner
        function changeBanner(direction) {
            currentBannerIndex += direction;
            
            if (currentBannerIndex >= bannerData.length) {
                currentBannerIndex = 0;
            } else if (currentBannerIndex < 0) {
                currentBannerIndex = bannerData.length - 1;
            }
            
            updateBanner();
            resetBannerInterval();
        }
        
        // Go to specific banner
        function goToBanner(index) {
            currentBannerIndex = index;
            updateBanner();
            resetBannerInterval();
        }
        
        // Update banner display
        function updateBanner() {
            const bannerImg = document.getElementById('bannerImage');
            const bannerLink = document.getElementById('bannerLink');
            const dots = document.querySelectorAll('.banner-dot');
            
            // Add fade effect
            bannerImg.style.opacity = '0';
            
            setTimeout(() => {
                const currentBanner = bannerData[currentBannerIndex];
                bannerImg.src = currentBanner.image;
                bannerImg.alt = currentBanner.alt;
                bannerLink.href = currentBanner.link;
                bannerImg.style.opacity = '1';
            }, 200);
            
            // Update dots
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentBannerIndex);
            });
        }
        
        // Auto slide
        function startBannerAutoSlide() {
            bannerInterval = setInterval(() => {
                changeBanner(1);
            }, 3000); // Change every 3 seconds
        }
        
        // Reset interval
        function resetBannerInterval() {
            clearInterval(bannerInterval);
            startBannerAutoSlide();
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            initBannerDots();
            startBannerAutoSlide();
        });
    </script>

    <!-- Categories -->
    <section class="categories">
        <div class="container">
            <h2 class="section-title"><?php echo t('product_categories'); ?></h2>
            <div class="category-grid">
                <!-- Sale Category - Special -->
                <a href="products.php?sale=1" class="category-card sale-card">
                    <div class="category-icon sale-icon">
                        <i class="fas fa-fire-alt"></i>
                    </div>
                    <h3>🔥 Sale Hot</h3>
                    <span class="sale-badge">Giảm đến 35%</span>
                </a>
                
                <?php foreach ($categories as $category): 
                    $cat_key_map = [
                        'Gundam' => 'gundam',
                        'Xe Mô Hình' => 'car_model',
                        'Máy Bay' => 'airplane',
                        'Tàu Chiến' => 'warship',
                        'Panini' => 'character',
                        'Lego' => 'lego'
                    ];
                    $cat_key = isset($cat_key_map[$category['name']]) ? $cat_key_map[$category['name']] : '';
                    $cat_display = $cat_key ? t($cat_key) : htmlspecialchars($category['name']);
                ?>
                <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card">
                    <div class="category-icon">
                        <i class="<?php echo $category['icon']; ?>"></i>
                    </div>
                    <h3><?php echo $cat_display; ?></h3>
                    <span><?php echo $category['product_count']; ?> <?php echo t('products_count'); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="products">
        <div class="container">
            <h2 class="section-title"><?php echo t('featured_products'); ?></h2>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php if (isset($product['is_new']) && $product['is_new'] == 1): ?>
                        <span class="badge-new">NEW</span>
                        <?php endif; ?>
                        <?php if ($product['discount'] > 0): ?>
                        <span class="badge">-<?php echo $product['discount']; ?>%</span>
                        <?php endif; ?>
                        <?php 
                        $stock_status = isset($product['stock_status']) ? $product['stock_status'] : 'in_stock';
                        $is_out_of_stock = ($stock_status == 'out_of_stock');
                        if ($is_out_of_stock): 
                        ?>
                        <div class="out-of-stock-overlay">
                            <span class="out-of-stock-badge">HẾT HÀNG</span>
                        </div>
                        <?php endif; ?>
                        <?php
                        $is_favorited = false;
                        if (isLoggedIn()) {
                            $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
                            $stmt->bind_param("ii", $_SESSION['user_id'], $product['id']);
                            $stmt->execute();
                            $is_favorited = $stmt->get_result()->num_rows > 0;
                        }
                        ?>
                        <button class="wishlist-btn <?php echo $is_favorited ? 'active' : ''; ?>" 
                                onclick="toggleWishlist(<?php echo $product['id']; ?>, this)" 
                                title="<?php echo $is_favorited ? 'Bỏ yêu thích' : 'Thêm vào yêu thích'; ?>">
                            <i class="<?php echo $is_favorited ? 'fas' : 'far'; ?> fa-heart"></i>
                        </button>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="product-price">
                            <?php if ($product['discount'] > 0): ?>
                            <span class="old-price"><?php echo number_format($product['price']); ?>đ</span>
                            <span class="new-price"><?php echo number_format($product['price'] * (1 - $product['discount']/100)); ?>đ</span>
                            <?php else: ?>
                            <span class="new-price"><?php echo number_format($product['price']); ?>đ</span>
                            <?php endif; ?>
                        </div>
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary" target="_self"><?php echo t('view_details'); ?></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <!-- Product Modal -->
    <div id="productModal" class="modal-overlay">
        <div class="product-modal">
            <div class="modal-header-actions">
                <a href="cart.php" class="modal-cart-btn" title="Giỏ hàng">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo getCartCount(); ?></span>
                </a>
                <button class="modal-close" onclick="closeProductModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-content">
                <div class="modal-image">
                    <img id="modalImage" src="" alt="Product">
                    <div id="modalDiscountBadge" class="modal-discount-badge" style="display: none;"></div>
                </div>
                
                <div class="modal-info">
                    <h2 id="modalTitle" class="modal-title"></h2>
                    
                    <div class="modal-meta">
                        <div class="modal-category">
                            <span>Mã sản phẩm:</span>
                            <span id="modalSKU">-</span>
                        </div>
                        <div class="modal-category">
                            <span>Thương hiệu:</span>
                            <span id="modalCategory"></span>
                        </div>
                        <div class="modal-category">
                            <span>Loại:</span>
                            <span>Khác</span>
                        </div>
                    </div>
                    
                    <div class="modal-price-section">
                        <div class="modal-price-label">Giá:</div>
                        <div id="modalPriceSection"></div>
                    </div>
                    
                    <div class="modal-quantity-section">
                        <div class="modal-quantity">
                            <label>Số lượng:</label>
                            <div class="modal-qty-input">
                                <button type="button" class="modal-qty-btn" onclick="decreaseModalQty()">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" id="modalQuantity" value="1" min="1" readonly>
                                <button type="button" class="modal-qty-btn" onclick="increaseModalQty()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button class="modal-btn modal-btn-cart" onclick="addToCartFromModal()">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Thêm vào giỏ</span>
                        </button>
                        <button class="modal-btn modal-btn-buy" onclick="buyNowFromModal()">
                            <span>Mua ngay</span>
                        </button>
                    </div>
                    
                    <div class="modal-features">
                        <div class="modal-features-title">Chính sách bán hàng</div>
                        <div class="modal-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Cam kết sản phẩm như hình</span>
                        </div>
                        <div class="modal-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Giao hàng toàn quốc, Freeship đơn từ 499k</span>
                        </div>
                        <div class="modal-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Hỗ trợ giao hóa tốc nhanh tại TP.HCM</span>
                        </div>
                    </div>
                    
                    <div class="modal-description">
                        <h4>Mô tả sản phẩm</h4>
                        <p id="modalDescription"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script src="assets/js/prevent-new-tab.js"></script>
    <script>
        // Cherry Blossom Animation
        function createSakura() {
            const sakuraContainer = document.getElementById('sakuraContainer');
            const sakura = document.createElement('div');
            sakura.classList.add('sakura');
            
            // Random position across entire screen width
            const startPosition = Math.random() * window.innerWidth;
            sakura.style.left = startPosition + 'px';
            
            // Random size variation
            const size = Math.random() * 10 + 18; // 18-28px
            sakura.style.fontSize = size + 'px';
            
            // Random fall duration
            const fallDuration = Math.random() * 8 + 10; // 10-18 seconds
            sakura.style.animationDuration = `${fallDuration}s`;
            
            // NO DELAY - start falling immediately
            sakura.style.animationDelay = '0s';
            
            sakuraContainer.appendChild(sakura);
            
            // Remove after animation
            setTimeout(() => {
                sakura.remove();
            }, fallDuration * 1000);
        }
        
        function startSakuraAnimation() {
            // Create initial petals
            for (let i = 0; i < 20; i++) {
                setTimeout(() => createSakura(), i * 300);
            }
            
            // Continue creating petals
            setInterval(() => {
                createSakura();
            }, 600);
        }
        
        window.addEventListener('load', startSakuraAnimation);
    </script>
    <script>
        function toggleWishlist(productId, button) {
            fetch('toggle_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const icon = button.querySelector('i');
                    if (data.action === 'added') {
                        button.classList.add('active');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        button.title = 'Bỏ yêu thích';
                    } else {
                        button.classList.remove('active');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        button.title = 'Thêm vào yêu thích';
                    }
                    // Hiển thị thông báo nhỏ
                    showNotification(data.message);
                } else {
                    if (data.message.includes('đăng nhập')) {
                        if (confirm(data.message + '. Bạn có muốn đăng nhập không?')) {
                            window.location.href = 'login.php';
                        }
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            });
        }

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); color: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3); z-index: 9999; animation: slideIn 0.3s ease;';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        }
    </script>
    <style>
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    </style>
</body>
</html>
