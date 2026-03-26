<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$_SESSION['cart'] = $_SESSION['cart'] ?? [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrf();
    $productId = (int)($_POST['product_id'] ?? 0);
    if ($productId > 0) {
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
    }
    header('Location: ' . url('cart'));
    exit;
}
$cart = $_SESSION['cart'];
$items = [];
$total = 0.0;
if ($cart) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $products = $db->query("SELECT id, name, price, stock FROM products WHERE id IN ($ids)")->fetchAll();
    foreach ($products as $p) {
        $qty = $cart[$p['id']] ?? 0;
        $line = $qty * (float)$p['price'];
        $total += $line;
        $items[] = ['product' => $p, 'qty' => $qty, 'line' => $line];
    }
}
$seo = seoDefaults('Cart | MZ Smart Life');
include __DIR__ . '/../includes/views/header.php';
?>
<div class="bg-white rounded-xl shadow p-4">
    <h1 class="text-2xl font-bold mb-3">Your Cart</h1>
    <?php foreach ($items as $item): ?>
        <div class="flex justify-between border-b py-2">
            <div><?= e($item['product']['name']) ?> × <?= (int)$item['qty'] ?></div>
            <div>$<?= number_format($item['line'], 2) ?></div>
        </div>
    <?php endforeach; ?>
    <div class="font-bold mt-3">Total: $<?= number_format($total,2) ?></div>
    <a href="<?= e(url('checkout')) ?>" class="inline-block mt-3 bg-emerald-600 text-white px-4 py-2 rounded">Proceed to Checkout</a>
</div>
<?php include __DIR__ . '/../includes/views/footer.php'; ?>
