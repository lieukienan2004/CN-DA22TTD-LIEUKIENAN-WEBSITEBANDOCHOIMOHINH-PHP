<?php
session_start();
require_once 'config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: admin_contacts.php');
    exit;
}

// Lấy thông tin tin nhắn
$sql = "SELECT * FROM contact_messages WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: admin_contacts.php');
    exit;
}

$message = $result->fetch_assoc();

// Cập nhật trạng thái thành "đã đọc" nếu đang là "mới"
if ($message['status'] === 'new') {
    $updateSql = "UPDATE contact_messages SET status = 'read' WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("i", $id);
    $updateStmt->execute();
    $updateStmt->close();
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Tin Nhắn #<?php echo $id; ?> - KIENANSHOP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #6366f1;
            --text-color: #1f2937;
            --text-light: #6b7280;
            --light-gray: #f9fafb;
            --border-color: #e5e7eb;
            --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-gray);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--gradient-1);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .message-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .message-header {
            background: var(--gradient-1);
            color: white;
            padding: 30px;
        }

        .message-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .message-meta {
            display: flex;
            gap: 20px;
            font-size: 14px;
            opacity: 0.9;
        }

        .message-body {
            padding: 40px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 35px;
        }

        .info-item {
            background: var(--light-gray);
            padding: 20px;
            border-radius: 12px;
        }

        .info-item label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-light);
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .info-item .value {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
        }

        .message-content {
            background: var(--light-gray);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .message-content label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-light);
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .message-content .text {
            font-size: 15px;
            line-height: 1.8;
            color: var(--text-color);
            white-space: pre-wrap;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-new {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-read {
            background: #d1fae5;
            color: #065f46;
        }

        .status-replied {
            background: #fef3c7;
            color: #92400e;
        }

        .actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_contacts.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Quay Lại Danh Sách
        </a>

        <div class="message-card">
            <div class="message-header">
                <h1><i class="fas fa-envelope-open"></i> Tin Nhắn #<?php echo $message['id']; ?></h1>
                <div class="message-meta">
                    <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?></span>
                    <?php
                    $statusClass = 'status-' . $message['status'];
                    $statusText = [
                        'new' => 'Mới',
                        'read' => 'Đã đọc',
                        'replied' => 'Đã trả lời'
                    ];
                    ?>
                    <span class="status-badge <?php echo $statusClass; ?>">
                        <?php echo $statusText[$message['status']] ?? 'Mới'; ?>
                    </span>
                </div>
            </div>

            <div class="message-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label><i class="fas fa-user"></i> Họ và Tên</label>
                        <div class="value"><?php echo htmlspecialchars($message['name']); ?></div>
                    </div>

                    <div class="info-item">
                        <label><i class="fas fa-phone"></i> Số Điện Thoại</label>
                        <div class="value">
                            <a href="tel:<?php echo $message['phone']; ?>" style="color: var(--primary-color); text-decoration: none;">
                                <?php echo htmlspecialchars($message['phone']); ?>
                            </a>
                        </div>
                    </div>

                    <div class="info-item">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <div class="value">
                            <a href="mailto:<?php echo $message['email']; ?>" style="color: var(--primary-color); text-decoration: none;">
                                <?php echo htmlspecialchars($message['email']); ?>
                            </a>
                        </div>
                    </div>

                    <div class="info-item">
                        <label><i class="fas fa-tag"></i> Chủ Đề</label>
                        <div class="value">
                            <?php
                            $subjects = [
                                'general' => 'Tư vấn chung',
                                'product' => 'Hỏi về sản phẩm',
                                'order' => 'Đơn hàng',
                                'complaint' => 'Khiếu nại',
                                'other' => 'Khác'
                            ];
                            echo $subjects[$message['subject']] ?? 'Khác';
                            ?>
                        </div>
                    </div>
                </div>

                <div class="message-content">
                    <label><i class="fas fa-comment-alt"></i> Nội Dung Tin Nhắn</label>
                    <div class="text"><?php echo htmlspecialchars($message['message']); ?></div>
                </div>

                <div class="actions">
                    <a href="mailto:<?php echo $message['email']; ?>?subject=Re: <?php echo urlencode($subjects[$message['subject']] ?? 'Liên hệ'); ?>" class="btn btn-primary">
                        <i class="fas fa-reply"></i>
                        Trả Lời Email
                    </a>
                    <a href="tel:<?php echo $message['phone']; ?>" class="btn btn-primary">
                        <i class="fas fa-phone"></i>
                        Gọi Điện
                    </a>
                    <button onclick="deleteMessage(<?php echo $message['id']; ?>)" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                        Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deleteMessage(id) {
            if (confirm('Bạn có chắc chắn muốn xóa tin nhắn này?')) {
                window.location.href = 'delete_contact.php?id=' + id;
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
