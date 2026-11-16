<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/email_codes.php';

// Reached from Forgot Password, which emails the code.
$email = $_SESSION['reset_email'] ?? '';
if ($email === '') {
  redirect(BASE_URL . 'views/forgot_password.php');
}

$page_title = 'Reset Password - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<h1>Reset Password</h1>
<p class="code-sent">Enter the 6-digit code we emailed to <strong><?= e($email) ?></strong>, then choose a new password. Check your Spam folder if you do not see it.</p>

<?= dev_code_notice() ?>

<form id="passwordForm" action="../actions/reset_password_action.php" method="POST">
  <?= csrf_field() ?>
  <label>Code from the email</label>
  <div class="code-inputs">
    <?php for ($i = 1; $i <= 6; $i++): ?>
      <input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="Digit <?= $i ?>" required>
    <?php endfor; ?>
  </div>
  <input type="hidden" name="code">
  <span class="error code-error"></span>

  <label>New Password</label>
  <input type="password" name="new_pass" data-strength required>
  <span class="error" id="new_pass_error"></span>

  <label>Confirm New Password</label>
  <input type="password" name="confirm_pass" required>
  <span class="error" id="confirm_pass_error"></span>

  <button type="submit">Reset Password</button>
</form>

<form action="../actions/resend_code_action.php" method="POST" class="inline-form resend">
  <?= csrf_field() ?>
  <input type="hidden" name="purpose" value="reset">
  Didn't get the code? <button type="submit" class="link-button">Send a new code</button>
</form>
<p><a href="forgot_password.php">Use a different email</a> | <a href="login.php">Back to Login</a></p>

<script src="../js/password.js"></script>
<script src="../js/code_input.js"></script>
<script>
document.getElementById('passwordForm').addEventListener('submit', function (event) {
  const strong = passwordStrength(this.new_pass.value).ok;
  const same = this.new_pass.value === this.confirm_pass.value;
  document.getElementById('new_pass_error').textContent = strong ? '' : 'This password is not strong enough yet.';
  document.getElementById('confirm_pass_error').textContent = same ? '' : 'The passwords do not match.';
  if (!strong || !same) event.preventDefault();
});
</script>
<?php require __DIR__ . '/layout/footer.php'; ?>
