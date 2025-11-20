<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : 0;
$sale_only = isset($_GET['sale']) ? intval($_GET['sale']) : 0;

$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 1";

if ($category_id > 0) {
    $sql .= " AND p.category_id = " . intval($category_id);
}

// Filter for sale products only
if ($sale_only == 1) {
    $sql .= " AND p.discount > 0";
}

if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $sql .= " AND (p.name LIKE '%" . $search_safe . "%' OR p.description LIKE '%" . $search_safe . "%')";
}

if ($min_price > 0) {
    $sql .= " AND p.price >= " . intval($min_price);
}

if ($max_price > 0) {
    $sql .= " AND p.price <= " . intval($max_price);
}

// Sorting
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'discount':
        $sql .= " ORDER BY p.discount DESC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
}

$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);

$categories = getCategories($conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm - KIENANSHOP</title>
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/modal.css">
    <link rel="stylesheet" href="assets/css/search.css">
    <link rel="stylesheet" href="assets/css/search-fix.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/premium-ui.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/sakura-animation.css">
</head>
<body>
    <!-- Sakura Container -->
    <div class="sakura-container" id="sakuraContainer"></div>
    
    <?php include 'includes/header.php'; ?>
    
    <!-- Quick View Modal -->
    <div id="quickViewModal" class="modal-overlay" onclick="closeQuickView(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="closeQuickView()">
                <i class="fas fa-times"></i>
            </button>
            <div id="modalBody" class="modal-loading">
                <div class="modal-spinner"></div>
            </div>
        </div>
    </div>
    
    <div class="container" style="padding: 40px 20px;">
        <h1 style="margin-bottom: 30px;">
            <?php if (!empty($search)): ?>
                Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($search); ?>"
            <?php elseif ($sale_only == 1): ?>
                🔥 Sale Hot - Giảm Giá Đặc Biệt
            <?php else: ?>
                Sản Phẩm
            <?php endif; ?>
        </h1>
        
        <?php if (!empty($search)): ?>
        <div style="background: #dbeafe; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <span>
                <i class="fas fa-search"></i> 
                Tìm thấy <strong><?php echo count($products); ?></strong> sản phẩm
            </span>
            <a href="products.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">
                <i class="fas fa-times"></i> Xóa tìm kiếm
            </a>
        </div>
        <?php endif; ?>
        
        <?php if ($sale_only == 1): ?>
        <div style="background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%); padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; color: white; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);">
            <span>
                <i class="fas fa-fire"></i> 
                Đang hiển thị <strong><?php echo count($products); ?></strong> sản phẩm đang giảm giá
            </span>
            <a href="products.php" style="color: white; text-decoration: none; font-weight: 600;">
                <i class="fas fa-times"></i> Xem tất cả
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Filter & Sort Bar -->
        <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                <?php if ($sale_only == 1): ?>
                <input type="hidden" name="sale" value="1">
                <?php endif; ?>
                
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Sắp xếp</label>
                    <select name="sort" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px;">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                        <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                        <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                        <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Tên A-Z</option>
                        <option value="discount" <?php echo $sort == 'discount' ? 'selected' : ''; ?>>Giảm giá nhiều</option>
                    </select>
                </div>
                
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Giá từ</label>
                    <input type="number" name="min_price" value="<?php echo $min_price; ?>" placeholder="0đ" 
                           style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px;">
                </div>
                
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Giá đến</label>
                    <input type="number" name="max_price" value="<?php echo $max_price; ?>" placeholder="∞" 
                           style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px;">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: auto; padding: 10px 30px;">
                    <i class="fas fa-filter"></i> Lọc
                </button>
                
                <a href="products.php?category=<?php echo $category_id; ?>" class="btn btn-secondary" style="width: auto; padding: 10px 20px;">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </form>
        </div>
        
        <div style="display: flex; gap: 30px;">
            <!-- Sidebar -->
            <aside style="width: 250px;">
                <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="margin-bottom: 15px;"><?php echo t('categories'); ?></h3>
                    <ul style="list-style: none;">
                        <li style="margin-bottom: 10px;">
                            <a href="products.php" style="text-decoration: none; color: var(--text-color);"><?php echo t('all_products'); ?></a>
                        </li>
                        <?php foreach ($categories as $cat): 
                            // Map category names to translation keys
                            $cat_key_map = [
                                'Gundam' => 'gundam',
                                'Xe Mô Hình' => 'car_model',
                                'Máy Bay' => 'airplane',
                                'Tàu Chiến' => 'warship',
                                'Nhân Vật' => 'character',
                                'Lego' => 'lego'
                            ];
                            $cat_key = isset($cat_key_map[$cat['name']]) ? $cat_key_map[$cat['name']] : '';
                            $cat_display = $cat_key ? t($cat_key) : htmlspecialchars($cat['name']);
                        ?>
                        <li style="margin-bottom: 10px;">
                            <a href="products.php?category=<?php echo $cat['id']; ?>" 
                               style="text-decoration: none; color: <?php echo $category_id == $cat['id'] ? 'var(--primary-color)' : 'var(--text-color)'; ?>;">
                                <?php echo $cat_display; ?> (<?php echo $cat['product_count']; ?>)
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
            
            <!-- Products -->
            <main style="flex: 1;">
                <div class="product-grid">
                    <?php if (empty($products)): ?>
                    <p>Không tìm thấy sản phẩm nào.</p>
                    <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php if (isset($product['is_new']) && $product['is_new'] == 1): ?>
                            <span class="badge-new">NEW</span>
                            <?php endif; ?>
                            <?php if ($product['discount'] > 0): ?>
                            <span class="badge">-<?php echo $product['discount']; ?>%</span>
                            <?php endif; ?>
                            <?php 
                            $stock_status = isset($product['stock_status']) ? $product['stock_status'] : 'in_stock';
                            $is_out_of_stock = ($stock_status == 'out_of_stock');
                            if ($is_out_of_stock): 
                            ?>
                            <div class="out-of-stock-overlay">
                                <span class="out-of-stock-badge">HẾT HÀNG</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="product-price">
                                <?php if ($product['discount'] > 0): ?>
                                <span class="old-price"><?php echo number_format($product['price']); ?>đ</span>
                                <span class="new-price"><?php echo number_format($product['price'] * (1 - $product['discount']/100)); ?>đ</span>
                                <?php else: ?>
                                <span class="new-price"><?php echo number_format($product['price']); ?>đ</span>
                                <?php endif; ?>
                            </div>
                            <?php 
                            $stock_status = isset($product['stock_status']) ? $product['stock_status'] : 'in_stock';
                            $is_out_of_stock = ($stock_status == 'out_of_stock');
                            if ($is_out_of_stock): 
                            ?>
                            <button class="btn btn-secondary" disabled style="opacity: 0.6; cursor: not-allowed;">Hết hàng</button>
                            <?php else: ?>
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary" target="_self">Xem Chi Tiết</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/dark-mode.js"></script>
    <script src="assets/js/prevent-new-tab.js"></script>
    <script src="assets/js/sakura-animation.js"></script>
</body>
</html>
