<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sample5.php");
    exit();
}

// Pull the current cart, along with which shop each product belongs to
$stmt = $conn->prepare("SELECT c.*, p.product_shop
                        FROM carts c
                        JOIN products p ON c.product_id = p.product_id
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) {
    header("Location: sample5.php?error=" . urlencode("Your cart is empty."));
    exit();
}

$conn->begin_transaction();

try {
    // One row per product in the cart, status starts as 'pending'
    $tx_stmt = $conn->prepare("INSERT INTO transactions
        (user_id, shop_id, product_id, product_quantity, total_amount, order_status)
        VALUES (?, ?, ?, ?, ?, 'pending')");

    foreach ($cart_items as $item) {
        $tx_stmt->bind_param(
            "iiiid",
            $user_id,
            $item['product_shop'],
            $item['product_id'],
            $item['product_quantity'],
            $item['product_amount']
        );
        $tx_stmt->execute();
    }

    // Empty the cart now that the order has been placed
    $clear_stmt = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
    $clear_stmt->bind_param("i", $user_id);
    $clear_stmt->execute();

    $conn->commit();

    header("Location: sample6.php?success=1");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header("Location: sample5.php?error=" . urlencode("Checkout failed. Please try again."));
    exit();
}
?>
