<?php
require_once __DIR__ . '/../../core/core.php';
require_login();

$page_title = 'Change Password - Shoppn';
require __DIR__ . '/../layout/header.php';
?>
<h1>Change Password</h1>

<?php if (!empty($_SESSION['must_change_password'])): ?>
  <p class="error">Your current password is too easy to guess. Please choose a stronger one to continue.</p>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
  <p class="error"><?= e($_SESSION['error']) ?></p>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form id="passwordForm" action="../../actions/change_password_action.php" method="POST">
  <?= csrf_field() ?>
  <label>Current Password</label>
  <input type="password" name="current_pass" required>

  <label>New Password</label>
  <input type="password" name="new_pass" data-strength required>
  <span class="error" id="new_pass_error"></span>

  <label>Confirm New Password</label>
  <input type="password" name="confirm_pass" required>
  <span class="error" id="confirm_pass_error"></span>

  <button type="submit">Change Password</button>
</form>

<script src="../../js/password.js"></script>
<script>
document.getElementById('passwordForm').addEventListener('submit', function (event) {
  const strong = passwordStrength(this.new_pass.value).ok;
  const same = this.new_pass.value === this.confirm_pass.value;
  document.getElementById('new_pass_error').textContent = strong ? '' : 'This password is not strong enough yet.';
  document.getElementById('confirm_pass_error').textContent = same ? '' : 'The passwords do not match.';
  if (!strong || !same) event.preventDefault();
});
</script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
