<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireLogin();
requirePermission($db, 'store.manage');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrf();
    if (isset($_POST['bulk_csv']) && !empty($_FILES['csv_file']['name'])) {
        $csv = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if ($csv) {
            fgetcsv($csv);
            while (($row = fgetcsv($csv)) !== false) {
                [$name, $sku, $price, $stock, $desc] = array_pad($row, 5, '');
                if (!$name || !$sku) continue;
                $slug = generateUniqueSlug($db, 'products', slugify($name));
                $stmt = $db->prepare('INSERT IGNORE INTO products (user_id,name,slug,sku,price,stock,description,created_at,updated_at) VALUES (:user_id,:name,:slug,:sku,:price,:stock,:description,:created_at,:updated_at)');
                $stmt->execute([
                    ':user_id'=>currentUser()['id'], ':name'=>$name, ':slug'=>$slug, ':sku'=>$sku,
                    ':price'=>(float)$price, ':stock'=>(int)$stock, ':description'=>$desc,
                    ':created_at'=>now(), ':updated_at'=>now()
                ]);
            }
            fclose($csv);
        }
    } else {
        $name = trim($_POST['name'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        if ($name && $sku) {
            $slug = generateUniqueSlug($db, 'products', slugify($name));
            $stmt = $db->prepare('INSERT INTO products (user_id,name,slug,sku,price,stock,description,meta_title,meta_keywords,meta_description,is_featured,created_at,updated_at) VALUES (:user_id,:name,:slug,:sku,:price,:stock,:description,:meta_title,:meta_keywords,:meta_description,:is_featured,:created_at,:updated_at)');
            $stmt->execute([
                ':user_id'=>currentUser()['id'], ':name'=>$name, ':slug'=>$slug, ':sku'=>$sku,
                ':price'=>(float)($_POST['price'] ?? 0), ':stock'=>(int)($_POST['stock'] ?? 0),
                ':description'=>trim($_POST['description'] ?? ''), ':meta_title'=>trim($_POST['meta_title'] ?? ''),
                ':meta_keywords'=>trim($_POST['meta_keywords'] ?? ''), ':meta_description'=>trim($_POST['meta_description'] ?? ''),
                ':is_featured'=>isset($_POST['is_featured']) ? 1 : 0,
                ':created_at'=>now(), ':updated_at'=>now()
            ]);
            $productId = (int)$db->lastInsertId();
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                    $single = [
                        'name' => $_FILES['images']['name'][$i],
                        'type' => $_FILES['images']['type'][$i],
                        'tmp_name' => $tmp,
                        'error' => $_FILES['images']['error'][$i],
                        'size' => $_FILES['images']['size'][$i],
                    ];
                    $path = validateUpload($single);
                    if ($path) {
                        $imgStmt = $db->prepare('INSERT INTO product_images (product_id,image_path,sort_order) VALUES (:product_id,:image_path,:sort_order)');
                        $imgStmt->execute([':product_id'=>$productId,':image_path'=>$path,':sort_order'=>$i]);
                    }
                }
            }
        }
    }
    cacheBust();
}

$products = $db->query('SELECT id,name,sku,price,stock,slug FROM products ORDER BY created_at DESC LIMIT 80')->fetchAll();
$seo = seoDefaults('Manage Products');
include __DIR__ . '/../../includes/views/header.php';
?>
<h1 class="text-xl font-bold mb-3">Store Dashboard</h1>
<form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow grid md:grid-cols-2 gap-2 mb-4">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <input class="border p-2 rounded" name="name" placeholder="Product name">
    <input class="border p-2 rounded" name="sku" placeholder="SKU">
    <input class="border p-2 rounded" type="number" step="0.01" name="price" placeholder="Price">
    <input class="border p-2 rounded" type="number" name="stock" placeholder="Stock">
    <textarea class="border p-2 rounded md:col-span-2" name="description" placeholder="Description"></textarea>
    <input class="border p-2 rounded md:col-span-2" name="meta_title" placeholder="Meta title">
    <input class="border p-2 rounded md:col-span-2" name="meta_keywords" placeholder="Meta keywords">
    <textarea class="border p-2 rounded md:col-span-2" name="meta_description" placeholder="Meta description"></textarea>
    <input class="border p-2 rounded md:col-span-2" type="file" name="images[]" multiple accept="image/*">
    <label><input type="checkbox" name="is_featured"> Featured</label>
    <button class="bg-slate-900 text-white p-2 rounded md:col-span-2">Create Product</button>
</form>
<form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow mb-4 flex gap-2 items-center">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <input type="hidden" name="bulk_csv" value="1">
    <input type="file" name="csv_file" accept=".csv" required>
    <button class="bg-emerald-600 text-white rounded px-3 py-2">Upload CSV</button>
</form>
<div class="space-y-2">
<?php foreach($products as $p): ?><div class="bg-white p-3 rounded shadow"><strong><?= e($p['name']) ?></strong> <span class="text-sm">(<?= e($p['sku']) ?>) - $<?= number_format((float)$p['price'],2) ?> / stock <?= (int)$p['stock'] ?></span></div><?php endforeach; ?>
</div>
<?php include __DIR__ . '/../../includes/views/footer.php'; ?>
