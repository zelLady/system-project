<?php
session_start();
include 'connect.php';

if (isset($_POST['signUp'])) {
    $user_name     = trim($_POST['user_name']);
    $user_email    = trim($_POST['email']);
    $user_password = $_POST['password'];

    $hashedPassword = password_hash($user_password, PASSWORD_DEFAULT);

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT user_id FROM users WHERE user_email = ?");
    $checkEmail->bind_param("s", $user_email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already exists!'); window.location.href='index.php';</script>";
        exit();
    }

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (user_name, user_email, user_password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user_name, $user_email, $hashedPassword);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Create empty shop for the new user
        $stmt2 = $conn->prepare("INSERT INTO shop (user_id, shop_name) VALUES (?, ?)");
        $defaultName = $user_name . "'s Shop";
        $stmt2->bind_param("is", $user_id, $defaultName);
        $stmt2->execute();

        echo "<script>alert('Registration successful! Please login.'); window.location.href='index.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

if (isset($_POST['signIn'])) {
    $user_email    = trim($_POST['email']);
    $user_password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($user_password, $user['user_password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email']   = $user['user_email'];
            $_SESSION['name']    = $user['user_name'];

            header("Location: restaurant_profile.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password!'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Email not found!'); window.location.href='index.php';</script>";
    }
}
?>