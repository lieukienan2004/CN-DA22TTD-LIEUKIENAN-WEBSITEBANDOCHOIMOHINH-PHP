<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: contacts.php');
    exit;
}

$contact_id = (int)$_GET['id'];

// Lấy thông tin tin nhắn
$stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->bind_param("i", $contact_id);
$stmt->execute();
$contact = $stmt->get_result()->fetch_assoc();

if (!$contact) {
    header('Location: contacts.php');
    exit;
}

// Xử lý gửi trả lời
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message'])) {
    $reply_message = trim($_POST['reply_message']);
    
    if (!empty($reply_message)) {
        // Lưu trả lời vào database
        $stmt = $conn->prepare("INSERT INTO traloithongbao (contact_id, admin_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $contact_id, $_SESSION['user_id'], $reply_message);
        
        if ($stmt->execute()) {
            // Cập nhật trạng thái tin nhắn
            $conn->query("UPDATE contact_messages SET status = 'replied' WHERE id = $contact_id");
            
            // Tạo thông báo cho người dùng (nếu họ có tài khoản)
            $user_check = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($contact['email']) . "'");
            if ($user_check && $user_check->num_rows > 0) {
                $user = $user_check->fetch_assoc();
                
                // Kiểm tra xem bảng có cột contact_id không
                $columns = $conn->query("SHOW COLUMNS FROM thongbao LIKE 'contact_id'");
                $has_contact_id = ($columns && $columns->num_rows > 0);
                
                $notif_title = "Phản hồi từ KIENANSHOP";
                $notif_message = "Chúng tôi đã trả lời tin nhắn của bạn về: " . $contact['subject'];
                $notif_link = "view_my_contact.php?highlight=" . $contact_id;
                
                if ($has_contact_id) {
                    $notif_stmt = $conn->prepare("
                        INSERT INTO thongbao (user_id, user_type, type, title, message, link, contact_id, created_at) 
                        VALUES (?, 'user', 'contact', ?, ?, ?, ?, NOW())
                    ");
                    $notif_stmt->bind_param("isssi", $user['id'], $notif_title, $notif_message, $notif_link, $contact_id);
                } else {
                    $notif_stmt = $conn->prepare("
                        INSERT INTO thongbao (user_id, user_type, type, title, message, link, created_at) 
                        VALUES (?, 'user', 'contact', ?, ?, ?, NOW())
                    ");
                    $notif_stmt->bind_param("isss", $user['id'], $notif_title, $notif_message, $notif_link);
                }
                $notif_stmt->execute();
            }
            
            logActivity($conn, 'reply_contact', "Trả lời tin nhắn ID: $contact_id");
            header('Location: contacts.php?success=replied');
            exit;
        }
    }
}

// Đánh dấu đã đọc
if ($contact['status'] == 'new') {
    $conn->query("UPDATE contact_messages SET status = 'read' WHERE id = $contact_id");
}

// Lấy lịch sử trả lời
$replies_result = $conn->query("
    SELECT cr.*, u.fullname as admin_name 
    FROM traloithongbao cr 
    LEFT JOIN users u ON cr.admin_id = u.id 
    WHERE cr.contact_id = $contact_id 
    ORDER BY cr.created_at ASC
");
$replies = $replies_result ? $replies_result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trả lời Tin nhắn - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <div class="page-header">
                <div>
                    <a href="contacts.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                    <h1 style="margin-top: 15px;"><i class="fas fa-reply"></i> Trả lời Tin nhắn</h1>
                </div>
            </div>
            
            <div class="dashboard-card">
                <!-- Thông tin người gửi -->
                <div class="contact-info-header">
                    <div class="sender-avatar-large">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="sender-details">
                        <h2><?php echo htmlspecialchars($contact['name']); ?></h2>
                        <div class="contact-meta">
                            <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact['email']); ?></span>
                            <?php if ($contact['phone']): ?>
                            <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact['phone']); ?></span>
                            <?php endif; ?>
                            <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="contact-status">
                        <span class="badge <?php echo $contact['status'] == 'new' ? 'badge-pending' : ($contact['status'] == 'replied' ? 'badge-completed' : 'badge-processing'); ?>">
                            <?php 
                                if ($contact['status'] == 'new') echo 'Chưa đọc';
                                elseif ($contact['status'] == 'read') echo 'Đã đọc';
                                else echo 'Đã trả lời';
                            ?>
                        </span>
                    </div>
                </div>
                
                <!-- Tin nhắn gốc -->
                <div class="original-message">
                    <div class="message-label">
                        <i class="fas fa-tag"></i> Chủ đề: <strong><?php echo htmlspecialchars($contact['subject']); ?></strong>
                    </div>
                    <div class="message-content-box">
                        <?php echo nl2br(htmlspecialchars($contact['message'])); ?>
                    </div>
                </div>
                
                <!-- Lịch sử trả lời -->
                <?php if (!empty($replies)): ?>
                <div class="replies-history">
                    <h3><i class="fas fa-comments"></i> Lịch sử trả lời</h3>
                    <?php foreach ($replies as $reply): ?>
                    <div class="reply-item">
                        <div class="reply-header">
                            <div class="reply-author">
                                <i class="fas fa-user-shield"></i>
                                <strong><?php echo htmlspecialchars($reply['admin_name']); ?></strong>
                                <span class="badge badge-info">Admin</span>
                            </div>
                            <div class="reply-time">
                                <i class="fas fa-clock"></i>
                                <?php echo date('d/m/Y H:i', strtotime($reply['created_at'])); ?>
                            </div>
                        </div>
                        <div class="reply-content">
                            <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Form trả lời -->
                <div class="reply-form-section">
                    <h3><i class="fas fa-pen"></i> Gửi trả lời</h3>
                    <form method="POST" class="reply-form">
                        <div class="form-group">
                            <label>Nội dung trả lời</label>
                            <textarea name="reply_message" rows="8" class="form-control" required 
                                placeholder="Nhập nội dung trả lời cho khách hàng..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Gửi trả lời
                            </button>
                            <a href="mailto:<?php echo $contact['email']; ?>" class="btn btn-secondary">
                                <i class="fas fa-envelope"></i> Gửi qua Email
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .contact-info-header {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            margin: -24px -24px 24px -24px;
        }
        
        .sender-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .sender-details {
            flex: 1;
        }
        
        .sender-details h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .contact-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            font-size: 14px;
            opacity: 0.95;
        }
        
        .contact-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .contact-status {
            align-self: flex-start;
        }
        
        .original-message {
            background: #f9fafb;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        
        .message-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .message-content-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            line-height: 1.6;
            color: #374151;
        }
        
        .replies-history {
            margin-bottom: 24px;
        }
        
        .replies-history h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .reply-item {
            background: #f0f9ff;
            border: 2px solid #bae6fd;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 12px;
        }
        
        .reply-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #bae6fd;
        }
        
        .reply-author {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #0c4a6e;
        }
        
        .reply-time {
            font-size: 13px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .reply-content {
            font-size: 14px;
            line-height: 1.6;
            color: #1e293b;
        }
        
        .reply-form-section {
            background: #fefce8;
            padding: 24px;
            border-radius: 12px;
            border: 2px solid #fde047;
        }
        
        .reply-form-section h3 {
            font-size: 18px;
            font-weight: 700;
            color: #713f12;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .reply-form textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #fde047;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
        }
        
        .reply-form textarea:focus {
            outline: none;
            border-color: #facc15;
            box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }
        
        .badge-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 4px;
        }
    </style>
</body>
</html>
