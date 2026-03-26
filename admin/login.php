<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrf();
    if (login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '', $db)) {
        header('Location: ' . url('admin'));
        exit;
    }
    $message = 'Invalid credentials';
}
$seo = seoDefaults('Admin Login');
include __DIR__ . '/../includes/views/header.php';
?>
<form method="post" class="max-w-md mx-auto bg-white rounded-xl p-4 shadow space-y-3">
    <h1 class="text-xl font-bold">Admin Login</h1>
    <?php if ($message): ?><div class="text-red-600 text-sm"><?= e($message) ?></div><?php endif; ?>
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <input class="w-full border p-2 rounded" type="email" name="email" placeholder="Email" required>
    <input class="w-full border p-2 rounded" type="password" name="password" placeholder="Password" required>
    <button class="w-full bg-slate-900 text-white p-2 rounded">Login</button>
</form>
<?php include __DIR__ . '/../includes/views/footer.php'; ?>
