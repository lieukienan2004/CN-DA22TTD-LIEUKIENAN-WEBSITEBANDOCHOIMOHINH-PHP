<?php 
require_once __DIR__ . '/language.php';
?>
<header class="header">
    <div class="header-top">
        <div class="header-top-bg"></div>
        <div class="container">
            <div class="header-top-content">
                <div class="header-info">
                    <span class="header-info-item">
                        <i class="fas fa-phone-alt"></i> 
                        <span><?php echo t('hotline'); ?>: 0912431719</span>
                    </span>
                    <span class="header-info-item">
                        <i class="fas fa-envelope"></i> 
                        <span>kienanshop@gmail.com</span>
                    </span>
                </div>
                <div class="header-links">
                    <a href="#" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="#" class="social-link" title="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="header-main">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php" class="logo-link">
                        <img src="assets/images/logo.jpeg" alt="KIENANSHOP Logo" class="logo-image">
                        <div class="logo-text">
                            <span class="logo-name">KIENANSHOP</span>
                            <span class="logo-tagline">MÔ HÌNH CAO CẤP</span>
                        </div>
                    </a>
                </div>
                
                <nav class="nav">
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> <?php echo t('home'); ?></a></li>
                        <li class="dropdown">
                            <a href="products.php"><i class="fas fa-box"></i> <?php echo t('products'); ?> <i class="fas fa-chevron-down"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="products.php?sale=1" class="sale-menu-item"><i class="fas fa-fire"></i> 🔥 Sale Hot</a></li>
                                <li class="menu-divider"></li>
                                <li><a href="products.php?category=1"><?php echo t('gundam'); ?></a></li>
                                <li><a href="products.php?category=2"><?php echo t('car_model'); ?></a></li>
                                <li><a href="products.php?category=3"><?php echo t('airplane'); ?></a></li>
                                <li><a href="products.php?category=4"><?php echo t('warship'); ?></a></li>
                                <li><a href="products.php?category=5"><?php echo t('character'); ?></a></li>
                                <li><a href="products.php?category=6"><?php echo t('lego'); ?></a></li>
                            </ul>
                        </li>
                        <li><a href="about.php"><i class="fas fa-info-circle"></i> <?php echo t('about'); ?></a></li>
                        <li><a href="contact.html"><i class="fas fa-phone"></i> <?php echo t('contact'); ?></a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <?php 
                    // Chỉ hiển thị search box ở trang chủ và trang sản phẩm
                    $current_page = basename($_SERVER['PHP_SELF']);
                    $show_search = in_array($current_page, ['index.php', 'products.php', 'product-detail.php']);
                    
                    if ($show_search): 
                    ?>
                    <div class="search-box" style="position: relative; z-index: 100;">
                        <i class="fas fa-search search-icon" style="pointer-events: none;"></i>
                        <input type="text" 
                               placeholder="<?php echo t('search_placeholder'); ?>" 
                               id="searchInput" 
                               autocomplete="off"
                               style="pointer-events: auto !important; cursor: text !important; user-select: text !important;">
                        <button type="button" class="search-btn" title="Tìm kiếm" style="pointer-events: auto !important;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Dark Mode Toggle -->
                    <button class="icon-btn theme-toggle" id="themeToggle" title="Chuyển chế độ sáng/tối">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </button>
                    
                    <!-- Language Switcher -->
                    <a href="?lang=<?php echo getCurrentLang() == 'vi' ? 'en' : 'vi'; ?>" 
                       class="icon-btn lang-toggle" 
                       title="<?php echo getCurrentLang() == 'vi' ? 'Switch to English' : 'Chuyển sang Tiếng Việt'; ?>">
                        <div class="lang-flag">
                            <img src="https://flagcdn.com/w40/<?php echo getCurrentLang() == 'vi' ? 'vn' : 'gb'; ?>.png" 
                                 alt="<?php echo getCurrentLang() == 'vi' ? 'Tiếng Việt' : 'English'; ?>">
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    
                    <a href="cart.php" class="icon-btn cart-btn" title="<?php echo t('cart'); ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo getCartCount(); ?></span>
                        <span class="cart-pulse"></span>
                    </a>
                    
                    <?php if (isLoggedIn()): 
                        try {
                            $user_stmt = $conn->prepare("SELECT avatar, fullname FROM users WHERE id = ?");
                            $user_stmt->bind_param("i", $_SESSION['user_id']);
                            $user_stmt->execute();
                            $user_data = $user_stmt->get_result()->fetch_assoc();
                            $has_avatar = isset($user_data['avatar']) && $user_data['avatar'] && file_exists($user_data['avatar']);
                        } catch (Exception $e) {
                            $has_avatar = false;
                        }
                    ?>
                    <div class="user-menu-wrapper">
                        <button class="icon-btn user-menu-btn" onclick="toggleUserMenu()" title="<?php echo t('account'); ?>">
                            <?php if (isset($has_avatar) && $has_avatar): ?>
                                <img src="<?php echo htmlspecialchars($user_data['avatar']); ?>" alt="Avatar" 
                                     style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-color);">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </button>
                        
                        <div class="user-dropdown" id="userDropdown">
                            <div class="user-dropdown-header">
                                <div class="user-avatar-large">
                                    <?php if (isset($has_avatar) && $has_avatar): ?>
                                        <img src="<?php echo htmlspecialchars($user_data['avatar']); ?>" alt="Avatar">
                                    <?php else: ?>
                                        <i class="fas fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($user_data['fullname']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
                                </div>
                            </div>
                            
                            <div class="user-dropdown-menu">
                                <a href="account.php" class="user-menu-item">
                                    <i class="fas fa-user"></i>
                                    <span>Thông tin tài khoản</span>
                                </a>
                                <a href="orders.php" class="user-menu-item">
                                    <i class="fas fa-shopping-bag"></i>
                                    <span>Đơn hàng của tôi</span>
                                </a>
                                <a href="notifications.php" class="user-menu-item">
                                    <i class="fas fa-bell"></i>
                                    <span>Thông báo</span>
                                    <?php
                                    // Đếm số thông báo chưa đọc
                                    try {
                                        $notif_stmt = $conn->prepare("
                                            SELECT COUNT(*) as unread_count 
                                            FROM thongbao 
                                            WHERE user_id = ? AND is_read = 0
                                        ");
                                        $notif_stmt->bind_param("i", $_SESSION['user_id']);
                                        $notif_stmt->execute();
                                        $notif_result = $notif_stmt->get_result()->fetch_assoc();
                                        $unread_count = $notif_result['unread_count'];
                                        
                                        if ($unread_count > 0):
                                    ?>
                                    <span class="notification-badge-small"><?php echo $unread_count > 99 ? '99+' : $unread_count; ?></span>
                                    <?php 
                                        endif;
                                    } catch (Exception $e) {
                                        // Bảng chưa tồn tại, bỏ qua
                                    }
                                    ?>
                                </a>
                                <a href="logout.php" class="user-menu-item logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Đăng xuất</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="login.php" class="icon-btn" title="<?php echo t('login'); ?>">
                        <i class="fas fa-sign-in-alt"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>

<link rel="stylesheet" href="assets/css/user-menu.css">

<script>
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const wrapper = document.querySelector('.user-menu-wrapper');
    const dropdown = document.getElementById('userDropdown');
    
    if (wrapper && dropdown && !wrapper.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>

<?php if (isset($_SESSION['user_id'])): ?>
<!-- Auto-check notifications -->
<script src="assets/js/notification-checker.js"></script>
<?php endif; ?>
