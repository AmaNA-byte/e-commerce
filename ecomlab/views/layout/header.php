<?php
require_once __DIR__ . '/../../controllers/CartController.php';
$cartController = new CartController();
$cart_count = $cartController->getCartCount(get_ip());
$cart_total = $cartController->getCartTotal(get_ip());
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page_title ?? 'Shoppn') ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body>
<?php // Side menu on the left of every page. ?>
<aside class="side-nav">
  <a href="<?= BASE_URL ?>index.php" class="logo">Shoppn</a>

  <p class="side-heading">Shop</p>
  <ul>
    <li><a href="<?= BASE_URL ?>index.php">All Products</a></li>
    <li><a href="<?= BASE_URL ?>views/cart.php">Cart (<?= $cart_count ?>)</a></li>
  </ul>

  <?php require __DIR__ . '/sidebar.php'; // categories and brands ?>

  <p class="side-heading"><?= is_logged_in() ? 'Hi, ' . e($_SESSION['customer_name']) : 'Account' ?></p>
  <ul>
    <?php if (is_logged_in()): ?>
      <li><a href="<?= BASE_URL ?>views/account/my_account.php">My Account</a></li>
      <li><a href="<?= BASE_URL ?>logout.php">Logout</a></li>
    <?php else: ?>
      <li><a href="<?= BASE_URL ?>views/login.php">Login</a></li>
      <li><a href="<?= BASE_URL ?>views/register.php">Register</a></li>
    <?php endif; ?>
  </ul>

  <?php if (is_admin()): ?>
    <p class="side-heading">Admin</p>
    <ul>
      <li><a href="<?= BASE_URL ?>views/admin/brand.php">Brands</a></li>
      <li><a href="<?= BASE_URL ?>views/admin/category.php">Categories</a></li>
      <li><a href="<?= BASE_URL ?>views/admin/product.php">Products</a></li>
      <li><a href="<?= BASE_URL ?>views/admin/customers.php">Customers</a></li>
    </ul>
  <?php endif; ?>
</aside>

<div class="page">
<header>
  <nav>
    <form action="<?= BASE_URL ?>views/search_results.php" method="GET" class="search">
      <input type="text" name="user_query" placeholder="Search products..." value="<?= e($_GET['user_query'] ?? '') ?>">
      <button type="submit">Search</button>
    </form>

    <div class="cart-summary">
      Shopping Cart &mdash; Total Items: <?= $cart_count ?> | Total Price: $<?= number_format($cart_total, 2) ?> |
      <a href="<?= BASE_URL ?>views/cart.php">Go to Cart</a>
    </div>
  </nav>
</header>
<main>
<?php // Messages set by actions (e.g. add to cart) show on whichever page the customer lands on. ?>
<?php if (isset($_SESSION['success'])): ?>
  <p class="success"><?= e($_SESSION['success']) ?></p>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
  <p class="error"><?= e($_SESSION['error']) ?></p>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>
