<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>FruzzHub - Welcome</title>
	<link rel="stylesheet" href="fonts/stylesheet.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
	<style>
		* {
			box-sizing: border-box;
		}

		body {
			margin: 0;
			min-height: 100vh;
			background: radial-gradient(circle at 30% 50%, #1e4d2b 0%, #7bc98c 70%);
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 40px 30px;
			font-family: Arial, sans-serif;
			background-image: url("images/mainbg.jpg");
			background-size: cover;
			background-attachment: fixed;
			background-repeat: no-repeat;
		}

		.wrap {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 45px;
			width: 100%;
			max-width: 1100px;
		}

		/* ---------- Logo + tagline ---------- */
		.header-logo {
			text-align: center;
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 14px;
		}

		.header-logo img {
			max-width: 220px;
			height: auto;
			display: block;
			filter: drop-shadow(0 8px 18px rgba(0, 0, 0, 0.35));
		}

		.welcome-title {
			font-size: 60px;
			font-family: 'Butflow';
			color: #fff;
			margin: 0;
			text-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
		}

		.welcome-sub {
			font-size: 18px;
			color: #eafff0;
			margin: 0;
			text-align: center;
			max-width: 520px;
			text-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
		}

		/* ---------- Role selection cards ---------- */
		.role-row {
			display: flex;
			gap: 40px;
			flex-wrap: wrap;
			justify-content: center;
			width: 100%;
		}

		.role-card {
			background-color: #a8dba8;
			border: 4px solid darkgreen;
			border-radius: 30px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
			width: 340px;
			max-width: 100%;
			padding: 45px 30px;
			text-align: center;
			text-decoration: none;
			color: black;
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 14px;
			transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
			position: relative;
			overflow: hidden;
		}

		.role-card::before {
			content: "";
			position: absolute;
			inset: 0;
			background: linear-gradient(to bottom, rgba(255, 255, 255, 0.35), transparent 60%);
			opacity: 0;
			transition: opacity 0.3s ease;
		}

		.role-card:hover {
			transform: translateY(-10px) scale(1.03);
			box-shadow: 0 18px 35px rgba(0, 0, 0, 0.45);
			background-color: #bfe8bf;
		}

		.role-card:hover::before {
			opacity: 1;
		}

		.role-card:active {
			transform: translateY(-4px) scale(0.99);
		}

		.role-icon {
			width: 90px;
			height: 90px;
			border-radius: 50%;
			background-image: linear-gradient(to bottom, #6FBF73, #2E7D32);
			display: flex;
			align-items: center;
			justify-content: center;
			box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
			margin-bottom: 6px;
		}

		.role-icon i {
			font-size: 40px;
			color: white;
		}

		.role-card h2 {
			font-size: 32px;
			font-family: 'Butflow';
			font-weight: 900;
			margin: 0;
			color: black;
		}

		.role-card p {
			font-size: 15px;
			color: #1d2f1d;
			margin: 0;
			line-height: 1.5;
		}

		.role-card .go-btn {
			margin-top: 10px;
			background-image: linear-gradient(to bottom, #6FBF73, #2E7D32);
			color: white;
			width: 100%;
			max-width: 220px;
			height: 48px;
			border: none;
			border-radius: 30px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: bold;
			font-size: 16px;
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
			transition: all 0.3s ease;
		}

		.role-card:hover .go-btn {
			background-image: linear-gradient(to bottom, #7DD182, #388E3C);
			transform: translateY(-2px);
			box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
		}

		@media screen and (max-width: 900px) {
			.welcome-title {
				font-size: 42px;
			}

			.role-row {
				gap: 25px;
			}
		}

		@media screen and (max-width: 500px) {
			.welcome-title {
				font-size: 34px;
			}

			.welcome-sub {
				font-size: 15px;
			}

			.role-card {
				padding: 35px 22px;
				width: 100%;
			}

			.role-icon {
				width: 74px;
				height: 74px;
			}

			.role-icon i {
				font-size: 32px;
			}

			.role-card h2 {
				font-size: 26px;
			}
		}
	</style>
</head>

<body>

	<div class="wrap">

		<div class="header-logo">
			<picture>
				<source srcset="images/mainlogo.png" media="(max-width: 900px)">
				<img src="images/logo.png" alt="FruzzHub">
			</picture>
			<h1 class="welcome-title">Welcome to FruzzHub</h1>
			<p class="welcome-sub">Fresh produce, straight from local shops to your table. Tell us who you are so we can get you started.</p>
		</div>

		<div class="role-row">

			<a class="role-card" href="sample1.php">
				<div class="role-icon"><i class="fas fa-shopping-basket"></i></div>
				<h2>I'm a Buyer</h2>
				<p>Browse fresh products from local shops, add them to your cart, and check out with ease.</p>
				<div class="go-btn">Continue as Buyer</div>
			</a>

			<a class="role-card" href="sample1a.php">
				<div class="role-icon"><i class="fas fa-store"></i></div>
				<h2>I'm a Seller</h2>
				<p>Set up your shop, list your products, and manage orders from your very own dashboard.</p>
				<div class="go-btn">Continue as Seller</div>
			</a>

		</div>

	</div>

</body>

</html>
