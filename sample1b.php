<?php
session_start();
include 'connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = "Please enter your email address.";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            // Generate a one-time token, valid for 1 hour
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE user_id = ?");
            $update->bind_param("ssi", $token, $expires, $user['user_id']);
            $update->execute();

            // No email server configured — go straight to the reset form
            header("Location: reset_password.php?token=" . urlencode($token));
            exit();
        } else {
            $error = "No account found with that email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FruzzHub Sample1b (Forgot Password)</title>
  <link rel="stylesheet" href="fonts/stylesheet.css">
  <style>
	* {
	  box-sizing: border-box;
	}

	body {
	  margin: 0;
	  min-height: 100vh;
	  display: flex;
	  align-items: center;
	  justify-content: center;
	  padding: 40px 20px;
	  font-family: Arial, sans-serif;
	  background: radial-gradient(circle at 30% 50%, #1e4d2b 0%, #7bc98c 70%);
	  background-image: url("images/forgotbg.jpg");
	  background-size: cover;
	  background-attachment: fixed;
	  background-repeat: no-repeat;
	}

	.page {
	  display: flex;
	  flex-direction: column;
	  align-items: center;
	  gap: 12px;
	}

	.logo {
	  display: flex;
	  justify-content: center;
	  align-items: center;
	}

	.logo img {
	  max-width: 220px;
	  height: auto;
	  display: block;
	}
	
	.reset {
	  width: 100%;
	  max-width: 480px;
	  background: #a8dba8;
	  border: 4px solid darkgreen;
	  border-radius: 25px;
	  padding: 30px;
	  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
	  text-align: center;
	}

	h1 {
	  font-size: 45px;
	  font-family: 'Butflow';
	  margin: 0 0 10px;
	  color: black;
	}

	p {
	  margin: 0 0 20px;
	  color: #1d2f1d;
	  font-size: 16px;
	}

	.error-msg {
	  background: #f8d7da;
	  color: #721c24;
	  padding: 10px 16px;
	  border-radius: 12px;
	  margin-bottom: 16px;
	  font-size: 14px;
	}

	input {
	  width: 100%;
	  height: 50px;
	  font-size: 18px;
	  padding: 5px 15px;
	  margin: 10px 0;
	  border: 3px solid darkgreen;
	  border-radius: 25px;
	  background-color: rgba(230, 255, 230, 0.85);
	}

	input:focus {
	  outline: none;
	  background-color: rgba(255, 255, 255, 0.95);
	  box-shadow: 0 6px 15px rgba(0, 100, 0, 0.25);
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
	  margin: 20px auto 12px;
	  cursor: pointer;
	  font-weight: bold;
	  font-size: 20px;
	  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
	}

	button:hover {
	  background-image: linear-gradient(to bottom, #7DD182, #388E3C);
	  transform: translateY(-2px);
	}

	.go_back {
	  display: inline-block;
	  margin-top: 8px;
	  color: darkgreen;
	  font-weight: bold;
	  text-decoration: none;
	}

	.go_back:hover {
	  text-decoration: underline;
	}

	@media screen and (max-width: 500px) {
	  .reset {
		padding: 22px;
	  }

	  h1 {
		font-size: 38px;
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
  <div class="page">
	<div class="logo">
	  <img src="images/mainlogo.png" alt="FruzzHub Logo">
	</div>

	<div class="reset">
	  <h1>Forgot Password</h1>
	  <p>Enter your email address and we will send you instructions to reset your password.</p>

	  <?php if ($error): ?>
	    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
	  <?php endif; ?>

	  <form method="POST" action="sample1b.php">
	    <input type="email" name="email" placeholder="Enter your email" required autofocus
	           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
	    <button type="submit">Next</button>
	  </form>

	  <a class="go_back" href="sample1.php">Back to Login</a>
	</div>
  </div>
</body>
</html>
