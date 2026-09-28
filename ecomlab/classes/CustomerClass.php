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

  // Returns the customer row if the password is correct, otherwise false.
  public function login($email, $pass) {
    $row = $this->getCustomerByEmail($email);
    if ($row && password_verify($pass, $row['customer_pass'])) {
      return $row;
    }
    return false;
  }
}
