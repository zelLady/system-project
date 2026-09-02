<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT user_name, user_email, user_contactnum, user_homeaddress FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
	<title>FruzzHub - Profile</title>
	<style>
		@keyframes gradientMove {
			0% {
				background-position: 0% 50%;
			}
			50% {
				background-position: 100% 50%;
			}
			100% {
				background-position: 0% 50%;
			}
		}
		body{
			margin: 0;
			background: linear-gradient(-45deg, #A8D5A2, #6FBF73, #2E7D32, #A9CBA4);
			background-size: 400% 400%;
			animation: gradientMove 12s ease infinite;
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
		ul{
			list-style-type: none;
			margin: 0;
			padding: 0;
			display: flex;
			flex: 1;	
			font-family: Arial, sans-serif;
			font-weight: bold;
		}
		li{
			flex: 1;
		}
		li a{
			color: white;
			display: block;
			text-decoration: none;
			padding: 20px;
			text-align: center;
			position: relative;
			transition: all 0.3s ease;
		}
		li a:hover{
			color: darkgreen;
			background-color: #A8D5A2;
			transform: translateY(-3px);
		}
		li a::after{
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
		li a:hover::after{
			width: 60%;
		}
		.profile_content{
    		background-color: #C8E6C9;
    		text-align: center;
    		padding: 4%;
    		width: 500px;
    		margin: 100px auto 0 auto;
    		border-radius: 8%;
    		box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    		border: 1px solid rgba(255, 255, 255, 0.5);
    		box-sizing: border-box;
		}
		.profile_content img{
			height: 256px;
			width: 256px;
			border-radius: 100%;
			display: block;
			margin: 0 auto;
		}
		.profile_content h2{
    		color: #FDF6E3;
    		font-family: "Georgia", serif;
    		font-size: 34px;
    		font-weight: bold;
    		letter-spacing: 1px;
    		margin: 20px 0 0 0;
    		text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.25);
		}
		.profile_content p{
    		color: #2E4A2E;
    		font-family: "Segoe UI", Arial, sans-serif;
    		font-size: 15px;
    		letter-spacing: 0.3px;
    		line-height: 1.8;
		}
		.buyer_details{
			margin: 20px 0 0 0;
			line-height: 1.6;
		}
		.success-msg{
			background: #d4edda;
			color: #155724;
			padding: 12px 18px;
			border-radius: 12px;
			margin: 0 0 18px 0;
			font-size: 14px;
			font-weight: 600;
		}
		.profile-actions{
			display: flex;
			gap: 12px;
			justify-content: center;
			flex-wrap: wrap;
			margin-top: 26px;
		}
		.profile-actions a{
			display: inline-flex;
			align-items: center;
			gap: 8px;
			text-decoration: none;
			font-weight: bold;
			font-size: 14px;
			padding: 12px 20px;
			border-radius: 24px;
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.25);
			transition: all 0.25s ease;
		}
		.btn-edit{
			background-image: linear-gradient(to bottom, #6FBF73, #2E7D32);
			color: white;
		}
		.btn-edit:hover{
			background-image: linear-gradient(to bottom, #7DD182, #388E3C);
			transform: translateY(-2px);
		}
		.btn-password{
			background-color: #a8dba8;
			color: black;
			border: 2px solid darkgreen;
		}
		.btn-password:hover{
			background-color: #bfe8bf;
			transform: translateY(-2px);
		}
		.btn-logout{
			background-color: #f8d7da;
			color: #721c24;
			border: 2px solid #c0392b;
		}
		.btn-logout:hover{
			background-color: #f5c2c7;
			transform: translateY(-2px);
		}

		@media screen and (max-width: 700px) {
			ul {
				flex-wrap: wrap;
			}
			li {
				flex: 1 1 33%;
			}
			.profile_content {
				width: 100%;
				max-width: 400px;
				margin: 40px auto 0 auto;
				padding: 8%;
			}
			.profile_content img{
				height: 180px;
				width: 180px;
			}
			.profile-actions{
				flex-direction: column;
			}
		}
	</style>
</head>
<body>
	<div class="header">
		<img src="images/logo.png" alt="FruzzHub" class="header-logo">
		<ul>
			<li> <a href="sample3.php">Home</a> </li>
			<li> <a href="sample4.php">Shops</a> </li>
			<li> <a href="sample5.php">Cart</a> </li>
			<li> <a href="sample6.php">Transactions</a> </li>
			<li> <a href="sample7.php">Profile</a> </li>
			<li> <a href="logout.php">Logout</a> </li>
		</ul>
	</div>
	<div class="profile_content">

		<?php if (isset($_GET['success'])): ?>
			<div class="success-msg">
				<?php
				$map = [
					'updated'  => 'Profile updated successfully!',
					'password' => 'Password changed successfully!',
				];
				echo htmlspecialchars($map[$_GET['success']] ?? 'Done!');
				?>
			</div>
		<?php endif; ?>

		<img src="images/cutie.jpg" alt="Profile photo">
		<h2><?php echo htmlspecialchars($user['user_name'] ?? ''); ?></h2>
		<p class="buyer_details">
			<?php echo htmlspecialchars($user['user_email'] ?? ''); ?> <br>
			<?php echo htmlspecialchars($user['user_homeaddress'] ?: 'No address set yet'); ?> <br>
			<?php echo htmlspecialchars($user['user_contactnum'] ?: 'No contact number set yet'); ?>
		</p>

		<div class="profile-actions">
			<a class="btn-edit" href="edit_profile.php">✎ Edit Profile</a>
			<a class="btn-password" href="change_password.php">🔒 Change Password</a>
			<a class="btn-logout" href="logout.php">⎋ Logout</a>
		</div>

	</div>
</body>
</html>
