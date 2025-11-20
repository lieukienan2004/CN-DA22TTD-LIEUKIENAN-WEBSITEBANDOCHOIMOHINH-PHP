// Auto-check for new notifications
(function() {
    let lastUnreadCount = 0;
    
    function checkNotifications() {
        fetch('get_user_notifications.php?count_only=1')
            .then(response => response.json())
            .then(data => {
                if (data.unread_count !== undefined) {
                    updateNotificationBadge(data.unread_count);
                    
                    // Nếu có thông báo mới, hiện popup nhỏ
                    if (data.unread_count > lastUnreadCount && lastUnreadCount > 0) {
                        showNewNotificationToast();
                    }
                    
                    lastUnreadCount = data.unread_count;
                }
            })
            .catch(error => console.log('Notification check failed:', error));
    }
    
    function updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge-small');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    function showNewNotificationToast() {
        // Tạo toast notification
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.innerHTML = `
            <i class="fas fa-bell"></i>
            <span>Bạn có thông báo mới!</span>
        `;
        
        // Thêm styles
        toast.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            cursor: pointer;
        `;
        
        document.body.appendChild(toast);
        
        // Click để đi đến trang thông báo
        toast.addEventListener('click', () => {
            window.location.href = 'notifications.php';
        });
        
        // Tự động ẩn sau 5 giây
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
    
    // Kiểm tra ngay khi load trang
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkNotifications);
    } else {
        checkNotifications();
    }
    
    // Kiểm tra mỗi 30 giây
    setInterval(checkNotifications, 30000);
    
    // Thêm CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .notification-toast:hover {
            transform: scale(1.05);
            transition: transform 0.2s ease;
        }
    `;
    document.head.appendChild(style);
})();
