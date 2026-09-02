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

if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
    exit();
}

$stmt = $conn->prepare("DELETE FROM carts WHERE cart_id = ? AND user_id = ?");
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
    exit();
}

$total_stmt = $conn->prepare("SELECT COALESCE(SUM(product_amount), 0) AS total, COUNT(*) AS cnt FROM carts WHERE user_id = ?");
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$row = $total_stmt->get_result()->fetch_assoc();

echo json_encode([
    'success' => true,
    'message' => 'Cart item removed.',
    'cart_total' => (float)($row['total'] ?? 0),
    'cart_empty' => ((int)$row['cnt'] === 0)
]);
exit();
