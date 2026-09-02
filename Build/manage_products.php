<?php
session_start();
include 'connect.php';
include 'includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$current_user_id = (int)$_SESSION['user_id'];

// Look up this user's shop (a user may not have set one up yet)
$shop_id = get_shop_id_for_user($conn, $current_user_id);

// Load this shop's own products (with category name) - empty array if no shop yet
$products = [];
if ($shop_id) {
    $stmt = $conn->prepare("SELECT p.*, c.category_name
                            FROM products p
                            LEFT JOIN product_category c ON p.category_id = c.category_id
                            WHERE p.product_shop = ?
                            ORDER BY p.product_pubdate DESC");
    $stmt->bind_param("i", $shop_id);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$active = 'manage';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<?php include 'includes/nav.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-boxes"></i> My Products</h1>
        <a href="add_product.php" class="btn"><i class="fas fa-plus-circle"></i> Add New Product</a>
    </div>

<?php if (isset($_GET['success'])): ?>
    <div class="msg msg-success">
        <?php
        $map = [
            'added'   => 'Product added successfully!',
            'updated' => 'Product updated successfully!',
            'deleted' => 'Product deleted successfully!',
        ];

        $success = $_GET['success'];

        echo htmlspecialchars($map[$success] ?? 'Done!');
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="msg msg-error">
        <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>

    <div class="card">

<?php if (empty($shop_id)): ?>

        <p class="empty-state">
            You don't have a shop set up yet.
            Please complete your shop details first.
        </p>

<?php elseif (empty($products)): ?>

        <p class="empty-state">
            You haven't added any products yet.
            Click "Add New Product" to get started.
        </p>

<?php else: ?>

        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Date Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <!-- PRODUCT IMAGE -->
                    <td>
                        <?php
                        $image = !empty($p['product_img_path'])
                            ? $p['product_img_path']
                            : 'https://via.placeholder.com/50';
                        ?>
                        <img
                            class="thumb"
                            src="<?php echo htmlspecialchars($image); ?>"
                            alt="<?php echo htmlspecialchars($p['product_nameEng'] ?? 'Product'); ?>"
                        >
                    </td>

                    <!-- PRODUCT NAME -->
                    <td>
                        <?php echo htmlspecialchars($p['product_nameEng'] ?? 'Unnamed Product'); ?>
                    </td>

                    <!-- CATEGORY -->
                    <td>
                        <?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorized'); ?>
                    </td>

                    <!-- PRICE -->
                    <td>
                        ₱<?php
                        echo number_format(
                            (float)($p['product_price'] ?? 0),
                            2
                        );
                        ?>
                    </td>

                    <!-- DATE -->
                    <td>
                        <?php
                        if (!empty($p['product_pubdate'])) {
                            echo htmlspecialchars(
                                date(
                                    'M d, Y',
                                    strtotime($p['product_pubdate'])
                                )
                            );
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </td>

                    <!-- ACTIONS -->
                    <td class="actions-cell">
                        <a
                            class="btn btn-small btn-outline"
                            href="edit_product.php?id=<?php echo (int)$p['product_id']; ?>"
                        >
                            <i class="fas fa-edit"></i> Edit
                        </a>

                        <a
                            class="btn btn-small btn-danger"
                            href="delete_product.php?id=<?php echo (int)$p['product_id']; ?>"
                            onclick="return confirm('Delete this product? This cannot be undone.');"
                        >
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

<?php endif; ?>

    </div>
</div>
</body>
</html>