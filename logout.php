<?php
session_start();

// Figure out which login page to send them back to before wiping the session
$user_type = $_SESSION['user_type'] ?? '';
$redirect_to = ($user_type === 'seller') ? 'sample1a.php' : 'sample1.php';

session_unset();
session_destroy();
header("Location: " . $redirect_to);
exit();
?>