<?php
ob_start(); // Bắt đầu output buffering để tránh lỗi header
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Ngăn cache trang
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Xử lý đánh dấu đã đọc
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notif_id = (int)$_GET['mark_read'];
    $stmt = $conn->prepare("UPDATE thongbao SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notif_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        // Kiểm tra xem có cập nhật được không
        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Đã đánh dấu thông báo là đã đọc!";
        }
    }
    $stmt->close();
    
    // Redirect về trang với filter hiện tại và timestamp để tránh cache
    $current_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    header('Location: notifications.php?filter=' . $current_filter . '&_=' . time());
    exit;
}

// Xử lý đánh dấu tất cả đã đọc
if (isset($_GET['mark_all_read'])) {
    $stmt = $conn->prepare("UPDATE thongbao SET is_read = 1 WHERE user_id = ? AND user_type = 'admin' AND type IN ('promotion', 'system', 'order', 'product')");
    $stmt->bind_param("i", $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        if ($affected > 0) {
            $_SESSION['success_message'] = "Đã đánh dấu $affected thông báo là đã đọc!";
        }
    }
    $stmt->close();
    
    // Redirect về trang với filter hiện tại và timestamp để tránh cache
    $current_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    header('Location: notifications.php?filter=' . $current_filter . '&_=' . time());
    exit;
}

