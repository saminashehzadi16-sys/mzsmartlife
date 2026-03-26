<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireLogin();
requirePermission($db, 'store.orders');
$orders = $db->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 100')->fetchAll();
$seo = seoDefaults('Manage Orders');
include __DIR__ . '/../../includes/views/header.php';
?>
<h1 class="text-xl font-bold mb-3">Orders</h1>
<?php foreach($orders as $o): ?><div class="bg-white p-3 rounded shadow mb-2"><strong><?= e($o['order_no']) ?></strong> - <?= e($o['customer_name']) ?> - $<?= number_format((float)$o['subtotal'],2) ?> <span class="text-xs"><?= e($o['status']) ?></span></div><?php endforeach; ?>
<?php include __DIR__ . '/../../includes/views/footer.php'; ?>
