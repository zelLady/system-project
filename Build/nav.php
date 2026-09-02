<?php
// includes/nav.php
//
// Expects (all optional, set by the page that includes this):
//   $active            - string key of the current section, used to
//                         highlight the matching nav link:
//                         'browse' | 'manage' | 'cart' | 'profile'
//   $current_user_id    - set by includes/auth.php; falsy means "guest"
//
// Requires session_start() and connect.php/auth.php to already have run.

$active = $active ?? '';
$is_logged_in = !empty($current_user_id);
?>
<nav class="navbar">
    <div class="navbar-inner">
        <a href="<?php echo $is_logged_in ? 'products_browse.php' : 'index.php'; ?>" class="nav-brand">
            <i class="fas fa-store"></i> Shop Portal
        </a>

        <button class="nav-toggle" type="button" aria-label="Toggle navigation" onclick="document.getElementById('navLinks').classList.toggle('open')">
            <i class="fas fa-bars"></i>
        </button>

        <ul class="nav-links" id="navLinks">
            <li><a href="products_browse.php" class="<?php echo $active === 'browse' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i> Browse
            </a></li>

            <?php if ($is_logged_in): ?>
                <li><a href="manage_products.php" class="<?php echo $active === 'manage' ? 'active' : ''; ?>">
                    <i class="fas fa-boxes"></i> My Products
                </a></li>
                <li><a href="cart.php" class="<?php echo $active === 'cart' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Cart
                </a></li>
                <li><a href="restaurant_profile.php" class="<?php echo $active === 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i> Profile
                </a></li>
            <?php endif; ?>
        </ul>

        <div class="nav-user">
            <?php if ($is_logged_in): ?>
                <span class="user-pill">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['name'] ?? 'Account'); ?>
                </span>
                <a href="logout.php" class="btn btn-small btn-outline" style="background:rgba(255,255,255,.15); border-color:rgba(255,255,255,.4); color:#fff;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="index.php" class="btn btn-small" style="background:rgba(255,255,255,.2);">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
