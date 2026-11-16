<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/email_codes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/reset_password.php');
}

$email = $_SESSION['reset_email'] ?? '';
if ($email === '') {
  redirect('../views/forgot_password.php');
}

$code = posted_code();
$new = $_POST['new_pass'] ?? '';
$confirm = $_POST['confirm_pass'] ?? '';

$error = '';
if ($code === '') {
  $error = 'Please enter all 6 digits of the code.';
} elseif ($new === '' || $confirm === '') {
  $error = 'Please fill in both password fields.';
} elseif ($new !== $confirm) {
  $error = 'The passwords do not match.';
} elseif (!password_is_strong($new)) {
  $error = PASSWORD_RULE;
}
if ($error) {
  $_SESSION['error'] = $error;
  redirect('../views/reset_password.php');
}

$controller = new CustomerController();
$customer = $controller->getCustomerByEmail($email);
// No account: answer exactly like a wrong code, so the page does not reveal who is registered.
$result = $customer ? $controller->checkCode($customer['customer_id'], 'reset', $code) : 'wrong';
if ($result !== 'ok') {
  $_SESSION['error'] = $result === 'wrong'
    ? 'That code is not correct. Please check the email and try again.'
    : 'That code has expired or was entered wrong too many times. Click "Send a new code" below.';
  redirect('../views/reset_password.php');
}

if (!$controller->resetPassword($customer['customer_id'], $new)) {
  $_SESSION['error'] = 'Could not reset the password. Please try again.';
  redirect('../views/reset_password.php');
}

unset($_SESSION['reset_email']);
$_SESSION['success'] = 'Your password has been reset. You can now log in with your new password.';
redirect('../views/login.php');
