<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT t.*, p.product_nameEng, s.shop_name FROM transactions t
                         JOIN products p ON t.product_id = p.product_id
                         JOIN shop s ON t.shop_id = s.shop_id
                         WHERE t.user_id = ?
                         ORDER BY t.transaction_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
	<title>FruzzHub - Transactions</title>
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
			display: block;
			text-decoration: none;
			padding: 20px;
			text-align: center;
			position: relative;
			transition: all 0.3s ease;
		}
		ul.navlist li a i{
			margin-right: 6px;
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
			padding: 10px 0;
			overflow: hidden;
		}

		table{
			width: 100%;
			border-collapse: collapse;
		}
		thead th{
			text-align: left;
			font-size: 13px;
			text-transform: uppercase;
			letter-spacing: 0.4px;
			color: #666;
			padding: 14px 18px;
			border-bottom: 2px solid #eee;
			background: #f7fdf7;
		}
		tbody td{
			padding: 16px 18px;
			border-bottom: 1px solid #eee;
			font-size: 14px;
			color: #333;
			vertical-align: middle;
		}
		tbody tr:last-child td{
			border-bottom: none;
		}
		tbody tr:hover{
			background: #f4fbf4;
		}

		.total-amount{
			font-weight: 700;
			color: #2E7D32;
		}

		.status-badge{
			display: inline-block;
			padding: 5px 14px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 700;
			text-transform: capitalize;
			letter-spacing: 0.3px;
		}
		.status-pending{
			background: #fff3cd;
			color: #856404;
		}
		.status-confirmed{
			background: #d1ecf1;
			color: #0c5460;
		}
		.status-shipped{
			background: #d4e6f9;
			color: #1a4e8a;
		}
		.status-delivered{
			background: #d4edda;
			color: #155724;
		}
		.status-cancelled{
			background: #f8d7da;
			color: #721c24;
		}

		.empty-state{
			color: #666;
			text-align: center;
			padding: 40px 20px;
			background: #fff;
			border-radius: 18px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
		}
		.empty-state a{
			color: #2E7D32;
			font-weight: 600;
		}

		@media screen and (max-width: 700px) {
			ul.navlist { flex-wrap: wrap; }
			ul.navlist li { flex: 1 1 33%; }

			.card{ overflow-x: auto; }
			table{ min-width: 640px; }
		}
	</style>
</head>
<body>
	<div class="header">
		<img src="images/logo.png" alt="FruzzHub" class="header-logo">
		<ul class="navlist">
			<li> <a href="sample3.php"><i class="fas fa-house"></i> Home</a> </li>
			<li> <a href="sample4.php"><i class="fas fa-store"></i> Shops</a> </li>
			<li> <a href="sample5.php"><i class="fas fa-shopping-cart"></i> Cart</a> </li>
			<li> <a href="sample6.php"><i class="fas fa-receipt"></i> Transactions</a> </li>
			<li> <a href="sample7.php"><i class="fas fa-user"></i> Profile</a> </li>
			<li> <a href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a> </li>
		</ul>
	</div>

	<div class="page-wrap">

		<h2 class="section-title"><i class="fas fa-receipt"></i> Your Transactions</h2>

		<?php if (empty($transactions)): ?>

			<div class="empty-state">
				You don't have any purchases yet. <a href="sample4.php">Browse products</a> to get started.
			</div>

		<?php else: ?>

			<div class="card">
				<table>
					<thead>
						<tr>
							<th>Shop Name</th>
							<th>Product Name</th>
							<th>Date</th>
							<th>Total Amount</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($transactions as $t): ?>
							<tr>
								<td><i class="fas fa-store" style="color:#2E7D32;"></i> <?php echo htmlspecialchars($t['shop_name']); ?></td>
								<td><?php echo htmlspecialchars($t['product_nameEng']); ?></td>
								<td><?php echo date('M d, Y g:ia', strtotime($t['transaction_date'])); ?></td>
								<td class="total-amount">₱<?php echo number_format($t['total_amount'], 2); ?></td>
								<td>
									<span class="status-badge status-<?php echo htmlspecialchars($t['order_status']); ?>">
										<?php echo htmlspecialchars($t['order_status']); ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

		<?php endif; ?>

	</div>
</body>
</html>
