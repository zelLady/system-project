<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
        $error = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirmation do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } else {
        $stmt = $conn->prepare("SELECT user_password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row || !password_verify($current_password, $row['user_password'])) {
            $error = "Your current password is incorrect.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET user_password = ? WHERE user_id = ?");
            $update->bind_param("si", $hashed, $user_id);

            if ($update->execute()) {
                header("Location: sample7.php?success=password");
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
  <title>FruzzHub - Change Password</title>
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
	}

	.page {
	  display: flex;
	  flex-direction: column;
	  align-items: center;
	  gap: 12px;
	  width: 100%;
	}

	.card {
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
	  font-size: 34px;
	  margin: 0 0 18px;
	  color: black;
	}

	label {
	  display: block;
	  text-align: left;
	  font-weight: bold;
	  color: #1d2f1d;
	  margin: 10px 4px 4px;
	  font-size: 14px;
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
	  margin: -2px 4px 4px;
	  min-height: 16px;
	}

	input {
	  width: 100%;
	  height: 48px;
	  font-size: 16px;
	  padding: 5px 15px;
	  margin: 4px 0 6px;
	  border: 3px solid darkgreen;
	  border-radius: 20px;
	  background-color: rgba(230, 255, 230, 0.85);
	}

	input.input-error {
	  border-color: #c0392b;
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
	  height: 52px;
	  border: none;
	  border-radius: 30px;
	  display: block;
	  margin: 20px auto 10px;
	  cursor: pointer;
	  font-weight: bold;
	  font-size: 18px;
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
  </style>
</head>
<body>
  <div class="page">
	<div class="card">
	  <h1>Change Password</h1>

	  <?php if ($error): ?>
	    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
	  <?php endif; ?>

	  <form method="POST" action="change_password.php" id="pwForm" novalidate>
	    <label for="current_password">Current Password</label>
	    <input type="password" id="current_password" name="current_password" required>

	    <label for="new_password">New Password</label>
	    <input type="password" id="new_password" name="new_password" required minlength="6">

	    <label for="confirm_password">Confirm New Password</label>
	    <input type="password" id="confirm_password" name="confirm_password" required>
	    <div class="field-error" id="matchError"></div>

	    <button type="submit">Update Password</button>
	  </form>

	  <a class="go_back" href="sample7.php">Cancel and go back</a>
	</div>
  </div>

  <script>
    // Instant client-side feedback if the two password fields don't match yet.
    // The server still re-checks everything on submit — this is just UX.
    const form = document.getElementById('pwForm');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const matchError = document.getElementById('matchError');

    function checkMatch() {
        if (confirmPassword.value.length === 0) {
            matchError.textContent = '';
            confirmPassword.classList.remove('input-error');
            return true;
        }
        if (newPassword.value !== confirmPassword.value) {
            matchError.textContent = 'Passwords do not match.';
            confirmPassword.classList.add('input-error');
            return false;
        }
        matchError.textContent = '';
        confirmPassword.classList.remove('input-error');
        return true;
    }

    newPassword.addEventListener('input', checkMatch);
    confirmPassword.addEventListener('input', checkMatch);

    form.addEventListener('submit', function (e) {
        if (!checkMatch()) {
            e.preventDefault();
        }
    });
  </script>
</body>
</html>
