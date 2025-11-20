<?php
// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Xử lý thay đổi ngôn ngữ
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, ['vi', 'en'])) {
        $_SESSION['lang'] = $lang;
        // Chuyển về trang hiện tại nhưng bỏ tham số lang
        $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
        if (!empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $params);
            unset($params['lang']);
            if (!empty($params)) {
                $redirect_url .= '?' . http_build_query($params);
            }
        }
        header('Location: ' . $redirect_url);
        exit;
    }
}

// Lấy ngôn ngữ hiện tại (mặc định là tiếng Việt)
$current_lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'vi';

// Định nghĩa các bản dịch
$translations = [
    'vi' => [
        // Header
        'hotline' => 'Hotline',
        'home' => 'Trang Chủ',
        'products' => 'Sản Phẩm',
        'about' => 'Giới Thiệu',
        'contact' => 'Liên Hệ',
        'search_placeholder' => 'Tìm kiếm sản phẩm...',
        'cart' => 'Giỏ hàng',
        'account' => 'Tài khoản',
        'login' => 'Đăng nhập',
        'register' => 'Đăng ký',
        'logout' => 'Đăng xuất',
        
        // Categories
        'categories' => 'Danh Mục',
        'all_products' => 'Tất cả sản phẩm',
        'gundam' => 'Gundam',
        'car_model' => 'Xe Mô Hình',
        'airplane' => 'Máy Bay',
        'warship' => 'Tàu Chiến',
        'character' => 'Panini',
        'lego' => 'Lego',
        
        // Auth
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'confirm_password' => 'Xác nhận mật khẩu',
        'fullname' => 'Họ và tên',
        'phone' => 'Số điện thoại',
        'address' => 'Địa chỉ',
        'forgot_password' => 'Quên mật khẩu?',
        'no_account' => 'Chưa có tài khoản?',
        'register_now' => 'Đăng ký ngay',
        'have_account' => 'Đã có tài khoản?',
        'login_now' => 'Đăng nhập',
        'remember_password' => 'Đã nhớ mật khẩu?',
        
        // Forgot Password
        'forgot_password_title' => 'Quên Mật Khẩu',
        'forgot_password_desc' => 'Nhập email để nhận link đặt lại mật khẩu',
        'send_reset_link' => 'Gửi Link Đặt Lại',
        'reset_password_title' => 'Đặt Lại Mật Khẩu',
        'reset_password_desc' => 'Nhập mật khẩu mới của bạn',
        'new_password' => 'Mật khẩu mới',
        'password_requirement' => 'Mật khẩu phải có ít nhất 6 ký tự',
        'reset_password_btn' => 'Đặt Lại Mật Khẩu',
        'request_new_link' => 'Yêu cầu link mới',
        
        // Common
        'submit' => 'Gửi',
        'cancel' => 'Hủy',
        'save' => 'Lưu',
        'edit' => 'Sửa',
        'delete' => 'Xóa',
        'view' => 'Xem',
        'back' => 'Quay lại',
        'continue' => 'Tiếp tục',
        'price' => 'Giá',
        'discount' => 'Giảm giá',
        'add_to_cart' => 'Thêm vào giỏ',
        'buy_now' => 'Mua ngay',
        'view_details' => 'Xem Chi Tiết',
        'products_count' => 'sản phẩm',
        
        // Index Page
        'hero_title' => 'Bộ Sưu Tập Mô Hình Cao Cấp',
        'hero_subtitle' => 'Khám phá thế giới mô hình đẳng cấp với hàng ngàn sản phẩm chất lượng',
        'view_products' => 'Xem Sản Phẩm',
        'product_categories' => 'Danh Mục Sản Phẩm',
        'featured_products' => 'Sản Phẩm Nổi Bật',
        
        // About Page
        'about_us' => 'Về Chúng Tôi',
        'trusted_since' => 'Đáng Tin Cậy Từ 2020',
        'about_subtitle' => 'Chúng tôi là điểm đến hàng đầu cho những người đam mê mô hình và đồ chơi cao cấp. Với hơn 5 năm kinh nghiệm, chúng tôi tự hào mang đến những sản phẩm chất lượng nhất từ khắp nơi trên thế giới.',
        'stat_products' => 'Sản Phẩm',
        'stat_customers' => 'Khách Hàng',
        'stat_rating' => 'Đánh Giá',
        'stat_delivery' => 'Giao Đúng Hẹn',
        'our_story' => 'Câu Chuyện Của Chúng Tôi',
        'why_choose_us' => 'Tại Sao Chọn Chúng Tôi?',
        'why_choose_subtitle' => 'Những giá trị cốt lõi làm nên sự khác biệt của KIENANSHOP',
        'authentic_products' => 'Hàng Chính Hãng 100%',
        'fast_delivery' => 'Giao Hàng Nhanh Chóng',
        'support_247' => 'Hỗ Trợ 24/7',
        'competitive_price' => 'Giá Cả Cạnh Tranh',
        'easy_return' => 'Đổi Trả Dễ Dàng',
        'attractive_gifts' => 'Quà Tặng Hấp Dẫn',
        'ready_explore' => 'Sẵn Sàng Khám Phá?',
        'start_journey' => 'Hãy bắt đầu hành trình sưu tầm của bạn cùng KIENANSHOP ngay hôm nay!',
        'contact_now' => 'Liên Hệ Ngay',
    ],
    'en' => [
        // Header
        'hotline' => 'Hotline',
        'home' => 'Home',
        'products' => 'Products',
        'about' => 'About',
        'contact' => 'Contact',
        'search_placeholder' => 'Search products...',
        'cart' => 'Cart',
        'account' => 'Account',
        'login' => 'Login',
        'register' => 'Register',
        'logout' => 'Logout',
        
        // Categories
        'categories' => 'Categories',
        'all_products' => 'All Products',
        'gundam' => 'Gundam',
        'car_model' => 'Car Models',
        'airplane' => 'Airplanes',
        'warship' => 'Warships',
        'character' => 'Characters',
        'lego' => 'Lego',
        
        // Auth
        'email' => 'Email',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'fullname' => 'Full Name',
        'phone' => 'Phone Number',
        'address' => 'Address',
        'forgot_password' => 'Forgot password?',
        'no_account' => "Don't have an account?",
        'register_now' => 'Register now',
        'have_account' => 'Already have an account?',
        'login_now' => 'Login',
        'remember_password' => 'Remember password?',
        
        // Forgot Password
        'forgot_password_title' => 'Forgot Password',
        'forgot_password_desc' => 'Enter your email to receive password reset link',
        'send_reset_link' => 'Send Reset Link',
        'reset_password_title' => 'Reset Password',
        'reset_password_desc' => 'Enter your new password',
        'new_password' => 'New Password',
        'password_requirement' => 'Password must be at least 6 characters',
        'reset_password_btn' => 'Reset Password',
        'request_new_link' => 'Request new link',
        
        // Common
        'submit' => 'Submit',
        'cancel' => 'Cancel',
        'save' => 'Save',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'view' => 'View',
        'back' => 'Back',
        'continue' => 'Continue',
        'price' => 'Price',
        'discount' => 'Discount',
        'add_to_cart' => 'Add to Cart',
        'buy_now' => 'Buy Now',
        'view_details' => 'View Details',
        'products_count' => 'products',
        
        // Index Page
        'hero_title' => 'Premium Model Collection',
        'hero_subtitle' => 'Discover the world of premium models with thousands of quality products',
        'view_products' => 'View Products',
        'product_categories' => 'Product Categories',
        'featured_products' => 'Featured Products',
        
        // About Page
        'about_us' => 'About Us',
        'trusted_since' => 'Trusted Since 2020',
        'about_subtitle' => 'We are the premier destination for model and premium toy enthusiasts. With over 5 years of experience, we pride ourselves on bringing the finest products from around the world.',
        'stat_products' => 'Products',
        'stat_customers' => 'Customers',
        'stat_rating' => 'Rating',
        'stat_delivery' => 'On-Time Delivery',
        'our_story' => 'Our Story',
        'why_choose_us' => 'Why Choose Us?',
        'why_choose_subtitle' => 'Core values that make KIENANSHOP different',
        'authentic_products' => '100% Authentic Products',
        'fast_delivery' => 'Fast Delivery',
        'support_247' => '24/7 Support',
        'competitive_price' => 'Competitive Prices',
        'easy_return' => 'Easy Returns',
        'attractive_gifts' => 'Attractive Gifts',
        'ready_explore' => 'Ready to Explore?',
        'start_journey' => 'Start your collecting journey with KIENANSHOP today!',
        'contact_now' => 'Contact Now',
    ]
];

// Hàm lấy bản dịch
function t($key) {
    global $translations, $current_lang;
    return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
}

// Hàm lấy ngôn ngữ hiện tại
function getCurrentLang() {
    global $current_lang;
    return $current_lang;
}
?>
