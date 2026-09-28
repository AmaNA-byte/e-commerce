<?php
require_once __DIR__ . '/../core/db_class.php';

class ProductClass extends Database {

  public function getCategories() {
    return $this->conn->query('SELECT cat_id, cat_name FROM categories ORDER BY cat_name')->fetch_all(MYSQLI_ASSOC);
  }

  public function getBrands() {
    return $this->conn->query('SELECT brand_id, brand_name FROM brands ORDER BY brand_name')->fetch_all(MYSQLI_ASSOC);
  }
}
