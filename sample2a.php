<?php
session_start();
include 'connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop_name        = trim($_POST['name'] ?? '');
    $fullname         = trim($_POST['fullname'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $mobile           = trim($_POST['mobile'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($shop_name === '' || $fullname === '' || $email === '' || $mobile === '' || $password === '') {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE user_email = ?");
        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = "An account with that email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (user_name, user_email, user_password, user_contactnum, user_type) VALUES (?, ?, ?, ?, 'seller')");
            $stmt->bind_param("ssss", $fullname, $email, $hashedPassword, $mobile);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;

                // Use the shop name they actually typed, not a generated default
                $shop_stmt = $conn->prepare("INSERT INTO shop (user_id, shop_name) VALUES (?, ?)");
                $shop_stmt->bind_param("is", $user_id, $shop_name);
                $shop_stmt->execute();

                echo "<script>alert('Registration successful! Please log in.'); window.location.href='sample1a.php';</script>";
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>FruzzHub Sample 2a (Seller's View)</title>
	<link rel="stylesheet" href="fonts/stylesheet.css">
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

		.main {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 60px;
			width: 100%;
			max-width: 1400px;
		}

		.header-logo {
			text-align: center;
			display: flex;
			justify-content: center;
			align-items: center;
		}

		.header-logo img {
			max-width: 100%;
			height: auto;
			display: block;
		}

		.login_form h1 {
			font-size: 60px;
			font-family: 'Butflow';
			margin-top: 10px;
			color: black;
			font-weight: 900;
		}

		.login_form {
			background-color: #a8dba8;
			padding: 4%;
			width: 500px;
			max-width: 100%;
			border-radius: 5%;
			text-align: center;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
			border: 4px solid darkgreen;
			box-sizing: border-box;
		}

		.error-msg {
			background: #f8d7da;
			color: #721c24;
			padding: 10px 16px;
			border-radius: 12px;
			margin-bottom: 10px;
			font-size: 14px;
		}

		.field-error {
			color: #721c24;
			font-size: 13px;
			text-align: left;
			margin: -4px 0 6px 15px;
			min-height: 16px;
		}

		input.input-error {
			border-color: #c0392b;
		}

		input {
			width: 100%;
			height: 50px;
			font-size: 18px;
			padding: 5px 15px;
			margin: 8px 0;
			border: 3px solid darkgreen;
			border-radius: 25px;
			box-sizing: border-box;
			display: block;
			margin-left: auto;
			margin-right: auto;
			background-color: rgba(230, 255, 230, 0.85);
			transition: all 0.25s ease;
		}

		input:hover,
		input:focus {
			transform: scale(1.02);
			box-shadow: 0 6px 15px rgba(0, 100, 0, 0.25);
			background-color: rgba(255, 255, 255, 0.95);
			outline: none;
		}

		button {
			background-image: linear-gradient(to bottom, #6FBF73, #2E7D32);
			color: white;
			width: 100%;
			max-width: 320px;
			height: 55px;
			border: none;
			border-radius: 30px;
			display: block;
			margin: 20px auto 15px auto;
			cursor: pointer;
			font-weight: bold;
			font-size: 20px;
			transition: all 0.3s ease;
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
		}

		button:hover {
			background-image: linear-gradient(to bottom, #7DD182, #388E3C);
			transform: translateY(-2px);
			box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
		}

		button:active {
			transform: scale(0.97);
		}

		p {
			font-family: Arial, sans-serif;
			color: black;
			text-align: center;
		}

		@media screen and (max-width: 900px) {
			body {
				align-items: flex-start;
				justify-content: flex-start;
				padding-top: 20px;
			}

			.main {
				flex-direction: column;
				gap: 30px;
				align-items: center;
				justify-content: center;
			}

			.header-logo {
				width: 100%;
				float: none;
				margin-bottom: 10px;
				order: 1;
			}

			.header-logo img {
				max-width: 240px;
			}

			.login_form {
				width: 100%;
				max-width: 420px;
				margin: 0 auto;
				order: 2;
			}
		}

		@media screen and (max-width: 500px) {
			.login_form h1 {
				font-size: 34px;
			}

			input {
				height: 45px;
				font-size: 16px;
			}

			button {
				height: 48px;
				font-size: 18px;
			}
		}
	</style>

</head>

<body>

	<div class="main">

		<div class="login_form">
			<form action="sample2a.php" method="post" id="signupForm" novalidate>
				<h1>REGISTER</h1>

				<?php if ($error): ?>
					<div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
				<?php endif; ?>

				<input type="text" name="name" placeholder="Shop Name" required autofocus
				       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
				<input type="text" name="fullname" placeholder="Owner Name" required
				       value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
				<input type="email" name="email" placeholder="Email" required
				       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
				<input type="number" name="mobile" placeholder="Mobile Number" required
				       value="<?php echo isset($_POST['mobile']) ? htmlspecialchars($_POST['mobile']) : ''; ?>">
				<input type="password" name="password" id="password" placeholder="Password" required>
				<input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
				<div class="field-error" id="matchError"></div>

				<button type="submit">Sign Up</button>
				<p>Already have an account? <a href="sample1a.php">Sign In</a></p>
			</form>
		</div>

		<div class="header-logo">
			<picture>
				<source srcset="images/mainlogo.png" media="(max-width: 900px)">
				<img src="images/logo.png" alt="FruzzHub">
			</picture>
		</div>

	</div>

	<script>
		// Instant feedback if the two password fields don't match yet.
		// The server still re-checks everything on submit — this is just UX.
		const form = document.getElementById('signupForm');
		const password = document.getElementById('password');
		const confirmPassword = document.getElementById('confirm_password');
		const matchError = document.getElementById('matchError');

		function checkMatch() {
			if (confirmPassword.value.length === 0) {
				matchError.textContent = '';
				confirmPassword.classList.remove('input-error');
				return true;
			}
			if (password.value !== confirmPassword.value) {
				matchError.textContent = 'Passwords do not match.';
				confirmPassword.classList.add('input-error');
				return false;
			}
			matchError.textContent = '';
			confirmPassword.classList.remove('input-error');
			return true;
		}

		password.addEventListener('input', checkMatch);
		confirmPassword.addEventListener('input', checkMatch);

		form.addEventListener('submit', function (e) {
			if (!checkMatch()) {
				e.preventDefault();
			}
		});
	</script>

</body>

</html>
