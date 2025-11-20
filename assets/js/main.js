// Search functionality - Simple version
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.querySelector('.search-btn');
    
    // Nếu không có search input (như trang checkout), bỏ qua
    if (!searchInput) {
        return;
    }
    
    // Debug: Check input properties
    console.log('Input disabled:', searchInput.disabled);
    console.log('Input readonly:', searchInput.readOnly);
    console.log('Input type:', searchInput.type);
    console.log('Input style.pointerEvents:', window.getComputedStyle(searchInput).pointerEvents);
    console.log('Input style.cursor:', window.getComputedStyle(searchInput).cursor);
    
    // Force enable input
    searchInput.disabled = false;
    searchInput.readOnly = false;
    searchInput.style.pointerEvents = 'auto';
    searchInput.style.cursor = 'text';
    
    console.log('Input force enabled!');
    
    // Function to perform search
    function performSearch() {
        const searchValue = searchInput.value.trim();
        console.log('Performing search with:', searchValue);
        
        if (searchValue) {
            window.location.href = 'products.php?search=' + encodeURIComponent(searchValue);
        } else {
            alert('Vui lòng nhập từ khóa tìm kiếm');
        }
    }
    
    // Test input functionality
    searchInput.addEventListener('focus', function() {
        console.log('Input focused');
    });
    
    searchInput.addEventListener('input', function() {
        console.log('Input value changed:', this.value);
    });
    
    // Search on Enter key
    searchInput.addEventListener('keypress', function(e) {
        console.log('Key pressed:', e.key);
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });
    
    // Search on button click
    if (searchBtn) {
        searchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Search button clicked');
            performSearch();
        });
    } else {
        console.error('Search button not found!');
    }
    
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe product cards and category cards
    document.querySelectorAll('.product-card, .category-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
    
    // Add to cart animation
    document.querySelectorAll('.btn-secondary').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (this.textContent.includes('Thêm vào giỏ')) {
                const ripple = document.createElement('span');
                ripple.style.position = 'absolute';
                ripple.style.width = '20px';
                ripple.style.height = '20px';
                ripple.style.background = 'white';
                ripple.style.borderRadius = '50%';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'ripple 0.6s ease-out';
                ripple.style.left = e.offsetX + 'px';
                ripple.style.top = e.offsetY + 'px';
                this.style.position = 'relative';
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            }
        });
    });
});

// Add ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

    // Language dropdown toggle for mobile
    const langToggle = document.querySelector('.lang-toggle');
    const langDropdown = document.querySelector('.language-dropdown');
    
    if (langToggle && langDropdown) {
        langToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            langDropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!langDropdown.contains(e.target)) {
                langDropdown.classList.remove('active');
            }
        });
    }

// Language dropdown toggle
document.addEventListener('DOMContentLoaded', function() {
    const langDropdown = document.querySelector('.language-dropdown');
    const langToggle = document.querySelector('.lang-toggle');
    
    if (langToggle && langDropdown) {
        // Toggle dropdown on click (for mobile)
        langToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            langDropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!langDropdown.contains(e.target)) {
                langDropdown.classList.remove('active');
            }
        });
        
        // Prevent dropdown from closing when clicking inside
        langDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});
