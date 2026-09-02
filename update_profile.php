<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];

    if ($type === 'personal') {
        $user_name = trim($_POST['user_name']);
        $user_email = trim($_POST['user_email']);
        $user_contactnum = trim($_POST['user_contactnum']);
        $user_homeaddress = trim($_POST['user_homeaddress']);
        $user_description = trim($_POST['user_description']);
        $new_password = $_POST['new_password'];

        // Check if email is already used by another user
        $check = $conn->prepare("SELECT user_id FROM users WHERE user_email = ? AND user_id != ?");
        $check->bind_param("si", $user_email, $user_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo "<script>alert('Email already used by another account!'); window.location.href='profile.php';</script>";
            exit();
        }

        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET user_name=?, user_email=?, user_contactnum=?, user_homeaddress=?, user_description=?, user_password=? WHERE user_id=?");
            $stmt->bind_param("ssssssi", $user_name, $user_email, $user_contactnum, $user_homeaddress, $user_description, $hashed, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET user_name=?, user_email=?, user_contactnum=?, user_homeaddress=?, user_description=? WHERE user_id=?");
            $stmt->bind_param("sssssi", $user_name, $user_email, $user_contactnum, $user_homeaddress, $user_description, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['name'] = $user_name;
            $_SESSION['email'] = $user_email;
            header("Location: restaurant_profile.php?success=personal");
            exit();
        }
    }

    if ($type === 'shop') {
        $shop_name = trim($_POST['shop_name']);
        $shop_address = trim($_POST['shop_address']);
        $shop_history = trim($_POST['shop_history']);
        $shop_mission = trim($_POST['shop_mission']);
        $shop_vision = trim($_POST['shop_vision']);

        $stmt = $conn->prepare("UPDATE shop SET shop_name=?, shop_address=?, shop_history=?, shop_mission=?, shop_vision=? WHERE user_id=?");
        $stmt->bind_param("sssssi", $shop_name, $shop_address, $shop_history, $shop_mission, $shop_vision, $user_id);

        if ($stmt->execute()) {
            header("Location: restaurant_profile.php?success=shop");
            exit();
        }
    }
}

header("Location: restaurant_profile.php");
exit();
?>