<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/email_codes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/verify_email.php');
}

$email = $_SESSION['verify_email'] ?? '';
if ($email === '') {
  redirect('../views/login.php');
}

$code = posted_code();
if ($code === '') {
  $_SESSION['error'] = 'Please enter all 6 digits of the code.';
  redirect('../views/verify_email.php');
}

$controller = new CustomerController();
$customer = $controller->getCustomerByEmail($email);
if (!$customer) {
  unset($_SESSION['verify_email']);
  redirect('../views/login.php');
}
if ($customer['is_verified']) {
  unset($_SESSION['verify_email']);
  $_SESSION['success'] = 'Your email is already verified. You can log in.';
  redirect('../views/login.php');
}

$result = $controller->checkCode($customer['customer_id'], 'verify', $code);
if ($result !== 'ok') {
  $_SESSION['error'] = $result === 'wrong'
    ? 'That code is not correct. Please check the email and try again.'
    : 'That code has expired or was entered wrong too many times. Click "Send a new code" below.';
  redirect('../views/verify_email.php');
}

$controller->markVerified($customer['customer_id']);
$controller->deleteCodes($customer['customer_id'], 'verify');
unset($_SESSION['verify_email']);

$_SESSION['success'] = 'Your email is verified! You can now log in.';
redirect('../views/login.php');
