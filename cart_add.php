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

function fail_with_error(string $context, mysqli $conn, $stmt = null) {
    // Surface the *real* database error instead of a silent crash, so the
    // frontend alert shows exactly what went wrong (e.g. a bad column name).
    $detail = $stmt ? $stmt->error : $conn->error;
    echo json_encode([
        'success' => false,
        'message' => "$context: $detail"
    ]);
    exit();
}

// Get current product price
$p_stmt = $conn->prepare("SELECT product_price FROM products WHERE product_id = ?");
if (!$p_stmt) fail_with_error('Failed to look up product', $conn);
$p_stmt->bind_param("i", $product_id);
$p_stmt->execute();
$product = $p_stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit();
}

// If this product is already in the user's cart, just increase the quantity
$check = $conn->prepare("SELECT cart_id, product_quantity FROM carts WHERE user_id = ? AND product_id = ?");
if (!$check) fail_with_error('Failed to check existing cart item', $conn);
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    $new_qty = $existing['product_quantity'] + $quantity;
    $new_amount = $new_qty * $product['product_price'];

    $stmt = $conn->prepare("UPDATE carts SET product_quantity = ?, product_amount = ? WHERE cart_id = ?");
    if (!$stmt) fail_with_error('Failed to prepare cart update', $conn);
    $stmt->bind_param("idi", $new_qty, $new_amount, $existing['cart_id']);
    if (!$stmt->execute()) fail_with_error('Failed to update cart', $conn, $stmt);
} else {
    $amount = $quantity * $product['product_price'];

    $stmt = $conn->prepare("INSERT INTO carts (user_id, product_id, product_quantity, product_amount) VALUES (?, ?, ?, ?)");
    if (!$stmt) fail_with_error('Failed to prepare cart insert', $conn);
    $stmt->bind_param("iiid", $user_id, $product_id, $quantity, $amount);
    if (!$stmt->execute()) fail_with_error('Failed to insert cart item', $conn, $stmt);
}

echo json_encode(['success' => true]);
exit();
?>
