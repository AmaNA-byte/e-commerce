<?php
require_once __DIR__ . '/../../core/core.php';
require_login();

$page_title = 'My Account - Shoppn';
require __DIR__ . '/../layout/header.php';
?>
<?php if (isset($_SESSION['success'])): ?>
  <p class="success"><?= e($_SESSION['success']) ?></p>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<h1>My Account</h1>
<p><strong>Name:</strong> <?= e($_SESSION['customer_name']) ?></p>
<p><strong>Email:</strong> <?= e($_SESSION['customer_email']) ?></p>
<p><strong>Account type:</strong> <?= is_admin() ? 'Admin' : 'Customer' ?></p>
<?php require __DIR__ . '/../layout/footer.php'; ?>
