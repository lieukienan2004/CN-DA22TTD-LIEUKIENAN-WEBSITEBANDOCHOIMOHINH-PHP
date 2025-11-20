// Product Modal Functions
let currentProductId = null;

function openProductModal(productId) {
    console.log('Opening modal for product:', productId);
    currentProductId = productId;

    // Show modal first
    const modal = document.getElementById('productModal');
    if (!modal) {
        console.error('Modal element not found!');
        alert('Lỗi: Không tìm thấy modal');
        return;
    }

    console.log('Modal found, adding active class');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Fetch product data
    fetch(`get-product.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
            if (product.error) {
                alert('Không tìm thấy sản phẩm');
                closeProductModal();
                return;
            }

            // Populate modal - check if elements exist
            const modalImage = document.getElementById('modalImage');
            const modalTitle = document.getElementById('modalTitle');
            const modalSKU = document.getElementById('modalSKU');
            const modalCategory = document.getElementById('modalCategory');
            const modalDescription = document.getElementById('modalDescription');
            const modalQuantity = document.getElementById('modalQuantity');
            const modalPriceSection = document.getElementById('modalPriceSection');
            const modalDiscountBadge = document.getElementById('modalDiscountBadge');

            if (modalImage) modalImage.src = product.image;
            if (modalTitle) modalTitle.textContent = product.name;
            if (modalSKU) modalSKU.textContent = `Tonjuto${String(product.id).padStart(3, '0')}`;
            if (modalCategory) modalCategory.textContent = product.category_name;
            if (modalDescription) {
                // Convert \r\n to <br> tags for proper line breaks
                modalDescription.innerHTML = product.description.replace(/\r\n/g, '<br>').replace(/\n/g, '<br>');
            }
            if (modalQuantity) {
                modalQuantity.max = product.stock;
                modalQuantity.value = 1;
            }

            // Price
            if (modalPriceSection) {
                if (product.discount > 0) {
                    const discountPrice = product.price * (1 - product.discount / 100);
                    modalPriceSection.innerHTML = `
                        <div class="modal-price-group">
                            <span class="modal-current-price">${formatPrice(discountPrice)}đ</span>
                            <span class="modal-original-price">${formatPrice(product.price)}đ</span>
                            <span class="modal-discount-percent">-${product.discount}%</span>
                        </div>
                    `;

                    // Show discount badge
                    if (modalDiscountBadge) {
                        modalDiscountBadge.style.display = 'block';
                        modalDiscountBadge.textContent = `-${product.discount}%\nOFF`;
                    }
                } else {
                    modalPriceSection.innerHTML = `
                        <div class="modal-price-group">
                            <span class="modal-current-price">${formatPrice(product.price)}đ</span>
                        </div>
                    `;
                    if (modalDiscountBadge) {
                        modalDiscountBadge.style.display = 'none';
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra. Vui lòng thử lại.');
        });
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('active');
    document.body.style.overflow = '';
    currentProductId = null;
}

function increaseModalQty() {
    const input = document.getElementById('modalQuantity');
    const max = parseInt(input.max);
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseModalQty() {
    const input = document.getElementById('modalQuantity');
    const min = 1;
    const current = parseInt(input.value);
    if (current > min) {
        input.value = current - 1;
    }
}

function addToCartFromModal() {
    const quantity = document.getElementById('modalQuantity').value;

    fetch('add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${currentProductId}&quantity=${quantity}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                }

                // Show success message
                showNotification('Đã thêm vào giỏ hàng!', 'success');
                closeProductModal();
            } else {
                showNotification('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        });
}

function buyNowFromModal() {
    addToCartFromModal();
    setTimeout(() => {
        window.location.href = 'cart.php';
    }, 500);
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price);
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${type === 'success' ? 'var(--success-color)' : 'var(--danger-color)'};
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        animation: slideInRight 0.3s ease;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Close modal when clicking outside
document.addEventListener('click', function (e) {
    const modal = document.getElementById('productModal');
    if (e.target === modal) {
        closeProductModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeProductModal();
    }
});

// Add notification animations
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
`;
document.head.appendChild(style);
