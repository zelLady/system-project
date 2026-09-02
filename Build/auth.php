<?php
// includes/auth.php
//
// Shared helper included by nearly every page AFTER connect.php and
// session_start(). This file must NOT exit() or echo anything — pages
// that need a logged-in user already do their own redirect check like:
//
//   if (!isset($_SESSION['user_id'])) {
//       header("Location: index.php");
//       exit();
//   }
//
// This file just exposes $current_user_id (0 if not logged in) and the
// get_shop_id_for_user() helper used by the product-management pages.

$current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

/**
 * Look up the shop_id that belongs to a given user.
 * Returns the shop_id as an int, or null if the user has no shop row yet.
 */
function get_shop_id_for_user(mysqli $conn, int $user_id): ?int {
    if ($user_id <= 0) {
        return null;
    }

    $stmt = $conn->prepare("SELECT shop_id FROM shop WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return $row ? (int)$row['shop_id'] : null;
}
?>