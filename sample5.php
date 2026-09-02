<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT c.*, p.product_nameEng, p.product_price, p.product_img_path, s.shop_name
                        FROM carts c
                        JOIN products p ON c.product_id = p.product_id
                        JOIN shop s ON p.product_shop = s.shop_id
                        WHERE c.user_id = ?
                        ORDER BY c.cart_id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['product_amount'];
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>FruzzHub - Cart</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
	<style>
		* {
			box-sizing: border-box;
		}

		@keyframes gradientMove {
			0% { background-position: 0% 50%; }
			50% { background-position: 100% 50%; }
			100% { background-position: 0% 50%; }
		}
		body{
			margin: 0;
			background: linear-gradient(-45deg, #A8D5A2, #6FBF73, #2E7D32, #A9CBA4);
			background-size: 400% 400%;
			animation: gradientMove 12s ease infinite;
			font-family: Arial, sans-serif;
		}
		.header{
			background-color: darkgreen;
			padding: 0;
			margin: 0;
			display: flex;
			align-items: center;
			justify-content: space-between;
		}
		.header-logo{
			max-height: 120px;
			width: auto;
		}
		ul.navlist{
			list-style-type: none;
			margin: 0;
			padding: 0;
			display: flex;
			flex: 1;
			font-family: Arial, sans-serif;
			font-weight: bold;
		}
		ul.navlist li{ flex: 1; }
		ul.navlist li a{
			color: white;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			text-decoration: none;
			padding: 20px;
			text-align: center;
			position: relative;
			transition: all 0.3s ease;
		}
		ul.navlist li a i{
			font-size: 16px;
		}
		ul.navlist li a:hover{
			color: darkgreen;
			background-color: #A8D5A2;
			transform: translateY(-3px);
		}
		ul.navlist li a::after{
			content: '';
			position: absolute;
			bottom: 8px;
			left: 50%;
			width: 0;
			height: 2px;
			background-color: darkgreen;
			transition: all 0.3s ease;
			transform: translateX(-50%);
		}
		ul.navlist li a:hover::after{ width: 60%; }

		.page-wrap{
			max-width: 900px;
			margin: 0 auto;
			padding: 40px 20px 60px;
		}

		.section-title{
			color: #fff;
			text-shadow: 1px 1px 4px rgba(0,0,0,0.35);
			font-size: 24px;
			margin: 0 0 18px 4px;
		}

		.card{
			background: #fff;
			border-radius: 18px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
			padding: 26px 28px;
			margin-bottom: 24px;
		}

		.msg{
			padding: 12px 18px;
			border-radius: 10px;
			margin-bottom: 20px;
			font-size: 14px;
			font-weight: 500;
		}
		.msg-error{
			background: #f8d7da;
			color: #721c24;
		}

		/* ---------- Cart rows ---------- */
		.cart-row{
			display: grid;
			grid-template-columns: 64px 1fr 100px 90px 110px 40px;
			align-items: center;
			gap: 14px;
			padding: 16px 0;
			border-bottom: 1px solid #eee;
		}
		.cart-row:last-child{
			border-bottom: none;
		}
		.cart-row img{
			width: 64px;
			height: 64px;
			object-fit: cover;
			border-radius: 10px;
		}
		.cart-row .p-name{
			font-weight: 600;
			color: #222;
			font-size: 15px;
		}
		.cart-row .p-shop{
			font-size: 12px;
			color: #888;
			margin-top: 2px;
		}
		.cart-row .p-price{
			color: #555;
			font-size: 14px;
		}
		.cart-row .qty-input{
			width: 64px;
			padding: 8px 6px;
			border: 2px solid darkgreen;
			border-radius: 8px;
			text-align: center;
		}
		.row-amount{
			font-weight: 700;
			color: #2E7D32;
			font-size: 15px;
		}
		.remove-btn{
			background: none;
			border: none;
			color: #c0392b;
			font-size: 17px;
			cursor: pointer;
			transition: 0.2s;
		}
		.remove-btn:hover{
			transform: scale(1.15);
		}

		.cart-summary{
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 16px;
			padding-top: 20px;
		}
		.cart-total{
			font-size: 20px;
			font-weight: 700;
			color: #2E7D32;
		}
		.checkout-btn{
			display: inline-flex;
			align-items: center;
			gap: 8px;
			background-image: linear-gradient(to bottom, #6FBF73, #2E7D32);
			color: #fff;
			border: none;
			padding: 13px 28px;
			border-radius: 24px;
			font-size: 15px;
			font-weight: 700;
			cursor: pointer;
			transition: 0.25s;
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.25);
		}
		.checkout-btn:hover{
			background-image: linear-gradient(to bottom, #7DD182, #388E3C);
			transform: translateY(-2px);
		}
		.checkout-btn:disabled{
			opacity: 0.6;
			cursor: not-allowed;
			transform: none;
		}

		.empty-state{
			text-align: center;
			padding: 30px 10px;
			color: #666;
		}
		.empty-state a{
			color: #2E7D32;
			font-weight: 600;
		}

		@media screen and (max-width: 700px) {
			ul.navlist { flex-wrap: wrap; }
			ul.navlist li { flex: 1 1 33%; }
			.cart-row{
				grid-template-columns: 50px 1fr;
				grid-template-areas:
					"img name"
					"img shop"
					"qty qty"
					"amount remove";
				row-gap: 8px;
			}
			.cart-row img{ grid-area: img; }
			.cart-row .p-info{ grid-area: name; }
			.cart-row .qty-input{ grid-area: qty; justify-self: start; }
			.cart-row .row-amount{ grid-area: amount; }
			.cart-row .remove-btn{ grid-area: remove; justify-self: end; }
		}
	</style>
</head>
<body>
	<div class="header">
		<img src="images/logo.png" alt="FruzzHub" class="header-logo">
		<ul class="navlist">
			<li> <a href="sample3.php"><i class="fas fa-home"></i> Home</a> </li>
			<li> <a href="sample4.php"><i class="fas fa-store"></i> Shops</a> </li>
			<li> <a href="sample5.php"><i class="fas fa-shopping-cart"></i> Cart</a> </li>
			<li> <a href="sample6.php"><i class="fas fa-receipt"></i> Transactions</a> </li>
			<li> <a href="sample7.php"><i class="fas fa-user"></i> Profile</a> </li>
			<li> <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a> </li>
		</ul>
	</div>

	<div class="page-wrap">

		<h2 class="section-title"><i class="fas fa-shopping-cart"></i> Your Cart</h2>

		<?php if (isset($_GET['error'])): ?>
			<div class="msg msg-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
		<?php endif; ?>

		<div class="card">
			<div id="cart-container">
				<?php if (empty($cart_items)): ?>
					<p class="empty-state" id="empty-msg">Your cart is empty. <a href="products.php">Browse products</a></p>
				<?php else: ?>
					<?php foreach ($cart_items as $item): ?>
						<div class="cart-row" id="cart-row-<?php echo $item['cart_id']; ?>">
							<img src="<?php echo htmlspecialchars($item['product_img_path'] ?: 'https://via.placeholder.com/64'); ?>" alt="">
							<div class="p-info">
								<div class="p-name"><?php echo htmlspecialchars($item['product_nameEng']); ?></div>
								<div class="p-shop"><i class="fas fa-store"></i> <?php echo htmlspecialchars($item['shop_name']); ?></div>
							</div>
							<div class="p-price">₱<?php echo number_format($item['product_price'], 2); ?></div>
							<input type="number" min="1" class="qty-input"
							       value="<?php echo (int)$item['product_quantity']; ?>"
							       data-cart-id="<?php echo $item['cart_id']; ?>"
							       data-price="<?php echo htmlspecialchars((string)$item['product_price']); ?>">
							<div class="row-amount">₱<?php echo number_format($item['product_amount'], 2); ?></div>
							<button class="remove-btn" onclick="deleteItem(<?php echo $item['cart_id']; ?>)" title="Remove item">
								<i class="fas fa-trash"></i>
							</button>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<div class="cart-summary" id="cart-summary" <?php echo empty($cart_items) ? 'style="display:none;"' : ''; ?>>
				<div class="cart-total" id="cart-total">Total: ₱<?php echo number_format($total, 2); ?></div>
				<form action="checkout.php" method="POST" onsubmit="return confirm('Place this order?');">
					<button type="submit" class="checkout-btn" id="checkout-btn">
						<i class="fas fa-credit-card"></i> Proceed to Checkout
					</button>
				</form>
			</div>
		</div>

	</div>

	<script>
	const money = value => Number(value).toLocaleString('en-PH', {
		minimumFractionDigits: 2,
		maximumFractionDigits: 2
	});

	function recalculateVisibleCartTotal() {
		let total = 0;
		document.querySelectorAll('.cart-row').forEach(row => {
			const input = row.querySelector('.qty-input');
			const price = Number(input.dataset.price);
			const qty = Math.max(1, Number(input.value) || 1);
			total += price * qty;
		});
		document.getElementById('cart-total').textContent = 'Total: ₱' + money(total);
	}

	// Fires on every keystroke and every click of the up/down spinner —
	// not just when the field loses focus — so the price updates instantly.
	const pendingSaves = new Map();

	document.querySelectorAll('.qty-input').forEach(input => {
		input.addEventListener('input', function () {
			const row = this.closest('.cart-row');
			const price = Number(this.dataset.price);
			const quantity = Math.max(1, Number(this.value) || 1);
			const cartId = this.dataset.cartId;

			// Immediate client-side calculation — no waiting on the server,
			// so the price visibly updates on every keystroke.
			row.querySelector('.row-amount').textContent = '₱' + money(price * quantity);
			recalculateVisibleCartTotal();

			// Debounce the actual save: only send one request to the server
			// after the user pauses typing, instead of one per keystroke.
			// This avoids overlapping requests racing each other (e.g. clearing
			// "1" and typing "2" firing two requests almost simultaneously).
			clearTimeout(pendingSaves.get(cartId));
			const timeoutId = setTimeout(() => {
				fetch('cart_update.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: `cart_id=${cartId}&quantity=${quantity}`
				})
				.then(res => res.json())
				.then(data => {
					if (!data.success) {
						alert(data.message || 'Could not update quantity.');
						return;
					}
					// Reconcile with the server-confirmed values (covers price changes, etc.)
					row.querySelector('.row-amount').textContent = '₱' + money(data.line_total);
					document.getElementById('cart-total').textContent = 'Total: ₱' + money(data.cart_total);
				})
				.catch(() => alert('Could not update quantity.'));
			}, 400);
			pendingSaves.set(cartId, timeoutId);
		});
	});

	function deleteItem(cartId) {
		if (!confirm('Remove this item from your cart?')) return;

		fetch('cart_delete.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: `cart_id=${cartId}`
		})
		.then(async res => {
			const text = await res.text();
			try {
				return JSON.parse(text);
			} catch (e) {
				console.error('Server did not return valid JSON:', text);
				throw new Error('Server error — check console for details.');
			}
		})
		.then(data => {
			if (data.success) {
				document.getElementById(`cart-row-${cartId}`).remove();
				document.getElementById('cart-total').textContent =
					'Total: ₱' + money(data.cart_total);
				if (data.cart_empty) {
					document.getElementById('cart-container').innerHTML =
						'<p class="empty-state" id="empty-msg">Your cart is empty. <a href="products.php">Browse products</a></p>';
					document.getElementById('cart-summary').style.display = 'none';
				}
			} else {
				alert(data.message || 'Could not remove item.');
			}
		})
		.catch(err => {
			console.error(err);
			alert('Could not remove item. Open DevTools (F12) → Console/Network for details.');
		});
	}
	</script>
</body>
</html>
