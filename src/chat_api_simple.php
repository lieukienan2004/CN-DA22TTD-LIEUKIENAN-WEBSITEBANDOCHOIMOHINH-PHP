<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    // Tạo session ID dựa trên user_id nếu đã đăng nhập
    if (isset($_SESSION['user_id'])) {
        // Nếu đã đăng nhập, dùng user_id làm session
        $session_id = 'user_' . $_SESSION['user_id'];
    } else {
        // Nếu chưa đăng nhập, dùng session tạm
        if (!isset($_SESSION['chat_session_id'])) {
            $_SESSION['chat_session_id'] = uniqid('guest_', true);
        }
        $session_id = $_SESSION['chat_session_id'];
    }
    
    switch ($action) {
        case 'init':
            // Tạo session trong DB
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $user_name = 'Khách';
            
            $check = $conn->query("SELECT id FROM chat_sessions WHERE session_id = '$session_id'");
            if ($check->num_rows == 0) {
                $conn->query("INSERT INTO chat_sessions (user_id, session_id, user_name) VALUES ($user_id, '$session_id', '$user_name')");
            }
            
            echo json_encode(['success' => true, 'session_id' => $session_id]);
            break;
            
        case 'send':
            $message = $conn->real_escape_string(trim($_POST['message'] ?? ''));
            if (empty($message)) {
                echo json_encode(['success' => false, 'message' => 'Tin nhắn trống']);
                exit;
            }
            
            $conn->query("INSERT INTO chat_messages (session_id, sender_type, message) VALUES ('$session_id', 'user', '$message')");
            echo json_encode(['success' => true, 'message_id' => $conn->insert_id]);
            break;
            
        case 'get_messages':
            $last_id = intval($_GET['last_id'] ?? 0);
            $result = $conn->query("SELECT * FROM chat_messages WHERE session_id = '$session_id' AND id > $last_id ORDER BY created_at ASC");
            $messages = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'messages' => $messages]);
            break;
            
        case 'check_admin_online':
            echo json_encode(['success' => true, 'online' => false]);
            break;
            
        case 'get_unread_count':
            echo json_encode(['success' => true, 'count' => 0]);
            break;
            
        case 'get_categories':
            $result = $conn->query("SELECT id, name FROM categories ORDER BY name");
            $categories = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'categories' => $categories]);
            break;
            
        case 'get_products':
            $category_id = intval($_GET['category_id'] ?? 0);
            $result = $conn->query("SELECT id, name, price FROM products WHERE category_id = $category_id ORDER BY name LIMIT 10");
            $products = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'products' => $products]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
