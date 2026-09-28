<?php
require_once __DIR__ . '/../core/core.php';
$page_title = 'Login - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<h1>Login</h1>

<?php if (isset($_SESSION['error'])): ?>
  <p class="error"><?= e($_SESSION['error']) ?></p>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form action="../actions/login_action.php" method="POST">
  <label>Email</label>
  <input type="email" name="customer_email" required>

  <label>Password</label>
  <input type="password" name="customer_pass" required>

  <button type="submit">Login</button>
</form>

<p>Don't have an account? <a href="register.php">Register</a></p>
<?php require __DIR__ . '/layout/footer.php'; ?>
