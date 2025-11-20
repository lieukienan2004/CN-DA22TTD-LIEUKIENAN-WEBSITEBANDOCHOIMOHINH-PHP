<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

try {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['avatar'])) {
        echo json_encode(['success' => false, 'message' => 'Không có file được tải lên']);
        exit;
    }

    $file = $_FILES['avatar'];
    $user_id = $_SESSION['user_id'];

    // Kiểm tra lỗi upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File quá lớn (vượt quá giới hạn server)',
            UPLOAD_ERR_FORM_SIZE => 'File quá lớn',
            UPLOAD_ERR_PARTIAL => 'File chỉ được tải lên một phần',
            UPLOAD_ERR_NO_FILE => 'Không có file nào được tải lên',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file',
            UPLOAD_ERR_EXTENSION => 'Extension PHP đã dừng upload'
        ];
        $message = isset($error_messages[$file['error']]) ? $error_messages[$file['error']] : 'Lỗi không xác định';
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

// Kiểm tra kích thước file (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File quá lớn. Tối đa 5MB']);
    exit;
}

// Kiểm tra loại file
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF)']);
    exit;
}

// Tạo thư mục uploads nếu chưa có
$upload_dir = 'uploads/avatars/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Tạo tên file unique
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
$filepath = $upload_dir . $filename;

    // Xóa avatar cũ nếu có
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (isset($user['avatar']) && $user['avatar'] && file_exists($user['avatar'])) {
        @unlink($user['avatar']);
    }

    // Upload file mới
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Cập nhật database
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $filepath, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật avatar thành công',
                'avatar_url' => $filepath
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật database: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu file. Kiểm tra quyền thư mục uploads/avatars/']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}

$conn->close();
?>
