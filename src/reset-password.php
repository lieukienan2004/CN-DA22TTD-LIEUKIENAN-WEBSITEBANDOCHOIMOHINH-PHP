<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/language.php';

// Nếu đã đăng nhập, chuyển về trang account
if (isLoggedIn()) {
    header('Location: account.php');
    exit;
}

$message = '';
$error = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';
$valid_token = false;

// Kiểm tra token
if (!empty($token)) {
    $stmt = $conn->prepare("SELECT email, expires_at, used FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $reset = $result->fetch_assoc();
        
        // Kiểm tra token đã được sử dụng chưa
        if ($reset['used'] == 1) {
            $error = 'Link này đã được sử dụng';
        }
        // Kiểm tra token còn hiệu lực không
        else if (strtotime($reset['expires_at']) < time()) {
            $error = 'Link đã hết hạn. Vui lòng yêu cầu link mới';
        } else {
            $valid_token = true;
            $email = $reset['email'];
        }
    } else {
        $error = 'Link không hợp lệ';
    }
} else {
    $error = 'Thiếu token xác thực';
}

// Xử lý form đặt lại mật khẩu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } else if (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } else if ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        // Cập nhật mật khẩu mới
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        
        if ($stmt->execute()) {
            // Đánh dấu token đã được sử dụng
            $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            
            // Chuyển về trang đăng nhập với thông báo thành công
            header('Location: login.php?message=' . urlencode('Đặt lại mật khẩu thành công. Vui lòng đăng nhập'));
            exit;
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('reset_password_title'); ?> - KIENANSHOP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
    <link rel="stylesheet" href="assets/css/footer.css?v=2.0">
    <link rel="stylesheet" href="assets/css/language-switcher.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .auth-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: var(--light-gray);
        }
        
        .auth-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 450px;
            width: 100%;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-header h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-color);
            margin-bottom: 10px;
        }
        
        .auth-header p {
            color: var(--text-light);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--gradient-1);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }
        
        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            color: var(--text-light);
        }
        
        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .password-requirements {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><?php echo t('reset_password_title'); ?></h1>
                <p><?php echo t('reset_password_desc'); ?></p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($valid_token): ?>
            <form method="POST">
                <div class="form-group">
                    <label><?php echo t('new_password'); ?></label>
                    <input type="password" name="password" required minlength="6" placeholder="<?php echo getCurrentLang() == 'vi' ? 'Nhập mật khẩu mới' : 'Enter new password'; ?>">
                    <div class="password-requirements">
                        <i class="fas fa-info-circle"></i> <?php echo t('password_requirement'); ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><?php echo t('confirm_password'); ?></label>
                    <input type="password" name="confirm_password" required minlength="6" placeholder="<?php echo getCurrentLang() == 'vi' ? 'Nhập lại mật khẩu mới' : 'Re-enter new password'; ?>">
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-key"></i> <?php echo t('reset_password_btn'); ?>
                </button>
            </form>
            <?php else: ?>
            <div class="auth-footer">
                <a href="forgot-password.php"><?php echo t('request_new_link'); ?></a> <?php echo getCurrentLang() == 'vi' ? 'hoặc' : 'or'; ?> <a href="login.php"><?php echo t('login'); ?></a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
