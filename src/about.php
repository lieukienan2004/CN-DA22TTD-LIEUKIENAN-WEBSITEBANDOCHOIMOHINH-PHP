<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/language.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('about'); ?> - KIENANSHOP</title>
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/sakura-animation.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #ec4899;
            --primary-dark: #db2777;
            --secondary-color: #f472b6;
            --accent-color: #fb7185;
            --text-color: #1f2937;
            --text-light: #6b7280;
            --light-gray: #f9fafb;
            --border-color: #e5e7eb;
            --gradient-1: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            --gradient-2: linear-gradient(135deg, #f472b6 0%, #ec4899 100%);
            --gradient-3: linear-gradient(135deg, #fb7185 0%, #f43f5e 100%);
            --gradient-4: linear-gradient(135deg, #fda4af 0%, #fb923c 100%);
        }

        body {
            font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            background: var(--light-gray);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* About Hero Section */
        .about-hero {
            background: var(--gradient-1);
            color: white;
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .about-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            animation: float 20s linear infinite;
        }

        @keyframes float {
            from { transform: translateY(0); }
            to { transform: translateY(-100px); }
        }

        .about-hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            animation: fadeInUp 1s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .about-hero h1 {
            font-size: 56px;
            font-weight: 900;
            margin-bottom: 20px;
            letter-spacing: -2px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .about-hero .subtitle {
            font-size: 22px;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto 40px;
            line-height: 1.7;
        }

        .about-hero .highlight {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }

        /* Stats Section */
        .stats-section {
            background: white;
            padding: 60px 0;
            margin-top: -40px;
            position: relative;
            z-index: 2;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .stat-card {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-1);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: var(--gradient-1);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
            transition: all 0.3s ease;
        }

        .stat-card:nth-child(2) .stat-icon {
            background: var(--gradient-2);
            box-shadow: 0 8px 25px rgba(245, 87, 108, 0.3);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: var(--gradient-3);
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);
        }

        .stat-card:nth-child(4) .stat-icon {
            background: var(--gradient-4);
            box-shadow: 0 8px 25px rgba(250, 112, 154, 0.3);
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(-5deg);
        }

        .stat-number {
            font-size: 42px;
            font-weight: 900;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
            line-height: 1;
        }

        .stat-card:nth-child(2) .stat-number {
            background: var(--gradient-2);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card:nth-child(3) .stat-number {
            background: var(--gradient-3);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card:nth-child(4) .stat-number {
            background: var(--gradient-4);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 15px;
            font-weight: 600;
        }

        /* CTA Section */
        .cta-section {
            background: var(--gradient-1);
            color: white;
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="dots" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="3" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="3" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100%" height="100%" fill="url(%23dots)"/></svg>');
        }

        .cta-content {
            position: relative;
            z-index: 1;
        }

        .cta-content h2 {
            font-size: 48px;
            font-weight: 900;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .cta-content p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        .btn {
            display: inline-block;
            padding: 18px 45px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-white {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-white:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
            margin-left: 15px;
        }

        .btn-outline:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-3px);
        }

        /* About Content Section */
        .about-content {
            padding: 100px 0;
            background: white;
        }

        .about-intro {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 80px;
        }

        .intro-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.1) 0%, rgba(219, 39, 119, 0.1) 100%);
            padding: 12px 24px;
            border-radius: 50px;
            color: var(--primary-color);
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 30px;
            border: 2px solid rgba(236, 72, 153, 0.2);
        }

        .intro-badge i {
            font-size: 18px;
        }

        .intro-title {
            font-size: 48px;
            font-weight: 900;
            color: var(--text-color);
            margin-bottom: 25px;
            letter-spacing: -1px;
            line-height: 1.2;
        }

        .intro-description {
            font-size: 18px;
            color: var(--text-light);
            line-height: 1.8;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 35px;
            margin-bottom: 100px;
        }

        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--gradient-1);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(236, 72, 153, 0.2);
            border-color: rgba(236, 72, 153, 0.3);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            background: var(--gradient-1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            box-shadow: 0 10px 30px rgba(236, 72, 153, 0.3);
            transition: all 0.4s ease;
        }

        .feature-card:nth-child(2) .feature-icon {
            background: var(--gradient-2);
        }

        .feature-card:nth-child(3) .feature-icon {
            background: var(--gradient-3);
        }

        .feature-card:nth-child(4) .feature-icon {
            background: var(--gradient-4);
        }

        .feature-card:nth-child(5) .feature-icon {
            background: var(--gradient-1);
        }

        .feature-card:nth-child(6) .feature-icon {
            background: var(--gradient-2);
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.15) rotate(-5deg);
        }

        .feature-card h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 15px;
        }

        .feature-card p {
            font-size: 15px;
            color: var(--text-light);
            line-height: 1.7;
        }

        /* Story Section */
        .story-section {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.05) 0%, rgba(219, 39, 119, 0.05) 100%);
            border-radius: 30px;
            padding: 80px 60px;
            position: relative;
            overflow: hidden;
        }

        .story-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .story-content {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .story-text h3 {
            font-size: 36px;
            font-weight: 900;
            color: var(--text-color);
            margin-bottom: 25px;
            letter-spacing: -0.5px;
        }

        .story-text p {
            font-size: 16px;
            color: var(--text-light);
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .story-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 40px;
        }

        .story-stat {
            text-align: center;
            padding: 25px 15px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .story-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(236, 72, 153, 0.2);
        }

        .stat-num {
            display: block;
            font-size: 32px;
            font-weight: 900;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .stat-text {
            display: block;
            font-size: 13px;
            color: var(--text-light);
            font-weight: 600;
        }

        .story-image {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-wrapper {
            width: 300px;
            height: 300px;
            background: var(--gradient-1);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 60px rgba(236, 72, 153, 0.3);
            position: relative;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .image-wrapper::before {
            content: '';
            position: absolute;
            inset: -3px;
            background: var(--gradient-1);
            border-radius: 30px;
            opacity: 0.5;
            filter: blur(20px);
            z-index: -1;
        }

        .image-wrapper i {
            font-size: 120px;
            color: white;
            opacity: 0.9;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .story-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .story-image {
                order: -1;
            }
        }

        @media (max-width: 768px) {
            .about-hero h1 {
                font-size: 36px;
            }

            .about-hero .subtitle {
                font-size: 16px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .intro-title {
                font-size: 32px;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }

            .story-section {
                padding: 50px 30px;
            }

            .story-text h3 {
                font-size: 28px;
            }

            .story-stats {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .image-wrapper {
                width: 200px;
                height: 200px;
            }

            .image-wrapper i {
                font-size: 80px;
            }

            .cta-content h2 {
                font-size: 32px;
            }

            .btn-outline {
                margin-left: 0;
                margin-top: 15px;
                display: block;
                max-width: 300px;
                margin-left: auto;
                margin-right: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Sakura Container -->
    <div class="sakura-container" id="sakuraContainer"></div>
    
    <?php include 'includes/header.php'; ?>
    
    <!-- About Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="about-hero-content">
                <div class="highlight">
                    <i class="fas fa-star"></i> <?php echo t('trusted_since'); ?>
                </div>
                <h1><?php echo t('about_us'); ?></h1>
                <p class="subtitle">
                    <?php echo t('about_subtitle'); ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="stat-number">5000+</div>
                    <div class="stat-label"><?php echo t('stat_products'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number">15K+</div>
                    <div class="stat-label"><?php echo t('stat_customers'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-number">4.9/5</div>
                    <div class="stat-label"><?php echo t('stat_rating'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="stat-number">99%</div>
                    <div class="stat-label"><?php echo t('stat_delivery'); ?></div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Content Section -->
    <section class="about-content">
        <div class="container">
            <div class="about-intro">
                <div class="intro-badge">
                    <i class="fas fa-cube"></i>
                    <span>Về Chúng Tôi</span>
                </div>
                <h2 class="intro-title">Điểm Đến Hàng Đầu Cho Những Người Đam Mê Mô Hình</h2>
                <p class="intro-description">
                    Chúng tôi là điểm đến hàng đầu cho những người đam mê mô hình và đồ chơi cao cấp. 
                    Với hơn 5 năm kinh nghiệm, chúng tôi tự hào mang đến những sản phẩm chất lượng nhất 
                    từ khắp nơi trên thế giới.
                </p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Cam Kết Chất Lượng</h3>
                    <p>Mọi sản phẩm đều được kiểm tra kỹ lưỡng trước khi giao đến tay khách hàng. Chúng tôi cam kết 100% hàng chính hãng.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Giao Hàng Nhanh Chóng</h3>
                    <p>Hệ thống giao hàng toàn quốc với tốc độ nhanh nhất. Freeship cho đơn hàng từ 499k, giao hỏa tốc tại TP.HCM.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Hỗ Trợ 24/7</h3>
                    <p>Đội ngũ tư vấn chuyên nghiệp luôn sẵn sàng hỗ trợ bạn mọi lúc, mọi nơi. Giải đáp mọi thắc mắc về sản phẩm.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h3>Ưu Đãi Hấp Dẫn</h3>
                    <p>Chương trình khuyến mãi liên tục, tích điểm đổi quà, và nhiều ưu đãi đặc biệt dành cho khách hàng thân thiết.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3>Sản Phẩm Đa Dạng</h3>
                    <p>Hơn 5000+ mô hình từ Gundam, xe hơi, máy bay đến nhân vật anime. Cập nhật liên tục các sản phẩm mới nhất.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h3>Đổi Trả Dễ Dàng</h3>
                    <p>Chính sách đổi trả linh hoạt trong 7 ngày. Hoàn tiền 100% nếu sản phẩm không đúng như mô tả.</p>
                </div>
            </div>

            <div class="story-section">
                <div class="story-content">
                    <div class="story-text">
                        <h3>Câu Chuyện Của Chúng Tôi</h3>
                        <p>
                            KIENANSHOP được thành lập từ niềm đam mê với mô hình và đồ chơi cao cấp. 
                            Chúng tôi hiểu rằng mỗi mô hình không chỉ là một sản phẩm, mà còn là một 
                            tác phẩm nghệ thuật, một kỷ niệm, và một phần của tuổi thơ.
                        </p>
                        <p>
                            Với sứ mệnh mang đến những sản phẩm chất lượng nhất với giá cả hợp lý, 
                            chúng tôi đã và đang phục vụ hàng nghìn khách hàng trên toàn quốc. 
                            Mỗi đơn hàng được chúng tôi chăm sóc tận tình như chính món đồ của mình.
                        </p>
                        <div class="story-stats">
                            <div class="story-stat">
                                <span class="stat-num">5+</span>
                                <span class="stat-text">Năm Kinh Nghiệm</span>
                            </div>
                            <div class="story-stat">
                                <span class="stat-num">15K+</span>
                                <span class="stat-text">Khách Hàng Tin Tưởng</span>
                            </div>
                            <div class="story-stat">
                                <span class="stat-num">50+</span>
                                <span class="stat-text">Thương Hiệu Hợp Tác</span>
                            </div>
                        </div>
                    </div>
                    <div class="story-image">
                        <div class="image-wrapper">
                            <i class="fas fa-cube"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2><?php echo t('ready_explore'); ?></h2>
                <p><?php echo t('start_journey'); ?></p>
                <a href="products.php" class="btn btn-white">
                    <i class="fas fa-shopping-bag"></i> <?php echo t('view_products'); ?>
                </a>
                <a href="contact.html" class="btn btn-outline">
                    <i class="fas fa-phone"></i> <?php echo t('contact_now'); ?>
                </a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/dark-mode.js"></script>
    <script src="assets/js/sakura-animation.js"></script>
</body>
</html>
