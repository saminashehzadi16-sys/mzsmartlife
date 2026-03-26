<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$cacheKey = 'shop';
if ($cached = cacheGet($cacheKey, 120)) { echo $cached; exit; }
$products = $db->query('SELECT p.*, (SELECT image_path FROM product_images pi WHERE pi.product_id=p.id ORDER BY sort_order ASC LIMIT 1) img FROM products p ORDER BY created_at DESC LIMIT 50')->fetchAll();
$seo = seoDefaults('Shop | MZ Smart Life');
ob_start(); include __DIR__ . '/../includes/views/header.php';
?>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
<?php foreach($products as $p): ?>
<div class="tilt-card glass rounded-xl p-4 border border-white/30">
    <?php if($p['img']): ?><img loading="lazy" src="<?= e(url($p['img'])) ?>" class="h-40 w-full object-cover rounded mb-2" alt="<?= e($p['name']) ?>"><?php endif; ?>
    <h3 class="font-bold"><?= e($p['name']) ?></h3>
    <div class="text-lg">$<?= number_format((float)$p['price'],2) ?></div>
    <div class="mt-3 flex gap-2">
        <button data-quick-view="qv-<?= (int)$p['id'] ?>" class="px-3 py-1 rounded bg-slate-200 text-sm">Quick View</button>
        <form method="post" action="<?= e(url('cart')) ?>" class="inline">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <button class="px-3 py-1 rounded bg-slate-900 text-white text-sm">Add to Cart</button>
        </form>
        <a href="<?= e(url('checkout?buy='.(int)$p['id'])) ?>" class="px-3 py-1 rounded bg-emerald-600 text-white text-sm">Buy Now</a>
    </div>
</div>
<div id="qv-<?= (int)$p['id'] ?>" class="modal hidden fixed inset-0 bg-black/40 p-4">
    <div class="bg-white max-w-md mx-auto mt-10 rounded-xl p-4">
        <h4 class="font-bold mb-2"><?= e($p['name']) ?></h4>
        <p class="text-sm mb-2"><?= e($p['description'] ?? '') ?></p>
        <button data-close-modal class="bg-slate-900 text-white px-4 py-1 rounded">Close</button>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/views/footer.php'; $html=ob_get_clean(); cacheSet($cacheKey,$html); echo $html; ?>
