<?php
require_once __DIR__ . '/../classes/ProductClass.php';

class ProductController {
  private $product;

  public function __construct() {
    $this->product = new ProductClass();
  }

  public function brandNameExists($name, $exclude_id = 0) {
    return $this->product->brandNameExists($name, $exclude_id);
  }

  public function categoryNameExists($name, $exclude_id = 0) {
    return $this->product->categoryNameExists($name, $exclude_id);
  }

  public function addBrand($name, $image = null) {
    return $this->product->addBrand($name, $image);
  }

  public function getAllBrands() {
    return $this->product->getAllBrands();
  }

  public function getBrandById($id) {
    return $this->product->getBrandById($id);
  }

  public function updateBrand($id, $name, $image = null) {
    return $this->product->updateBrand($id, $name, $image);
  }

  public function deleteBrand($id) {
    return $this->product->deleteBrand($id);
  }

  public function deleteCategory($id) {
    return $this->product->deleteCategory($id);
  }

  public function deleteProduct($id) {
    return $this->product->deleteProduct($id);
  }

  public function addCategory($name) {
    return $this->product->addCategory($name);
  }

  public function getAllCategories() {
    return $this->product->getAllCategories();
  }

  public function getCategoryById($id) {
    return $this->product->getCategoryById($id);
  }

  public function updateCategory($id, $name) {
    return $this->product->updateCategory($id, $name);
  }

  // $image_filename is just the file's name; the Action has already moved the file.
  public function addProduct($cat, $brand, $title, $price, $desc, $image_filename, $keywords) {
    return $this->product->addProduct($cat, $brand, $title, $price, $desc, $image_filename, $keywords);
  }

  public function updateProduct($id, $cat, $brand, $title, $price, $desc, $image_filename, $keywords) {
    return $this->product->updateProduct($id, $cat, $brand, $title, $price, $desc, $image_filename, $keywords);
  }

  public function getProductById($id) {
    return $this->product->getProductById($id);
  }

  public function getAllProducts() {
    return $this->product->getAllProducts();
  }

  public function getFeaturedProducts($limit = 6) {
    return $this->product->getFeaturedProducts($limit);
  }

  public function getProductsByCategory($cat_id) {
    return $this->product->getProductsByCategory($cat_id);
  }

  public function getProductsByBrand($brand_id) {
    return $this->product->getProductsByBrand($brand_id);
  }

  public function searchProducts($query) {
    return $this->product->searchProducts($query);
  }

  // Checks the product form fields shared by add and update.
  // Returns [cleaned data, error message ('' if everything is fine)].
  public function validateProduct($post) {
    $int = ['options' => ['min_range' => 1]];
    $data = [
      'cat'      => filter_var($post['product_cat'] ?? '', FILTER_VALIDATE_INT, $int),
      'brand'    => filter_var($post['product_brand'] ?? '', FILTER_VALIDATE_INT, $int),
      'title'    => trim(strip_tags($post['product_title'] ?? '')),
      'price'    => filter_var($post['product_price'] ?? '', FILTER_VALIDATE_FLOAT),
      'desc'     => trim(strip_tags($post['product_desc'] ?? '')),
      'keywords' => trim(strip_tags($post['product_keywords'] ?? '')),
    ];

    $error = '';
    if ($data['title'] === '' || strlen($data['title']) > 200) {
      $error = 'Please enter a product title (200 characters or fewer).';
    } elseif ($data['price'] === false || $data['price'] <= 0) {
      $error = 'Please enter a price greater than 0.';
    } elseif (!$data['cat'] || !$this->product->getCategoryById($data['cat'])) {
      $error = 'Please choose a category.';
    } elseif (!$data['brand'] || !$this->product->getBrandById($data['brand'])) {
      $error = 'Please choose a brand.';
    } elseif (strlen($data['desc']) > 500) {
      $error = 'The description must be 500 characters or fewer.';
    } elseif (strlen($data['keywords']) > 100) {
      $error = 'The keywords must be 100 characters or fewer.';
    }
    return [$data, $error];
  }
}
