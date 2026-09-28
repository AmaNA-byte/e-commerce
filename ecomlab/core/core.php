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

function get_ip() {
  return $_SERVER['REMOTE_ADDR'];
}

function redirect($url) {
  header('Location: ' . $url);
  exit;
}

// Safely print user data in HTML.
function e($text) {
  return htmlspecialchars($text ?? '');
}

function is_logged_in() {
  return isset($_SESSION['customer_id']);
}

function is_admin() {
  return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 1;
}

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
