<?php
function admin_escape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function admin_require_access(): void
{
    global $pdo;

    if (!isset($_SESSION['user_id'])) {
        header('Location: ?page=login');
        exit;
    }

    $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $role = $stmt->fetchColumn();

    if ($role !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../../includes/header.php';
        echo '<div class="max-w-3xl mx-auto px-4 py-20 text-center"><h1 class="text-2xl font-bold text-slate-900 mb-2">Akses ditolak</h1><p class="text-sm text-slate-600">Halaman ini hanya dapat diakses oleh administrator.</p><a href="?page=home" class="inline-flex mt-6 px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold">Kembali ke beranda</a></div>';
        require __DIR__ . '/../../includes/footer.php';
        exit;
    }
}

function admin_csrf_is_valid(): bool
{
    $token = $_POST['csrf_token'] ?? '';

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function admin_post_string(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function admin_slugify(string $value): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $value = strtolower($ascii === false ? $value : $ascii);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $value);

    return trim((string)$slug, '-');
}

function admin_valid_url(string $value): bool
{
    if ($value === '') {
        return true;
    }

    if (filter_var($value, FILTER_VALIDATE_URL) === false) {
        return false;
    }

    $scheme = strtolower((string)parse_url($value, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true);
}

function admin_render_navigation(string $active): void
{
    $links = [
        'index' => ['Ringkasan', '?admin=index'],
        'apps' => ['Software', '?admin=apps'],
        'category' => ['Kategori', '?admin=category'],
    ];
    ?>
    <nav aria-label="Navigasi admin" class="flex flex-wrap gap-2 mb-6">
        <?php foreach ($links as $key => [$label, $href]): ?>
            <a href="<?= admin_escape($href) ?>" class="px-4 py-2 rounded-lg text-sm font-semibold <?= $active === $key ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' ?>">
                <?= admin_escape($label) ?>
            </a>
        <?php endforeach; ?>
        <a href="?page=home" class="px-4 py-2 rounded-lg text-sm font-semibold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">Lihat situs</a>
    </nav>
    <?php
}

function admin_render_message(string $message, bool $isError = false): void
{
    if ($message === '') {
        return;
    }
    ?>
    <div role="status" class="mb-5 rounded-lg border px-4 py-3 text-sm <?= $isError ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' ?>">
        <?= admin_escape($message) ?>
    </div>
    <?php
}
