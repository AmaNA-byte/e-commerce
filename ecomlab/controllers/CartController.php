<?php
require_once __DIR__ . '/../classes/CartClass.php';

class CartController {
  private $cart;

  public function __construct() {
    $this->cart = new CartClass();
  }

  public function isInCart($product_id, $ip) {
    return $this->cart->isInCart($product_id, $ip);
  }

  public function addToCart($product_id, $qty, $ip, $customer_id = null) {
    return $this->cart->addToCart($product_id, $qty, $ip, $customer_id);
  }

  public function getCartItems($ip) {
    return $this->cart->getCartItems($ip);
  }

  public function getCartCount($ip) {
    return $this->cart->getCartCount($ip);
  }

  public function getCartTotal($ip) {
    return $this->cart->getCartTotal($ip);
  }

  public function removeFromCart($product_id, $ip) {
    return $this->cart->removeFromCart($product_id, $ip);
  }

  public function updateQuantity($product_id, $qty, $ip) {
    return $this->cart->updateQuantity($product_id, $qty, $ip);
  }
}
