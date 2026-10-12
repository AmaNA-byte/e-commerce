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
redirect('../index.php');
