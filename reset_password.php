<?php
session_start();
include 'connect.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$error = '';

if ($token === '') {
    header("Location: sample1b.php");
    exit();
}

// Look up the token and make sure it hasn't expired
$stmt = $conn->prepare("SELECT user_id, reset_expires FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$token_valid = $user && strtotime($user['reset_expires']) > time();

if (!$token_valid) {
    $error = "This reset link is invalid or has expired. Please request a new one.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($password === '') {
        $error = "Password cannot be empty.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET user_password = ?, reset_token = NULL, reset_expires = NULL WHERE user_id = ?");
        $update->bind_param("si", $hashed, $user['user_id']);
        $update->execute();

        header("Location: sample1.php?reset=success");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FruzzHub - Reset Password</title>
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

	.field-error {
	  color: #721c24;
	  font-size: 13px;
	  text-align: left;
	  margin: -6px 0 6px 15px;
	  min-height: 16px;
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

	input.input-error {
	  border-color: #c0392b;
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

	button:disabled {
	  opacity: 0.6;
	  cursor: not-allowed;
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
	  <h1>Reset Password</h1>

	  <?php if (!$token_valid): ?>

	    <p><?php echo htmlspecialchars($error); ?></p>
	    <a class="go_back" href="sample1b.php">Request a new link</a>

	  <?php else: ?>

	    <p>Enter your new password below.</p>

	    <?php if ($error): ?>
	      <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
	    <?php endif; ?>

	    <form method="POST" action="reset_password.php" id="resetForm" novalidate>
	      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

	      <input type="password" id="password" name="password" placeholder="New password" required autofocus>

	      <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
	      <div class="field-error" id="matchError"></div>

	      <button type="submit" id="submitBtn">Reset Password</button>
	    </form>

	    <a class="go_back" href="sample1.php">Back to Login</a>

	  <?php endif; ?>
	</div>
  </div>

  <script>
    // Instant client-side feedback if the two password fields don't match yet.
    // The server still re-checks everything on submit, this is just UX.
    const form = document.getElementById('resetForm');
    if (form) {
      const password = document.getElementById('password');
      const confirm = document.getElementById('confirm_password');
      const matchError = document.getElementById('matchError');
      const submitBtn = document.getElementById('submitBtn');

      function checkMatch() {
        if (confirm.value.length === 0) {
          matchError.textContent = '';
          confirm.classList.remove('input-error');
          return true;
        }
        if (password.value !== confirm.value) {
          matchError.textContent = 'Passwords do not match.';
          confirm.classList.add('input-error');
          return false;
        }
        matchError.textContent = '';
        confirm.classList.remove('input-error');
        return true;
      }

      password.addEventListener('input', checkMatch);
      confirm.addEventListener('input', checkMatch);

      form.addEventListener('submit', function (e) {
        if (!checkMatch()) {
          e.preventDefault();
        }
      });
    }
  </script>
</body>
</html>
