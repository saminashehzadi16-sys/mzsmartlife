<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$slug = $_GET['slug'] ?? '';
$stmt = $db->prepare('SELECT * FROM products WHERE slug=:slug LIMIT 1');
$stmt->execute([':slug' => $slug]);
$product = $stmt->fetch();
if (!$product) { http_response_code(404); exit('Product not found'); }
$imgStmt = $db->prepare('SELECT image_path FROM product_images WHERE product_id=:product ORDER BY sort_order ASC');
$imgStmt->execute([':product' => $product['id']]);
$images = $imgStmt->fetchAll();
$seo = [
    'title' => $product['meta_title'] ?: $product['name'],
    'description' => $product['meta_description'] ?: substr(strip_tags((string)$product['description']), 0, 150),
    'keywords' => $product['meta_keywords'] ?: 'shop, ecommerce, products',
];
include __DIR__ . '/../includes/views/header.php';
?>
<div class="grid md:grid-cols-2 gap-6 bg-white p-4 rounded-xl shadow">
    <div>
        <?php foreach ($images as $img): ?><img loading="lazy" src="<?= e(url($img['image_path'])) ?>" class="w-full rounded mb-2" alt="<?= e($product['name']) ?>"><?php endforeach; ?>
    </div>
    <div>
        <h1 class="text-2xl font-bold"><?= e($product['name']) ?></h1>
        <div class="text-xl my-2">$<?= number_format((float)$product['price'], 2) ?></div>
        <p><?= e($product['description'] ?? '') ?></p>
        <form method="post" action="<?= e(url('cart')) ?>" class="mt-3">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
            <button class="bg-slate-900 text-white px-4 py-2 rounded">Add to Cart</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/views/footer.php'; ?>
