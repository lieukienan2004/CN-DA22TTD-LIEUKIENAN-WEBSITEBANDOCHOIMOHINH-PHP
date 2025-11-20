/**
 * KIENANSHOP ADMIN - PREMIUM INTERACTIONS
 * Các hiệu ứng tương tác cao cấp
 */

// Smooth Page Load Animation
document.addEventListener('DOMContentLoaded', function() {
    // Animate stats cards on load
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 * index);
    });

    // Animate dashboard cards
    const dashboardCards = document.querySelectorAll('.dashboard-card');
    dashboardCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 200 + (100 * index));
    });

    // Number counter animation for stats
    animateNumbers();
    
    // Add ripple effect to buttons
    addRippleEffect();
    
    // Initialize tooltips
    initTooltips();
    
    // Add parallax effect to background
    addParallaxEffect();
});

// Animate numbers counting up
function animateNumbers() {
    const statNumbers = document.querySelectorAll('.stat-info h3');
    
    statNumbers.forEach(element => {
        const target = parseInt(element.textContent.replace(/[^0-9]/g, ''));
        if (isNaN(target)) return;
        
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            // Format number with commas
            const formatted = Math.floor(current).toLocaleString('vi-VN');
            const originalText = element.textContent;
            const suffix = originalText.replace(/[0-9,.\s]/g, '');
            element.textContent = formatted + suffix;
        }, 16);
    });
}

// Add ripple effect to clickable elements
function addRippleEffect() {
    const buttons = document.querySelectorAll('.btn-primary, .btn-icon, .nav-item, .stat-card');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple-effect');
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
}

// Add CSS for ripple effect
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(rippleStyle);

// Initialize custom tooltips
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.position = 'relative';
        });
    });
}

// Parallax effect for background
function addParallaxEffect() {
    let mouseX = 0, mouseY = 0;
    let currentX = 0, currentY = 0;
    
    document.addEventListener('mousemove', (e) => {
        mouseX = (e.clientX / window.innerWidth - 0.5) * 20;
        mouseY = (e.clientY / window.innerHeight - 0.5) * 20;
    });
    
    function animate() {
        currentX += (mouseX - currentX) * 0.1;
        currentY += (mouseY - currentY) * 0.1;
        
        const mainContent = document.querySelector('.main-content');
        if (mainContent && mainContent.querySelector('::before')) {
            mainContent.style.setProperty('--mouse-x', currentX + 'px');
            mainContent.style.setProperty('--mouse-y', currentY + 'px');
        }
        
        requestAnimationFrame(animate);
    }
    
    animate();
}

// Smooth scroll for navigation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add loading state to buttons
function addLoadingState(button) {
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="loading-spinner"></span>';
    
    return function removeLoading() {
        button.disabled = false;
        button.innerHTML = originalContent;
    };
}

// Show success notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '10000';
    notification.style.minWidth = '300px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.5s ease';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

// Add CSS for notification slide out
const notificationStyle = document.createElement('style');
notificationStyle.textContent = `
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100px);
        }
    }
`;
document.head.appendChild(notificationStyle);

// Sidebar collapse functionality
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
    
    // Save state to localStorage
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// Restore sidebar state on load
window.addEventListener('load', () => {
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        document.querySelector('.sidebar')?.classList.add('collapsed');
        document.querySelector('.main-content')?.classList.add('expanded');
    }
});

// Add hover effect to table rows
document.querySelectorAll('.data-table tr').forEach(row => {
    row.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.01)';
    });
    
    row.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
});

// Lazy load images with fade-in effect
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.5s ease';
                
                img.onload = () => {
                    img.style.opacity = '1';
                };
                
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

lazyLoadImages();

// Add search highlight effect
function highlightSearchResults(searchTerm) {
    if (!searchTerm) return;
    
    const elements = document.querySelectorAll('.data-table td, .product-info h4');
    
    elements.forEach(element => {
        const text = element.textContent;
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        
        if (regex.test(text)) {
            element.innerHTML = text.replace(regex, '<mark style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 2px 4px; border-radius: 4px; font-weight: 700;">$1</mark>');
        }
    });
}

