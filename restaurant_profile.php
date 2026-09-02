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
    <title><?php echo htmlspecialchars($shop['shop_name'] ?? 'My Shop'); ?> - Profile</title>
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
            padding: 40px 20px;
        }

        .container {
            max-width: 850px;
            margin: 0 auto;
        }

        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .actions {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }

        .actions a {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }

        .actions a:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .content {
            padding: 40px 30px;
        }

        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
        }

        .info-item i {
            color: #764ba2;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .info-item h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .info-item p {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }

        .description {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .description h3 {
            color: #764ba2;
            margin-bottom: 12px;
            font-size: 18px;
        }

        .description p {
            color: #555;
            line-height: 1.7;
            font-size: 15px;
        }

        .edit-btn {
            display: block;
            text-align: center;
            margin-top: 30px;
        }

        .edit-btn a {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 14px 35px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: 0.3s;
        }

        .edit-btn a:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(118, 75, 162, 0.3);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="profile-card">
            <div class="header">
                <div class="actions">
                    <a href="profile.php"><i class="fas fa-edit"></i> Edit</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
                <h1><?php echo htmlspecialchars($shop['shop_name'] ?? 'My Shop'); ?></h1>
                <p>Owned by <?php echo htmlspecialchars($user['user_name'] ?? ''); ?></p>
            </div>

            <div class="content">
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-msg">
                        <?php
                        if ($_GET['success'] == 'personal')
                            echo "Personal information updated successfully!";
                        if ($_GET['success'] == 'shop')
                            echo "Shop details updated successfully!";
                        ?>
                    </div>
                <?php endif; ?>

                <div class="info-grid">
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>Shop Address</h3>
                        <p><?php echo htmlspecialchars($shop['shop_address'] ?: 'Not set yet'); ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <h3>Contact Number</h3>
                        <p><?php echo htmlspecialchars($user['user_contactnum'] ?: 'Not set yet'); ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <h3>Email</h3>
                        <p><?php echo htmlspecialchars($user['user_email'] ?? ''); ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-home"></i>
                        <h3>Home Address</h3>
                        <p><?php echo htmlspecialchars($user['user_homeaddress'] ?: 'Not set yet'); ?></p>
                    </div>
                </div>

                <div class="description">
                    <h3><i class="fas fa-info-circle"></i> About the Owner</h3>
                    <p><?php echo nl2br(htmlspecialchars($user['user_description'] ?: 'No description yet.')); ?></p>
                </div>

                <div class="description">
                    <h3><i class="fas fa-history"></i> Shop History</h3>
                    <p><?php echo nl2br(htmlspecialchars($shop['shop_history'] ?: 'No history yet.')); ?></p>
                </div>

                <div class="description">
                    <h3><i class="fas fa-bullseye"></i> Mission</h3>
                    <p><?php echo nl2br(htmlspecialchars($shop['shop_mission'] ?: 'No mission yet.')); ?></p>
                </div>

                <div class="description">
                    <h3><i class="fas fa-eye"></i> Vision</h3>
                    <p><?php echo nl2br(htmlspecialchars($shop['shop_vision'] ?: 'No vision yet.')); ?></p>
                </div>

                <div class="edit-btn">
                    <a href="profile.php"><i class="fas fa-edit"></i> Edit Profile</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>