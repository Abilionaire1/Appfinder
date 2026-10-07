<?php
require_once __DIR__ . '/../includes/header.php';

// Filter inputs
$query = trim($_GET['q'] ?? '');
$categoryId = (int)($_GET['c'] ?? 0);
$licenseFilter = $_GET['filter'] ?? '';
$sort = $_GET['sort'] ?? 'rating';

// Build SQL
$sql = "SELECT a.*, c.name as category_name 
        FROM apps a 
        JOIN categories c ON a.category_id = c.id 
        WHERE 1=1";
$params = [];

if ($query !== '') {
    $sql .= " AND (a.name LIKE ? OR a.description LIKE ? OR a.alternative_to LIKE ? OR a.developer LIKE ?)";
    $searchTerm = "%{$query}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($categoryId > 0) {
    $sql .= " AND a.category_id = ?";
    $params[] = $categoryId;
}

if ($licenseFilter === 'open-source') {
    $sql .= " AND a.is_open_source = 1";
} elseif ($licenseFilter === 'freeware') {
    $sql .= " AND a.is_open_source = 0";
}

// Sorting
if ($sort === 'name') {
    $sql .= " ORDER BY a.name ASC";
} elseif ($sort === 'reviews') {
    $sql .= " ORDER BY a.reviews_count DESC";
} else {
    $sql .= " ORDER BY a.rating DESC, a.reviews_count DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$apps = $stmt->fetchAll();

// All categories for sidebar filter
$allCats = $pdo->query("SELECT id, name, (SELECT COUNT(*) FROM apps WHERE category_id = categories.id) as count FROM categories ORDER BY name ASC")->fetchAll();

// User bookmarks
$userBookmarks = [];
if (isset($_SESSION['user_id'])) {
    $stmtUserBm = $pdo->prepare("SELECT app_id FROM bookmarks WHERE user_id = ?");
    $stmtUserBm->execute([$_SESSION['user_id']]);
    $userBookmarks = $stmtUserBm->fetchAll(PDO::FETCH_COLUMN);
}
?>

<div class="bg-slate-100/70 border-b border-slate-200 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-mono text-slate-500 mb-1">
                    <a href="?page=home" class="hover:text-brand-600">Home</a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">Explore Software</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-navy font-heading">
                    Katalog Software Legal &amp; Gratis
                </h1>
            </div>
            
            <div class="text-xs font-mono text-slate-500">
                Menampilkan <span class="font-bold text-navy"><?= count($apps) ?></span> software terverifikasi
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Left Filter Sidebar -->
        <aside class="space-y-6">
            
            <!-- Search Widget -->
            <form action="index.php" method="GET" class="space-y-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <input type="hidden" name="page" value="explore">
                <?php if ($categoryId > 0): ?>
                    <input type="hidden" name="c" value="<?= $categoryId ?>">
                <?php endif; ?>
                <?php if ($licenseFilter !== ''): ?>
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($licenseFilter) ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 font-mono mb-2">Pencarian</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="q" 
                            value="<?= htmlspecialchars($query) ?>" 
                            placeholder="Nama software atau alternatif..." 
                            class="w-full px-3 py-2 pl-9 text-xs border border-slate-300 rounded-lg focus:ring-brand-500 focus:border-brand-500"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 font-mono mb-2">Urutkan</label>
                    <select name="sort" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg bg-white focus:ring-brand-500 focus:border-brand-500">
                        <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Rating Tertinggi</option>
                        <option value="reviews" <?= $sort === 'reviews' ? 'selected' : '' ?>>Ulasan Terbanyak</option>
                        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Nama (A-Z)</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-navy hover:bg-slate-800 text-white text-xs font-semibold py-2 rounded-lg transition-colors">
                    Terapkan Filter
                </button>
            </form>

            <!-- License Model Filters -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 font-mono mb-3">Model Lisensi</h3>
                <div class="space-y-1 text-xs">
                    <a 
                        href="?page=explore&c=<?= $categoryId ?>&q=<?= urlencode($query) ?>&sort=<?= $sort ?>" 
                        class="flex items-center justify-between px-2.5 py-1.5 rounded-lg <?= empty($licenseFilter) ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?>"
                    >
                        <span>Semua Lisensi</span>
                    </a>
                    <a 
                        href="?page=explore&filter=open-source&c=<?= $categoryId ?>&q=<?= urlencode($query) ?>&sort=<?= $sort ?>" 
                        class="flex items-center justify-between px-2.5 py-1.5 rounded-lg <?= $licenseFilter === 'open-source' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?>"
                    >
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Open Source
                        </span>
                    </a>
                    <a 
                        href="?page=explore&filter=freeware&c=<?= $categoryId ?>&q=<?= urlencode($query) ?>&sort=<?= $sort ?>" 
                        class="flex items-center justify-between px-2.5 py-1.5 rounded-lg <?= $licenseFilter === 'freeware' ? 'bg-cyan-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?>"
                    >
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                            Freeware / Gratis
                        </span>
                    </a>
                </div>
            </div>

            <!-- Category List Filter -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 font-mono mb-3">Kategori</h3>
                <div class="space-y-1 text-xs max-h-72 overflow-y-auto pr-1">
                    <a 
                        href="?page=explore&filter=<?= urlencode($licenseFilter) ?>&q=<?= urlencode($query) ?>&sort=<?= $sort ?>" 
                        class="flex items-center justify-between px-2.5 py-1.5 rounded-lg <?= $categoryId === 0 ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?>"
                    >
                        <span>Semua Kategori</span>
                    </a>
                    <?php foreach ($allCats as $c): ?>
                        <a 
                            href="?page=explore&c=<?= $c['id'] ?>&filter=<?= urlencode($licenseFilter) ?>&q=<?= urlencode($query) ?>&sort=<?= $sort ?>" 
                            class="flex items-center justify-between px-2.5 py-1.5 rounded-lg <?= $categoryId == $c['id'] ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?>"
                        >
                            <span><?= htmlspecialchars($c['name']) ?></span>
                            <span class="font-mono text-[10px] text-slate-400"><?= $c['count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Clear All Filters Link -->
            <?php if ($query !== '' || $categoryId > 0 || $licenseFilter !== ''): ?>
                <div class="text-center">
                    <a href="?page=explore" class="text-xs font-semibold text-rose-600 hover:underline">
                        &times; Reset Semua Filter
                    </a>
                </div>
            <?php endif; ?>

        </aside>

        <!-- Right Software Catalog Grid -->
        <main class="lg:col-span-3">
            <?php if (empty($apps)): ?>
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-base font-bold text-navy mb-1 font-heading">Tidak Ada Software Ditemukan</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mb-6">
                        Pencarian Anda tidak cocok dengan kriteria filter saat ini. Coba gunakan kata kunci umum atau reset filter.
                    </p>
                    <a href="?page=explore" class="inline-block px-4 py-2 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-slate-800 transition-colors">
                        Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <?php foreach ($apps as $app): ?>
                        <?php 
                            $isBookmarked = in_array($app['id'], $userBookmarks);
                        ?>
                        <div class="ui-card rounded-xl p-5 flex flex-col justify-between group">
                            
                            <div>
                                <!-- Header & Bookmark -->
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3">
                                        <img 
                                            src="<?= htmlspecialchars($app['icon']) ?>" 
                                            alt="<?= htmlspecialchars($app['name']) ?> logo" 
                                            class="w-10 h-10 rounded-xl object-contain p-1 border border-slate-200 bg-white"
                                            onerror="this.src='https://placehold.co/40x40/f1f5f9/475569?text=App'"
                                        >
                                        <div>
                                            <h3 class="text-sm font-bold text-navy group-hover:text-brand-600 transition-colors leading-tight">
                                                <a href="?page=detail&id=<?= $app['id'] ?>">
                                                    <?= htmlspecialchars($app['name']) ?>
                                                </a>
                                            </h3>
                                            <div class="text-[11px] text-slate-500 mt-0.5 truncate max-w-[140px]">
                                                <?= htmlspecialchars($app['developer']) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <button 
                                        type="button" 
                                        onclick="toggleBookmark(<?= $app['id'] ?>, this)"
                                        class="p-1.5 rounded-lg border border-slate-200 hover:border-slate-300 transition-colors <?= $isBookmarked ? 'text-brand-600 bg-brand-50' : 'text-slate-400 bg-white' ?>"
                                        title="Simpan software"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="<?= $isBookmarked ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Licensing Pill -->
                                <div class="flex flex-wrap items-center gap-1.5 mb-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 font-mono">
                                        <?= htmlspecialchars($app['category_name']) ?>
                                    </span>
                                    <?php if ($app['is_open_source']): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Open-Source
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-50 text-brand-700 border border-brand-200 font-mono">
                                            <?= htmlspecialchars($app['license']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Description -->
                                <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed mb-3">
                                    <?= htmlspecialchars($app['description']) ?>
                                </p>

                                <?php if (!empty($app['alternative_to'])): ?>
                                    <div class="text-[10px] font-mono text-slate-500 bg-slate-50 px-2 py-1 rounded border border-slate-200/60 mb-3 truncate">
                                        <span class="text-slate-400">Alternatif:</span> <?= htmlspecialchars($app['alternative_to']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Bottom Platform & Link -->
                            <div class="pt-3 border-t border-slate-100">
                                <div class="flex items-center justify-between text-xs mb-3">
                                    <span class="font-mono text-[10px] text-slate-400 truncate max-w-[130px]">
                                        <?= htmlspecialchars($app['platforms']) ?>
                                    </span>
                                    <?php if ((float)$app['rating'] > 0): ?>
                                        <div class="flex items-center gap-1 font-semibold text-slate-700 text-xs">
                                            <svg class="w-3.5 h-3.5 text-amber-500 fill-amber-500" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            <span><?= number_format((float)$app['rating'], 1) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[10px] font-medium text-slate-400">Belum dinilai</span>
                                    <?php endif; ?>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <a 
                                        href="?page=detail&id=<?= $app['id'] ?>" 
                                        class="w-full text-center px-2.5 py-1.5 rounded-lg border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold transition-colors bg-white hover:bg-slate-50"
                                    >
                                        Detail
                                    </a>
                                    <a 
                                        href="<?= htmlspecialchars($app['play_store_url']) ?>" 
                                        target="_blank" 
                                        rel="noopener noreferrer" 
                                        class="w-full text-center px-2.5 py-1.5 rounded-lg bg-navy hover:bg-slate-800 text-white text-xs font-semibold transition-colors flex items-center justify-center gap-1"
                                    >
                                        <span>Unduh</span>
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
