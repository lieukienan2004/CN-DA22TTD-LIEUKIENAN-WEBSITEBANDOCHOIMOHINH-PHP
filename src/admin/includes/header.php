<header class="admin-header">
    <div class="header-left">
        <button class="btn-toggle-sidebar" onclick="toggleSidebar()" data-tooltip="Toggle Menu">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <div class="header-right">
        <!-- Dark Mode Toggle -->
        <button class="btn-dark-mode" onclick="toggleDarkMode()" data-tooltip="Chế độ tối">
            <i class="fas fa-moon"></i>
        </button>
        
        <!-- Notifications -->
        <div class="notification-wrapper">
            <button class="btn-notifications" onclick="toggleNotifications()" data-tooltip="Thông báo">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationCount">0</span>
            </button>
            
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">
                    <h3><i class="fas fa-bell"></i> Thông báo</h3>
                    <button class="btn-mark-read" onclick="markAllAsRead()">
                        <i class="fas fa-check-double"></i> Đánh dấu đã đọc
                    </button>
                </div>
                <div class="notification-list" id="notificationList">
                    <div class="notification-loading">
                        <i class="fas fa-spinner fa-spin"></i> Đang tải...
                    </div>
                </div>
                <div class="notification-footer">
                    <a href="contacts.php">Xem tất cả tin nhắn <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
        
        <!-- View Site -->
        <a href="../index.php" class="btn-view-site" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>Xem website</span>
        </a>
        
        <!-- Admin Profile -->
        <div class="admin-profile">
            <div class="profile-info">
                <span class="profile-name"><?php echo htmlspecialchars($_SESSION['admin_fullname']); ?></span>
                <span class="profile-role"><?php echo ucfirst($_SESSION['admin_role']); ?></span>
            </div>
            <div class="profile-avatar" data-tooltip="Tài khoản">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </div>
</header>

