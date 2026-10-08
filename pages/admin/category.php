<?php
require_once __DIR__ . '/_common.php';
admin_require_access();

$error = '';
$message = isset($_GET['saved']) ? 'Data kategori berhasil disimpan.' : (isset($_GET['deleted']) ? 'Kategori berhasil dihapus.' : '');
$editingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editingId = $editingId === false ? null : $editingId;
$values = ['name' => '', 'slug' => '', 'icon' => '', 'description' => ''];

if ($editingId !== null) {
    $stmt = $pdo->prepare('SELECT id, name, slug, icon, description FROM categories WHERE id = ?');
    $stmt->execute([$editingId]);
    $category = $stmt->fetch();
    if (!$category) {
        $editingId = null;
        $error = 'Kategori yang diminta tidak ditemukan.';
    } else {
        $values = array_merge($values, $category);
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
                $error = 'ID kategori tidak valid.';
            } else {
                $pdo->beginTransaction();
                $lock = $pdo->prepare('SELECT id FROM categories WHERE id = ? FOR UPDATE');
                $lock->execute([$submittedId]);
                if (!$lock->fetch()) {
                    $pdo->rollBack();
                    $error = 'Kategori tidak ditemukan atau sudah dihapus.';
                } else {
                    $appCountStmt = $pdo->prepare('SELECT COUNT(*) FROM apps WHERE category_id = ?');
                    $appCountStmt->execute([$submittedId]);
                    $appCount = (int)$appCountStmt->fetchColumn();
                    if ($appCount > 0) {
                        $pdo->rollBack();
                        $error = 'Kategori masih digunakan oleh ' . $appCount . ' software. Pindahkan software tersebut sebelum menghapus kategori.';
                    } else {
                        $delete = $pdo->prepare('DELETE FROM categories WHERE id = ?');
                        $delete->execute([$submittedId]);
                        $pdo->commit();
                        header('Location: ?admin=category&deleted=1');
                        exit;
                    }
                }
            }
        } elseif ($action === 'save') {
            $editingId = $submittedId === false ? null : $submittedId;
            $values = [
                'name' => admin_post_string('name'),
                'slug' => admin_post_string('slug'),
                'icon' => admin_post_string('icon'),
                'description' => admin_post_string('description'),
            ];
            $values['slug'] = admin_slugify($values['slug'] !== '' ? $values['slug'] : $values['name']);

            if ($values['name'] === '' || strlen($values['name']) > 100) {
                $error = 'Nama kategori wajib diisi dan maksimal 100 karakter.';
            } elseif ($values['slug'] === '' || strlen($values['slug']) > 100) {
                $error = 'Slug tidak valid atau terlalu panjang.';
            } elseif (strlen($values['icon']) > 100) {
                $error = 'Nama ikon maksimal 100 karakter.';
            } else {
                $slugCheck = $pdo->prepare('SELECT id FROM categories WHERE slug = ? AND id <> ?');
                $slugCheck->execute([$values['slug'], $editingId ?? 0]);
                if ($slugCheck->fetch()) {
                    $error = 'Slug sudah digunakan kategori lain.';
                } elseif ($editingId !== null) {
                    $exists = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
                    $exists->execute([$editingId]);
                    if (!$exists->fetch()) {
                        $error = 'Kategori tidak ditemukan. Muat ulang halaman lalu coba lagi.';
                    } else {
                        $save = $pdo->prepare('UPDATE categories SET name = ?, slug = ?, icon = ?, description = ? WHERE id = ?');
                        $save->execute([$values['name'], $values['slug'], $values['icon'], $values['description'], $editingId]);
                        header('Location: ?admin=category&saved=1');
                        exit;
                    }
                } else {
                    $save = $pdo->prepare('INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)');
                    $save->execute([$values['name'], $values['slug'], $values['icon'], $values['description']]);
                    header('Location: ?admin=category&saved=1');
                    exit;
                }
            }
        } else {
            $error = 'Aksi yang diminta tidak dikenali.';
        }
    }
}

$categories = $pdo->query('SELECT c.id, c.name, c.slug, c.icon, COUNT(a.id) AS app_count FROM categories c LEFT JOIN apps a ON a.category_id = c.id GROUP BY c.id, c.name, c.slug, c.icon ORDER BY c.name')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <p class="text-xs font-mono uppercase tracking-wider text-brand-600 mb-2">Administrasi katalog</p>
        <h1 class="text-3xl font-extrabold text-slate-900 font-heading">Kelola kategori</h1>
        <p class="text-sm text-slate-600 mt-2">Kategori yang masih berisi software tidak dapat dihapus.</p>
    </div>
    <?php admin_render_navigation('category'); ?>
    <?php admin_render_message($message); ?>
    <?php admin_render_message($error, true); ?>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(20rem,0.8fr)]">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="flex items-center justify-between gap-4 mb-5">
                <h2 class="text-lg font-bold text-slate-900">Daftar kategori</h2>
                <span class="text-xs text-slate-500"><?= count($categories) ?> kategori</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[34rem] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                        <tr><th class="py-3 pr-3">Kategori</th><th class="py-3 pr-3">Software</th><th class="py-3">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($categories as $item): ?>
                            <tr>
                                <td class="py-3 pr-3"><span class="font-semibold text-slate-900"><?= admin_escape($item['name']) ?></span><span class="block text-xs text-slate-500"><?= admin_escape($item['slug']) ?></span></td>
                                <td class="py-3 pr-3"><?= (int)$item['app_count'] ?></td>
                                <td class="py-3">
                                    <div class="flex items-center gap-3">
                                        <a href="?admin=category&amp;id=<?= (int)$item['id'] ?>" class="font-semibold text-brand-700 hover:underline">Edit</a>
                                        <form method="post" action="?admin=category" onsubmit="return confirm('Hapus kategori ini? Kategori yang masih digunakan tidak dapat dihapus.');">
                                            <input type="hidden" name="csrf_token" value="<?= admin_escape($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                            <button class="font-semibold text-rose-700 hover:underline" type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="flex items-center justify-between gap-4 mb-5">
                <h2 class="text-lg font-bold text-slate-900"><?= $editingId === null ? 'Tambah kategori' : 'Edit kategori' ?></h2>
                <?php if ($editingId !== null): ?><a href="?admin=category" class="text-sm font-semibold text-brand-700 hover:underline">Batalkan edit</a><?php endif; ?>
            </div>
            <form method="post" action="?admin=category<?= $editingId !== null ? '&id=' . (int)$editingId : '' ?>" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= admin_escape($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="save">
                <?php if ($editingId !== null): ?><input type="hidden" name="id" value="<?= (int)$editingId ?>"><?php endif; ?>
                <label class="block text-sm font-medium text-slate-700">Nama kategori *
                    <input name="name" required maxlength="100" value="<?= admin_escape($values['name']) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                </label>
                <label class="block text-sm font-medium text-slate-700">Slug
                    <input name="slug" maxlength="100" value="<?= admin_escape($values['slug']) ?>" placeholder="Otomatis dari nama jika kosong" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                </label>
                <label class="block text-sm font-medium text-slate-700">Ikon (opsional)
                    <input name="icon" maxlength="100" value="<?= admin_escape($values['icon']) ?>" placeholder="Contoh: fa-solid fa-code" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                </label>
                <label class="block text-sm font-medium text-slate-700">Deskripsi
                    <textarea name="description" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"><?= admin_escape($values['description']) ?></textarea>
                </label>
                <button class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700" type="submit"><?= $editingId === null ? 'Tambah kategori' : 'Simpan perubahan' ?></button>
            </form>
        </section>
    </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
