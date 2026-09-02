<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';

// Load current values so the form starts pre-filled
$stmt = $conn->prepare("SELECT user_name, user_email, user_contactnum, user_homeaddress FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name        = trim($_POST['user_name'] ?? '');
    $user_email       = trim($_POST['user_email'] ?? '');
    $user_contactnum  = trim($_POST['user_contactnum'] ?? '');
    $user_homeaddress = trim($_POST['user_homeaddress'] ?? '');

    if ($user_name === '' || $user_email === '') {
        $error = "Name and email are required.";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Make sure no other account is already using this email
        $check = $conn->prepare("SELECT user_id FROM users WHERE user_email = ? AND user_id != ?");
        $check->bind_param("si", $user_email, $user_id);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = "That email is already used by another account.";
        } else {
            $update = $conn->prepare("UPDATE users SET user_name = ?, user_email = ?, user_contactnum = ?, user_homeaddress = ? WHERE user_id = ?");
            $update->bind_param("ssssi", $user_name, $user_email, $user_contactnum, $user_homeaddress, $user_id);

            if ($update->execute()) {
                $_SESSION['name']  = $user_name;
                $_SESSION['email'] = $user_email;
                header("Location: sample7.php?success=updated");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }

    // Keep whatever the user just typed so they don't lose their edits
    $user = [
        'user_name'        => $user_name,
        'user_email'       => $user_email,
        'user_contactnum'  => $user_contactnum,
        'user_homeaddress' => $user_homeaddress,
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FruzzHub - Edit Profile</title>
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
	  <h1>Edit Profile</h1>

	  <?php if ($error): ?>
	    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
	  <?php endif; ?>

	  <form method="POST" action="edit_profile.php">
	    <label for="user_name">Full Name</label>
	    <input type="text" id="user_name" name="user_name" required
	           value="<?php echo htmlspecialchars($user['user_name'] ?? ''); ?>">

	    <label for="user_email">Email</label>
	    <input type="email" id="user_email" name="user_email" required
	           value="<?php echo htmlspecialchars($user['user_email'] ?? ''); ?>">

	    <label for="user_contactnum">Mobile Number</label>
	    <input type="text" id="user_contactnum" name="user_contactnum"
	           value="<?php echo htmlspecialchars($user['user_contactnum'] ?? ''); ?>">

	    <label for="user_homeaddress">Home Address</label>
	    <input type="text" id="user_homeaddress" name="user_homeaddress"
	           value="<?php echo htmlspecialchars($user['user_homeaddress'] ?? ''); ?>">

	    <button type="submit">Save Changes</button>
	  </form>

	  <a class="go_back" href="sample7.php">Cancel and go back</a>
	</div>
  </div>
</body>
</html>
