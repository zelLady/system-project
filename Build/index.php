<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Portal - Login & Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sign In Form -->
        <div class="form-box login" id="signIn">
            <h2>Sign In</h2>
            <form method="post" action="register.php">
                <div class="input-box">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div>
                <div class="input-box">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>
                <button type="submit" class="btn" name="signIn">Sign In</button>
                <div class="toggle-text">
                    Don't have an account? <a href="#" id="signUpButton">Sign Up</a>
                </div>
            </form>
        </div>

        <!-- Sign Up Form -->
        <div class="form-box register" id="signup" style="display: none;">
            <h2>Create Account</h2>
            <form method="post" action="register.php">
                <div class="input-box">
                    <i class="fas fa-user"></i>
                    <input type="text" name="user_name" required>
                    <label>Full Name</label>
                </div>
                <div class="input-box">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div>
                <div class="input-box">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>
                <button type="submit" class="btn" name="signUp">Sign Up</button>
                <div class="toggle-text">
                    Already have an account? <a href="#" id="signInButton">Sign In</a>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>