-- Xóa admin cũ nếu có
DELETE FROM admins WHERE username = 'admin';

-- Thêm tài khoản admin mới
-- Password: admin123 (đã hash)
INSERT INTO admins (username, password, fullname, email, role, status, created_at) 
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Administrator',
    'admin@kienanshop.com',
    'super_admin',
    1,
    NOW()
);
