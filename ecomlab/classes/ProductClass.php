<?php
require_once __DIR__ . '/../core/db_class.php';

class ProductClass extends Database {

  // ---- Brands ----

  // Brand pictures need a brand_image column that the original course database does not have.
  // Adds it the first time a brand is saved on a database that is missing it (e.g. the school server).
  private function ensureBrandImageColumn() {
    $result = $this->conn->query("SHOW COLUMNS FROM brands LIKE 'brand_image'");
    if ($result && $result->num_rows === 0) {
      $this->conn->query('ALTER TABLE brands ADD COLUMN brand_image VARCHAR(100) NULL');
    }
  }

  // True if another brand already has this name (upper/lower case is ignored).
  // Pass $exclude_id when editing, so a brand does not clash with itself.
  public function brandNameExists($name, $exclude_id = 0) {
    $stmt = $this->conn->prepare('SELECT brand_id FROM brands WHERE LOWER(brand_name) = LOWER(?) AND brand_id != ?');
    $stmt->bind_param('si', $name, $exclude_id);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
  }

  // Returns true on success, false on failure.
  public function addBrand($name, $image = null) {
    $this->ensureBrandImageColumn();
    $stmt = $this->conn->prepare('INSERT INTO brands (brand_name, brand_image) VALUES (?, ?)');
    $stmt->bind_param('ss', $name, $image);
    return $stmt->execute();
  }

  public function getAllBrands() {
    return $this->conn->query('SELECT * FROM brands ORDER BY brand_name ASC')->fetch_all(MYSQLI_ASSOC);
  }

  // Returns one brand row, or false if not found.
  public function getBrandById($id) {
    $stmt = $this->conn->prepare('SELECT * FROM brands WHERE brand_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: false;
  }

  // Leave $image as null to keep the picture the brand already has.
  public function updateBrand($id, $name, $image = null) {
    $this->ensureBrandImageColumn();
    $stmt = $this->conn->prepare('UPDATE brands SET brand_name = ?, brand_image = COALESCE(?, brand_image) WHERE brand_id = ?');
    $stmt->bind_param('ssi', $name, $image, $id);
    return $stmt->execute();
  }

  // Fails (returns false) if a product still uses the brand.
  public function deleteBrand($id) {
    $stmt = $this->conn->prepare('DELETE FROM brands WHERE brand_id = ?');
    $stmt->bind_param('i', $id);
    return $stmt->execute();
  }

  // ---- Categories ----

  // True if another category already has this name (upper/lower case is ignored).
  public function categoryNameExists($name, $exclude_id = 0) {
    $stmt = $this->conn->prepare('SELECT cat_id FROM categories WHERE LOWER(cat_name) = LOWER(?) AND cat_id != ?');
    $stmt->bind_param('si', $name, $exclude_id);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
  }

  public function addCategory($name) {
    $stmt = $this->conn->prepare('INSERT INTO categories (cat_name) VALUES (?)');
    $stmt->bind_param('s', $name);
    return $stmt->execute();
  }

  public function getAllCategories() {
    return $this->conn->query('SELECT * FROM categories ORDER BY cat_name ASC')->fetch_all(MYSQLI_ASSOC);
  }

  // Returns one category row, or false if not found.
  public function getCategoryById($id) {
    $stmt = $this->conn->prepare('SELECT * FROM categories WHERE cat_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: false;
  }

  public function updateCategory($id, $name) {
    $stmt = $this->conn->prepare('UPDATE categories SET cat_name = ? WHERE cat_id = ?');
    $stmt->bind_param('si', $name, $id);
    return $stmt->execute();
  }

  // Fails (returns false) if a product still uses the category.
  public function deleteCategory($id) {
    $stmt = $this->conn->prepare('DELETE FROM categories WHERE cat_id = ?');
    $stmt->bind_param('i', $id);
    return $stmt->execute();
  }

  // ---- Products ----

  public function addProduct($cat, $brand, $title, $price, $desc, $image_filename, $keywords) {
    $stmt = $this->conn->prepare(
      'INSERT INTO products (product_cat, product_brand, product_title, product_price, product_desc, product_image, product_keywords)
       VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('iisdsss', $cat, $brand, $title, $price, $desc, $image_filename, $keywords);
    return $stmt->execute();
  }

  public function updateProduct($id, $cat, $brand, $title, $price, $desc, $image_filename, $keywords) {
    $stmt = $this->conn->prepare(
      'UPDATE products
       SET product_cat = ?, product_brand = ?, product_title = ?, product_price = ?, product_desc = ?, product_image = ?, product_keywords = ?
       WHERE product_id = ?'
    );
    $stmt->bind_param('iisdsssi', $cat, $brand, $title, $price, $desc, $image_filename, $keywords, $id);
    return $stmt->execute();
  }

  // Fails (returns false) if the product is part of an order. Cart rows for it are removed automatically.
  public function deleteProduct($id) {
    $stmt = $this->conn->prepare('DELETE FROM products WHERE product_id = ?');
    $stmt->bind_param('i', $id);
    return $stmt->execute();
  }

  // Returns one product row with its category and brand names, or false if not found.
  public function getProductById($id) {
    $stmt = $this->conn->prepare(
      'SELECT p.*, c.cat_name, b.brand_name
       FROM products p
       JOIN categories c ON c.cat_id = p.product_cat
       JOIN brands b ON b.brand_id = p.product_brand
       WHERE p.product_id = ?'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: false;
  }

  // The four methods below feed the customer pages. Each returns an array of rows (empty if none).

  public function getFeaturedProducts($limit = 6) {
    $stmt = $this->conn->prepare('SELECT * FROM products ORDER BY RAND() LIMIT ?');
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getProductsByCategory($cat_id) {
    $stmt = $this->conn->prepare('SELECT * FROM products WHERE product_cat = ?');
    $stmt->bind_param('i', $cat_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getProductsByBrand($brand_id) {
    $stmt = $this->conn->prepare('SELECT * FROM products WHERE product_brand = ?');
    $stmt->bind_param('i', $brand_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function searchProducts($query) {
    // Escape % and _ so they are searched for as ordinary characters.
    $like = '%' . addcslashes($query, '%_\\') . '%';
    $stmt = $this->conn->prepare('SELECT * FROM products WHERE product_title LIKE ? OR product_keywords LIKE ?');
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getAllProducts() {
    return $this->conn->query(
      'SELECT p.*, c.cat_name, b.brand_name
       FROM products p
       JOIN categories c ON c.cat_id = p.product_cat
       JOIN brands b ON b.brand_id = p.product_brand
       ORDER BY p.product_title ASC'
    )->fetch_all(MYSQLI_ASSOC);
  }
}
