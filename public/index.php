<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$cacheKey = 'home';
if ($cached = cacheGet($cacheKey, 180)) {
    echo $cached;
    exit;
}

$breaking = $db->query("SELECT title, slug FROM articles WHERE is_breaking = 1 ORDER BY published_at DESC LIMIT 8")->fetchAll();
$featuredNews = $db->query("SELECT a.title, a.slug, a.featured_image, c.name category FROM articles a JOIN categories c ON c.id=a.category_id ORDER BY a.is_featured DESC, a.published_at DESC LIMIT 6")->fetchAll();
$featuredProducts = $db->query("SELECT p.id, p.name, p.slug, p.price, (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY sort_order ASC LIMIT 1) img FROM products p ORDER BY p.is_featured DESC, p.created_at DESC LIMIT 6")->fetchAll();

$seo = seoDefaults('MZ Smart Life | News + Store');
ob_start();
include __DIR__ . '/../includes/views/header.php';
?>
<section class="mb-6">
    <div class="glass rounded-xl p-4 bg-gradient-to-r from-slate-900 to-slate-700 text-white">
        <h1 class="text-2xl font-bold mb-2">Smart Living Starts Here</h1>
        <div class="text-sm">Latest headlines + curated products in one hybrid platform.</div>
    </div>
</section>

<section class="mb-6 bg-white rounded-xl p-4 shadow">
    <h2 class="font-bold mb-2">🔥 Breaking News</h2>
    <ul data-ticker class="text-sm min-h-[1.5rem]">
        <?php foreach ($breaking as $item): ?>
            <li><a class="text-red-600" href="<?= e(url('news/' . $item['slug'])) ?>"><?= e($item['title']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</section>

<div class="grid lg:grid-cols-3 gap-6">
    <section class="lg:col-span-2">
        <div class="bg-white rounded-xl p-4 shadow mb-4">
            <h2 class="font-bold mb-3">Featured Articles</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <?php foreach ($featuredNews as $article): ?>
                    <article class="border rounded-lg overflow-hidden">
                        <?php if (!empty($article['featured_image'])): ?>
                            <img loading="lazy" src="<?= e(url($article['featured_image'])) ?>" class="w-full h-40 object-cover" alt="<?= e($article['title']) ?>">
                        <?php endif; ?>
                        <div class="p-3">
                            <div class="text-xs text-slate-500"><?= e($article['category']) ?></div>
                            <a href="<?= e(url('news/' . $article['slug'])) ?>" class="font-semibold"><?= e($article['title']) ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow">
            <h2 class="font-bold mb-3">Product of the Day</h2>
            <div class="grid md:grid-cols-3 gap-4">
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="tilt-card bg-slate-900 text-white rounded-xl p-3 transition-transform duration-150">
                        <?php if (!empty($product['img'])): ?><img loading="lazy" src="<?= e(url($product['img'])) ?>" class="rounded mb-2 h-28 w-full object-cover" alt="<?= e($product['name']) ?>"><?php endif; ?>
                        <div class="font-semibold"><?= e($product['name']) ?></div>
                        <div>$<?= number_format((float)$product['price'], 2) ?></div>
                        <a href="<?= e(url('product/' . $product['slug'])) ?>" class="mt-2 inline-block text-xs bg-white text-slate-900 px-3 py-1 rounded">View</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <aside class="space-y-4">
        <div class="bg-white rounded-xl p-4 shadow text-center">
            <div class="font-bold">Ad Placeholder</div>
            <div class="text-xs text-slate-500">Top Leaderboard (728x90)</div>
            <div class="h-24 bg-slate-100 mt-2 rounded"></div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow text-center">
            <div class="font-bold">Ad Placeholder</div>
            <div class="text-xs text-slate-500">Sidebar Ad (300x250)</div>
            <div class="h-64 bg-slate-100 mt-2 rounded"></div>
        </div>
    </aside>
</div>
<?php
include __DIR__ . '/../includes/views/footer.php';
$html = ob_get_clean();
cacheSet($cacheKey, $html);
echo $html;