// Auto-save form data
function enableAutoSave(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            localStorage.setItem(`autosave_${formId}`, JSON.stringify(data));
            
            // Show save indicator
            showSaveIndicator();
        });
    });
    
    // Restore saved data
    const savedData = localStorage.getItem(`autosave_${formId}`);
    if (savedData) {
        const data = JSON.parse(savedData);
        Object.keys(data).forEach(key => {
            const input = form.querySelector(`[name="${key}"]`);
            if (input) input.value = data[key];
        });
    }
}

function showSaveIndicator() {
    let indicator = document.querySelector('.save-indicator');
    
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.className = 'save-indicator';
        indicator.textContent = '✓ Đã lưu tự động';
        indicator.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.4);
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        document.body.appendChild(indicator);
    }
    
    indicator.style.opacity = '1';
    
    setTimeout(() => {
        indicator.style.opacity = '0';
    }, 2000);
}

// Export functions for global use
window.adminPremium = {
    showNotification,
    addLoadingState,
    highlightSearchResults,
    enableAutoSave,
    toggleSidebar
};

console.log('🎨 KIENANSHOP Admin Premium loaded successfully!');


/* ============================================
   LOW STOCK MONITORING
   ============================================ */

// Check for low stock products and show notification
function checkLowStock() {
    const lowStockCount = document.querySelector('.stat-card.glow-pulse');
    
    if (lowStockCount) {
        const count = parseInt(lowStockCount.querySelector('h3').textContent);
        
        if (count > 0) {
            // Show notification after 2 seconds
            setTimeout(() => {
                const hasOutOfStock = lowStockCount.querySelector('.stat-revenue').textContent.includes('hết hàng');
                
                if (hasOutOfStock) {
                    showNotification(
                        `⚠️ Cảnh báo: Có sản phẩm đã hết hàng! Vui lòng nhập thêm ngay.`,
                        'error'
                    );
                } else if (count > 3) {
                    showNotification(
                        `📦 Cảnh báo: Có ${count} sản phẩm còn ≤ 5 cái. Cần nhập hàng ngay!`,
                        'warning'
                    );
                }
            }, 2000);
        }
    }
}

// Add warning notification style
const warningNotificationStyle = document.createElement('style');
warningNotificationStyle.textContent = `
    .alert-warning {
        background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(245, 158, 11, 0.1) 100%);
        border-left: 4px solid #f59e0b;
        padding: 16px 20px;
        border-radius: 12px;
        color: #92400e;
        font-weight: 600;
        animation: slideInRight 0.5s ease;
        box-shadow: 0 4px 16px rgba(245, 158, 11, 0.2);
    }
`;
document.head.appendChild(warningNotificationStyle);

// Enhanced showNotification to support warning type
const originalShowNotification = showNotification;
showNotification = function(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '10000';
    notification.style.minWidth = '350px';
    notification.style.maxWidth = '500px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.5s ease';
        setTimeout(() => notification.remove(), 500);
    }, type === 'error' ? 5000 : 3000);
};

// Run check on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkLowStock);
} else {
    checkLowStock();
}

// Highlight low stock items with animation
document.querySelectorAll('.product-item[style*="border-left"]').forEach((item, index) => {
    item.style.opacity = '0';
    item.style.transform = 'translateX(-20px)';
    
    setTimeout(() => {
        item.style.transition = 'all 0.5s ease';
        item.style.opacity = '1';
        item.style.transform = 'translateX(0)';
    }, 100 * index);
});

// Add click tracking for restock buttons
document.querySelectorAll('.btn-restock').forEach(btn => {
    btn.addEventListener('click', function(e) {
        // Add loading state
        const originalContent = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang chuyển...';
        this.disabled = true;
        
        // Restore after navigation
        setTimeout(() => {
            this.innerHTML = originalContent;
            this.disabled = false;
        }, 1000);
    });
});

// Auto-refresh stock data every 5 minutes
let stockRefreshInterval;
function startStockMonitoring() {
    stockRefreshInterval = setInterval(() => {
        // Check if we're still on dashboard
        if (window.location.pathname.includes('index.php') || window.location.pathname.endsWith('/admin/')) {
            console.log('🔄 Checking stock levels...');
            // You can add AJAX call here to refresh stock data
        }
    }, 300000); // 5 minutes
}

// Start monitoring
startStockMonitoring();

// Stop monitoring when leaving page
window.addEventListener('beforeunload', () => {
    if (stockRefreshInterval) {
        clearInterval(stockRefreshInterval);
    }
});

console.log('📦 Low stock monitoring activated!');
