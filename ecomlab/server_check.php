<?php
// Server check: open this page in the browser (e.g. .../ecomlab/server_check.php) to see whether
// the server has everything Shoppn needs. It fixes what it safely can and explains the rest.
// It works on its own (does not load core.php), so it still opens when other pages are broken.
// Delete this file once everything shows OK.

$results = [];
function report($ok, $label, $help = '') {
  global $results;
  $results[] = [$ok, $label, $help];
}

// ---- PHP
report(version_compare(PHP_VERSION, '7.1', '>='), 'PHP version is ' . PHP_VERSION . ' (needs 7.1 or newer)',
  'Ask the server administrator for a newer PHP version.');
report(extension_loaded('mysqli'), 'mysqli extension is available', 'Ask the server administrator to enable mysqli.');
report(method_exists('mysqli_stmt', 'get_result'), 'mysqlnd driver is available',
  'The code uses get_result() and fetch_all(), which need the mysqlnd driver. Ask the server administrator to enable it.');
report((bool) ini_get('file_uploads'), 'File uploads are switched on (largest file: ' . ini_get('upload_max_filesize') . ')',
  'Ask the server administrator to enable file_uploads.');

// ---- Files
$needed = [
  'core/core.php', 'core/db_class.php', 'core/db_cred.php', 'css/style.css',
  'classes/CustomerClass.php', 'classes/ProductClass.php', 'classes/CartClass.php',
  'controllers/CustomerController.php', 'controllers/ProductController.php', 'controllers/CartController.php',
  'views/home.php', 'views/cart.php', 'views/single_product.php', 'views/search_results.php',
  'views/layout/header.php', 'views/layout/footer.php', 'views/layout/sidebar.php', 'views/layout/product_card.php',
  'views/admin/brand.php', 'views/admin/category.php', 'views/admin/product.php', 'views/admin/customers.php',
  'views/account/my_account.php', 'views/account/change_password.php',
  'actions/add_brand_action.php', 'actions/update_brand_action.php', 'actions/delete_brand_action.php',
  'actions/add_category_action.php', 'actions/update_category_action.php', 'actions/delete_category_action.php',
  'actions/add_product_action.php', 'actions/update_product_action.php', 'actions/delete_product_action.php',
  'actions/add_to_cart_action.php', 'actions/update_cart_action.php', 'actions/remove_from_cart_action.php',
  'actions/update_role_action.php', 'actions/change_password_action.php',
  'js/validate.js', 'js/password.js', 'js/product.js', 'js/confirm.js',
];
$missing = [];
foreach ($needed as $file) {
  if (!is_file(__DIR__ . '/' . $file)) {
    $missing[] = $file;
  }
}
report(!$missing, 'All project files are uploaded',
  'Upload these missing files (file names must match exactly, including capital letters): ' . implode(', ', $missing));

// ---- Picture folders
foreach (['uploads', 'images/products'] as $folder) {
  $path = __DIR__ . '/' . $folder;
  if (!is_dir($path)) {
    @mkdir($path, 0777, true);
  }
  if (is_dir($path) && !is_writable($path)) {
    @chmod($path, 0777);
  }
  report(is_dir($path) && is_writable($path), 'Folder "' . $folder . '" exists and pictures can be saved in it',
    'Create the folder "' . $folder . '" inside the project and set its permission to 777 (in your FTP program or file manager).');
}

// ---- Sessions
@session_start();
report(session_status() === PHP_SESSION_ACTIVE, 'Sessions work (needed for login)', 'Ask the server administrator to check PHP sessions.');

// ---- Database
$conn = null;
if (extension_loaded('mysqli') && is_file(__DIR__ . '/core/db_cred.php')) {
  require_once __DIR__ . '/core/db_cred.php';
  mysqli_report(MYSQLI_REPORT_OFF);
  $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  $connected = !$conn->connect_error;
  report($connected, 'Connected to the database "' . DB_NAME . '"',
    'The database refused the connection (' . ($conn->connect_error ?? '') . '). Check the username, password and database name in core/db_cred.php.');
  if (!$connected) {
    $conn = null;
  }
}

function has_column($conn, $table, $column) {
  $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
  return $result && $result->num_rows > 0;
}

if ($conn) {
  $columns = [
    'customer'   => ['customer_id', 'customer_name', 'customer_email', 'customer_pass', 'customer_country', 'customer_city', 'customer_contact', 'customer_image', 'user_role'],
    'brands'     => ['brand_id', 'brand_name'],
    'categories' => ['cat_id', 'cat_name'],
    'products'   => ['product_id', 'product_cat', 'product_brand', 'product_title', 'product_price', 'product_desc', 'product_image', 'product_keywords'],
    'cart'       => ['p_id', 'ip_add', 'c_id', 'qty'],
  ];
  foreach ($columns as $table => $names) {
    $exists = $conn->query("SHOW TABLES LIKE '$table'");
    if (!$exists || $exists->num_rows === 0) {
      report(false, 'Table "' . $table . '" exists', 'Import the course database file (the .sql file) into ' . DB_NAME . ' using phpMyAdmin.');
      continue;
    }
    $absent = [];
    foreach ($names as $name) {
      if (!has_column($conn, $table, $name)) {
        $absent[] = $name;
      }
    }
    report(!$absent, 'Table "' . $table . '" has the columns the code uses',
      'Missing column(s): ' . implode(', ', $absent) . '. Re-import the course database file, or add them in phpMyAdmin.');
  }

  // Brand pictures use an extra column: add it here if this database does not have it yet.
  if (has_column($conn, 'brands', 'brand_id')) {
    if (!has_column($conn, 'brands', 'brand_image')) {
      $conn->query('ALTER TABLE brands ADD COLUMN brand_image VARCHAR(100) NULL');
    }
    report(has_column($conn, 'brands', 'brand_image'), 'Table "brands" has the brand_image column for brand pictures',
      'Run this in phpMyAdmin (SQL tab): ALTER TABLE brands ADD COLUMN brand_image VARCHAR(100) NULL;');
  }

  // At least one admin is needed to reach the Brand, Category, Product and Customers pages.
  if (has_column($conn, 'customer', 'user_role')) {
    $admins = (int) $conn->query('SELECT COUNT(*) FROM customer WHERE user_role = 1')->fetch_row()[0];
    report($admins > 0, 'There is at least one admin account (' . $admins . ' found)',
      "Register on the site, then run this in phpMyAdmin (SQL tab) with your own email: UPDATE customer SET user_role = 1 WHERE customer_email = 'your@email.com'; Then log out and log in again.");
  }
}

$problems = count(array_filter($results, function ($r) { return !$r[0]; }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Shoppn server check</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 760px; margin: 30px auto; padding: 0 16px; color: #1f2937; }
    li { margin-bottom: 12px; list-style: none; padding: 10px 14px; border-radius: 4px; }
    .ok { background: #dcfce7; }
    .bad { background: #fee2e2; }
    .help { display: block; margin-top: 4px; font-size: 14px; }
    ul { padding: 0; }
  </style>
</head>
<body>
  <h1>Shoppn server check</h1>
  <p><strong><?= $problems === 0 ? 'Everything is OK. You can delete server_check.php now.' : $problems . ' problem(s) found. Fix the red items, then reload this page.' ?></strong></p>
  <ul>
    <?php foreach ($results as [$ok, $label, $help]): ?>
      <li class="<?= $ok ? 'ok' : 'bad' ?>">
        <?= $ok ? 'OK' : 'PROBLEM' ?> &mdash; <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        <?php if (!$ok): ?><span class="help"><strong>How to fix:</strong> <?= htmlspecialchars($help, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</body>
</html>
