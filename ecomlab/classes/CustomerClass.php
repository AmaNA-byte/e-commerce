<?php
require_once __DIR__ . '/../core/db_class.php';

class CustomerClass extends Database {

  public function emailExists($email) {
    $stmt = $this->conn->prepare('SELECT customer_email FROM customer WHERE customer_email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
  }

  // Returns the new customer's id, or false on failure.
  public function addCustomer($name, $email, $pass, $country, $city, $contact, $image = null) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $this->conn->prepare(
      'INSERT INTO customer (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, customer_image)
       VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('sssssss', $name, $email, $hash, $country, $city, $contact, $image);
    return $stmt->execute() ? $this->conn->insert_id : false;
  }

  // Returns one customer row, or false if not found.
  public function getCustomerByEmail($email) {
    $stmt = $this->conn->prepare('SELECT * FROM customer WHERE customer_email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: false;
  }

  public function updatePassword($id, $pass) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $this->conn->prepare('UPDATE customer SET customer_pass = ? WHERE customer_id = ?');
    $stmt->bind_param('si', $hash, $id);
    return $stmt->execute();
  }

  public function getAllCustomers() {
    return $this->conn->query(
      'SELECT customer_id, customer_name, customer_email, user_role FROM customer ORDER BY customer_name ASC'
    )->fetch_all(MYSQLI_ASSOC);
  }

  // Returns one customer row, or false if not found.
  public function getCustomerById($id) {
    $stmt = $this->conn->prepare('SELECT * FROM customer WHERE customer_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: false;
  }

  // $role: 1 = admin, 2 = customer
  public function updateRole($id, $role) {
    $stmt = $this->conn->prepare('UPDATE customer SET user_role = ? WHERE customer_id = ?');
    $stmt->bind_param('ii', $role, $id);
    return $stmt->execute();
  }

  // Returns the customer row if the password is correct, otherwise false.
  public function login($email, $pass) {
    $row = $this->getCustomerByEmail($email);
    if ($row && password_verify($pass, $row['customer_pass'])) {
      return $row;
    }
    return false;
  }
}
