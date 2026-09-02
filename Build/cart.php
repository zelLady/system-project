<?php
session_start();
include 'connect.php';
include 'includes/auth.php';

$stmt = $conn->prepare("SELECT c.*, p.product_nameEng, p.product_price, p.product_img_path
                        FROM carts c
                        JOIN products p ON c.product_id = p.product_id
                        WHERE c.user_id = ?
                        ORDER BY c.cart_id DESC");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['product_amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<?php $active = 'cart'; include 'includes/nav.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-shopping-cart"></i> My Cart</h1>
    </div>

    <div class="card">
        <div id="cart-container">
            <?php if (empty($cart_items)): ?>
                <p class="empty-state" id="empty-msg">Your cart is empty. <a href="products_browse.php" style="color:#764ba2;">Browse products</a></p>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-row" id="cart-row-<?php echo $item['cart_id']; ?>">
                        <img src="<?php echo htmlspecialchars($item['product_img_path'] ?: 'https://via.placeholder.com/55'); ?>" alt="">
                        <div><?php echo htmlspecialchars($item['product_nameEng']); ?></div>
                        <div>₱<?php echo number_format($item['product_price'], 2); ?></div>
                        <input type="number" min="1" class="qty-input"
                               value="<?php echo (int)$item['product_quantity']; ?>"
                               data-cart-id="<?php echo $item['cart_id']; ?>"
                               onchange="updateQty(this)">
                        <div class="row-amount">₱<?php echo number_format($item['product_amount'], 2); ?></div>
                        <button class="btn btn-small btn-danger" onclick="deleteItem(<?php echo $item['cart_id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="cart-total" id="cart-total">
            Total: ₱<?php echo number_format($total, 2); ?>
        </div>
    </div>
</div>

<script>
function updateQty(input) {
    const cartId = input.dataset.cartId;
    const quantity = input.value;

    fetch('cart_update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `cart_id=${cartId}&quantity=${quantity}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`#cart-row-${cartId} .row-amount`).textContent = 
                '₱' + Number(data.line_total).toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('cart-total').textContent = 
                'Total: ₱' + Number(data.cart_total).toLocaleString(undefined, {minimumFractionDigits: 2});
        } else {
            alert(data.message || 'Could not update quantity.');
        }
    });
}

function deleteItem(cartId) {
    if (!confirm('Remove this item from your cart?')) return;

    fetch('cart_delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `cart_id=${cartId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`cart-row-${cartId}`).remove();
            document.getElementById('cart-total').textContent = 
                'Total: ₱' + Number(data.cart_total).toLocaleString(undefined, {minimumFractionDigits: 2});
            if (data.cart_empty) {
                document.getElementById('cart-container').innerHTML = 
                    '<p class="empty-state">Your cart is empty. <a href="products_browse.php" style="color:#764ba2;">Browse products</a></p>';
            }
        } else {
            alert(data.message || 'Could not remove item.');
        }
    });
}
</script>
</body>
</html>