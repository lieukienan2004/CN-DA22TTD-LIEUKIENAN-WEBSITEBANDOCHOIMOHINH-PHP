// Cherry Blossom Animation
function createSakura() {
    const sakuraContainer = document.getElementById('sakuraContainer');
    if (!sakuraContainer) return;
    
    const sakura = document.createElement('div');
    sakura.classList.add('sakura');
    
    // Random position across entire screen width
    const startPosition = Math.random() * window.innerWidth;
    sakura.style.left = startPosition + 'px';
    
    // Random size variation
    const size = Math.random() * 10 + 18; // 18-28px
    sakura.style.fontSize = size + 'px';
    
    // Random fall duration
    const fallDuration = Math.random() * 8 + 10; // 10-18 seconds
    sakura.style.animationDuration = `${fallDuration}s`;
    
    // NO DELAY - start falling immediately
    sakura.style.animationDelay = '0s';
    
    sakuraContainer.appendChild(sakura);
    
    // Remove after animation
    setTimeout(() => {
        sakura.remove();
    }, fallDuration * 1000);
}

function startSakuraAnimation() {
    // Create initial petals
    for (let i = 0; i < 20; i++) {
        setTimeout(() => createSakura(), i * 300);
    }
    
    // Continue creating petals
    setInterval(() => {
        createSakura();
    }, 600);
}

// Start animation when page loads
window.addEventListener('load', startSakuraAnimation);
