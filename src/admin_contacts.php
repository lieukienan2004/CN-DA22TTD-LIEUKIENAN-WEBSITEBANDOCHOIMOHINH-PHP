<?php
session_start();
require_once 'config/database.php';

// Lấy danh sách tin nhắn
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tin Nhắn Liên Hệ - KIENANSHOP</title>
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
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .header {
            background: white;
            padding: 25px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 800;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .stat-card h3 {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary-color);
        }

        .messages-table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table-header {
            padding: 25px;
            border-bottom: 2px solid var(--border-color);
        }

        .table-header h2 {
            font-size: 24px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--light-gray);
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            color: var(--text-light);
            letter-spacing: 0.5px;
        }

        td {
            padding: 20px 15px;
            border-bottom: 1px solid var(--border-color);
        }

        tbody tr:hover {
            background: var(--light-gray);
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
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

        .subject-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            background: var(--light-gray);
            color: var(--text-color);
        }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-view {
            background: var(--primary-color);
            color: white;
        }

        .btn-view:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: var(--danger-color);
            color: white;
            margin-left: 5px;
        }

        .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .no-messages {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .no-messages i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
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
            margin-bottom: 20px;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Về Trang Chủ
            </a>
            <h1><i class="fas fa-envelope"></i> Quản Lý Tin Nhắn Liên Hệ</h1>
        </div>
    </div>

    <div class="container">
        <?php
        // Thống kê
        $total = $result->num_rows;
        $new = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status='new'")->fetch_assoc()['count'];
        $read = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status='read'")->fetch_assoc()['count'];
        $replied = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status='replied'")->fetch_assoc()['count'];
        ?>

        <div class="stats">
            <div class="stat-card">
                <h3>Tổng Tin Nhắn</h3>
                <div class="number"><?php echo $total; ?></div>
            </div>
            <div class="stat-card">
                <h3>Tin Nhắn Mới</h3>
                <div class="number" style="color: #3b82f6;"><?php echo $new; ?></div>
            </div>
            <div class="stat-card">
                <h3>Đã Đọc</h3>
                <div class="number" style="color: #10b981;"><?php echo $read; ?></div>
            </div>
            <div class="stat-card">
                <h3>Đã Trả Lời</h3>
                <div class="number" style="color: #f59e0b;"><?php echo $replied; ?></div>
            </div>
        </div>

        <div class="messages-table">
            <div class="table-header">
                <h2>Danh Sách Tin Nhắn</h2>
            </div>

            <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ Tên</th>
                        <th>Liên Hệ</th>
                        <th>Chủ Đề</th>
                        <th>Nội Dung</th>
                        <th>Trạng Thái</th>
                        <th>Thời Gian</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?php echo $row['id']; ?></strong></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                        </td>
                        <td>
                            <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['phone']); ?></div>
                            <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?></div>
                        </td>
                        <td>
                            <?php
                            $subjects = [
                                'general' => 'Tư vấn chung',
                                'product' => 'Hỏi về sản phẩm',
                                'order' => 'Đơn hàng',
                                'complaint' => 'Khiếu nại',
                                'other' => 'Khác'
                            ];
                            ?>
                            <span class="subject-badge"><?php echo $subjects[$row['subject']] ?? 'Khác'; ?></span>
                        </td>
                        <td>
                            <div class="message-preview"><?php echo htmlspecialchars($row['message']); ?></div>
                        </td>
                        <td>
                            <?php
                            $statusClass = 'status-' . $row['status'];
                            $statusText = [
                                'new' => 'Mới',
                                'read' => 'Đã đọc',
                                'replied' => 'Đã trả lời'
                            ];
                            ?>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo $statusText[$row['status']] ?? 'Mới'; ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $date = new DateTime($row['created_at']);
                            echo $date->format('d/m/Y H:i');
                            ?>
                        </td>
                        <td>
                            <a href="view_contact.php?id=<?php echo $row['id']; ?>" class="action-btn btn-view">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-messages">
                <i class="fas fa-inbox"></i>
                <h3>Chưa có tin nhắn nào</h3>
                <p>Các tin nhắn liên hệ từ khách hàng sẽ hiển thị ở đây.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