// Lấy filter từ URL (mặc định là 'all')
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Kiểm tra bảng tồn tại
$table_check = $conn->query("SHOW TABLES LIKE 'thongbao'");
if ($table_check && $table_check->num_rows > 0) {
    // Xây dựng query dựa trên filter
    $where_clause = "WHERE user_id = ? AND user_type = 'admin' AND type IN ('promotion', 'system', 'order', 'product')";
    
    if ($filter === 'unread') {
        $where_clause .= " AND is_read = 0";
    } elseif ($filter === 'read') {
        $where_clause .= " AND is_read = 1";
    }
    
    // Lấy danh sách thông báo (chỉ từ admin: ưu đãi, sự kiện, thông báo hệ thống)
    $stmt = $conn->prepare("
        SELECT * FROM thongbao 
        $where_clause
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Đếm thông báo chưa đọc (chỉ từ admin)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread 
        FROM thongbao 
        WHERE user_id = ? 
        AND is_read = 0 
        AND user_type = 'admin'
        AND type IN ('promotion', 'system', 'order', 'product')
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $unread_count = $stmt->get_result()->fetch_assoc()['unread'];
    
    // Đếm tổng số thông báo
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM thongbao 
        WHERE user_id = ? 
        AND user_type = 'admin'
        AND type IN ('promotion', 'system', 'order', 'product')
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $total_count = $stmt->get_result()->fetch_assoc()['total'];
} else {
    // Bảng chưa tồn tại
    $notifications = [];
    $unread_count = 0;
    $total_count = 0;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Báo - KIENANSHOP</title>
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/footer.css?v=3.0">
    <link rel="stylesheet" href="assets/css/modal.css?v=3.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .notifications-container {
            padding: 60px 20px;
            background: #f9fafb;
            min-height: 70vh;
        }
        
        .page-header {
            margin-bottom: 32px;
        }
        
        .page-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: #1f2937;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-header p {
            font-size: 15px;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }
        
        .stat-details h3 {
            font-size: 28px;
            font-weight: 800;
            color: #1f2937;
            margin: 0 0 4px 0;
        }
        
        .stat-details p {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }
        
        .filters-bar {
            background: white;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-mark-all {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .btn-mark-all:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .dashboard-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .notifications-list {
            background: white;
        }
        
        .notification-item {
            padding: 20px 25px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            gap: 20px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item:hover {
            background: #fdf2f8;
        }
        
        .notification-item.unread {
            background: linear-gradient(90deg, rgba(236, 72, 153, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            border-left: 4px solid #ec4899;
        }
        
        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
            flex-shrink: 0;
        }
        
        .notification-icon.order {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .notification-icon.product {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        
        .notification-icon.promotion {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .notification-icon.system {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .notification-message {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
            line-height: 1.5;
        }
        
        .notification-time {
            font-size: 12px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .notification-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-mark-read {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-mark-read:hover {
            background: #667eea;
            color: white;
        }
        
        .empty-state {
            padding: 80px 20px;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 80px;
            color: #e5e7eb;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #6b7280;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-unread {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .badge-read {
            background: #e5e7eb;
            color: #6b7280;
        }
        
        .filter-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .filter-tab {
            padding: 12px 24px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .filter-tab:hover {
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-2px);
        }
        
        .filter-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .filter-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .filter-tab.active .filter-count {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="notifications-container">
        <div class="container">
            <?php if (isset($_SESSION['success_message'])): ?>
            <div style="background: #10b981; color: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="page-header">
                <div>
                    <h1><i class="fas fa-bell"></i> Thông Báo</h1>
                    <p>Xem tất cả thông báo và cập nhật từ hệ thống</p>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo count($notifications); ?></h3>
                        <p>Tổng thông báo</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $unread_count; ?></h3>
                        <p>Chưa đọc</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo count($notifications) - $unread_count; ?></h3>
                        <p>Đã đọc</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo count(array_filter($notifications, fn($n) => $n['type'] == 'promotion')); ?></h3>
                        <p>Ưu đãi</p>
                    </div>
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i>
                    Tất cả
                    <span class="filter-count"><?php echo $total_count; ?></span>
                </a>
                <a href="?filter=unread" class="filter-tab <?php echo $filter === 'unread' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i>
                    Chưa đọc
                    <span class="filter-count"><?php echo $unread_count; ?></span>
                </a>
                <a href="?filter=read" class="filter-tab <?php echo $filter === 'read' ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle"></i>
                    Đã đọc
                    <span class="filter-count"><?php echo $total_count - $unread_count; ?></span>
                </a>
            </div>
            
            <!-- Filters Bar -->
            <?php if ($unread_count > 0 && $filter !== 'read'): ?>
            <div class="filters-bar">
                <div>
                    <strong style="color: #1f2937; font-size: 15px;">
                        <i class="fas fa-filter"></i> Bạn có <?php echo $unread_count; ?> thông báo chưa đọc
                    </strong>
                </div>
                <a href="?mark_all_read=1" class="btn-mark-all">
                    <i class="fas fa-check-double"></i>
                    Đánh dấu tất cả đã đọc
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Notifications List -->
            <div class="dashboard-card">
                <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <?php if ($filter === 'unread'): ?>
                        <h3>Không có thông báo chưa đọc</h3>
                        <p>Tuyệt vời! Bạn đã đọc hết tất cả thông báo.</p>
                    <?php elseif ($filter === 'read'): ?>
                        <h3>Không có thông báo đã đọc</h3>
                        <p>Bạn chưa đọc thông báo nào.</p>
                    <?php else: ?>
                        <h3>Chưa có thông báo nào</h3>
                        <p>Bạn sẽ nhận được thông báo về đơn hàng, khuyến mãi và nhiều hơn nữa tại đây.</p>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="notifications-list">
                    <?php foreach ($notifications as $notif): 
                        $icon_class = '';
                        switch($notif['type']) {
                            case 'order': $icon_class = 'fa-shopping-cart'; break;
                            case 'product': $icon_class = 'fa-box'; break;
                            case 'promotion': $icon_class = 'fa-gift'; break;
                            case 'system': $icon_class = 'fa-info-circle'; break;
                            default: $icon_class = 'fa-bell';
                        }
                        
                        $time_ago = time_elapsed_string($notif['created_at']);
                    ?>
                    <div class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                        <div class="notification-icon <?php echo $notif['type']; ?>">
                            <i class="fas <?php echo $icon_class; ?>"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                            <div class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></div>
                            <div class="notification-time">
                                <i class="fas fa-clock"></i>
                                <?php echo $time_ago; ?>
                            </div>
                        </div>
                        <div class="notification-actions">
                            <?php if (!$notif['is_read']): ?>
                            <a href="?mark_read=<?php echo $notif['id']; ?>" class="btn-mark-read">
                                <i class="fas fa-check"></i> Đã đọc
                            </a>
                            <?php else: ?>
                            <span class="badge badge-read">
                                <i class="fas fa-check"></i> Đã đọc
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
    <script>
    // Xử lý đánh dấu đã đọc bằng AJAX
    document.addEventListener('DOMContentLoaded', function() {
        const markReadButtons = document.querySelectorAll('.btn-mark-read');
        
        markReadButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const url = this.getAttribute('href');
                const notifItem = this.closest('.notification-item');
                const notifActions = this.closest('.notification-actions');
                const notifId = url.match(/mark_read=(\d+)/)[1];
                
                console.log('🔵 Bắt đầu đánh dấu đã đọc, ID:', notifId);
                
                // Disable button để tránh click nhiều lần
                this.style.opacity = '0.5';
                this.style.pointerEvents = 'none';
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
                
                // Gửi request AJAX
                fetch('api/mark_notification_read.php?id=' + notifId)
                    .then(response => {
                        console.log('🔵 Response status:', response.status);
                        if (!response.ok) {
                            throw new Error('HTTP error! status: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('🔵 Response data:', data);
                        
                        if (data.success) {
                            console.log('✅ Cập nhật database thành công!');
                            
                            // Cập nhật giao diện SAU KHI database đã update
                            notifItem.classList.remove('unread');
                            
                            // Thay đổi nút thành badge "Đã đọc"
                            notifActions.innerHTML = '<span class="badge badge-read"><i class="fas fa-check"></i> Đã đọc</span>';
                            
                            // Cập nhật số đếm
                            updateNotificationCounts();
                            
                            // Hiển thị thông báo thành công
                            showSuccessMessage(data.message);
                        } else {
                            console.error('❌ API trả về lỗi:', data.message);
                            alert(data.message || 'Có lỗi xảy ra!');
                            // Khôi phục button
                            this.style.opacity = '1';
                            this.style.pointerEvents = 'auto';
                            this.innerHTML = '<i class="fas fa-check"></i> Đã đọc';
                        }
                    })
                    .catch(error => {
                        console.error('❌ Lỗi fetch:', error);
                        console.log('⚠️ Fallback: Sử dụng redirect thay vì AJAX');
                        // Fallback: Nếu AJAX fail, dùng cách cũ (redirect)
                        window.location.href = url;
                    });
            });
        });
        
        // Cập nhật số đếm thông báo
        function updateNotificationCounts() {
            const unreadItems = document.querySelectorAll('.notification-item.unread').length;
            const totalItems = document.querySelectorAll('.notification-item').length;
            const readItems = totalItems - unreadItems;
            
            // Cập nhật stats
            const statCards = document.querySelectorAll('.stat-details h3');
            if (statCards[1]) statCards[1].textContent = unreadItems;
            if (statCards[2]) statCards[2].textContent = readItems;
            
            // Cập nhật filter counts
            const filterCounts = document.querySelectorAll('.filter-count');
            if (filterCounts[1]) filterCounts[1].textContent = unreadItems;
            if (filterCounts[2]) filterCounts[2].textContent = readItems;
            
            // Ẩn filters bar nếu không còn thông báo chưa đọc
            if (unreadItems === 0) {
                const filtersBar = document.querySelector('.filters-bar');
                if (filtersBar) filtersBar.style.display = 'none';
            }
        }
        
        // Hiển thị thông báo thành công
        function showSuccessMessage(message) {
            const existingMsg = document.querySelector('.success-message');
            if (existingMsg) existingMsg.remove();
            
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.style.cssText = 'background: #10b981; color: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; position: fixed; top: 80px; right: 20px; z-index: 9999; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); animation: slideIn 0.3s ease;';
            successDiv.innerHTML = '<i class="fas fa-check-circle"></i><span>' + message + '</span>';
            
            document.body.appendChild(successDiv);
            
            setTimeout(() => {
                successDiv.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => successDiv.remove(), 300);
            }, 3000);
        }
    });
    
    // CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>

<?php
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d > 0) {
        return $diff->d . ' ngày trước';
    } elseif ($diff->h > 0) {
        return $diff->h . ' giờ trước';
    } elseif ($diff->i > 0) {
        return $diff->i . ' phút trước';
    } else {
        return 'Vừa xong';
    }
}
?>
