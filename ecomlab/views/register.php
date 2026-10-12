<?php
require_once __DIR__ . '/../core/core.php';
$page_title = 'Register - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<h1>Register</h1>

<?php if (isset($_SESSION['error'])): ?>
  <p class="error"><?= e($_SESSION['error']) ?></p>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form id="registerForm" action="../actions/register_action.php" method="POST" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <label>Full Name</label>
  <input type="text" name="customer_name" maxlength="100">
  <span class="error" id="customer_name_error"></span>

  <label>Email</label>
  <input type="text" name="customer_email" maxlength="50">
  <span class="error" id="customer_email_error"></span>

  <label>Password</label>
  <input type="password" name="customer_pass" data-strength>
  <span class="hint">At least 8 characters, with at least 3 of: lowercase letter, uppercase letter, number, symbol.</span>
  <span class="error" id="customer_pass_error"></span>

  <label>Country</label>
  <select name="customer_country">
    <option value="">-- Select country --</option>
    <option>Ghana</option>
    <option>Nigeria</option>
    <option>Kenya</option>
    <option>South Africa</option>
    <option>United Kingdom</option>
    <option>United States</option>
  </select>
  <span class="error" id="customer_country_error"></span>

  <label>City</label>
  <input type="text" name="customer_city" maxlength="30">
  <span class="error" id="customer_city_error"></span>

  <label>Contact Number</label>
  <input type="text" name="customer_contact" maxlength="15">
  <span class="error" id="customer_contact_error"></span>

  <label>Profile Image (optional)</label>
  <input type="file" name="customer_image" accept="image/*">

  <button type="submit">Register</button>
</form>

<p>Already have an account? <a href="login.php">Login</a></p>

<script src="../js/password.js"></script>
<script src="../js/validate.js"></script>
<?php require __DIR__ . '/layout/footer.php'; ?>
