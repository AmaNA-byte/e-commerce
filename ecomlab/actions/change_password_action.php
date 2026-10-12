<?php
require_once __DIR__ . '/../core/core.php';
require_login();
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/account/change_password.php');
}

$current = $_POST['current_pass'] ?? '';
$new = $_POST['new_pass'] ?? '';
$confirm = $_POST['confirm_pass'] ?? '';

$error = '';
if ($current === '' || $new === '' || $confirm === '') {
  $error = 'Please fill in all fields.';
} elseif ($new !== $confirm) {
  $error = 'The new passwords do not match.';
} elseif ($new === $current) {
  $error = 'The new password must be different from the current one.';
} elseif (!password_is_strong($new)) {
  $error = PASSWORD_RULE;
}

if ($error) {
  $_SESSION['error'] = $error;
  redirect('../views/account/change_password.php');
}

$controller = new CustomerController();
$result = $controller->changePassword($_SESSION['customer_id'], $_SESSION['customer_email'], $current, $new);

if (!$result['success']) {
  $_SESSION['error'] = $result['error'];
  redirect('../views/account/change_password.php');
}

unset($_SESSION['must_change_password']);
$_SESSION['success'] = 'Password changed.';
redirect('../views/account/my_account.php');
