// Admin Dark Mode Toggle
document.addEventListener('DOMContentLoaded', function() {
    // Check for saved theme preference or default to 'light'
    const currentTheme = localStorage.getItem('admin-theme') || 'light';
    document.body.setAttribute('data-theme', currentTheme);
    updateIcon(currentTheme);
    
    // Create theme toggle button if it doesn't exist
    const headerRight = document.querySelector('.header-right');
    if (headerRight && !document.getElementById('adminThemeToggle')) {
        const themeToggle = document.createElement('button');
        themeToggle.id = 'adminThemeToggle';
        themeToggle.className = 'theme-toggle-admin';
        themeToggle.title = 'Chuyển chế độ sáng/tối';
        themeToggle.innerHTML = '<i class="fas fa-moon" id="adminThemeIcon"></i>';
        
        // Insert before profile
        const profile = headerRight.querySelector('.admin-profile');
        if (profile) {
            headerRight.insertBefore(themeToggle, profile);
        } else {
            headerRight.appendChild(themeToggle);
        }
        
        // Add click event
        themeToggle.addEventListener('click', toggleTheme);
    }
    
    function toggleTheme() {
        const currentTheme = document.body.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.body.setAttribute('data-theme', newTheme);
        localStorage.setItem('admin-theme', newTheme);
        updateIcon(newTheme);
        
        // Add rotation animation
        const icon = document.getElementById('adminThemeIcon');
        if (icon) {
            icon.style.transform = 'rotate(360deg)';
            setTimeout(() => {
                icon.style.transform = 'rotate(0deg)';
            }, 500);
        }
    }
    
    function updateIcon(theme) {
        const icon = document.getElementById('adminThemeIcon');
        const toggle = document.getElementById('adminThemeToggle');
        
        if (icon && toggle) {
            if (theme === 'dark') {
                icon.className = 'fas fa-sun';
                toggle.title = 'Chuyển sang chế độ sáng';
            } else {
                icon.className = 'fas fa-moon';
                toggle.title = 'Chuyển sang chế độ tối';
            }
        }
    }
});
