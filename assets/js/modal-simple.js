// Simple Modal for Product Quick View
function openProductModal(productId) {
    console.log('=== Opening Modal ===');
    console.log('Product ID:', productId);
    
    const modal = document.getElementById('productModal');
    console.log('Modal element:', modal);
    
    if (!modal) {
        alert('ERROR: Modal not found in DOM!');
        return;
    }
    
    // Show modal immediately with inline styles to ensure visibility
    modal.classList.add('active');
    modal.style.display = 'flex';
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.right = '0';
    modal.style.bottom = '0';
    modal.style.zIndex = '9999';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.background = 'rgba(0, 0, 0, 0.8)';
    document.body.style.overflow = 'hidden';
    
    console.log('Modal should be visible now');
    console.log('Modal classes:', modal.className);
    console.log('Modal computed style:', window.getComputedStyle(modal).display);
    
    // Fetch product data
    fetch(`get-product.php?id=${productId}`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(product => {
            console.log('Product data:', product);
            
            if (product.error) {
                alert('Không tìm thấy sản phẩm: ' + product.error);
                closeProductModal();
                return;
            }
            
            // Update modal content
            const modalImage = document.getElementById('modalImage');
            const modalTitle = document.getElementById('modalTitle');
            const modalSKU = document.getElementById('modalSKU');
            const modalCategory = document.getElementById('modalCategory');
            const modalDescription = document.getElementById('modalDescription');
            const modalQuantity = document.getElementById('modalQuantity');
            
            if (modalImage) modalImage.src = product.image;
            if (modalTitle) modalTitle.textContent = product.name;
            if (modalSKU) modalSKU.textContent = `KIENAN${String(product.id).padStart(3, '0')}`;
            if (modalCategory) modalCategory.textContent = product.category_name;
            if (modalDescription) modalDescription.textContent = product.description;
            if (modalQuantity) {
                modalQuantity.max = product.stock;
                modalQuantity.value = 1;
            }
            
            // Update price
            const priceSection = document.getElementById('modalPriceSection');
            if (product.discount > 0) {
                const discountPrice = product.price * (1 - product.discount / 100);
                priceSection.innerHTML = `
                    <div class="modal-price-group">
                        <span class="modal-original-price">${formatPrice(product.price)}đ</span>
                        <span class="modal-discount-percent">-${product.discount}%</span>
                    </div>
                    <div class="modal-current-price">${formatPrice(discountPrice)}đ</div>
                `;
                document.getElementById('modalDiscountBadge').style.display = 'block';
                document.getElementById('modalDiscountBadge').textContent = `-${product.discount}%`;
            } else {
                priceSection.innerHTML = `<div class="modal-current-price">${formatPrice(product.price)}đ</div>`;
                document.getElementById('modalDiscountBadge').style.display = 'none';
            }
            
            // Store current product ID
            window.currentProductId = productId;
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Có lỗi xảy ra khi tải dữ liệu: ' + error.message);
            closeProductModal();
        });
}

function closeProductModal() {
    console.log('Closing modal');
    const modal = document.getElementById('productModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
    document.body.style.overflow = '';
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
    const current = parseInt(input.value);
    if (current > 1) {
        input.value = current - 1;
    }
}

function addToCartFromModal() {
    const quantity = document.getElementById('modalQuantity').value;
    const productId = window.currentProductId;
    
    if (!productId) {
        alert('Lỗi: Không xác định được sản phẩm');
        return;
    }
    
    fetch('add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = data.cart_count;
            }
            
            alert('Đã thêm vào giỏ hàng!');
            closeProductModal();
        } else {
            alert('Có lỗi: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
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

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('productModal');
    if (e.target === modal) {
        closeProductModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeProductModal();
    }
});

console.log('Modal script loaded successfully');
