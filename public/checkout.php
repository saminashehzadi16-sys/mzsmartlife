<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$_SESSION['cart'] = $_SESSION['cart'] ?? [];
if (isset($_GET['buy'])) {
    $_SESSION['cart'] = [(int)$_GET['buy'] => 1];
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
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrf();
    $db->beginTransaction();
    try {
        $orderNo = 'MZ' . date('YmdHis') . random_int(100, 999);
        $stmt = $db->prepare('INSERT INTO orders (order_no, customer_name, customer_email, customer_phone, shipping_address, subtotal, status, created_at) VALUES (:order_no,:name,:email,:phone,:address,:subtotal,:status,:created_at)');
        $stmt->execute([
            ':order_no' => $orderNo,
            ':name' => trim($_POST['name'] ?? ''),
            ':email' => trim($_POST['email'] ?? ''),
            ':phone' => trim($_POST['phone'] ?? ''),
            ':address' => trim($_POST['address'] ?? ''),
            ':subtotal' => $total,
            ':status' => 'pending',
            ':created_at' => now(),
        ]);
        $orderId = (int)$db->lastInsertId();

        $itemStmt = $db->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (:order_id,:product_id,:quantity,:unit_price)');
        $stockStmt = $db->prepare('UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty');

        foreach ($items as $item) {
            $itemStmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $item['product']['id'],
                ':quantity' => $item['qty'],
                ':unit_price' => $item['product']['price'],
            ]);
            $stockStmt->execute([':qty' => $item['qty'], ':id' => $item['product']['id']]);
        }
        $db->commit();
        $_SESSION['cart'] = [];
        $message = 'Order placed successfully! Order #: ' . $orderNo;
    } catch (Throwable $e) {
        $db->rollBack();
        $message = 'Checkout failed: ' . $e->getMessage();
    }
}
$seo = seoDefaults('Checkout | MZ Smart Life');
include __DIR__ . '/../includes/views/header.php';
?>
<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="font-bold mb-2">Order Summary</h2>
        <?php foreach($items as $item): ?><div class="flex justify-between border-b py-1"><span><?= e($item['product']['name']) ?> × <?= (int)$item['qty'] ?></span><span>$<?= number_format($item['line'],2) ?></span></div><?php endforeach; ?>
        <div class="font-bold mt-2">Total: $<?= number_format($total,2) ?></div>
    </div>
    <form method="post" class="bg-white p-4 rounded-xl shadow space-y-2">
        <?php if($message): ?><div class="bg-slate-100 p-2 rounded text-sm"><?= e($message) ?></div><?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input class="w-full border rounded p-2" name="name" placeholder="Full name" required>
        <input class="w-full border rounded p-2" type="email" name="email" placeholder="Email" required>
        <input class="w-full border rounded p-2" name="phone" placeholder="Phone">
        <textarea class="w-full border rounded p-2" name="address" placeholder="Shipping address" required></textarea>
        <button class="w-full bg-emerald-600 text-white rounded p-2">Place Order</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/views/footer.php'; ?>
