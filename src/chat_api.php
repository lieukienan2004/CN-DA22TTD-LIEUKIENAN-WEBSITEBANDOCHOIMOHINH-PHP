<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Tạo hoặc lấy session ID
function getSessionId() {
    if (!isset($_SESSION['chat_session_id'])) {
        $_SESSION['chat_session_id'] = uniqid('chat_', true);
    }
    return $_SESSION['chat_session_id'];
}

switch ($action) {
    case 'init':
        // Khởi tạo chat session
        $session_id = getSessionId();
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $user_name = $_POST['name'] ?? 'Khách';
        $user_email = $_POST['email'] ?? '';
        
        // Kiểm tra session đã tồn tại chưa
        $check = $conn->prepare("SELECT id FROM chat_sessions WHERE session_id = ?");
        $check->bind_param("s", $session_id);
        $check->execute();
        
        if ($check->get_result()->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO chat_sessions (user_id, session_id, user_name, user_email) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $session_id, $user_name, $user_email);
            $stmt->execute();
        }
        
        echo json_encode([
            'success' => true,
            'session_id' => $session_id,
            'user_name' => $user_name
        ]);
        break;
        
    case 'send':
        // Gửi tin nhắn
        $session_id = getSessionId();
        $message = trim($_POST['message'] ?? '');
        $sender_type = 'user';
        
        if (empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Tin nhắn không được để trống']);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO chat_messages (session_id, sender_type, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $session_id, $sender_type, $message);
        
        if ($stmt->execute()) {
            // Cập nhật thời gian session
            $conn->query("UPDATE chat_sessions SET updated_at = NOW() WHERE session_id = '$session_id'");
            
            echo json_encode([
                'success' => true,
                'message_id' => $stmt->insert_id,
                'timestamp' => date('H:i')
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi gửi tin nhắn']);
        }
        break;
        
    case 'get_messages':
        // Lấy tin nhắn
        $session_id = getSessionId();
        $last_id = intval($_GET['last_id'] ?? 0);
        
        $stmt = $conn->prepare("
            SELECT m.*, p.name as product_name, p.image as product_image, p.price as product_price
            FROM chat_messages m
            LEFT JOIN products p ON m.product_id = p.id
            WHERE m.session_id = ? AND m.id > ?
            ORDER BY m.created_at ASC
        ");
        $stmt->bind_param("si", $session_id, $last_id);
        $stmt->execute();
        $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Đánh dấu đã đọc (nếu là user)
        if (!empty($messages)) {
            $conn->query("UPDATE chat_messages SET is_read = 1 WHERE session_id = '$session_id' AND sender_type = 'admin' AND is_read = 0");
        }
        
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
        break;
        
    case 'check_admin_online':
        // Kiểm tra admin có online không
        $result = $conn->query("SELECT COUNT(*) as count FROM admin_online WHERE last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        $online = $result->fetch_assoc()['count'] > 0;
        
        echo json_encode([
            'success' => true,
            'online' => $online
        ]);
        break;
        
    case 'get_unread_count':
        // Đếm tin nhắn chưa đọc
        $session_id = getSessionId();
        $result = $conn->query("SELECT COUNT(*) as count FROM chat_messages WHERE session_id = '$session_id' AND sender_type = 'admin' AND is_read = 0");
        $count = $result->fetch_assoc()['count'];
        
        echo json_encode([
            'success' => true,
            'count' => $count
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
