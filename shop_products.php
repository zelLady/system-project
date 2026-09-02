<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

$shop_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$shop_stmt = $conn->prepare("SELECT s.*, u.user_email, u.user_contactnum FROM shop s
                              JOIN users u ON s.user_id = u.user_id
                              WHERE s.shop_id = ?");
$shop_stmt->bind_param("i", $shop_id);
$shop_stmt->execute();
$shop = $shop_stmt->get_result()->fetch_assoc();

if (!$shop) {
    header("Location: sample4.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM products WHERE product_shop = ? ORDER BY product_pubdate DESC");
$stmt->bind_param("i", $shop_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo htmlspecialchars($shop['shop_name']); ?> - Products</title>
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

		/* ---------- Shop info banner ---------- */
		.shop-info-banner{
			background-color: #C8E6C9;
			display: flex;
			align-items: center;
			gap: 22px;
			padding: 26px 28px;
			border-radius: 20px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
			border: 1px solid rgba(255, 255, 255, 0.5);
			margin-bottom: 35px;
		}
		.shop-banner-icon{
			width: 80px;
			height: 80px;
			border-radius: 50%;
			flex-shrink: 0;
			background-image: linear-gradient(135deg, #6FBF73, #2E7D32);
			display: flex;
			align-items: center;
			justify-content: center;
			box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
			overflow: hidden;
		}
		.shop-banner-icon img{
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
		.shop-banner-icon i{
			font-size: 32px;
			color: white;
		}
		.shop-info-text h1{
			font-size: 26px;
			color: #1b5e20;
			margin: 0 0 8px 0;
		}
		.shop-info-text p{
			font-size: 14px;
			color: #2E4A2E;
			margin: 0 0 4px 0;
		}
		.shop-info-text p i{
			width: 16px;
			color: #2E7D32;
		}

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
		.product-card .price{
			color: #2E7D32;
			font-weight: 700;
			font-size: 15px;
		}
		.empty-state{
			color: #eafff0;
			text-align: center;
			padding: 30px;
			text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
		}

		@media screen and (max-width: 700px) {
			ul.navlist { flex-wrap: wrap; }
			ul.navlist li { flex: 1 1 33%; }
			.shop-info-banner { flex-direction: column; text-align: center; }
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

		<a class="back-link" href="sample4.php"><i class="fas fa-arrow-left"></i> Back to Shops</a>

		<div class="shop-info-banner">
			<div class="shop-banner-icon">
				<?php if (!empty($shop['shop_banner_path'])): ?>
					<img src="<?php echo htmlspecialchars($shop['shop_banner_path']); ?>" alt="<?php echo htmlspecialchars($shop['shop_name']); ?>">
				<?php else: ?>
					<i class="fas fa-store"></i>
				<?php endif; ?>
			</div>
			<div class="shop-info-text">
				<h1><?php echo htmlspecialchars($shop['shop_name']); ?></h1>
				<p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($shop['shop_address'] ?: 'Address not set yet'); ?></p>
				<p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($shop['user_contactnum'] ?: 'No contact number yet'); ?></p>
				<p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($shop['user_email'] ?? ''); ?></p>
			</div>
		</div>

		<h2 class="section-title"><i class="fas fa-apple-alt"></i> Products from this Shop</h2>

		<?php if (empty($products)): ?>
			<p class="empty-state">This shop hasn't listed any products yet.</p>
		<?php else: ?>
			<div class="product-grid">
				<?php foreach ($products as $p): ?>
					<a class="product-card" href="product_page.php?id=<?php echo (int)$p['product_id']; ?>">
						<img src="<?php echo htmlspecialchars($p['product_img_path'] ?: 'https://via.placeholder.com/220x150'); ?>" alt="">
						<div class="p-body">
							<h3><?php echo htmlspecialchars($p['product_nameEng']); ?></h3>
							<div class="price">₱<?php echo number_format($p['product_price'], 2); ?></div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</body>
</html>
