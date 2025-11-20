<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

requireAdmin();

// Cập nhật admin online status
$admin_id = $_SESSION['admin_id'];
$conn->query("INSERT INTO admin_online (admin_id) VALUES ($admin_id) ON DUPLICATE KEY UPDATE last_seen = NOW()");

// Lấy danh sách chat sessions
$sessions = $conn->query("
    SELECT cs.*, 
           (SELECT COUNT(*) FROM chat_messages WHERE session_id = cs.session_id AND sender_type = 'user' AND is_read = 0) as unread_count,
           (SELECT message FROM chat_messages WHERE session_id = cs.session_id ORDER BY created_at DESC LIMIT 1) as last_message,
           (SELECT created_at FROM chat_messages WHERE session_id = cs.session_id ORDER BY created_at DESC LIMIT 1) as last_message_time
    FROM chat_sessions cs
    WHERE cs.status = 'active'
    ORDER BY cs.updated_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Lấy session hiện tại
$current_session = isset($_GET['session']) ? $_GET['session'] : ($sessions[0]['session_id'] ?? null);

// Lấy tin nhắn của session hiện tại
$messages = [];
if ($current_session) {
    $messages = $conn->query("
        SELECT m.*, p.name as product_name, p.image as product_image, p.price as product_price
        FROM chat_messages m
        LEFT JOIN products p ON m.product_id = p.id
        WHERE m.session_id = '$current_session'
        ORDER BY m.created_at ASC
    ")->fetch_all(MYSQLI_ASSOC);
    
    // Đánh dấu đã đọc
    $conn->query("UPDATE chat_messages SET is_read = 1 WHERE session_id = '$current_session' AND sender_type = 'user'");
}

// Lấy danh sách sản phẩm để gửi
$products = $conn->query("SELECT id, name, image, price FROM products WHERE status = 1 ORDER BY name ASC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Chat - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin-dark-mode.css">
    <style>
        .chat-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 0;
            height: calc(100vh - 140px);
            background: var(--bg-secondary);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .chat-sessions {
            background: var(--bg-tertiary);
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
        }
        
        .session-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            gap: 12px;
            align-items: start;
        }
        
        .session-item:hover {
            background: var(--bg-secondary);
        }
        
        .session-item.active {
            background: var(--bg-secondary);
            border-left: 4px solid #ec4899;
        }
        
        .session-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .session-info {
            flex: 1;
            min-width: 0;
        }
        
        .session-name {
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-primary);
        }
        
        .session-last-message {
            font-size: 13px;
            color: var(--text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .session-time {
            font-size: 11px;
            color: var(--text-secondary);
        }
        
        .session-unread {
            background: #ef4444;
            color: white;
            border-radius: 50%;
            min-width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }
        
        .chat-main {
            display: flex;
            flex-direction: column;
        }
        
        .chat-header-admin {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-tertiary);
        }
        
        .chat-header-admin h3 {
            margin: 0 0 5px 0;
            color: var(--text-primary);
        }
        
        .chat-header-admin p {
            margin: 0;
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .chat-messages-admin {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: var(--bg-primary);
        }
        
        .message-admin {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .message-admin.admin {
            flex-direction: row-reverse;
        }
        
        .message-avatar-admin {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        
        .message-admin.admin .message-avatar-admin {
            background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
        }
        
        .message-bubble-admin {
            background: white;
            padding: 12px 16px;
            border-radius: 18px;
            max-width: 60%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .message-admin.admin .message-bubble-admin {
            background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
            color: white;
        }
        
        .message-time-admin {
            font-size: 11px;
            color: var(--text-secondary);
            margin-top: 5px;
        }
        
        .chat-input-admin {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            background: var(--bg-secondary);
        }
        
        .input-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .chat-textarea {
            flex: 1;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 14px;
            resize: none;
            font-family: inherit;
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        .chat-textarea:focus {
            outline: none;
            border-color: #ec4899;
        }
        
        .btn-send-admin {
            padding: 12px 24px;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-send-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.4);
        }
        
        .product-selector {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .product-select {
            flex: 1;
            padding: 10px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        .btn-send-product {
            padding: 10px 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .btn-send-product:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 60px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-comments"></i> Quản lý Chat</h1>
                <p>Trả lời tin nhắn khách hàng</p>
            </div>
            
            <div class="chat-container">
                <!-- Sessions List -->
                <div class="chat-sessions">
                    <?php if (empty($sessions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Chưa có tin nhắn nào</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($sessions as $session): ?>
                    <div class="session-item <?php echo $session['session_id'] == $current_session ? 'active' : ''; ?>" 
                         onclick="window.location.href='?session=<?php echo $session['session_id']; ?>'">
                        <div class="session-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="session-info">
                            <div class="session-name"><?php echo htmlspecialchars($session['user_name']); ?></div>
                            <div class="session-last-message"><?php echo htmlspecialchars(substr($session['last_message'] ?? '', 0, 40)); ?></div>
                            <div class="session-time">
                                <?php 
                                if ($session['last_message_time']) {
                                    echo date('H:i d/m', strtotime($session['last_message_time']));
                                }
                                ?>
                            </div>
                        </div>
                        <?php if ($session['unread_count'] > 0): ?>
                        <div class="session-unread"><?php echo $session['unread_count']; ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Chat Main -->
                <div class="chat-main">
                    <?php if ($current_session): ?>
                    <?php 
                    $session_info = array_filter($sessions, fn($s) => $s['session_id'] == $current_session)[0] ?? null;
                    ?>
                    <div class="chat-header-admin">
                        <h3><?php echo htmlspecialchars($session_info['user_name'] ?? 'Khách'); ?></h3>
                        <p><?php echo htmlspecialchars($session_info['user_email'] ?? ''); ?></p>
                    </div>
                    
                    <div class="chat-messages-admin" id="chatMessages">
                        <?php foreach ($messages as $msg): ?>
                        <div class="message-admin <?php echo $msg['sender_type']; ?>">
                            <div class="message-avatar-admin">
                                <i class="fas fa-<?php echo $msg['sender_type'] == 'admin' ? 'headset' : 'user'; ?>"></i>
                            </div>
                            <div>
                                <?php if ($msg['message_type'] == 'product_link' && $msg['product_id']): ?>
                                <div class="product-link-message">
                                    <img src="../<?php echo $msg['product_image']; ?>" style="width: 200px; border-radius: 8px;">
                                    <div style="padding: 10px;">
                                        <strong><?php echo htmlspecialchars($msg['product_name']); ?></strong><br>
                                        <span style="color: #ec4899; font-weight: 700;"><?php echo number_format($msg['product_price']); ?>đ</span>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="message-bubble-admin">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </div>
                                <?php endif; ?>
                                <div class="message-time-admin">
                                    <?php echo date('H:i d/m/Y', strtotime($msg['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="chat-input-admin">
                        <form id="chatForm" onsubmit="sendMessage(event)">
                            <div class="input-row">
                                <textarea class="chat-textarea" id="messageInput" rows="2" 
                                          placeholder="Nhập tin nhắn..." required></textarea>
                                <button type="submit" class="btn-send-admin">
                                    <i class="fas fa-paper-plane"></i> Gửi
                                </button>
                            </div>
                        </form>
                        
                        <div class="product-selector">
                            <select class="product-select" id="productSelect">
                                <option value="">-- Chọn sản phẩm để gửi --</option>
                                <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?> - <?php echo number_format($product['price']); ?>đ
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn-send-product" onclick="sendProduct()">
                                <i class="fas fa-box"></i> Gửi sản phẩm
                            </button>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <p>Chọn một cuộc trò chuyện để bắt đầu</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/admin-dark-mode.js"></script>
    <script>
        const currentSession = '<?php echo $current_session; ?>';
        
        function sendMessage(e) {
            e.preventDefault();
            const message = document.getElementById('messageInput').value.trim();
            
            if (!message) return;
            
            fetch('../chat_admin_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send&session_id=${currentSession}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('messageInput').value = '';
                    location.reload();
                } else {
                    alert(data.message || 'Lỗi gửi tin nhắn');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi kết nối');
            });
        }
        
        function sendProduct() {
            const productId = document.getElementById('productSelect').value;
            
            if (!productId) {
                alert('Vui lòng chọn sản phẩm');
                return;
            }
            
            fetch('../chat_admin_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_product&session_id=${currentSession}&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('productSelect').value = '';
                    location.reload();
                } else {
                    alert(data.message || 'Lỗi gửi sản phẩm');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi kết nối');
            });
        }
        
        // Auto scroll to bottom
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Auto reload every 5 seconds
        setInterval(() => {
            if (currentSession) {
                location.reload();
            }
        }, 5000);
    </script>
</body>
</html>
