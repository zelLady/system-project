<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$user_stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Get shop data
$shop_stmt = $conn->prepare("SELECT * FROM shop WHERE user_id = ?");
$shop_stmt->bind_param("i", $user_id);
$shop_stmt->execute();
$shop = $shop_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 15px;
        }

        .profile-container {
            max-width: 950px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
        }

        .header a {
            color: white;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 14px;
        }

        .header a:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .content {
            padding: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
        }

        .card h2 {
            color: #764ba2;
            margin-bottom: 20px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #764ba2;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="profile-container">
        <div class="header">
            <h1><i class="fas fa-edit"></i> Edit Profile</h1>
            <div>
                <a href="restaurant_profile.php">← Back to Profile</a>
                <a href="logout.php" style="margin-left:10px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="content">
            <!-- Personal Information -->
            <div class="card">
                <h2><i class="fas fa-user"></i> Personal Information</h2>

                <form action="update_profile.php" method="POST">
                    <input type="hidden" name="type" value="personal">

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="user_name"
                            value="<?php echo htmlspecialchars($user['user_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="user_email"
                            value="<?php echo htmlspecialchars($user['user_email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="user_contactnum"
                            value="<?php echo htmlspecialchars($user['user_contactnum'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Home Address</label>
                        <input type="text" name="user_homeaddress"
                            value="<?php echo htmlspecialchars($user['user_homeaddress'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea
                            name="user_description"><?php echo htmlspecialchars($user['user_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>New Password (leave blank if no change)</label>
                        <input type="password" name="new_password" placeholder="Enter new password">
                    </div>
                    <button type="submit" class="btn">Update Personal Info</button>
                </form>
            </div>

            <!-- Shop Details -->
            <div class="card">
                <h2><i class="fas fa-store"></i> Shop Details</h2>

                <form action="update_profile.php" method="POST">
                    <input type="hidden" name="type" value="shop">

                    <div class="form-group">
                        <label>Shop Name</label>
                        <input type="text" name="shop_name"
                            value="<?php echo htmlspecialchars($shop['shop_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Shop Address</label>
                        <input type="text" name="shop_address"
                            value="<?php echo htmlspecialchars($shop['shop_address'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Shop History</label>
                        <textarea
                            name="shop_history"><?php echo htmlspecialchars($shop['shop_history'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Shop Mission</label>
                        <textarea
                            name="shop_mission"><?php echo htmlspecialchars($shop['shop_mission'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Shop Vision</label>
                        <textarea
                            name="shop_vision"><?php echo htmlspecialchars($shop['shop_vision'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn">Update Shop Details</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>