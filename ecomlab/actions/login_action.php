<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/login.php');
}

$email = trim(strip_tags($_POST['customer_email'] ?? ''));
$pass = $_POST['customer_pass'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') {
  $_SESSION['error'] = 'Please enter a valid email and password.';
  redirect('../views/login.php');
}

$controller = new CustomerController();
$result = $controller->login($email, $pass);

if (isset($result['error'])) {
  $_SESSION['error'] = $result['error'];
  redirect('../views/login.php');
}

// Correct password, but the email is not verified yet: send a code (at most one a minute) and ask for it.
if (!(int) $result['is_verified']) {
  require_once __DIR__ . '/../core/email_codes.php';
  $status = send_code($controller, $result, 'verify');
  $_SESSION['verify_email'] = $result['customer_email'];
  if ($status === 'sent') {
    $_SESSION['success'] = 'Please verify your email first. We emailed a 6-digit code to ' . $result['customer_email'] . '.';
  } elseif ($status === 'too_soon') {
    $_SESSION['success'] = 'Please verify your email first. Enter the code we emailed you a moment ago.';
  }
  redirect('../views/verify_email.php');
}

session_regenerate_id(true);
$_SESSION['customer_id'] = $result['customer_id'];
$_SESSION['customer_name'] = $result['customer_name'];
$_SESSION['customer_email'] = $result['customer_email'];
$_SESSION['user_role'] = (int) $result['user_role'];

// Old accounts may still have a weak password: make them pick a strong one now.
if (!password_is_strong($pass)) {
  $_SESSION['must_change_password'] = true;
  redirect('../views/account/change_password.php');
}

// Came from checkout? Go back there instead of the home page.
$after_login = $_SESSION['after_login'] ?? '../index.php';
unset($_SESSION['after_login']);
redirect($after_login);
