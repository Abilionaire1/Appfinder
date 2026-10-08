<?php
require_once __DIR__ . '/_common.php';
admin_require_access();

$error = '';
$message = isset($_GET['saved']) ? 'Data software berhasil disimpan.' : (isset($_GET['deleted']) ? 'Software berhasil dihapus.' : '');
$editingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editingId = $editingId === false ? null : $editingId;
$values = [
    'name' => '',
    'slug' => '',
    'category_id' => '',
    'description' => '',
    'developer' => '',
    'icon' => '',
    'play_store_url' => '',
    'website_url' => '',
    'license' => 'Freeware',
    'alternative_to' => '',
    'version' => '',
    'platforms' => '',
    'is_open_source' => 0,
    'featured' => 0,
];

if ($editingId !== null) {
    $stmt = $pdo->prepare('SELECT id, name, slug, category_id, description, developer, icon, play_store_url, website_url, license, alternative_to, version, platforms, is_open_source, featured FROM apps WHERE id = ?');
    $stmt->execute([$editingId]);
    $app = $stmt->fetch();
    if (!$app) {
        $editingId = null;
        $error = 'Software yang diminta tidak ditemukan.';
    } else {
        $values = array_merge($values, $app);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_csrf_is_valid()) {
        $error = 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        $action = admin_post_string('action');
        $submittedId = filter_var(admin_post_string('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($action === 'delete') {
            if ($submittedId === false) {
                $error = 'ID software tidak valid.';
            } else {
                $delete = $pdo->prepare('DELETE FROM apps WHERE id = ?');
                $delete->execute([$submittedId]);
                if ($delete->rowCount() === 0) {
                    $error = 'Software tidak ditemukan atau sudah dihapus.';
                } else {
                    header('Location: ?admin=apps&deleted=1');
                    exit;
                }
            }
        } elseif ($action === 'save') {
            $editingId = $submittedId === false ? null : $submittedId;
            $values = [
                'name' => admin_post_string('name'),
                'slug' => admin_post_string('slug'),
                'category_id' => admin_post_string('category_id'),
                'description' => admin_post_string('description'),
                'developer' => admin_post_string('developer'),
                'icon' => admin_post_string('icon'),
                'play_store_url' => admin_post_string('play_store_url'),
                'website_url' => admin_post_string('website_url'),
                'license' => admin_post_string('license'),
                'alternative_to' => admin_post_string('alternative_to'),
                'version' => admin_post_string('version'),
                'platforms' => admin_post_string('platforms'),
                'is_open_source' => isset($_POST['is_open_source']) ? 1 : 0,
                'featured' => isset($_POST['featured']) ? 1 : 0,
            ];
            $values['slug'] = admin_slugify($values['slug'] !== '' ? $values['slug'] : $values['name']);
            $categoryId = filter_var($values['category_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($values['name'] === '' || strlen($values['name']) > 100) {
                $error = 'Nama software wajib diisi dan maksimal 100 karakter.';
            } elseif ($values['slug'] === '' || strlen($values['slug']) > 150) {
                $error = 'Slug tidak valid atau terlalu panjang.';
            } elseif ($categoryId === false) {
                $error = 'Pilih kategori yang valid.';
            } elseif (strlen($values['developer']) > 100 || strlen($values['icon']) > 255 || strlen($values['license']) > 100 || strlen($values['alternative_to']) > 255 || strlen($values['version']) > 50 || strlen($values['platforms']) > 100) {
                $error = 'Salah satu field melebihi batas panjang yang diizinkan.';
            } elseif (!admin_valid_url($values['icon']) || !admin_valid_url($values['play_store_url']) || !admin_valid_url($values['website_url'])) {
                $error = 'URL ikon, unduhan, dan situs harus menggunakan URL http atau https yang valid.';
            } else {
                $categoryCheck = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
                $categoryCheck->execute([$categoryId]);
                $slugCheck = $pdo->prepare('SELECT id FROM apps WHERE slug = ? AND id <> ?');
                $slugCheck->execute([$values['slug'], $editingId ?? 0]);

                if (!$categoryCheck->fetch()) {
                    $error = 'Kategori tidak ditemukan. Muat ulang halaman dan pilih kategori lain.';
                } elseif ($slugCheck->fetch()) {
                    $error = 'Slug sudah digunakan software lain.';
                } else {
                    $fields = [
                        $values['name'],
                        $values['slug'],
                        $categoryId,
                        $values['description'],
                        $values['developer'],
                        $values['icon'],
                        $values['play_store_url'],
                        $values['website_url'],
                        $values['license'],
                        $values['alternative_to'],
                        $values['version'],
                        $values['platforms'],
                        $values['is_open_source'],
                        $values['featured'],
                    ];

                    if ($editingId !== null) {
                        $fields[] = $editingId;
                        $save = $pdo->prepare('UPDATE apps SET name = ?, slug = ?, category_id = ?, description = ?, developer = ?, icon = ?, play_store_url = ?, website_url = ?, license = ?, alternative_to = ?, version = ?, platforms = ?, is_open_source = ?, featured = ? WHERE id = ?');
                        $save->execute($fields);
                    } else {
                        $save = $pdo->prepare('INSERT INTO apps (name, slug, category_id, description, developer, icon, play_store_url, website_url, license, alternative_to, version, platforms, is_open_source, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                        $save->execute($fields);
                    }

                    header('Location: ?admin=apps&saved=1');
                    exit;
                }
            }
        } else {
            $error = 'Aksi yang diminta tidak dikenali.';
        }
    }
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$search = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
$pageNumber = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$pageNumber = $pageNumber === false || $pageNumber === null ? 1 : $pageNumber;
$pageSize = 50;
$offset = ($pageNumber - 1) * $pageSize;

if ($search !== '') {
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM apps WHERE name LIKE ? OR slug LIKE ?');
    $searchTerm = '%' . $search . '%';
    $countStmt->execute([$searchTerm, $searchTerm]);
    $totalApps = (int)$countStmt->fetchColumn();
    $listStmt = $pdo->prepare('SELECT a.id, a.name, a.slug, c.name AS category_name, a.featured, (SELECT COUNT(*) FROM bookmarks b WHERE b.app_id = a.id) AS bookmark_count FROM apps a JOIN categories c ON c.id = a.category_id WHERE a.name LIKE ? OR a.slug LIKE ? ORDER BY a.name LIMIT ? OFFSET ?');
    $listStmt->bindValue(1, $searchTerm);
    $listStmt->bindValue(2, $searchTerm);
    $listStmt->bindValue(3, $pageSize, PDO::PARAM_INT);
    $listStmt->bindValue(4, $offset, PDO::PARAM_INT);
    $listStmt->execute();
} else {
    $totalApps = (int)$pdo->query('SELECT COUNT(*) FROM apps')->fetchColumn();
    $listStmt = $pdo->prepare('SELECT a.id, a.name, a.slug, c.name AS category_name, a.featured, (SELECT COUNT(*) FROM bookmarks b WHERE b.app_id = a.id) AS bookmark_count FROM apps a JOIN categories c ON c.id = a.category_id ORDER BY a.name LIMIT ? OFFSET ?');
    $listStmt->bindValue(1, $pageSize, PDO::PARAM_INT);
    $listStmt->bindValue(2, $offset, PDO::PARAM_INT);
    $listStmt->execute();
}
$apps = $listStmt->fetchAll();
$pageCount = max(1, (int)ceil($totalApps / $pageSize));

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <p class="text-xs font-mono uppercase tracking-wider text-brand-600 mb-2">Administrasi katalog</p>
        <h1 class="text-3xl font-extrabold text-slate-900 font-heading">Kelola software</h1>
        <p class="text-sm text-slate-600 mt-2">Rating dan jumlah ulasan tidak diubah dari formulir ini agar data yang belum terverifikasi tidak dikarang.</p>
    </div>
    <?php admin_render_navigation('apps'); ?>
    <?php admin_render_message($message); ?>
    <?php admin_render_message($error, true); ?>

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1.6fr)_minmax(20rem,1fr)]">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                <h2 class="text-lg font-bold text-slate-900"><?= $editingId === null ? 'Tambah software' : 'Edit software' ?></h2>
                <?php if ($editingId !== null): ?>
                    <a href="?admin=apps" class="text-sm font-semibold text-brand-700 hover:underline">Batalkan edit</a>
                <?php endif; ?>
            </div>
            <form method="post" action="?admin=apps<?= $editingId !== null ? '&id=' . (int)$editingId : '' ?>" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= admin_escape($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="save">
                <?php if ($editingId !== null): ?><input type="hidden" name="id" value="<?= (int)$editingId ?>"><?php endif; ?>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-medium text-slate-700">Nama software *
                        <input name="name" required maxlength="100" value="<?= admin_escape($values['name']) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-slate-700">Slug
                        <input name="slug" maxlength="150" value="<?= admin_escape($values['slug']) ?>" placeholder="Otomatis dari nama jika kosong" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-slate-700">Kategori *
                        <select name="category_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                            <option value="">Pilih kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int)$category['id'] ?>" <?= (string)$values['category_id'] === (string)$category['id'] ? 'selected' : '' ?>><?= admin_escape($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="text-sm font-medium text-slate-700">Developer
                        <input name="developer" maxlength="100" value="<?= admin_escape($values['developer']) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-slate-700">URL ikon
                        <input type="url" name="icon" maxlength="255" value="<?= admin_escape($values['icon']) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-slate-700">URL unduhan resmi
                        <input type="url" name="play_store_url" maxlength="255" value="<?= admin_escape($values['play_store_url']) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-slate-700">Situs resmi
                        <input type="url" name="website_url" maxlength="255" value="<?= admin_escape($values['website_url']) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-slate-700">Lisensi
                        <input name="license" maxlength="100" value="<?= admin_escape($values['license']) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-slate-700">Versi
                        <input name="version" maxlength="50" value="<?= admin_escape($values['version']) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-slate-700">Platform
                        <input name="platforms" maxlength="100" value="<?= admin_escape($values['platforms']) ?>" placeholder="Windows, macOS, Linux" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-slate-700 sm:col-span-2">Alternatif untuk
                        <input name="alternative_to" maxlength="255" value="<?= admin_escape($values['alternative_to']) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    </label>
                    <label class="text-sm font-medium text-slate-700 sm:col-span-2">Deskripsi
                        <textarea name="description" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"><?= admin_escape($values['description']) ?></textarea>
                    </label>
                </div>
                <div class="flex flex-wrap gap-5 text-sm text-slate-700">
                    <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_open_source" value="1" <?= !empty($values['is_open_source']) ? 'checked' : '' ?>> Open source</label>
                    <label class="inline-flex items-center gap-2"><input type="checkbox" name="featured" value="1" <?= !empty($values['featured']) ? 'checked' : '' ?>> Tampilkan sebagai unggulan</label>
                </div>
                <button class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700" type="submit"><?= $editingId === null ? 'Tambah software' : 'Simpan perubahan' ?></button>
            </form>
        </section>

        <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="flex items-center justify-between gap-4 mb-4">
                <h2 class="text-lg font-bold text-slate-900">Daftar software</h2>
                <span class="text-xs text-slate-500"><?= $totalApps ?> item</span>
            </div>
            <form method="get" class="flex gap-2 mb-4">
                <input type="hidden" name="admin" value="apps">
                <label class="sr-only" for="app-search">Cari software</label>
                <input id="app-search" name="q" value="<?= admin_escape($search) ?>" placeholder="Cari nama atau slug" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold hover:bg-slate-50">Cari</button>
            </form>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[42rem] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                        <tr><th class="py-3 pr-3">Software</th><th class="py-3 pr-3">Kategori</th><th class="py-3 pr-3">Bookmark</th><th class="py-3">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($apps as $item): ?>
                            <tr>
                                <td class="py-3 pr-3"><span class="font-semibold text-slate-900"><?= admin_escape($item['name']) ?></span><span class="block text-xs text-slate-500"><?= admin_escape($item['slug']) ?><?= $item['featured'] ? ' · Unggulan' : '' ?></span></td>
                                <td class="py-3 pr-3"><?= admin_escape($item['category_name']) ?></td>
                                <td class="py-3 pr-3"><?= (int)$item['bookmark_count'] ?></td>
                                <td class="py-3">
                                    <div class="flex items-center gap-3">
                                        <a href="?admin=apps&amp;id=<?= (int)$item['id'] ?>" class="font-semibold text-brand-700 hover:underline">Edit</a>
                                        <form method="post" action="?admin=apps" onsubmit="return confirm('Hapus software ini? Bookmark pengguna juga akan dihapus.');">
                                            <input type="hidden" name="csrf_token" value="<?= admin_escape($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                            <button class="font-semibold text-rose-700 hover:underline" type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$apps): ?><tr><td colspan="4" class="py-8 text-center text-slate-500">Tidak ada software yang cocok.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($pageCount > 1): ?>
                <div class="mt-5 flex items-center justify-between text-sm">
                    <?php if ($pageNumber > 1): ?><a class="font-semibold text-brand-700" href="?admin=apps&amp;q=<?= urlencode($search) ?>&amp;p=<?= $pageNumber - 1 ?>">← Sebelumnya</a><?php else: ?><span></span><?php endif; ?>
                    <span class="text-slate-500">Halaman <?= $pageNumber ?> / <?= $pageCount ?></span>
                    <?php if ($pageNumber < $pageCount): ?><a class="font-semibold text-brand-700" href="?admin=apps&amp;q=<?= urlencode($search) ?>&amp;p=<?= $pageNumber + 1 ?>">Berikutnya →</a><?php else: ?><span></span><?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
