<?php
// Loaded by index.php, which has already required core.php.
$page_title = 'Home - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<section>
  <?php if (isset($_SESSION['error'])): ?>
    <p class="error"><?= e($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <div class="hero">
    <?php if (is_logged_in()): ?>
      <h1>Hello, <?= e($_SESSION['customer_name']) ?>!</h1>
      <p>Welcome back to Shoppn.</p>
      <a href="<?= BASE_URL ?>views/account/my_account.php" class="btn">My Account</a>
    <?php else: ?>
      <h1>Welcome to Shoppn</h1>
      <p>Your one-stop shop for great products at great prices.</p>
      <a href="<?= BASE_URL ?>views/register.php" class="btn">Register</a>
      <a href="<?= BASE_URL ?>views/login.php" class="btn">Login</a>
    <?php endif; ?>
  </div>

  <h2>Products</h2>
  <p>Products will appear here.</p>

  <?php require __DIR__ . '/layout/sidebar.php'; ?>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
