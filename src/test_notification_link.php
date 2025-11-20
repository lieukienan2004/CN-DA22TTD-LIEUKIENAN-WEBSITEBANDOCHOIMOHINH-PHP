<?php
session_start();
require_once 'config/database.php';

// Kiểm tra thông báo trong database
echo "<h2>KIỂM TRA THÔNG BÁO VÀ LINK</h2>";

$result = $conn->query("SELECT * FROM thongbao ORDER BY created_at DESC LIMIT 5");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Type</th><th>Title</th><th>Message</th><th>Link</th><th>Created</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['type'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['message'], 0, 50)) . "...</td>";
        echo "<td><strong>" . htmlspecialchars($row['link']) . "</strong></td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><br><h3>TEST LINK:</h3>";
    $notif = $conn->query("SELECT * FROM thongbao ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
    echo "<p>Link trong DB: <strong>" . htmlspecialchars($notif['link']) . "</strong></p>";
    echo "<p>Link đầy đủ: <a href='" . htmlspecialchars($notif['link']) . "' target='_blank'>" . htmlspecialchars($notif['link']) . "</a></p>";
    
    // Test JSON encode
    echo "<br><h3>TEST JSON ENCODE:</h3>";
    echo "<pre>";
    echo htmlspecialchars(json_encode($notif, JSON_UNESCAPED_UNICODE));
    echo "</pre>";
    
} else {
    echo "<p>Không có thông báo nào trong database</p>";
}

// Kiểm tra contact messages
echo "<br><br><h2>KIỂM TRA CONTACT MESSAGES</h2>";
$contacts = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 3");

if ($contacts && $contacts->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Created</th></tr>";
    
    while ($row = $contacts->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Không có contact message nào</p>";
}
?>
