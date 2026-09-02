<?php
session_start();
include 'connect.php';
include 'includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$current_user_id = (int)$_SESSION['user_id'];
$shop_id = get_shop_id_for_user($conn, $current_user_id);
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ownership check: product must belong to the current user's shop
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? AND product_shop = ?");
$stmt->bind_param("ii", $product_id, $shop_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: manage_products.php?error=" . urlencode("Product not found or you don't have permission to edit it."));
    exit();
}

$categories = $conn->query("SELECT * FROM product_category ORDER BY category_name")->fetch_all(MYSQLI_ASSOC);
$mode = 'edit';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<?php $active = 'manage'; include 'includes/nav.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-edit"></i> Edit Product</h1>
    </div>
    <div class="card">
        <?php include 'includes/product_form.php'; ?>
    </div>
</div>
</body>
</html>