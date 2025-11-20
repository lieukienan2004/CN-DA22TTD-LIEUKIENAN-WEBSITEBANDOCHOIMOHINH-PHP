<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

// Lấy ID tin nhắn cần highlight (nếu có)
$highlight_id = isset($_GET['highlight']) ? (int)$_GET['highlight'] : 0;

// Lấy danh sách tin nhắn của user (theo email)
$stmt = $conn->prepare("
    SELECT * FROM contact_messages 
    WHERE email = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin Nhắn Liên Hệ Của Tôi - KIENANSHOP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .contact-container {
            padding: 60px 20px;
            background: #f3f4f6;
            min-height: 70vh;
        }
        
        .contact-header {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .contact-header h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .message-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .message-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .message-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .message-subject {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
        }
        
        .message-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .status-new {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }
        
        .status-read {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }
        
        .status-replied {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }
        
        .message-meta {
            display: flex;
            gap: 20px;
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        
        .message-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .message-content {
            background: #f9fafb;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #667eea;
            line-height: 1.6;
            color: #374151;
            margin-bottom: 15px;
        }
        
        .message-replies {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f3f4f6;
        }
        
        .reply-item {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 10px;
            border-left: 4px solid #0ea5e9;
        }
        
        .reply-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 13px;
        }
        
        .reply-author {
            font-weight: 600;
            color: #0c4a6e;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .reply-time {
            color: #64748b;
        }
        
        .reply-content {
            color: #1e293b;
            line-height: 1.5;
        }
        
        .empty-state {
            background: white;
            padding: 80px 20px;
            text-align: center;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .empty-state i {
            font-size: 80px;
            color: #e5e7eb;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: var(--text-light);
            margin-bottom: 20px;
        }
        
        .btn-contact {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-contact:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        /* Highlight style */
        .message-highlight {
            animation: highlightPulse 2s ease-in-out;
            border: 3px solid #667eea !important;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.5) !important;
        }
        
        @keyframes highlightPulse {
            0%, 100% {
                box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
            }
            50% {
                box-shadow: 0 0 40px rgba(102, 126, 234, 0.8);
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    
    <div class="contact-container">
        <div class="container">
            <div class="contact-header">
                <h1>
                    <i class="fas fa-envelope"></i>
                    Tin Nhắn Liên Hệ Của Tôi
                </h1>
                <p>Xem lại các tin nhắn bạn đã gửi và phản hồi từ chúng tôi</p>
            </div>
            
            <?php if (empty($messages)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Chưa có tin nhắn nào</h3>
                <p>Bạn chưa gửi tin nhắn liên hệ nào. Hãy liên hệ với chúng tôi nếu cần hỗ trợ!</p>
                <a href="contact.html" class="btn-contact">
                    <i class="fas fa-paper-plane"></i> Gửi Tin Nhắn
                </a>
            </div>
            <?php else: ?>
            <div class="message-list">
                <?php foreach ($messages as $msg): 
                    $status_text = [
                        'new' => 'Chưa đọc',
                        'read' => 'Đã đọc',
                        'replied' => 'Đã trả lời'
                    ];
                    
                    // Lấy các câu trả lời
                    $reply_stmt = $conn->prepare("
                        SELECT tr.*, a.fullname as admin_name 
                        FROM traloithongbao tr 
                        LEFT JOIN admins a ON tr.admin_id = a.id 
                        WHERE tr.contact_id = ? 
                        ORDER BY tr.created_at ASC
                    ");
                    $reply_stmt->bind_param("i", $msg['id']);
                    $reply_stmt->execute();
                    $replies = $reply_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    
                    // Kiểm tra xem có phải tin nhắn cần highlight không
                    $is_highlighted = ($highlight_id > 0 && $msg['id'] == $highlight_id);
                    $highlight_class = $is_highlighted ? ' message-highlight' : '';
                ?>
                <div class="message-card<?php echo $highlight_class; ?>" id="message-<?php echo $msg['id']; ?>">
                    <div class="message-header">
                        <div class="message-subject">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($msg['subject']); ?>
                        </div>
                        <div class="message-status status-<?php echo $msg['status']; ?>">
                            <?php echo $status_text[$msg['status']]; ?>
                        </div>
                    </div>
                    
                    <div class="message-meta">
                        <span>
                            <i class="fas fa-clock"></i>
                            <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                        </span>
                        <span>
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($msg['name']); ?>
                        </span>
                        <span>
                            <i class="fas fa-phone"></i>
                            <?php echo htmlspecialchars($msg['phone']); ?>
                        </span>
                    </div>
                    
                    <div class="message-content">
                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    </div>
                    
                    <?php if (!empty($replies)): ?>
                    <div class="message-replies">
                        <h4 style="margin-bottom: 15px; color: #1f2937;">
                            <i class="fas fa-reply"></i> Phản hồi từ KIENANSHOP:
                        </h4>
                        <?php foreach ($replies as $reply): ?>
                        <div class="reply-item">
                            <div class="reply-header">
                                <div class="reply-author">
                                    <i class="fas fa-user-shield"></i>
                                    <?php echo htmlspecialchars($reply['admin_name'] ?? 'Admin'); ?>
                                </div>
                                <div class="reply-time">
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
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    
    <?php if ($highlight_id > 0): ?>
    <script>
        // Scroll đến tin nhắn được highlight
        window.addEventListener('load', function() {
            const highlightedMessage = document.getElementById('message-<?php echo $highlight_id; ?>');
            if (highlightedMessage) {
                setTimeout(function() {
                    highlightedMessage.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }, 500);
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
