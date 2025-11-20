<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập trước!");
}

echo "<h2>Kiểm tra Links trong Thông Báo</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #667eea; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    .error { color: red; font-weight: bold; }
    .success { color: green; font-weight: bold; }
</style>";

// Lấy thông báo của user
$stmt = $conn->prepare("SELECT * FROM thongbao WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($notifications)) {
    echo "<p class='error'>Không có thông báo nào!</p>";
    exit;
}

echo "<table>";
echo "<tr>
    <th>ID</th>
    <th>Type</th>
    <th>Title</th>
    <th>Link</th>
    <th>Link đúng?</th>
    <th>Action</th>
</tr>";

foreach ($notifications as $notif) {
    $link = $notif['link'];
    $is_valid = false;
    $message = "";
    
    // Kiểm tra link
    if (empty($link) || $link === '#' || $link === 'notifications.php') {
        $message = "<span class='error'>Link không hợp lệ</span>";
    } else {
        // Kiểm tra file có tồn tại không
        $file_path = $link;
        if (strpos($link, '?') !== false) {
            $file_path = substr($link, 0, strpos($link, '?'));
        }
        
        if (file_exists($file_path)) {
            $message = "<span class='success'>✓ File tồn tại</span>";
            $is_valid = true;
        } else {
            $message = "<span class='error'>✗ File không tồn tại: $file_path</span>";
        }
    }
    
    echo "<tr>";
    echo "<td>" . $notif['id'] . "</td>";
    echo "<td>" . $notif['type'] . "</td>";
    echo "<td>" . htmlspecialchars($notif['title']) . "</td>";
    echo "<td>" . htmlspecialchars($link) . "</td>";
    echo "<td>$message</td>";
    echo "<td>";
    if ($is_valid) {
        echo "<a href='" . htmlspecialchars($link) . "' target='_blank' style='color: #667eea; text-decoration: none;'>Test Link</a>";
    } else {
        echo "-";
    }
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

// Hiển thị contact messages
echo "<h2 style='margin-top: 40px;'>Contact Messages của bạn</h2>";
$stmt = $conn->prepare("SELECT * FROM contact_messages WHERE email = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("s", $_SESSION['user_email']);
$stmt->execute();
$contacts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!empty($contacts)) {
    echo "<table>";
    echo "<tr>
        <th>ID</th>
        <th>Subject</th>
        <th>Status</th>
        <th>Created</th>
        <th>Link đúng</th>
    </tr>";
    
    foreach ($contacts as $contact) {
        $correct_link = "view_my_contact.php?highlight=" . $contact['id'];
        echo "<tr>";
        echo "<td>" . $contact['id'] . "</td>";
        echo "<td>" . htmlspecialchars($contact['subject']) . "</td>";
        echo "<td>" . $contact['status'] . "</td>";
        echo "<td>" . $contact['created_at'] . "</td>";
        echo "<td><a href='$correct_link' style='color: #667eea;'>$correct_link</a></td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Không có contact message nào.</p>";
}

echo "<br><br>";
echo "<a href='notifications.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 8px;'>← Quay lại Thông Báo</a>";
?>
