<?php
session_start();
include 'connect.php';
include 'includes/auth.php';

$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $stmt = $conn->prepare("SELECT p.*, s.shop_name FROM products p
                            JOIN shop s ON p.product_shop = s.shop_id
                            WHERE p.product_nameEng LIKE ?
                            ORDER BY p.product_pubdate DESC");
    $like = "%$search%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $products = $conn->query("SELECT p.*, s.shop_name FROM products p
                              JOIN shop s ON p.product_shop = s.shop_id
                              ORDER BY p.product_pubdate DESC")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<?php $active = 'browse'; include 'includes/nav.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-shopping-bag"></i> Browse Products</h1>
    </div>

    <div class="card">
        <form method="GET" style="display:flex; gap:10px;">
            <input type="text" name="q" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>"
                   style="flex:1; padding:11px 14px; border:1px solid #ddd; border-radius:8px;">
            <button class="btn" type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>

<?php if (empty($products)): ?>
    <div class="card"><p class="empty-state">No products found.</p></div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $p): ?>
            <a href="product_page.php?id=<?php echo $p['product_id']; ?>" class="product-tile">
                <img src="<?php echo htmlspecialchars($p['product_img_path'] ?: 'https://via.placeholder.com/220x150'); ?>" alt="">
                <div class="p-body">
                    <h3><?php echo htmlspecialchars($p['product_nameEng']); ?></h3>
                    <div class="shop-name"><i class="fas fa-store"></i> <?php echo htmlspecialchars($p['shop_name']); ?></div>
                    <div class="price">₱<?php echo number_format($p['product_price'], 2); ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>
</body>
</html>