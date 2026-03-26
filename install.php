<?php
$lockFile = __DIR__ . '/cache/install.lock';
if (file_exists($lockFile)) {
    exit('Installation locked. Remove cache/install.lock to rerun.');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? '';
    $baseUrl = rtrim(trim($_POST['base_url'] ?? ''), '/');
    $adminName = trim($_POST['admin_name'] ?? 'Admin');
    $adminEmail = trim($_POST['admin_email'] ?? 'admin@mzsmartlife.store');
    $adminPass = $_POST['admin_pass'] ?? 'Admin@12345';

    try {
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $schema = [
            "CREATE TABLE IF NOT EXISTS roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(80) UNIQUE NOT NULL,
                created_at DATETIME NOT NULL
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS permissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                `key` VARCHAR(120) UNIQUE NOT NULL,
                label VARCHAR(120) NOT NULL
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS role_permissions (
                role_id INT NOT NULL,
                permission_id INT NOT NULL,
                PRIMARY KEY (role_id, permission_id),
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                role_id INT NOT NULL,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL,
                FOREIGN KEY (role_id) REFERENCES roles(id)
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(80) NOT NULL,
                slug VARCHAR(120) UNIQUE NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_categories_slug (slug)
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS articles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                category_id INT NOT NULL,
                title VARCHAR(200) NOT NULL,
                slug VARCHAR(220) UNIQUE NOT NULL,
                content MEDIUMTEXT NOT NULL,
                featured_image VARCHAR(255) DEFAULT NULL,
                meta_title VARCHAR(200) DEFAULT NULL,
                meta_keywords VARCHAR(255) DEFAULT NULL,
                meta_description VARCHAR(255) DEFAULT NULL,
                is_breaking TINYINT(1) DEFAULT 0,
                is_featured TINYINT(1) DEFAULT 0,
                published_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (category_id) REFERENCES categories(id),
                INDEX idx_articles_slug (slug),
                INDEX idx_articles_published (published_at),
                FULLTEXT KEY ft_articles (title, content)
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                name VARCHAR(200) NOT NULL,
                slug VARCHAR(220) UNIQUE NOT NULL,
                sku VARCHAR(80) UNIQUE NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                stock INT NOT NULL DEFAULT 0,
                description TEXT,
                meta_title VARCHAR(200) DEFAULT NULL,
                meta_keywords VARCHAR(255) DEFAULT NULL,
                meta_description VARCHAR(255) DEFAULT NULL,
                is_featured TINYINT(1) DEFAULT 0,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id),
                INDEX idx_products_slug (slug),
                INDEX idx_products_sku (sku)
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS product_images (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_no VARCHAR(40) UNIQUE NOT NULL,
                customer_name VARCHAR(120) NOT NULL,
                customer_email VARCHAR(190) NOT NULL,
                customer_phone VARCHAR(40) DEFAULT NULL,
                shipping_address TEXT NOT NULL,
                subtotal DECIMAL(10,2) NOT NULL,
                status VARCHAR(40) NOT NULL DEFAULT 'pending',
                created_at DATETIME NOT NULL,
                INDEX idx_orders_created (created_at)
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL,
                unit_price DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id)
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS media (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                mime_type VARCHAR(120) NOT NULL,
                created_at DATETIME NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id)
            ) ENGINE=InnoDB"
        ];

        foreach ($schema as $sql) {
            $pdo->exec($sql);
        }

        $now = date('Y-m-d H:i:s');

        foreach (['Admin', 'Editor', 'Store Manager'] as $roleName) {
            $stmt = $pdo->prepare('INSERT IGNORE INTO roles (name, created_at) VALUES (:name, :created_at)');
            $stmt->execute([':name' => $roleName, ':created_at' => $now]);
        }

        $permissions = [
            ['news.manage', 'Manage news articles'],
            ['news.categories', 'Manage categories'],
            ['store.manage', 'Manage products'],
            ['store.orders', 'Manage orders'],
            ['settings.manage', 'Manage settings'],
            ['media.manage', 'Manage media'],
        ];

        foreach ($permissions as [$key, $label]) {
            $stmt = $pdo->prepare('INSERT IGNORE INTO permissions (`key`, label) VALUES (:key, :label)');
            $stmt->execute([':key' => $key, ':label' => $label]);
        }

        $roleMap = $pdo->query('SELECT id, name FROM roles')->fetchAll(PDO::FETCH_KEY_PAIR);
        $permMap = $pdo->query('SELECT id, `key` FROM permissions')->fetchAll(PDO::FETCH_KEY_PAIR);
        $permByKey = array_flip($permMap);

        $grants = [
            'Admin' => ['news.manage', 'news.categories', 'store.manage', 'store.orders', 'settings.manage', 'media.manage'],
            'Editor' => ['news.manage', 'news.categories', 'media.manage'],
            'Store Manager' => ['store.manage', 'store.orders', 'media.manage'],
        ];

        foreach ($grants as $role => $keys) {
            $roleId = $roleMap[$role] ?? null;
            if (!$roleId) continue;
            foreach ($keys as $key) {
                if (!isset($permByKey[$key])) continue;
                $stmt = $pdo->prepare('INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)');
                $stmt->execute([':role_id' => $roleId, ':permission_id' => $permByKey[$key]]);
            }
        }

        $adminRoleId = $roleMap['Admin'] ?? 1;
        $stmt = $pdo->prepare('INSERT INTO users (role_id, name, email, password_hash, created_at) VALUES (:role_id, :name, :email, :password_hash, :created_at)');
        $stmt->execute([
            ':role_id' => $adminRoleId,
            ':name' => $adminName,
            ':email' => $adminEmail,
            ':password_hash' => password_hash($adminPass, PASSWORD_DEFAULT),
            ':created_at' => $now,
        ]);

        $defaultCategories = ['Showbiz', 'Sports', 'World', 'Italy'];
        foreach ($defaultCategories as $cat) {
            $stmt = $pdo->prepare('INSERT IGNORE INTO categories (name, slug, created_at) VALUES (:name, :slug, :created_at)');
            $stmt->execute([
                ':name' => $cat,
                ':slug' => strtolower($cat),
                ':created_at' => $now,
            ]);
        }

        $config = "<?php\n" .
            "define('DB_HOST', '" . addslashes($dbHost) . "');\n" .
            "define('DB_NAME', '" . addslashes($dbName) . "');\n" .
            "define('DB_USER', '" . addslashes($dbUser) . "');\n" .
            "define('DB_PASS', '" . addslashes($dbPass) . "');\n" .
            "define('BASE_URL', '" . addslashes($baseUrl) . "');\n" .
            "define('APP_INSTALLED', true);\n";

        file_put_contents(__DIR__ . '/config.php', $config, LOCK_EX);

        if (!is_dir(__DIR__ . '/cache')) {
            mkdir(__DIR__ . '/cache', 0755, true);
        }
        file_put_contents($lockFile, 'Installed at ' . $now);

        $success = 'Installation completed. Delete install.php or keep lock file in place.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install MZ Smart Life</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen p-6">
<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">
    <h1 class="text-2xl font-bold mb-4">MZ Smart Life Installer</h1>
    <?php if ($error): ?><p class="bg-red-100 text-red-700 p-3 rounded mb-3"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="bg-green-100 text-green-700 p-3 rounded mb-3"><?= htmlspecialchars($success) ?></p><?php endif; ?>
    <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <input class="border p-2 rounded" name="db_host" placeholder="DB Host" required>
        <input class="border p-2 rounded" name="db_name" placeholder="DB Name" required>
        <input class="border p-2 rounded" name="db_user" placeholder="DB User" required>
        <input class="border p-2 rounded" type="password" name="db_pass" placeholder="DB Password">
        <input class="border p-2 rounded md:col-span-2" name="base_url" placeholder="Base URL (https://mzsmartlife.store)" required>
        <input class="border p-2 rounded" name="admin_name" placeholder="Admin Name" required>
        <input class="border p-2 rounded" type="email" name="admin_email" placeholder="Admin Email" required>
        <input class="border p-2 rounded md:col-span-2" type="password" name="admin_pass" placeholder="Admin Password" required>
        <button class="bg-slate-900 text-white rounded p-2 md:col-span-2">Install Now</button>
    </form>
</div>
</body>
</html>
