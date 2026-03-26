<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();
$seo = seoDefaults('Admin Dashboard');
include __DIR__ . '/../includes/views/header.php';
?>
<h1 class="text-2xl font-bold mb-4">Admin Dashboard</h1>
<div class="grid md:grid-cols-2 gap-4">
    <a class="bg-white rounded-xl p-4 shadow block" href="<?= e(url('admin/news/articles.php')) ?>">
        <h2 class="font-bold">News Dashboard</h2>
        <p class="text-sm text-slate-600">Articles, SEO fields, categories.</p>
    </a>
    <a class="bg-white rounded-xl p-4 shadow block" href="<?= e(url('admin/store/products.php')) ?>">
        <h2 class="font-bold">Store Dashboard</h2>
        <p class="text-sm text-slate-600">Products, orders, CSV bulk upload.</p>
    </a>
</div>
<div class="mt-4 flex gap-3 text-sm">
    <a href="<?= e(url('admin/media/index.php')) ?>" class="underline">Media Manager</a>
    <a href="<?= e(url('admin/settings.php')) ?>" class="underline">RBAC / Permissions</a>
    <a href="<?= e(url('admin/logout.php')) ?>" class="underline">Logout</a>
</div>
<?php include __DIR__ . '/../includes/views/footer.php'; ?>
