/**
 * Modal Fix - Đảm bảo modal luôn hiển thị đúng vị trí
 */

// Fix modal position khi mở
document.addEventListener('DOMContentLoaded', function() {
    // Theo dõi tất cả modal
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    // Kiểm tra nếu là modal
                    if (node.classList && (
                        node.classList.contains('modal-overlay') ||
                        node.classList.contains('modal') ||
                        node.id && node.id.includes('modal')
                    )) {
                        fixModalPosition(node);
                    }
                    
                    // Kiểm tra các modal con
                    const modals = node.querySelectorAll('.modal-overlay, .modal, [id*="modal"]');
                    modals.forEach(fixModalPosition);
                }
            });
        });
    });
    
    // Bắt đầu theo dõi
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Fix các modal đã có sẵn
    document.querySelectorAll('.modal-overlay, .modal, [id*="modal"]').forEach(fixModalPosition);
});

function fixModalPosition(modal) {
    if (!modal) return;
    
    // Đảm bảo modal có style đúng
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.right = '0';
    modal.style.bottom = '0';
    modal.style.width = '100vw';
    modal.style.height = '100vh';
    modal.style.margin = '0';
    modal.style.padding = '20px';
    modal.style.zIndex = '999999';
    modal.style.display = 'flex';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.boxSizing = 'border-box';
    
    // Thêm class để dễ quản lý
    modal.classList.add('modal-fixed');
    
    // Ngăn scroll body
    document.body.style.overflow = 'hidden';
    document.body.classList.add('modal-open');
    
    // Xử lý khi đóng modal
    const closeButtons = modal.querySelectorAll('.modal-close, .close, [data-dismiss="modal"], [aria-label="Close"]');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            closeModal(modal);
        });
    });
    
    // Đóng khi click overlay
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal(modal);
        }
    });
    
    // Đóng khi nhấn ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('modal-fixed')) {
            closeModal(modal);
        }
    });
}

function closeModal(modal) {
    if (!modal) return;
    
    // Animation đóng
    modal.style.opacity = '0';
    modal.style.transform = 'scale(0.95)';
    
    setTimeout(() => {
        modal.style.display = 'none';
        modal.remove();
        
        // Kiểm tra nếu không còn modal nào thì cho phép scroll lại
        const remainingModals = document.querySelectorAll('.modal-fixed');
        if (remainingModals.length === 0) {
            document.body.style.overflow = '';
            document.body.classList.remove('modal-open');
        }
    }, 300);
}

// Helper function để tạo modal
window.createModal = function(content, options = {}) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay modal-fixed';
    modal.style.cssText = `
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        margin: 0 !important;
        padding: 20px !important;
        z-index: 999999 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: rgba(0, 0, 0, 0.75) !important;
        backdrop-filter: blur(8px) !important;
        box-sizing: border-box !important;
    `;
    
    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content';
    modalContent.style.cssText = `
        background: white;
        border-radius: 20px;
        padding: 30px;
        max-width: ${options.maxWidth || '600px'};
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    `;
    
    // Nút đóng
    const closeBtn = document.createElement('button');
    closeBtn.className = 'modal-close';
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.style.cssText = `
        position: absolute;
        top: 15px;
        right: 15px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #374151;
        transition: all 0.3s ease;
        z-index: 10;
    `;
    closeBtn.onclick = () => closeModal(modal);
    
    modalContent.innerHTML = content;
    modalContent.insertBefore(closeBtn, modalContent.firstChild);
    modal.appendChild(modalContent);
    
    document.body.appendChild(modal);
    fixModalPosition(modal);
    
    return modal;
};

console.log('Modal fix loaded successfully');
