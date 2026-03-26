<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();
requirePermission($db, 'settings.manage');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrf();
    $roleId = (int)($_POST['role_id'] ?? 0);
    $perms = $_POST['permissions'] ?? [];
    $db->prepare('DELETE FROM role_permissions WHERE role_id = :role_id')->execute([':role_id' => $roleId]);
    $ins = $db->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)');
    foreach ($perms as $pid) {
        $ins->execute([':role_id' => $roleId, ':permission_id' => (int)$pid]);
    }
}
$roles = $db->query('SELECT * FROM roles ORDER BY id')->fetchAll();
$permissions = $db->query('SELECT * FROM permissions ORDER BY id')->fetchAll();
$assigned = [];
$rows = $db->query('SELECT role_id, permission_id FROM role_permissions')->fetchAll();
foreach ($rows as $r) { $assigned[$r['role_id']][] = $r['permission_id']; }
$seo = seoDefaults('RBAC Settings');
include __DIR__ . '/../includes/views/header.php';
?>
<h1 class="text-xl font-bold mb-3">Role Permissions</h1>
<?php foreach($roles as $role): ?>
<form method="post" class="bg-white p-4 rounded shadow mb-3">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <input type="hidden" name="role_id" value="<?= (int)$role['id'] ?>">
    <h2 class="font-semibold mb-2"><?= e($role['name']) ?></h2>
    <div class="grid md:grid-cols-3 gap-2">
        <?php foreach($permissions as $p): ?>
            <label class="text-sm"><input type="checkbox" name="permissions[]" value="<?= (int)$p['id'] ?>" <?= in_array($p['id'], $assigned[$role['id']] ?? [], true) ? 'checked' : '' ?>> <?= e($p['label']) ?></label>
        <?php endforeach; ?>
    </div>
    <button class="mt-2 bg-slate-900 text-white rounded px-3 py-1">Save</button>
</form>
<?php endforeach; ?>
<?php include __DIR__ . '/../includes/views/footer.php'; ?>
