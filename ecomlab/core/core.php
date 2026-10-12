<?php
// Included at the top of every page.
session_start();
date_default_timezone_set('Africa/Accra');
require_once __DIR__ . '/db_class.php';

// Site root, used to build links that work from any folder.
// Worked out automatically, so it works on any server (e.g. /ecomlab/ or /~name/shoppn/).
$project_dir = realpath(__DIR__ . '/..');
$page_file = realpath($_SERVER['SCRIPT_FILENAME']);
$page_path = substr($page_file, strlen($project_dir)); // e.g. /views/login.php
define('BASE_URL', substr($_SERVER['SCRIPT_NAME'], 0, -strlen($page_path)) . '/');

// Protection against forged requests (CSRF): every form carries a secret token that only our own
// pages know, and every POST must send it back. A form on another website cannot know the token.
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
    && !hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
  // When an upload is bigger than the server allows, PHP discards the whole form, token included.
  $too_big = empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;
  $_SESSION['error'] = $too_big ? 'That file is too large for the server. Please choose a smaller one.'
                                : 'Your form expired. Please try again.';
  redirect(previous_page());
}

// Hidden field holding the token; every POST form in the views prints it with csrf_field().
function csrf_field() {
  return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

// The page the visitor came from (only if it is a page on this site), otherwise the home page.
function previous_page() {
  $referer = $_SERVER['HTTP_REFERER'] ?? '';
  $this_site = parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
  if ($referer !== '' && parse_url($referer, PHP_URL_HOST) === $this_site) {
    return $referer;
  }
  return BASE_URL . 'index.php';
}

// Anyone who logged in with a weak (old) password must change it before using the site.
if (!empty($_SESSION['must_change_password'])
    && !in_array($page_path, ['/views/account/change_password.php', '/actions/change_password_action.php', '/logout.php'])) {
  redirect(BASE_URL . 'views/account/change_password.php');
}

// Strong password: 8+ characters and at least 3 of: lowercase, uppercase, number, symbol.
const PASSWORD_RULE = 'Password must be at least 8 characters and include at least 3 of: lowercase letter, uppercase letter, number, symbol.';

function password_is_strong($pass) {
  $types = preg_match('/[a-z]/', $pass) + preg_match('/[A-Z]/', $pass)
         + preg_match('/[0-9]/', $pass) + preg_match('/[^a-zA-Z0-9]/', $pass);
  return strlen($pass) >= 8 && $types >= 3;
}

// Makes sure a picture folder exists and can be written to (a fresh server copy may be missing it).
function picture_folder_ready($folder) {
  if (!is_dir($folder)) {
    @mkdir($folder, 0777, true);
  }
  return is_dir($folder) && is_writable($folder);
}

// Pictures: any image format is accepted. The list is of file endings we are willing to save,
// so a file can never be stored with an ending the server would run (such as .php).
// (SVG is left out on purpose: an SVG file can contain scripts.)
const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'avif', 'bmp', 'tif', 'tiff', 'heic', 'heif', 'ico'];
const IMAGE_MAX_BYTES = 8 * 1024 * 1024; // 8 MB (also checked in js/product.js)

// True if the uploaded file's contents really are an image, whatever its name says.
function is_image_file($path, $ext) {
  if (@getimagesize($path)) {
    return true;
  }
  // Newer formats (HEIC, AVIF) are not known to getimagesize() on every server.
  $type = function_exists('mime_content_type') ? (string) @mime_content_type($path) : '';
  if (strpos($type, 'image/') === 0 && $type !== 'image/svg+xml') {
    return true;
  }
  return in_array($ext, ['heic', 'heif', 'avif']) && in_array($type, ['', 'application/octet-stream']);
}

// Checks one uploaded picture ($file is an entry of $_FILES) and moves it into $folder (e.g. 'uploads').
// Returns ['filename' => ...] on success, or ['error' => a message saying what actually went wrong].
function save_picture($file, $folder, $keep_name = false) {
  if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
    return ['error' => 'The picture is too large for this server (its limit is ' . ini_get('upload_max_filesize') . '). Please choose a smaller one.'];
  }
  if ($file['size'] > IMAGE_MAX_BYTES) {
    return ['error' => 'The picture must be 8 MB or smaller.'];
  }
  if ($file['error'] !== UPLOAD_ERR_OK) {
    return ['error' => 'The server could not receive the picture (upload error ' . $file['error'] . '). Please try again.'];
  }

  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, IMAGE_EXTENSIONS) || !is_image_file($file['tmp_name'], $ext)) {
    return ['error' => 'That file is not a picture. Please choose an image file.'];
  }
  if (!picture_folder_ready(__DIR__ . '/../' . $folder)) {
    return ['error' => 'The server cannot save pictures yet: the "' . $folder . '" folder is missing or not writable.'];
  }

  // Unique name; optionally keep the safe characters of the original name.
  $name = $keep_name ? '_' . substr(preg_replace('/[^A-Za-z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME)), 0, 50) : '';
  $filename = uniqid() . $name . '.' . $ext;
  if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $folder . '/' . $filename)) {
    return ['error' => 'The picture could not be saved. Please try again.'];
  }
  return ['filename' => $filename];
}

// Brand and profile pictures go into /uploads.
// Returns null if no file was chosen, otherwise ['path' => 'uploads/abc.jpg'] or ['error' => message].
function upload_image($field) {
  if (empty($_FILES[$field]['name'])) {
    return null;
  }
  $result = save_picture($_FILES[$field], 'uploads');
  return isset($result['error']) ? $result : ['path' => 'uploads/' . $result['filename']];
}

// Product pictures go into /images/products.
// Returns ['filename' => ...] or ['error' => message].
function save_product_image($file) {
  return save_picture($file, 'images/products', true);
}

function get_ip() {
  return $_SERVER['REMOTE_ADDR'];
}

function redirect($url) {
  header('Location: ' . $url);
  exit;
}

// Safely print user data in HTML.
function e($text) {
  return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
  return isset($_SESSION['customer_id']);
}

function is_admin() {
  return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 1;
}

// user_role in the customer table: 1 = admin, 2 = customer
const ROLE_ADMIN = 1;
const ROLE_CUSTOMER = 2;

function require_login() {
  if (!is_logged_in()) {
    redirect(BASE_URL . 'views/login.php');
  }
}

function require_admin() {
  if (!is_admin()) {
    $_SESSION['error'] = 'You must be an admin to view that page.';
    redirect(BASE_URL . 'index.php');
  }
}