<style>
.btn-dark-mode,
.btn-notifications {
    background: rgba(102, 126, 234, 0.1);
    border: none;
    font-size: 18px;
    color: #667eea;
    cursor: pointer;
    padding: 10px;
    border-radius: 12px;
    transition: all 0.3s ease;
    position: relative;
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-dark-mode:hover,
.btn-notifications:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Notification Styles */
.notification-wrapper {
    position: relative;
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
    animation: pulse 2s infinite;
    display: inline-block;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

@keyframes slideIn {
    from { transform: translateX(400px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(400px); opacity: 0; }
}

.notification-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 380px;
    max-height: 500px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    display: none;
    flex-direction: column;
    z-index: 1000;
    overflow: hidden;
}

.notification-dropdown.show {
    display: flex;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-header {
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
}

.notification-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-mark-read {
    background: none;
    border: none;
    color: #667eea;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-mark-read:hover {
    background: rgba(102, 126, 234, 0.1);
}

.notification-list {
    flex: 1;
    overflow-y: auto;
    max-height: 400px;
}

.notification-item {
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    gap: 12px;
    text-decoration: none;
    color: inherit;
}

.notification-item:hover {
    background: rgba(102, 126, 234, 0.05);
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
    flex-shrink: 0;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.notification-message {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 4px;
}

.notification-time {
    font-size: 11px;
    color: #9ca3af;
    font-weight: 500;
}

.notification-loading,
.notification-empty {
    padding: 40px 20px;
    text-align: center;
    color: #9ca3af;
}

.notification-empty i {
    font-size: 48px;
    margin-bottom: 10px;
    opacity: 0.3;
}

.notification-footer {
    padding: 12px 20px;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    text-align: center;
    background: rgba(0, 0, 0, 0.02);
}

.notification-footer a {
    color: #667eea;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.notification-footer a:hover {
    color: #764ba2;
}
</style>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
}

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const icon = document.querySelector('.btn-dark-mode i');
    if (document.body.classList.contains('dark-mode')) {
        icon.className = 'fas fa-sun';
    } else {
        icon.className = 'fas fa-moon';
    }
}

// Notification System
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('show');
    
    if (dropdown.classList.contains('show')) {
        loadNotifications();
    }
}

function loadNotifications() {
    const listEl = document.getElementById('notificationList');
    
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge(data.count);
                
                if (data.notifications.length === 0) {
                    listEl.innerHTML = `
                        <div class="notification-empty">
                            <i class="fas fa-bell-slash"></i>
                            <p>Không có thông báo mới</p>
                        </div>
                    `;
                } else {
                    listEl.innerHTML = data.notifications.map(notif => `
                        <a href="${notif.link}" class="notification-item" data-notif-id="${notif.id}" onclick="markNotificationAsRead(event, ${notif.id})">
                            <div class="notification-icon" style="background: ${notif.color};">
                                <i class="fas ${notif.icon}"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">${notif.title}</div>
                                <div class="notification-message">${notif.message}</div>
                                <div class="notification-time">${notif.time}</div>
                            </div>
                        </a>
                    `).join('');
                }
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            listEl.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Không thể tải thông báo</p>
                </div>
            `;
        });
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationCount');
    console.log('updateNotificationBadge called with count:', count);
    console.log('Badge element:', badge);
    
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'inline-block';
            console.log('Badge updated to:', badge.textContent);
        } else {
            badge.style.display = 'none';
        }
    } else {
        console.error('Badge element not found!');
    }
}

function markAllAsRead() {
    console.log('🔵 Đánh dấu tất cả đã đọc...');
    
    // Gọi API để cập nhật database
    fetch('api/mark_notifications_read.php')
        .then(response => response.json())
        .then(data => {
            console.log('🔵 Response:', data);
            
            if (data.success) {
                // Cập nhật UI
                updateNotificationBadge(0);
                
                // Hiển thị thông báo thành công
                showSuccessToast(data.message);
                
                // Đóng dropdown
                document.getElementById('notificationDropdown').classList.remove('show');
                
                // Reload notifications để cập nhật danh sách
                setTimeout(() => {
                    loadNotifications();
                }, 500);
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
            }
        })
        .catch(error => {
            console.error('❌ Lỗi:', error);
            alert('Có lỗi xảy ra. Vui lòng thử lại!');
        });
}

function showSuccessToast(message) {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
    `;
    toast.innerHTML = `<i class="fas fa-check-circle"></i><span>${message}</span>`;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function markNotificationAsRead(event, notifId) {
    // Gọi API để đánh dấu đã đọc
    fetch('api/mark_notifications_read.php?id=' + notifId)
        .then(response => response.json())
        .then(data => {
            console.log('Đã đánh dấu thông báo #' + notifId + ' là đã đọc');
            
            // Cập nhật badge
            const currentCount = parseInt(document.getElementById('notificationCount').textContent) || 0;
            if (currentCount > 0) {
                updateNotificationBadge(currentCount - 1);
            }
            
            // Xóa thông báo khỏi danh sách
            const notifItem = event.currentTarget;
            notifItem.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                notifItem.remove();
                
                // Kiểm tra nếu không còn thông báo nào
                const listEl = document.getElementById('notificationList');
                if (listEl.children.length === 0) {
                    listEl.innerHTML = `
                        <div class="notification-empty">
                            <i class="fas fa-bell-slash"></i>
                            <p>Không có thông báo mới</p>
                        </div>
                    `;
                }
            }, 300);
        })
        .catch(error => {
            console.error('Lỗi đánh dấu đã đọc:', error);
        });
    
    // Vẫn cho phép link hoạt động
    return true;
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const wrapper = document.querySelector('.notification-wrapper');
    const dropdown = document.getElementById('notificationDropdown');
    
    if (wrapper && !wrapper.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

// Auto refresh notifications every 30 seconds
setInterval(function() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge(data.count);
            }
        })
        .catch(error => console.error('Error refreshing notifications:', error));
}, 30000);

// Load initial notification count
document.addEventListener('DOMContentLoaded', function() {
    console.log('Loading notifications...');
    fetch('get_notifications.php')
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Notification data:', data);
            if (data.success) {
                updateNotificationBadge(data.count);
                console.log('Updated badge with count:', data.count);
            } else {
                console.error('API returned success=false');
            }
        })
        .catch(error => {
            console.error('Error loading initial notifications:', error);
        });
});
</script>
