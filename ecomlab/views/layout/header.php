<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page_title ?? 'Shoppn') ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body>
<header>
  <nav>
    <a href="<?= BASE_URL ?>index.php" class="logo">Shoppn</a>

    <form action="<?= BASE_URL ?>index.php" method="GET" class="search">
      <input type="text" name="q" placeholder="Search products...">
      <button type="submit">Search</button>
    </form>

    <div class="links">
      <?php if (is_logged_in()): ?>
        Welcome <?= e($_SESSION['customer_name']) ?> |
        <a href="<?= BASE_URL ?>views/account/my_account.php">My Account</a> |
        <a href="<?= BASE_URL ?>logout.php">Logout</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>views/register.php">Register</a> |
        <a href="<?= BASE_URL ?>views/login.php">Login</a>
      <?php endif; ?>
    </div>
  </nav>
</header>
<main>
