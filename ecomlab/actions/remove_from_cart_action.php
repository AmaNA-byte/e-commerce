<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/cart.php');
}

// remove[] holds the product ids of the ticked rows
$ids = array_filter((array) ($_POST['remove'] ?? []), 'ctype_digit');

if (!$ids) {
  $_SESSION['error'] = 'Tick the items you want to remove first.';
  redirect('../views/cart.php');
}

$controller = new CartController();
$ip = get_ip();
foreach ($ids as $product_id) {
  $controller->removeFromCart((int) $product_id, $ip);
}

$_SESSION['success'] = count($ids) === 1 ? 'Item removed from your cart.' : 'Items removed from your cart.';
redirect('../views/cart.php');
