<?php
require_once __DIR__ . '/_common.php';
admin_require_access();
require_once __DIR__ . '/../../includes/header.php';

$appCount = (int)$pdo->query('SELECT COUNT(*) FROM apps')->fetchColumn();
$categoryCount = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
?>

<section class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <p class="text-xs font-mono uppercase tracking-wider text-brand-600 mb-2">Administrasi</p>
        <h1 class="text-3xl font-extrabold text-slate-900 font-heading">Kelola katalog AppFinder</h1>
        <p class="text-sm text-slate-600 mt-2">Perbarui daftar software dan kategori yang tampil di katalog publik.</p>
    </div>

    <?php admin_render_navigation('index'); ?>

    <div class="grid gap-4 sm:grid-cols-2">
        <a href="?admin=apps" class="block rounded-2xl border border-slate-200 bg-white p-6 hover:border-slate-400">
            <p class="text-sm font-semibold text-slate-500">Software dalam katalog</p>
            <p class="mt-2 text-4xl font-extrabold text-slate-900"><?= $appCount ?></p>
            <span class="inline-block mt-5 text-sm font-semibold text-brand-700">Kelola software &rarr;</span>
        </a>
        <a href="?admin=category" class="block rounded-2xl border border-slate-200 bg-white p-6 hover:border-slate-400">
            <p class="text-sm font-semibold text-slate-500">Kategori</p>
            <p class="mt-2 text-4xl font-extrabold text-slate-900"><?= $categoryCount ?></p>
            <span class="inline-block mt-5 text-sm font-semibold text-brand-700">Kelola kategori &rarr;</span>
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
