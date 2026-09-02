<?php
session_start();
include 'connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

// Get current product price
$p_stmt = $conn->prepare("SELECT product_price FROM products WHERE product_id = ?");
$p_stmt->bind_param("i", $product_id);
$p_stmt->execute();
$product = $p_stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit();
}

// If this product is already in the user's cart, just increase the quantity
$check = $conn->prepare("SELECT cart_id, product_quantity FROM carts WHERE user_id = ? AND product_id = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    $new_qty = $existing['product_quantity'] + $quantity;
    $new_amount = $new_qty * $product['product_price'];
    $stmt = $conn->prepare("UPDATE carts SET product_quantity = ?, product_amount = ? WHERE cart_id = ?");
    $stmt->bind_param("idi", $new_qty, $new_amount, $existing['cart_id']);
    $stmt->execute();
} else {
    $amount = $quantity * $product['product_price'];
    $stmt = $conn->prepare("INSERT INTO carts (user_id, product_id, product_quantity, product_amount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $user_id, $product_id, $quantity, $amount);
    $stmt->execute();
}

echo json_encode(['success' => true]);
exit();
?>