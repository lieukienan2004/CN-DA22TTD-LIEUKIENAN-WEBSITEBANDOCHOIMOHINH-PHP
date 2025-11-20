<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-cube"></i>
            <span>KIENANSHOP</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="products.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i>
            <span>Sản phẩm</span>
        </a>
        
        <a href="categories.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            <span>Danh mục</span>
        </a>
        
        <a href="orders.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Đơn hàng</span>
        </a>
        
        <a href="users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Khách hàng</span>
        </a>
        
        <a href="contacts.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'contacts.php' ? 'active' : ''; ?>">
            <i class="fas fa-envelope"></i>
            <span>Tin nhắn</span>
            <?php
            $unread_result = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'new'");
            if ($unread_result) {
                $unread = $unread_result->fetch_assoc()['total'];
                if ($unread > 0):
            ?>
            <span class="badge"><?php echo $unread; ?></span>
            <?php 
                endif;
            }
            ?>
        </a>
        
        <a href="coupons.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'coupons.php' ? 'active' : ''; ?>">
            <i class="fas fa-ticket-alt"></i>
            <span>Mã giảm giá</span>
        </a>
        
        <a href="payments.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
            <i class="fas fa-credit-card"></i>
            <span>Thanh toán trực tuyến</span>
            <?php
            $payment_pending_result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE payment_method = 'online' AND payment_status = 'pending'");
            if ($payment_pending_result) {
                $payment_pending = $payment_pending_result->fetch_assoc()['total'];
                if ($payment_pending > 0):
            ?>
            <span class="badge"><?php echo $payment_pending; ?></span>
            <?php 
                endif;
            }
            ?>
        </a>
        
        <a href="notifications.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i>
            <span>Thông báo</span>
            <?php
            $notif_unread_result = $conn->query("SELECT COUNT(*) as total FROM thongbao WHERE user_type = 'admin' AND is_read = 0");
            if ($notif_unread_result) {
                $notif_unread = $notif_unread_result->fetch_assoc()['total'];
                if ($notif_unread > 0):
            ?>
            <span class="badge"><?php echo $notif_unread; ?></span>
            <?php 
                endif;
            }
            ?>
        </a>
        
        <a href="send_notification.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'send_notification.php' ? 'active' : ''; ?>">
            <i class="fas fa-paper-plane"></i>
            <span>Gửi thông báo</span>
        </a>
        
        <a href="settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Cài đặt</span>
        </a>
        
        <a href="logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Đăng xuất</span>
        </a>
    </nav>
</aside>
