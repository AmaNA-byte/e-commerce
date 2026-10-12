<?php
require_once __DIR__ . '/../core/db_class.php';

// Cart rows are tied to the visitor's IP address, so guests can shop without an account.
class CartClass extends Database {

  public function isInCart($product_id, $ip) {
    $stmt = $this->conn->prepare('SELECT p_id FROM cart WHERE p_id = ? AND ip_add = ?');
    $stmt->bind_param('is', $product_id, $ip);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
  }

  public function addToCart($product_id, $qty, $ip, $customer_id = null) {
    $stmt = $this->conn->prepare('INSERT INTO cart (p_id, ip_add, c_id, qty) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isii', $product_id, $ip, $customer_id, $qty);
    return $stmt->execute();
  }

  public function getCartItems($ip) {
    $stmt = $this->conn->prepare(
      'SELECT cart.*, products.product_title, products.product_price, products.product_image
       FROM cart JOIN products ON cart.p_id = products.product_id
       WHERE cart.ip_add = ?'
    );
    $stmt->bind_param('s', $ip);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getCartCount($ip) {
    $stmt = $this->conn->prepare('SELECT COUNT(*) FROM cart WHERE ip_add = ?');
    $stmt->bind_param('s', $ip);
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_row()[0];
  }

  public function getCartTotal($ip) {
    $total = 0.0;
    foreach ($this->getCartItems($ip) as $item) {
      $total += $item['qty'] * $item['product_price'];
    }
    return $total;
  }

  public function removeFromCart($product_id, $ip) {
    $stmt = $this->conn->prepare('DELETE FROM cart WHERE p_id = ? AND ip_add = ?');
    $stmt->bind_param('is', $product_id, $ip);
    return $stmt->execute();
  }

  public function updateQuantity($product_id, $qty, $ip) {
    $stmt = $this->conn->prepare('UPDATE cart SET qty = ? WHERE p_id = ? AND ip_add = ?');
    $stmt->bind_param('iis', $qty, $product_id, $ip);
    return $stmt->execute();
  }
}
