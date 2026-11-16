<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/email_codes.php';

// Reached after registering, or after logging in to an account that is not verified yet.
$email = $_SESSION['verify_email'] ?? '';
if ($email === '') {
  redirect(BASE_URL . 'views/login.php');
}

$page_title = 'Verify Email - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<h1>Verify Your Email</h1>
<p class="code-sent">We sent a 6-digit code to <strong><?= e($email) ?></strong>. Enter it below to verify your account. Check your Spam folder if you do not see it.</p>

<?= dev_code_notice() ?>

<form action="../actions/verify_email_action.php" method="POST">
  <?= csrf_field() ?>
  <label>Verification code</label>
  <div class="code-inputs">
    <?php for ($i = 1; $i <= 6; $i++): ?>
      <input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="Digit <?= $i ?>" required>
    <?php endfor; ?>
  </div>
  <input type="hidden" name="code">
  <span class="error code-error"></span>

  <button type="submit">Verify Email</button>
</form>

<form action="../actions/resend_code_action.php" method="POST" class="inline-form resend">
  <?= csrf_field() ?>
  <input type="hidden" name="purpose" value="verify">
  Didn't get the code? <button type="submit" class="link-button">Send a new code</button>
</form>
<p><a href="login.php">Back to Login</a></p>

<script src="../js/code_input.js"></script>
<?php require __DIR__ . '/layout/footer.php'; ?>
