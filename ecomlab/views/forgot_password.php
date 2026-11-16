<?php
require_once __DIR__ . '/../core/core.php';

$page_title = 'Forgot Password - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<h1>Forgot Password</h1>
<p>Enter the email you registered with and we will email you a 6-digit code to choose a new password.</p>

<form action="../actions/forgot_password_action.php" method="POST">
  <?= csrf_field() ?>
  <label>Email</label>
  <input type="email" name="customer_email" required>

  <button type="submit">Send Code</button>
</form>

<p><a href="login.php">Back to Login</a></p>
<?php require __DIR__ . '/layout/footer.php'; ?>
