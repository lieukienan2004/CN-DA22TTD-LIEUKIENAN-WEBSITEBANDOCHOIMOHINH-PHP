<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Về Chúng Tôi</h3>
                <p>Cửa hàng mô hình uy tín với hơn 10 năm kinh nghiệm. Chuyên cung cấp các sản phẩm mô hình cao cấp từ khắp nơi trên thế giới.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Liên Kết</h3>
                <ul>
                    <li><a href="products.php">Sản Phẩm</a></li>
                    <li><a href="about.php">Giới Thiệu</a></li>
                    <li><a href="contact.html">Liên Hệ</a></li>
                    <li><a href="policy.php">Chính Sách</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Hỗ Trợ</h3>
                <ul>
                    <li><a href="#">Hướng Dẫn Mua Hàng</a></li>
                    <li><a href="#">Chính Sách Đổi Trả</a></li>
                    <li><a href="#">Phương Thức Thanh Toán</a></li>
                    <li><a href="#">Câu Hỏi Thường Gặp</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Liên Hệ</h3>
                <ul class="contact-info">
                    <li><i class="fas fa-map-marker-alt"></i> Khóm 6, Xã Càng Long, Tỉnh Vĩnh Long</li>
                    <li><i class="fas fa-phone"></i> 0912431719</li>
                    <li><i class="fas fa-envelope"></i> kienanshop@gmail.com</li>
                    <li><i class="fas fa-clock"></i> 8:00 - 22:00 (Hàng ngày)</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 KIENANSHOP. Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" class="back-to-top" title="Lên đầu trang">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Dark Mode Script -->
<script src="assets/js/dark-mode.js"></script>

<!-- Live Chat Widget -->
<link rel="stylesheet" href="assets/css/chat-widget.css">
<script src="assets/js/chat-widget.js"></script>

<script>
// Back to Top Button
const backToTopBtn = document.getElementById('backToTop');

window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
        backToTopBtn.classList.add('show');
    } else {
        backToTopBtn.classList.remove('show');
    }
});

backToTopBtn.addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>
