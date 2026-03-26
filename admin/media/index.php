<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireLogin();
requirePermission($db, 'media.manage');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrf();
    if (!empty($_FILES['file']['name'])) {
        $path = validateUpload($_FILES['file']);
        if ($path) {
            $stmt = $db->prepare('INSERT INTO media (user_id,file_path,mime_type,created_at) VALUES (:user_id,:file_path,:mime_type,:created_at)');
            $stmt->execute([':user_id'=>currentUser()['id'], ':file_path'=>$path, ':mime_type'=>mime_content_type(__DIR__ . '/../../' . $path), ':created_at'=>now()]);
        }
    }
}
$media = $db->query('SELECT * FROM media ORDER BY created_at DESC LIMIT 80')->fetchAll();
$seo = seoDefaults('Media Manager');
include __DIR__ . '/../../includes/views/header.php';
?>
<h1 class="text-xl font-bold mb-3">Media Manager</h1>
<form method="post" enctype="multipart/form-data" class="bg-white rounded p-3 shadow mb-3 flex gap-2">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <input type="file" name="file" required>
    <button class="bg-slate-900 text-white px-3 rounded">Upload</button>
</form>
<div class="grid grid-cols-2 md:grid-cols-4 gap-2">
<?php foreach($media as $m): ?><img loading="lazy" src="<?= e(url($m['file_path'])) ?>" class="w-full h-24 object-cover rounded" alt="Media"><?php endforeach; ?>
</div>
<?php include __DIR__ . '/../../includes/views/footer.php'; ?>
