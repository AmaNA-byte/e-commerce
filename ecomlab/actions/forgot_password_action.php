<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/email_codes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/forgot_password.php');
}

$email = trim($_POST['customer_email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $_SESSION['error'] = 'Please enter a valid email address.';
  redirect('../views/forgot_password.php');
}

$controller = new CustomerController();
$customer = $controller->getCustomerByEmail($email);
if ($customer) {
  send_code($controller, $customer, 'reset'); // at most one email a minute (see core/email_codes.php)
}

// Everyone goes to the code page with the same message, whether or not the email is registered,
// so this form cannot be used to find out who has an account.
$_SESSION['reset_email'] = $email;
$_SESSION['success'] = 'If an account exists for ' . $email . ', we have emailed it a 6-digit code. It works for ' . CODE_MINUTES . ' minutes.';
redirect('../views/reset_password.php');
