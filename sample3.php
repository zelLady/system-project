<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

$display_name = $_SESSION['name'] ?? 'there';

// ----- Featured Fruit Products -----
// Try to pull products from a category that looks like "Fruits".
// If no such category exists yet (or has no products), fall back
// to just showing the most recently added products.
$featured = [];

$stmt = $conn->prepare("SELECT p.*, s.shop_name FROM products p
                         JOIN shop s ON p.product_shop = s.shop_id
                         LEFT JOIN product_category c ON p.category_id = c.category_id
                         WHERE c.category_name LIKE ?
                         ORDER BY p.product_pubdate DESC
                         LIMIT 6");
$fruitLike = '%fruit%';
$stmt->bind_param("s", $fruitLike);
$stmt->execute();
$featured = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($featured)) {
    $fallback = $conn->query("SELECT p.*, s.shop_name FROM products p
                               JOIN shop s ON p.product_shop = s.shop_id
                               ORDER BY p.product_pubdate DESC
                               LIMIT 6");
    $featured = $fallback ? $fallback->fetch_all(MYSQLI_ASSOC) : [];
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>FruzzHub - Home</title>
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
			max-width: 1100px;
			margin: 0 auto;
			padding: 40px 20px 60px;
		}

		/* ---------- Welcome banner ---------- */
		.welcome-banner{
			background-color: #C8E6C9;
			text-align: center;
			padding: 35px 25px;
			border-radius: 24px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
			border: 1px solid rgba(255, 255, 255, 0.5);
			margin-bottom: 35px;
		}
		.welcome-banner h1{
			color: #FDF6E3;
			font-family: "Georgia", serif;
			font-size: 32px;
			font-weight: bold;
			letter-spacing: 1px;
			margin: 0 0 8px 0;
			text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.25);
		}
		.welcome-banner p{
			color: #2E4A2E;
			font-size: 15px;
			line-height: 1.6;
			margin: 0 0 22px 0;
		}

		/* ---------- Search bar ---------- */
		.search-bar{
			display: flex;
			max-width: 560px;
			margin: 0 auto;
			gap: 10px;
		}
		.search-bar input{
			flex: 1;
			height: 48px;
			padding: 0 18px;
			font-size: 15px;
			border: 3px solid darkgreen;
			border-radius: 25px;
			background-color: rgba(255, 255, 255, 0.85);
			outline: none;
		}
		.search-bar input:focus{
			background-color: #fff;
			box-shadow: 0 6px 15px rgba(0, 100, 0, 0.25);
		}
		.search-bar button{
			background-image: linear-gradient(to bottom, #6FBF73, #2E7D32);
			color: white;
			border: none;
			border-radius: 25px;
			padding: 0 26px;
			font-weight: bold;
			cursor: pointer;
			transition: 0.3s;
		}
		.search-bar button:hover{
			background-image: linear-gradient(to bottom, #7DD182, #388E3C);
			transform: translateY(-2px);
		}

		/* ---------- Featured products ---------- */
		.section-title{
			color: #fff;
			text-shadow: 1px 1px 4px rgba(0,0,0,0.35);
			font-size: 24px;
			margin: 0 0 18px 4px;
		}
		.product-grid{
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
			gap: 20px;
			margin-bottom: 10px;
		}
		.product-card{
			background: #fff;
			border-radius: 16px;
			overflow: hidden;
			box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
			text-decoration: none;
			color: #333;
			display: block;
			transition: 0.2s;
		}
		.product-card:hover{
			transform: translateY(-4px);
			box-shadow: 0 14px 28px rgba(0, 60, 0, 0.25);
		}
		.product-card img{
			width: 100%;
			height: 140px;
			object-fit: cover;
			display: block;
		}
		.product-card .p-body{
			padding: 14px 16px 18px;
		}
		.product-card h3{
			font-size: 15px;
			margin: 0 0 6px 0;
		}
		.product-card .shop{
			font-size: 12px;
			color: #666;
			margin-bottom: 8px;
		}
		.product-card .price{
			color: #2E7D32;
			font-weight: 700;
			font-size: 15px;
		}
		.empty-state{
			color: #eafff0;
			text-align: center;
			padding: 20px;
			text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
		}

		@media screen and (max-width: 700px) {
			ul.navlist { flex-wrap: wrap; }
			ul.navlist li { flex: 1 1 33%; }
			.search-bar { flex-direction: column; }
			.search-bar button { height: 46px; }
			.welcome-banner h1 { font-size: 26px; }
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

		<div class="welcome-banner">
			<h1>Welcome back, <?php echo htmlspecialchars($display_name); ?>!</h1>
			<p>Fresh produce from local shops, just a search away.</p>

			<form class="search-bar" action="products.php" method="GET">
				<input type="text" name="q" placeholder="Search by product name or GTIN code" required>
				<button type="submit"><i class="fas fa-search"></i> Search</button>
			</form>
		</div>

		<h2 class="section-title"><i class="fas fa-apple-alt"></i> Featured Fruit Products</h2>

		<?php if (empty($featured)): ?>
			<p class="empty-state">No products to show just yet — check back soon!</p>
		<?php else: ?>
			<div class="product-grid">
				<?php foreach ($featured as $p): ?>
					<a class="product-card" href="product_page.php?id=<?php echo (int)$p['product_id']; ?>">
						<img src="<?php echo htmlspecialchars($p['product_img_path'] ?: 'https://via.placeholder.com/220x150'); ?>" alt="">
						<div class="p-body">
							<h3><?php echo htmlspecialchars($p['product_nameEng']); ?></h3>
							<div class="shop"><i class="fas fa-store"></i> <?php echo htmlspecialchars($p['shop_name']); ?></div>
							<div class="price">₱<?php echo number_format($p['product_price'], 2); ?></div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</body>
</html>
