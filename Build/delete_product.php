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

// Ownership check happens right in the WHERE clause
$stmt = $conn->prepare("DELETE FROM products WHERE product_id = ? AND product_shop = ?");
$stmt->bind_param("ii", $product_id, $shop_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    header("Location: manage_products.php?success=deleted");
} else {
    header("Location: manage_products.php?error=" . urlencode("Could not delete that product."));
}
exit();
?>