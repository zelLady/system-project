<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

$current_user_id = (int)$_SESSION['user_id'];

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT p.*, s.shop_name, s.shop_id FROM products p
                        JOIN shop s ON p.product_shop = s.shop_id
                        WHERE p.product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: products.php");
    exit();
}

// Get feedbacks for this product, newest first
$fb_stmt = $conn->prepare("SELECT f.*, u.user_name FROM customer_feedbacks f
                           JOIN users u ON f.user_id = u.user_id
                           WHERE f.product_id = ?
                           ORDER BY f.feedback_date DESC");
$fb_stmt->bind_param("i", $product_id);
$fb_stmt->execute();
$feedbacks = $fb_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo htmlspecialchars($product['product_nameEng']); ?> - FruzzHub</title>
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
			max-width: 1000px;
			margin: 0 auto;
			padding: 40px 20px 60px;
		}

		.back-link{
			display: inline-flex;
			align-items: center;
			gap: 8px;
			color: #fff;
			text-decoration: none;
			font-weight: bold;
			font-size: 14px;
			text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
			margin-bottom: 20px;
			transition: 0.2s;
		}
		.back-link:hover{
			opacity: 0.8;
		}

		.card{
			background: #fff;
			border-radius: 18px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
			padding: 28px;
			margin-bottom: 24px;
		}

		/* ---------- Product detail ---------- */
		.product-detail{
			display: grid;
			grid-template-columns: 300px 1fr;
			gap: 30px;
		}
		.product-detail img{
			width: 100%;
			border-radius: 14px;
			object-fit: cover;
		}
		.shop-name{
			color: #666;
			font-size: 13px;
			margin-bottom: 10px;
		}
		.gtin-tag{
			font-family: "Courier New", monospace;
			font-size: 12px;
			color: #888;
			margin-bottom: 10px;
		}

		.lang-toggle{
			display: inline-flex;
			gap: 4px;
			background: #eafbea;
			padding: 4px;
			border-radius: 10px;
			margin-bottom: 14px;
		}
		.lang-btn{
			border: none;
			background: transparent;
			padding: 6px 16px;
			border-radius: 7px;
			font-size: 13px;
			font-weight: 700;
			letter-spacing: 0.5px;
			color: #2E7D32;
			cursor: pointer;
			transition: 0.2s;
		}
		.lang-btn:hover{
			background: rgba(46, 125, 50, 0.12);
		}
		.lang-btn.active{
			background-image: linear-gradient(to bottom, #6FBF73, #2E7D32);
			color: #fff;
		}

		.price-tag{
			color: #2E7D32;
			font-size: 26px;
			font-weight: 700;
			margin: 6px 0 16px;
		}

		.qty-input{
			width: 70px;
			padding: 9px 10px;
			border: 2px solid darkgreen;
			border-radius: 8px;
			text-align: center;
		}

		.btn{
			display: inline-flex;
			align-items: center;
			gap: 8px;
			background-image: linear-gradient(to bottom, #6FBF73, #2E7D32);
			color: #fff;
			border: none;
			padding: 11px 22px;
			border-radius: 20px;
			font-size: 14px;
			font-weight: 700;
			cursor: pointer;
			transition: 0.25s;
		}
		.btn:hover{
			background-image: linear-gradient(to bottom, #7DD182, #388E3C);
			transform: translateY(-2px);
		}

		.msg{
			padding: 12px 18px;
			border-radius: 10px;
			margin-bottom: 16px;
			font-size: 14px;
			font-weight: 500;
		}
		.msg-success{
			background: #d4edda;
			color: #155724;
		}
		.msg-error{
			background: #f8d7da;
			color: #721c24;
		}

		/* ---------- Feedback ---------- */
		.card h2{
			color: #1b5e20;
			margin-bottom: 18px;
			font-size: 20px;
		}
		.form-group{
			margin-bottom: 16px;
		}
		.form-group label{
			display: block;
			margin-bottom: 6px;
			font-weight: 600;
			font-size: 14px;
			color: #333;
		}
		.form-group select,
		.form-group textarea{
			width: 100%;
			padding: 10px 14px;
			border: 1px solid #ccc;
			border-radius: 8px;
			font-size: 14px;
			font-family: inherit;
			outline: none;
		}
		.form-group select:focus,
		.form-group textarea:focus{
			border-color: #2E7D32;
		}
		.form-group textarea{
			resize: vertical;
			min-height: 80px;
		}

		.empty-state{
			color: #666;
			text-align: center;
			padding: 20px;
		}
		.feedback-item{
			padding: 16px 0;
			border-bottom: 1px solid #eee;
		}
		.feedback-item:last-child{
			border-bottom: none;
		}
		.fb-head{
			display: flex;
			justify-content: space-between;
			align-items: center;
			flex-wrap: wrap;
			gap: 6px;
			margin-bottom: 6px;
		}
		.fb-name{
			font-weight: 700;
			margin-right: 8px;
			color: #333;
		}
		.stars{
			color: #f5a623;
			letter-spacing: 1px;
		}
		.fb-date{
			color: #888;
			font-size: 13px;
		}
		.fb-actions{
			display: flex;
			gap: 14px;
			margin-top: 8px;
			font-size: 13px;
		}
		.fb-actions a{
			color: #2E7D32;
			text-decoration: none;
			display: inline-flex;
			align-items: center;
			gap: 4px;
		}
		.fb-actions a:hover{
			text-decoration: underline;
		}

		@media screen and (max-width: 700px) {
			ul.navlist { flex-wrap: wrap; }
			ul.navlist li { flex: 1 1 33%; }
			.product-detail { grid-template-columns: 1fr; }
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

		<a class="back-link" href="products.php"><i class="fas fa-arrow-left"></i> Back to Products</a>

		<div class="card">
			<div class="product-detail">
				<div>
					<img src="<?php echo htmlspecialchars($product['product_img_path'] ?: 'https://via.placeholder.com/320x220'); ?>" alt="">
				</div>
				<div>
					<div class="shop-name"><i class="fas fa-store"></i> <?php echo htmlspecialchars($product['shop_name']); ?></div>
					<div class="gtin-tag">GTIN: <?php echo htmlspecialchars($product['product_gtin'] ?: 'N/A'); ?></div>

					<!-- ===== Language toggle (ENG / CEB) ===== -->
					<div class="lang-toggle">
						<button type="button" class="lang-btn active" data-lang="eng" onclick="setLanguage('eng')">ENG</button>
						<button type="button" class="lang-btn" data-lang="ceb" onclick="setLanguage('ceb')">CEB</button>
					</div>

					<!-- Product name: one span per language, DB-driven -->
					<h1 style="margin: 4px 0; font-size: 26px; color: #222;">
						<span class="lang-content" data-lang="eng"><?php echo htmlspecialchars($product['product_nameEng']); ?></span>
						<span class="lang-content" data-lang="ceb" style="display:none;"><?php echo htmlspecialchars($product['product_nameCeb'] ?: $product['product_nameEng']); ?></span>
					</h1>

					<div class="price-tag">₱<?php echo number_format($product['product_price'], 2); ?></div>

					<!-- Product description: one span per language, DB-driven -->
					<p style="color:#555; line-height:1.6; margin-bottom:20px;">
						<span class="lang-content" data-lang="eng">
							<?php echo nl2br(htmlspecialchars($product['product_descriptionEng'] ?: 'No description available.')); ?>
						</span>
						<span class="lang-content" data-lang="ceb" style="display:none;">
							<?php echo nl2br(htmlspecialchars($product['product_descriptionCeb'] ?: 'Walay deskripsyon nga makita.')); ?>
						</span>
					</p>

					<!-- Add to Cart: saved via AJAX to cart_add.php -->
					<div id="cart-msg" class="msg msg-success" style="display:none;"></div>
					<form id="add-cart-form" style="display:flex; gap:10px; align-items:center;">
						<input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
						<label style="margin:0; font-weight:600; font-size:14px;">Qty</label>
						<input type="number" name="quantity" value="1" min="1" class="qty-input">
						<button type="submit" class="btn"><i class="fas fa-cart-plus"></i> Add to Cart</button>
					</form>
				</div>
			</div>
		</div>

		<!-- ===== Customer Feedback CRUD ===== -->
		<div class="card">
			<h2><i class="fas fa-comments"></i> Customer Feedback</h2>

			<?php if (isset($_GET['fbsuccess'])): ?>
				<div class="msg msg-success">
					<?php
					$map = ['added' => 'Feedback submitted!', 'updated' => 'Feedback updated!', 'deleted' => 'Feedback removed.'];
					echo $map[$_GET['fbsuccess']] ?? 'Done!';
					?>
				</div>
			<?php endif; ?>

			<!-- Add feedback form -->
			<form action="feedback_add.php" method="POST" style="margin-bottom:25px;">
				<input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
				<input type="hidden" name="shop_id" value="<?php echo $product['shop_id']; ?>">
				<div class="form-group">
					<label>Rating</label>
					<select name="feedback_rating" required>
						<option value="5">★★★★★ Excellent</option>
						<option value="4">★★★★☆ Good</option>
						<option value="3">★★★☆☆ Average</option>
						<option value="2">★★☆☆☆ Poor</option>
						<option value="1">★☆☆☆☆ Terrible</option>
					</select>
				</div>
				<div class="form-group">
					<label>Your Feedback</label>
					<textarea name="feedback_message" placeholder="Share your thoughts about this product..." required></textarea>
				</div>
				<button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Submit Feedback</button>
			</form>

			<!-- Feedback list -->
			<?php if (empty($feedbacks)): ?>
				<p class="empty-state">No feedback yet. Be the first to review this product!</p>
			<?php else: ?>
				<?php foreach ($feedbacks as $f): ?>
					<div class="feedback-item">
						<div class="fb-head">
							<div>
								<span class="fb-name"><?php echo htmlspecialchars($f['user_name']); ?></span>
								<span class="stars"><?php echo str_repeat('★', (int)$f['feedback_rating']) . str_repeat('☆', 5 - (int)$f['feedback_rating']); ?></span>
							</div>
							<div class="fb-date"><?php echo date('M d, Y g:ia', strtotime($f['feedback_date'])); ?></div>
						</div>
						<p style="color:#555;"><?php echo nl2br(htmlspecialchars($f['feedback_message'])); ?></p>

						<?php if ((int)$f['user_id'] === $current_user_id): ?>
							<div class="fb-actions">
								<a href="feedback_edit.php?id=<?php echo $f['feedback_id']; ?>"><i class="fas fa-edit"></i> Edit</a>
								<a href="feedback_delete.php?id=<?php echo $f['feedback_id']; ?>&product_id=<?php echo $product['product_id']; ?>"
								   onclick="return confirm('Delete your feedback?');"><i class="fas fa-trash"></i> Delete</a>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>

	<script>
	// ===== Language toggle: switch product name/description between ENG and CEB =====
	function setLanguage(lang) {
		document.querySelectorAll('.lang-content').forEach(function (el) {
			el.style.display = (el.dataset.lang === lang) ? '' : 'none';
		});
		document.querySelectorAll('.lang-btn').forEach(function (btn) {
			btn.classList.toggle('active', btn.dataset.lang === lang);
		});
	}

	// Add to cart via AJAX (no page reload)
	document.getElementById('add-cart-form').addEventListener('submit', function (e) {
		e.preventDefault();
		const formData = new FormData(this);

		fetch('cart_add.php', {
			method: 'POST',
			body: formData
		})
		.then(res => res.json())
		.then(data => {
			const msg = document.getElementById('cart-msg');
			msg.style.display = 'block';
			if (data.success) {
				msg.className = 'msg msg-success';
				msg.textContent = 'Added to cart!';
			} else {
				msg.className = 'msg msg-error';
				msg.textContent = data.message || 'Could not add to cart.';
			}
			setTimeout(() => { msg.style.display = 'none'; }, 3000);
		})
		.catch(() => {
			const msg = document.getElementById('cart-msg');
			msg.style.display = 'block';
			msg.className = 'msg msg-error';
			msg.textContent = 'Something went wrong.';
		});
	});
	</script>
</body>
</html>
