<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$q = trim($_GET['q'] ?? '');

$categories = $conn->query("SELECT * FROM product_category ORDER BY category_name")->fetch_all(MYSQLI_ASSOC);

// Build the product query dynamically so category + keyword/GTIN search can combine
$sql = "SELECT p.*, s.shop_name FROM products p
        JOIN shop s ON p.product_shop = s.shop_id
        WHERE 1=1";
$types = "";
$params = [];

if ($category_id > 0) {
    $sql .= " AND p.category_id = ?";
    $types .= "i";
    $params[] = $category_id;
}

if ($q !== '') {
    $sql .= " AND (p.product_nameEng LIKE ? OR p.product_nameCeb LIKE ? OR p.product_gtin LIKE ?)";
    $like = "%$q%";
    $types .= "sss";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY p.product_pubdate DESC";

$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Look up the currently selected category's name for the heading
$active_category_name = '';
if ($category_id > 0) {
    foreach ($categories as $c) {
        if ((int)$c['category_id'] === $category_id) {
            $active_category_name = $c['category_name'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>FruzzHub - Products</title>
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

		/* ---------- Search bar ---------- */
		.search-bar{
			display: flex;
			max-width: 560px;
			margin: 0 auto 30px;
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

		/* ---------- Category filter ---------- */
		.category-filter{
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			justify-content: center;
			margin-bottom: 30px;
		}
		.category-filter a{
			background-color: #a8dba8;
			border: 2px solid darkgreen;
			color: black;
			text-decoration: none;
			padding: 9px 20px;
			border-radius: 20px;
			font-weight: bold;
			font-size: 13px;
			transition: 0.25s;
		}
		.category-filter a:hover{
			background-color: #bfe8bf;
			transform: translateY(-2px);
		}
		.category-filter a.active{
			background-image: linear-gradient(to bottom, #6FBF73, #2E7D32);
			color: white;
			border-color: #2E7D32;
			box-shadow: 0 4px 10px rgba(0,0,0,0.25);
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
		.product-card .gtin{
			font-size: 11px;
			color: #888;
			margin-bottom: 4px;
			font-family: "Courier New", monospace;
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
			padding: 30px;
			text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
		}

		@media screen and (max-width: 700px) {
			ul.navlist { flex-wrap: wrap; }
			ul.navlist li { flex: 1 1 33%; }
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

		<form class="search-bar" action="products.php" method="GET">
			<?php if ($category_id > 0): ?>
				<input type="hidden" name="category" value="<?php echo $category_id; ?>">
			<?php endif; ?>
			<input type="text" name="q" placeholder="Search by product name or GTIN code"
			       value="<?php echo htmlspecialchars($q); ?>">
			<button type="submit"><i class="fas fa-search"></i> Search</button>
		</form>

		<div class="category-filter">
			<a href="products.php<?php echo $q !== '' ? '?q=' . urlencode($q) : ''; ?>"
			   class="<?php echo $category_id === 0 ? 'active' : ''; ?>">All Categories</a>
			<?php foreach ($categories as $c):
				$catUrl = 'products.php?category=' . (int)$c['category_id'];
				if ($q !== '') $catUrl .= '&q=' . urlencode($q);
			?>
				<a href="<?php echo $catUrl; ?>"
				   class="<?php echo $category_id === (int)$c['category_id'] ? 'active' : ''; ?>">
					<?php echo htmlspecialchars($c['category_name']); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<h2 class="section-title">
			<?php if ($q !== ''): ?>
				<i class="fas fa-search"></i> Results for "<?php echo htmlspecialchars($q); ?>"
			<?php elseif ($active_category_name !== ''): ?>
				<i class="fas fa-filter"></i> <?php echo htmlspecialchars($active_category_name); ?>
			<?php else: ?>
				<i class="fas fa-apple-alt"></i> All Products
			<?php endif; ?>
		</h2>

		<?php if (empty($products)): ?>
			<p class="empty-state">
				<?php echo $q !== '' ? 'No products matched your search.' : 'No products found in this category.'; ?>
			</p>
		<?php else: ?>
			<div class="product-grid">
				<?php foreach ($products as $p): ?>
					<a class="product-card" href="product_page.php?id=<?php echo (int)$p['product_id']; ?>">
						<img src="<?php echo htmlspecialchars($p['product_img_path'] ?: 'https://via.placeholder.com/220x150'); ?>" alt="">
						<div class="p-body">
							<h3><?php echo htmlspecialchars($p['product_nameEng']); ?></h3>
							<div class="gtin">GTIN: <?php echo htmlspecialchars($p['product_gtin'] ?: 'N/A'); ?></div>
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
