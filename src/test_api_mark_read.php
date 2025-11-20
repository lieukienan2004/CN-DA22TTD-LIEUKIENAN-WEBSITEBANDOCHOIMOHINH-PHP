<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập!");
}

echo "<h2>🧪 Test API Mark Read</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #ffcdd2; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; cursor: pointer; border: none; }
</style>";

$user_id = $_SESSION['user_id'];

// Lấy một thông báo chưa đọc
$stmt = $conn->prepare("SELECT id, title, is_read FROM thongbao WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $notif = $result->fetch_assoc();
    
    echo "<div class='info'>";
    echo "<strong>📋 Thông báo test:</strong><br>";
    echo "ID: {$notif['id']}<br>";
    echo "Title: " . htmlspecialchars($notif['title']) . "<br>";
    echo "is_read: " . ($notif['is_read'] ? '✅ Đã đọc' : '❌ Chưa đọc') . " ({$notif['is_read']})";
    echo "</div>";
    
    $notif_id = $notif['id'];
    
    // Test 1: Gọi API bằng file_get_contents
    echo "<h3>Test 1: Gọi API bằng PHP</h3>";
    echo "<button class='btn' onclick='testAPI1()'>🧪 Test API</button>";
    echo "<div id='result1'></div>";
    
    // Test 2: Gọi API bằng JavaScript fetch
    echo "<h3>Test 2: Gọi API bằng JavaScript</h3>";
    echo "<button class='btn' onclick='testAPI2()'>🧪 Test API (JavaScript)</button>";
    echo "<div id='result2'></div>";
    
    // Test 3: Cập nhật trực tiếp database
    echo "<h3>Test 3: Cập nhật trực tiếp Database</h3>";
    if (isset($_GET['direct_update'])) {
        $stmt = $conn->prepare("UPDATE thongbao SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notif_id, $user_id);
        
        if ($stmt->execute()) {
            $affected = $stmt->affected_rows;
            echo "<div class='success'>✅ UPDATE thành công! Affected rows: $affected</div>";
            
            // Kiểm tra lại
            $stmt = $conn->prepare("SELECT is_read FROM thongbao WHERE id = ?");
            $stmt->bind_param("i", $notif_id);
            $stmt->execute();
            $check = $stmt->get_result()->fetch_assoc();
            echo "<div class='info'>is_read sau khi update: {$check['is_read']}</div>";
            
            // Rollback
            $stmt = $conn->prepare("UPDATE thongbao SET is_read = 0 WHERE id = ?");
            $stmt->bind_param("i", $notif_id);
            $stmt->execute();
            echo "<div class='info'>🔄 Đã rollback để test lại</div>";
        } else {
            echo "<div class='error'>❌ Lỗi: " . $conn->error . "</div>";
        }
    }
    echo "<a href='?direct_update=1' class='btn'>🧪 Test Direct Update</a>";
    
    echo "<br><br><a href='notifications.php' class='btn'>← Quay lại Thông Báo</a>";
    
    // JavaScript
    echo "<script>
    function testAPI1() {
        document.getElementById('result1').innerHTML = '<div class=\"info\">⏳ Đang gọi API...</div>';
        
        fetch('api/mark_notification_read.php?id={$notif_id}')
            .then(response => response.text())
            .then(text => {
                document.getElementById('result1').innerHTML = '<div class=\"success\"><strong>Response:</strong><br><pre>' + text + '</pre></div>';
            })
            .catch(error => {
                document.getElementById('result1').innerHTML = '<div class=\"error\">❌ Lỗi: ' + error + '</div>';
            });
    }
    
    function testAPI2() {
        document.getElementById('result2').innerHTML = '<div class=\"info\">⏳ Đang gọi API...</div>';
        
        fetch('api/mark_notification_read.php?id={$notif_id}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('result2').innerHTML = '<div class=\"success\"><strong>Response JSON:</strong><br><pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
                
                // Kiểm tra lại database
                setTimeout(() => {
                    location.reload();
                }, 2000);
            })
            .catch(error => {
                document.getElementById('result2').innerHTML = '<div class=\"error\">❌ Lỗi: ' + error + '</div>';
            });
    }
    </script>";
    
} else {
    echo "<div class='info'>ℹ️ Không có thông báo nào</div>";
}
?>
