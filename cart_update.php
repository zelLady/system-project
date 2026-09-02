<?php
session_start();
include 'connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($cart_id <= 0 || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1.']);
    exit();
}

// Confirm ownership and get the product's current price.
$stmt = $conn->prepare("SELECT c.cart_id, p.product_price
                        FROM carts c
                        JOIN products p ON c.product_id = p.product_id
                        WHERE c.cart_id = ? AND c.user_id = ?");
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
    exit();
}

$line_total = $quantity * (float)$row['product_price'];

$update = $conn->prepare("UPDATE carts SET product_quantity = ?, product_amount = ? WHERE cart_id = ? AND user_id = ?");
$update->bind_param("idii", $quantity, $line_total, $cart_id, $user_id);
$update->execute();

$total_stmt = $conn->prepare("SELECT COALESCE(SUM(product_amount), 0) AS total FROM carts WHERE user_id = ?");
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$cart_total = (float)($total_stmt->get_result()->fetch_assoc()['total'] ?? 0);

echo json_encode([
    'success' => true,
    'line_total' => $line_total,
    'cart_total' => $cart_total
]);
exit();
