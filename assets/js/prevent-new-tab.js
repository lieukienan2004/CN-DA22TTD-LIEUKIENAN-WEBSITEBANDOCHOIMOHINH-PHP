// Prevent opening links in new tab - Force same page navigation
document.addEventListener('DOMContentLoaded', function() {
    // Get all product detail links
    const productLinks = document.querySelectorAll('a[href*="product-detail.php"]');
    
    productLinks.forEach(link => {
        // Remove target attribute if exists
        link.removeAttribute('target');
        
        // Ensure it opens in same window
        link.addEventListener('click', function(e) {
            // If user is holding Ctrl/Cmd or middle-clicking, allow default behavior
            if (e.ctrlKey || e.metaKey || e.button === 1) {
                return;
            }
            
            // Otherwise, navigate in same window
            e.preventDefault();
            window.location.href = this.href;
        });
    });
    
    console.log('Prevented new tab for', productLinks.length, 'product links');
});
