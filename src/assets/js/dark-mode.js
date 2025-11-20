// Dark Mode Toggle
document.addEventListener('DOMContentLoaded', function() {
    const html = document.documentElement;
    
    // Create fixed theme toggle button
    const themeToggleFixed = document.createElement('button');
    themeToggleFixed.className = 'theme-toggle-fixed';
    themeToggleFixed.id = 'themeToggleFixed';
    themeToggleFixed.innerHTML = '<i class="fas fa-moon" id="themeIconFixed"></i>';
    document.body.appendChild(themeToggleFixed);
    
    const themeIcon = document.getElementById('themeIconFixed');
    
    // Check for saved theme preference or default to 'light'
    const currentTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', currentTheme);
    updateIcon(currentTheme);
    
    // Toggle theme
    themeToggleFixed.addEventListener('click', function() {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateIcon(newTheme);
        
        // Add rotation animation
        themeIcon.style.transform = 'rotate(360deg)';
        setTimeout(() => {
            themeIcon.style.transform = 'rotate(0deg)';
        }, 500);
    });
    
    function updateIcon(theme) {
        if (themeIcon) {
            if (theme === 'dark') {
                themeIcon.className = 'fas fa-sun';
                themeToggleFixed.title = 'Chuyển sang chế độ sáng';
            } else {
                themeIcon.className = 'fas fa-moon';
                themeToggleFixed.title = 'Chuyển sang chế độ tối';
            }
        }
    }
});
