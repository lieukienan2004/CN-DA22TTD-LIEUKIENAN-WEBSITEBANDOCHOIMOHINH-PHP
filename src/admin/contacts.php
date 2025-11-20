<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

// Tạo bảng contact_messages nếu chưa có
$conn->query("CREATE TABLE IF NOT EXISTS contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Xử lý xóa tin nhắn
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM contact_messages WHERE id = $id");
    logActivity($conn, 'delete_contact', "Xóa tin nhắn ID: $id");
    header('Location: contacts.php?success=deleted');
    exit;
}

// Xử lý đánh dấu đã đọc
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $conn->query("UPDATE contact_messages SET status = 'read' WHERE id = $id");
    logActivity($conn, 'mark_contact_read', "Đánh dấu đã đọc tin nhắn ID: $id");
    header('Location: contacts.php?success=marked');
    exit;
}

// Lấy danh sách tin nhắn
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$where = "WHERE 1=1";
if ($status_filter != 'all') {
    $where .= " AND status = '$status_filter'";
}

$contacts_result = $conn->query("SELECT * FROM contact_messages $where ORDER BY created_at DESC");
$contacts = $contacts_result ? $contacts_result->fetch_all(MYSQLI_ASSOC) : [];

// Thống kê
$total_result = $conn->query("SELECT COUNT(*) as total FROM contact_messages");
$pending_result = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'new'");
$read_result = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE status IN ('read', 'replied')");

$stats = [
    'total' => $total_result ? $total_result->fetch_assoc()['total'] : 0,
    'pending' => $pending_result ? $pending_result->fetch_assoc()['total'] : 0,
    'read' => $read_result ? $read_result->fetch_assoc()['total'] : 0
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tin nhắn - Admin</title>
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
                    <h1><i class="fas fa-envelope"></i> Quản lý Tin nhắn</h1>
                    <p>Xem và quản lý tin nhắn từ khách hàng</p>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <?php 
                    if ($_GET['success'] == 'deleted') echo 'Xóa tin nhắn thành công!';
                    if ($_GET['success'] == 'marked') echo 'Đã đánh dấu đã đọc!';
                ?>
            </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Tổng tin nhắn</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['pending']; ?></h3>
                        <p>Chưa đọc</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['read']; ?></h3>
                        <p>Đã đọc</p>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="filters-bar">
                <div class="filter-tabs">
                    <a href="?status=all" class="filter-tab <?php echo $status_filter == 'all' ? 'active' : ''; ?>">
                        Tất cả (<?php echo $stats['total']; ?>)
                    </a>
                    <a href="?status=new" class="filter-tab <?php echo $status_filter == 'new' ? 'active' : ''; ?>">
                        Chưa đọc (<?php echo $stats['pending']; ?>)
                    </a>
                    <a href="?status=read" class="filter-tab <?php echo $status_filter == 'read' ? 'active' : ''; ?>">
                        Đã đọc (<?php echo $stats['read']; ?>)
                    </a>
                </div>
            </div>
            
            <!-- Messages List -->
            <div class="dashboard-card">
                <div class="messages-list">
                    <?php if (empty($contacts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Chưa có tin nhắn</h3>
                        <p>Chưa có tin nhắn nào từ khách hàng</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($contacts as $contact): ?>
                    <div class="message-item <?php echo $contact['status'] == 'new' ? 'unread' : ''; ?>">
                        <div class="message-header">
                            <div class="message-sender">
                                <div class="sender-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="sender-info">
                                    <h4><?php echo htmlspecialchars($contact['name']); ?></h4>
                                    <p>
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact['email']); ?>
                                        <?php if ($contact['phone']): ?>
                                        <span class="separator">•</span>
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact['phone']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="message-meta">
                                <span class="message-time">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?>
                                </span>
                                <span class="badge <?php echo $contact['status'] == 'new' ? 'badge-pending' : 'badge-completed'; ?>">
                                    <?php 
                                        if ($contact['status'] == 'new') echo 'Chưa đọc';
                                        elseif ($contact['status'] == 'read') echo 'Đã đọc';
                                        else echo 'Đã trả lời';
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="message-subject">
                            <strong>Chủ đề:</strong> <?php echo htmlspecialchars($contact['subject']); ?>
                        </div>
                        
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($contact['message'])); ?>
                        </div>
                        
                        <div class="message-actions">
                            <?php if ($contact['status'] == 'new'): ?>
                            <a href="?mark_read=<?php echo $contact['id']; ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-check"></i> Đánh dấu đã đọc
                            </a>
                            <?php endif; ?>
                            <a href="contact_reply.php?id=<?php echo $contact['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-reply"></i> Trả lời
                            </a>
                            <a href="mailto:<?php echo $contact['email']; ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-envelope"></i> Email
                            </a>
                            <a href="?delete=<?php echo $contact['id']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Xóa tin nhắn này?')">
                                <i class="fas fa-trash"></i> Xóa
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .messages-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 24px;
        }
        
        .message-item {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .message-item.unread {
            background: linear-gradient(135deg, #fef3f8 0%, #fef3f8 100%);
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }
        
        .message-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .message-sender {
            display: flex;
            gap: 12px;
        }
        
        .sender-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .sender-info h4 {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .sender-info p {
            font-size: 13px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .separator {
            color: #d1d5db;
        }
        
        .message-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }
        
        .message-time {
            font-size: 13px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .message-subject {
            font-size: 14px;
            color: #374151;
            margin-bottom: 12px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
        }
        
        .message-content {
            font-size: 14px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 16px;
            padding: 16px;
            background: #fafafa;
            border-radius: 8px;
            border-left: 4px solid #ec4899;
        }
        
        .message-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 20px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        
        .filter-tabs {
            display: flex;
            gap: 8px;
        }
        
        .filter-tab {
            padding: 10px 20px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            color: #6b7280;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .filter-tab:hover {
            border-color: #ec4899;
            color: #ec4899;
        }
        
        .filter-tab.active {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            border-color: #ec4899;
            color: white;
        }
    </style>
</body>
</html>
