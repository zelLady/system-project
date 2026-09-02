<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

$q = trim($_GET['q'] ?? '');
$shops = [];

if ($q !== '') {
    $stmt = $conn->prepare("SELECT s.*, u.user_email, u.user_contactnum FROM shop s
                             JOIN users u ON s.user_id = u.user_id
                             WHERE s.shop_name LIKE ?
                             ORDER BY s.shop_name ASC");
    $like = "%$q%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $shops = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $result = $conn->query("SELECT s.*, u.user_email, u.user_contactnum FROM shop s
                             JOIN users u ON s.user_id = u.user_id
                             ORDER BY s.shop_name ASC");
    $shops = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>FruzzHub - Shops</title>
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

		.search-bar{
			display: flex;
			max-width: 560px;
			margin: 0 auto 35px;
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

		.section-title{
			color: #fff;
			text-shadow: 1px 1px 4px rgba(0,0,0,0.35);
			font-size: 24px;
			margin: 0 0 18px 4px;
		}

		/* ---------- Shop directory ---------- */
		.shop-grid{
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
			gap: 22px;
		}
		.shop-card{
			background: #fff;
			border-radius: 18px;
			overflow: hidden;
			box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
			transition: 0.2s;
		}
		.shop-card:hover{
			transform: translateY(-4px);
			box-shadow: 0 14px 28px rgba(0, 60, 0, 0.25);
		}
		.shop-banner{
			height: 110px;
			background-image: linear-gradient(135deg, #6FBF73 0%, #2E7D32 100%);
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.shop-banner img{
			width: 100%;
			height: 100%;
			object-fit: cover;
			display: block;
		}
		.shop-banner i{
			font-size: 42px;
			color: rgba(255, 255, 255, 0.85);
		}
		.shop-body{
			padding: 18px 20px 22px;
		}
		.shop-body h3{
			font-size: 18px;
			color: #1b5e20;
			margin: 0 0 10px 0;
		}
		.shop-detail-row{
			display: flex;
			align-items: flex-start;
			gap: 8px;
			font-size: 13px;
			color: #444;
			margin-bottom: 6px;
			line-height: 1.4;
		}
		.shop-detail-row i{
			color: #2E7D32;
			width: 16px;
			margin-top: 2px;
			flex-shrink: 0;
		}
		.view-products-btn{
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			margin-top: 16px;
			background-image: linear-gradient(to bottom, #6FBF73, #2E7D32);
			color: white;
			text-decoration: none;
			padding: 11px 0;
			border-radius: 20px;
			font-weight: bold;
			font-size: 14px;
			transition: 0.3s;
		}
		.view-products-btn:hover{
			background-image: linear-gradient(to bottom, #7DD182, #388E3C);
			transform: translateY(-2px);
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
			.search-bar { flex-direction: column; }
			.search-bar button { height: 46px; }
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

		<form class="search-bar" action="sample4.php" method="GET">
			<input type="text" name="q" placeholder="Search shops by name"
			       value="<?php echo htmlspecialchars($q); ?>">
			<button type="submit"><i class="fas fa-search"></i> Search</button>
		</form>

		<h2 class="section-title">
			<?php if ($q !== ''): ?>
				<i class="fas fa-search"></i> Results for "<?php echo htmlspecialchars($q); ?>"
			<?php else: ?>
				<i class="fas fa-store"></i> All Shops
			<?php endif; ?>
		</h2>

		<?php if (empty($shops)): ?>
			<p class="empty-state">
				<?php echo $q !== '' ? 'No shops matched your search.' : 'No shops available yet.'; ?>
			</p>
		<?php else: ?>
			<div class="shop-grid">
				<?php foreach ($shops as $s): ?>
					<div class="shop-card">
						<div class="shop-banner">
							<?php if (!empty($s['shop_banner_path'])): ?>
								<img src="<?php echo htmlspecialchars($s['shop_banner_path']); ?>" alt="<?php echo htmlspecialchars($s['shop_name']); ?> banner">
							<?php else: ?>
								<i class="fas fa-store"></i>
							<?php endif; ?>
						</div>
						<div class="shop-body">
							<h3><?php echo htmlspecialchars($s['shop_name']); ?></h3>

							<div class="shop-detail-row">
								<i class="fas fa-map-marker-alt"></i>
								<span><?php echo htmlspecialchars($s['shop_address'] ?: 'Address not set yet'); ?></span>
							</div>
							<div class="shop-detail-row">
								<i class="fas fa-phone"></i>
								<span><?php echo htmlspecialchars($s['user_contactnum'] ?: 'No contact number yet'); ?></span>
							</div>
							<div class="shop-detail-row">
								<i class="fas fa-envelope"></i>
								<span><?php echo htmlspecialchars($s['user_email'] ?? ''); ?></span>
							</div>

							<a class="view-products-btn" href="shop_products.php?id=<?php echo (int)$s['shop_id']; ?>">
								<i class="fas fa-box-open"></i> View Products
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</body>
</html>
