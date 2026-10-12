<?php
require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/CustomerController.php';

$customers = (new CustomerController())->getAllCustomers();

$page_title = 'Customers - Shoppn';
require __DIR__ . '/../layout/header.php';
?>
<h1>Customers</h1>
<p>Admins can manage brands, categories, products and customers. A change takes effect the next time that person logs in.</p>

<table class="wide">
  <tr>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th>Actions</th>
  </tr>
  <?php foreach ($customers as $customer): ?>
    <?php $is_admin_row = (int) $customer['user_role'] === ROLE_ADMIN; ?>
    <tr>
      <td><?= e($customer['customer_name']) ?></td>
      <td><?= e($customer['customer_email']) ?></td>
      <td><?= $is_admin_row ? 'Admin' : 'Customer' ?></td>
      <td>
        <?php if ((int) $customer['customer_id'] === (int) $_SESSION['customer_id']): ?>
          This is you
        <?php else: ?>
          <form action="../../actions/update_role_action.php" method="POST" class="inline-form"
                data-confirm="<?= $is_admin_row ? 'Remove admin rights from' : 'Make an admin of' ?> <?= e($customer['customer_name']) ?>?">
            <?= csrf_field() ?>
            <input type="hidden" name="customer_id" value="<?= e($customer['customer_id']) ?>">
            <input type="hidden" name="user_role" value="<?= $is_admin_row ? ROLE_CUSTOMER : ROLE_ADMIN ?>">
            <button type="submit" class="<?= $is_admin_row ? 'danger' : '' ?>"><?= $is_admin_row ? 'Remove Admin' : 'Make Admin' ?></button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<script src="../../js/confirm.js"></script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
