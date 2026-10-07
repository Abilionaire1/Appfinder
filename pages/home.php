<?php
require_once __DIR__ . '/../includes/header.php';

// Fetch categories with real live counts
$stmtCat = $pdo->query("
    SELECT c.*, COUNT(a.id) as app_count 
    FROM categories c 
    LEFT JOIN apps a ON c.id = a.category_id 
    GROUP BY c.id 
    ORDER BY c.id ASC
");
$categories = $stmtCat->fetchAll();

// Category Icon & Color Configuration (Lucide-grade SVGs + Soft Tint Badges)
$categoryMeta = [
    'Education' => [
        'badge' => 'bg-blue-50 text-blue-600 border border-blue-100 group-hover:bg-blue-600 group-hover:text-white',
        'svg' => '<path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>'
    ],
    'Office' => [
        'badge' => 'bg-amber-50 text-amber-600 border border-amber-100 group-hover:bg-amber-600 group-hover:text-white',
        'svg' => '<path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/>'
    ],
    'Editing' => [
        'badge' => 'bg-violet-50 text-violet-600 border border-violet-100 group-hover:bg-violet-600 group-hover:text-white',
        'svg' => '<path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/><path d="m15 5 3 3"/>'
    ],
    'Coding' => [
        'badge' => 'bg-cyan-50 text-cyan-600 border border-cyan-100 group-hover:bg-cyan-600 group-hover:text-white',
        'svg' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>'
    ],
    'Streaming' => [
        'badge' => 'bg-rose-50 text-rose-600 border border-rose-100 group-hover:bg-rose-600 group-hover:text-white',
        'svg' => '<path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.934a.5.5 0 0 0-.777-.416L16 11"/><rect width="14" height="12" x="2" y="6" rx="2"/>'
    ],
    'Entertainment' => [
        'badge' => 'bg-indigo-50 text-indigo-600 border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white',
        'svg' => '<line x1="6" x2="10" y1="11" y2="11"/><line x1="8" x2="8" y1="9" y2="13"/><line x1="15" x2="15.01" y1="12" y2="12"/><line x1="18" x2="18.01" y1="10" y2="10"/><path d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59c-.006.052-.01.101-.017.152C2.604 9.416 2 14.456 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.414-1.414A2 2 0 0 1 9.828 16h4.344a2 2 0 0 1 1.414.586L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.545-.604-6.584-.685-7.258-.007-.05-.011-.1-.017-.151A4 4 0 0 0 17.32 5z"/>'
    ],
    'Art' => [
        'badge' => 'bg-fuchsia-50 text-fuchsia-600 border border-fuchsia-100 group-hover:bg-fuchsia-600 group-hover:text-white',
        'svg' => '<circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.563-2.512 5.563-5.563C22 6.5 17.5 2 12 2z"/>'
    ],
    'Music' => [
        'badge' => 'bg-pink-50 text-pink-600 border border-pink-100 group-hover:bg-pink-600 group-hover:text-white',
        'svg' => '<path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>'
    ],
    'Books' => [
        'badge' => 'bg-emerald-50 text-emerald-600 border border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white',
        'svg' => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>'
    ],
    'Quran' => [
        'badge' => 'bg-teal-50 text-teal-600 border border-teal-100 group-hover:bg-teal-600 group-hover:text-white',
        'svg' => '<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/><path d="M10 2v8l3-2 3 2V2"/>'
    ]
];

// Fetch featured & top rated apps
$stmtFeatured = $pdo->query("
    SELECT a.*, c.name as category_name 
    FROM apps a 
    JOIN categories c ON a.category_id = c.id 
    WHERE a.featured = 1 
    ORDER BY a.rating DESC, a.reviews_count DESC 
    LIMIT 6
");
$featuredApps = $stmtFeatured->fetchAll();

// Fetch bookmark IDs for the current logged-in user
$userBookmarks = [];
if (isset($_SESSION['user_id'])) {
    $stmtUserBm = $pdo->prepare("SELECT app_id FROM bookmarks WHERE user_id = ?");
    $stmtUserBm->execute([$_SESSION['user_id']]);
    $userBookmarks = $stmtUserBm->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!-- C. Hero Section -->
<section class="relative bg-tech-grid pt-16 pb-20 md:pt-24 md:pb-28 border-b border-slate-200 overflow-hidden">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center relative z-10">
        
        <!-- Eyebrow -->
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-slate-200 text-slate-700 text-xs font-semibold mb-6 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
            <span class="tracking-wide uppercase font-mono text-[11px] text-slate-600">
                OPEN-SOURCE &bull; FREEWARE &bull; LEGAL ALTERNATIVES
            </span>
        </div>

        <!-- Main Headline -->
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-navy tracking-tight leading-[1.15] mb-6 font-heading">
            Software Berkualitas.<br class="hidden sm:inline">
            <span class="text-brand-500">Legal. Gratis.</span>
        </h1>

        <!-- Supporting Description -->
        <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed font-normal">
            Temukan software terpercaya untuk kreativitas, produktivitas, pengembangan, dan berbagai kebutuhan digital Anda tanpa kompromi keamanan atau jebakan pembajakan.
        </p>

        <!-- Prominent Search Component -->
        <div class="max-w-3xl mx-auto">
            <form action="index.php" method="GET" class="bg-white p-2 sm:p-2.5 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center gap-2 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 transition-all">
                <input type="hidden" name="page" value="explore">
                
                <!-- Search Input with Icon -->
                <div class="flex items-center gap-3 px-3 sm:px-4 w-full flex-grow">
                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input 
                        type="text" 
                        name="q" 
                        placeholder="Cari software, kategori, atau alternatif (cth: Blender, Office)..." 
                        class="w-full py-2.5 bg-transparent text-slate-800 placeholder-slate-400 focus:outline-none text-sm font-medium"
                        autocomplete="off"
                    >
                </div>

                <!-- Category Selector & Submit -->
                <div class="flex items-center gap-2 w-full sm:w-auto border-t sm:border-t-0 sm:border-l border-slate-200 pt-2 sm:pt-0 sm:pl-3">
                    <select name="c" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium rounded-xl px-3 py-2.5 focus:outline-none focus:border-brand-500 w-full sm:w-40 cursor-pointer">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition-colors shadow-sm flex items-center justify-center gap-1.5 w-full sm:w-auto flex-shrink-0">
                        <span>Cari</span>
                    </button>
                </div>
            </form>

            <!-- Popular Search Suggestions -->
            <div class="flex flex-wrap items-center justify-center gap-1.5 mt-5 text-xs text-slate-500">
                <span class="font-medium mr-1 text-slate-400">Pencarian Populer:</span>
                <a href="?page=explore&q=Blender" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:border-slate-400 hover:text-navy transition-colors">Blender</a>
                <a href="?page=explore&q=VS+Code" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:border-slate-400 hover:text-navy transition-colors">VS Code</a>
                <a href="?page=explore&q=VLC" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:border-slate-400 hover:text-navy transition-colors">VLC Player</a>
                <a href="?page=explore&q=GIMP" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:border-slate-400 hover:text-navy transition-colors">GIMP</a>
                <a href="?page=explore&q=LibreOffice" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:border-slate-400 hover:text-navy transition-colors">LibreOffice</a>
                <a href="?page=explore&q=Obsidian" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:border-slate-400 hover:text-navy transition-colors">Obsidian</a>
            </div>
        </div>

    </div>
</section>

<!-- D. Trust Indicators (Compact & Verifiable) -->
<section class="border-b border-slate-200 bg-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-left">
            
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-navy uppercase font-mono tracking-wider">Official Sources</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Langsung ke Google Play Store &amp; repositori pengembang resmi.</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600 flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-navy uppercase font-mono tracking-wider">Clear Licensing</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Pembedaan jelas antara Open-Source (GPL/MIT) dan Freeware legal.</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-navy uppercase font-mono tracking-wider">Curated Software</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Diuji kegunaan tier gratisnya secara mandiri, bukan klaim sepihak.</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-navy uppercase font-mono tracking-wider">Free Alternatives</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Solusi pengganti software berbayar mahal tanpa risiko malware.</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- E. Explore by Category -->
<section id="categories" class="py-16 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
            <div>
                <span class="text-xs font-bold text-brand-600 uppercase tracking-wider font-mono">Direktori Lengkap</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-navy mt-1 font-heading">Jelajahi Berdasarkan Kategori</h2>
            </div>
            <a href="?page=explore" class="text-xs font-semibold text-slate-600 hover:text-brand-600 flex items-center gap-1 transition-colors">
                Lihat Semua Software (<?= array_sum(array_column($categories, 'app_count')) ?>) &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3.5">
            <?php foreach ($categories as $cat): ?>
                <?php 
                    $meta = $categoryMeta[$cat['name']] ?? [
                        'badge' => 'bg-slate-50 text-slate-700 border border-slate-200 group-hover:bg-slate-900 group-hover:text-white',
                        'svg' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>'
                    ];
                ?>
                <a href="?page=explore&c=<?= $cat['id'] ?>" class="ui-card p-4 rounded-xl flex flex-col justify-between group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl <?= $meta['badge'] ?> flex items-center justify-center transition-all duration-200 shadow-sm">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <?= $meta['svg'] ?>
                            </svg>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 group-hover:text-brand-600 transition-colors">
                            <?= $cat['app_count'] ?> app
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-navy group-hover:text-brand-600 transition-colors">
                            <?= htmlspecialchars($cat['name']) ?>
                        </h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- F. Featured Software (Curated Discovery) -->
<section class="py-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
            <div>
                <span class="text-xs font-bold text-brand-600 uppercase tracking-wider font-mono">Pilihan Terverifikasi</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-navy mt-1 font-heading">Software Pilihan Editor</h2>
            </div>
            <a href="?page=explore" class="text-xs font-semibold text-slate-600 hover:text-brand-600 flex items-center gap-1 transition-colors">
                Buka Direktori Penuh &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($featuredApps as $app): ?>
                <?php 
                    $isBookmarked = in_array($app['id'], $userBookmarks);
                ?>
                <div class="ui-card rounded-xl p-5 flex flex-col justify-between relative group">
                    
                    <!-- Top Info Header -->
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <img 
                                    src="<?= htmlspecialchars($app['icon']) ?>" 
                                    alt="<?= htmlspecialchars($app['name']) ?> logo" 
                                    class="w-11 h-11 rounded-xl object-contain p-1 border border-slate-200 bg-white"
                                    onerror="this.src='https://placehold.co/48x48/f1f5f9/475569?text=App'"
                                >
                                <div>
                                    <h3 class="text-base font-bold text-navy group-hover:text-brand-600 transition-colors leading-tight">
                                        <a href="?page=detail&id=<?= $app['id'] ?>">
                                            <?= htmlspecialchars($app['name']) ?>
                                        </a>
                                    </h3>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        <?= htmlspecialchars($app['developer']) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Save / Bookmark Button -->
                            <button 
                                type="button" 
                                onclick="toggleBookmark(<?= $app['id'] ?>, this)"
                                class="p-1.5 rounded-lg border border-slate-200 hover:border-slate-300 transition-colors <?= $isBookmarked ? 'text-brand-600 bg-brand-50' : 'text-slate-400 bg-white' ?>"
                                title="Simpan ke favorit"
                                aria-label="Simpan ke favorit"
                            >
                                <svg class="w-4 h-4" fill="<?= $isBookmarked ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Badges: Category & Licensing -->
                        <div class="flex flex-wrap items-center gap-1.5 mb-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 font-mono">
                                <?= htmlspecialchars($app['category_name']) ?>
                            </span>

                            <?php if ($app['is_open_source']): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Open-Source (<?= htmlspecialchars($app['license']) ?>)
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-50 text-brand-700 border border-brand-200 font-mono">
                                    <?= htmlspecialchars($app['license']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed mb-4">
                            <?= htmlspecialchars($app['description']) ?>
                        </p>
                    </div>

                    <!-- Card Footer: Platform Compatibility & Actions -->
                    <div class="pt-3 border-t border-slate-100 mt-2">
                        <div class="flex items-center justify-between text-xs text-slate-500 mb-3">
                            <span class="font-mono text-[11px] truncate max-w-[170px]" title="<?= htmlspecialchars($app['platforms']) ?>">
                                <?= htmlspecialchars($app['platforms']) ?>
                            </span>
                            <div class="flex items-center gap-1 font-semibold text-slate-700">
                                <svg class="w-3.5 h-3.5 text-amber-500 fill-amber-500" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <span><?= number_format((float)$app['rating'], 1) ?></span>
                                <span class="text-slate-400 font-normal text-[11px]">(<?= number_format($app['reviews_count']) ?>)</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <a 
                                href="?page=detail&id=<?= $app['id'] ?>" 
                                class="w-full text-center px-3 py-1.5 rounded-lg border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold transition-colors bg-white hover:bg-slate-50"
                            >
                                Detail &amp; Lisensi
                            </a>
                            <a 
                                href="<?= htmlspecialchars($app['play_store_url']) ?>" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="w-full text-center px-3 py-1.5 rounded-lg bg-navy hover:bg-slate-800 text-white text-xs font-semibold transition-colors flex items-center justify-center gap-1"
                            >
                                <span>Unduh</span>
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- G. Software Discovery / Alternative Section -->
<section id="alternatives" class="py-16 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="max-w-3xl mx-auto text-center mb-12">
            <span class="text-xs font-bold text-brand-600 uppercase tracking-wider font-mono">Bebas Biaya Berlangganan</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-navy mt-1 font-heading">
                Alternatif Legal untuk Software Komersial Populer
            </h2>
            <p class="text-sm text-slate-600 mt-2">
                Tidak perlu membayar langganan bulanan mahal atau menggunakan patch ilegal berbahaya. Berikut adalah padanan legal dan open-source yang siap menggantikan workflow Anda.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            
            <!-- Alternative 1: Photoshop -> GIMP -->
            <div class="ui-card rounded-xl p-5 flex flex-col justify-between">
                <div>
                    <div class="text-[11px] font-mono font-semibold text-rose-600 mb-1">PENGGANTI ADOBE PHOTOSHOP</div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-sm font-medium text-slate-400 line-through">Photoshop (Berbayar)</span>
                        <span class="text-xs text-slate-500 font-mono">&rarr;</span>
                        <span class="text-sm font-bold text-navy">GIMP</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-3 leading-relaxed">
                        Mendukung layer blending, tool retouching presisi, path editing, serta dukungan plugin komunitas untuk pengeditan foto profesional tanpa biaya.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[10px] font-semibold border border-emerald-200">
                        GNU GPLv3 Open Source
                    </span>
                    <a href="?page=explore&q=GIMP" class="font-semibold text-brand-600 hover:underline">
                        Lihat Profil &rarr;
                    </a>
                </div>
            </div>

            <!-- Alternative 2: MS Office -> LibreOffice -->
            <div class="ui-card rounded-xl p-5 flex flex-col justify-between">
                <div>
                    <div class="text-[11px] font-mono font-semibold text-rose-600 mb-1">PENGGANTI MICROSOFT 365</div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-sm font-medium text-slate-400 line-through">Office 365 (Langganan)</span>
                        <span class="text-xs text-slate-500 font-mono">&rarr;</span>
                        <span class="text-sm font-bold text-navy">LibreOffice</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-3 leading-relaxed">
                        Suite dokumen mandiri yang kompatibel penuh dengan format .docx, .xlsx, dan .pptx tanpa lock-in lisensi proprietary Microsoft.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[10px] font-semibold border border-emerald-200">
                        MPL 2.0 Open Source
                    </span>
                    <a href="?page=explore&q=LibreOffice" class="font-semibold text-brand-600 hover:underline">
                        Lihat Profil &rarr;
                    </a>
                </div>
            </div>

            <!-- Alternative 3: Autodesk Maya -> Blender -->
            <div class="ui-card rounded-xl p-5 flex flex-col justify-between">
                <div>
                    <div class="text-[11px] font-mono font-semibold text-rose-600 mb-1">PENGGANTI AUTODESK MAYA / 3DS MAX</div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-sm font-medium text-slate-400 line-through">Maya ($1,875/thn)</span>
                        <span class="text-xs text-slate-500 font-mono">&rarr;</span>
                        <span class="text-sm font-bold text-navy">Blender 3D</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-3 leading-relaxed">
                        Standar industri pembuatan aset 3D, animasi karakter, game modeling, dan simulasi fisika yang 100% bebas biaya seumur hidup.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[10px] font-semibold border border-emerald-200">
                        GPL v3 Open Source
                    </span>
                    <a href="?page=explore&q=Blender" class="font-semibold text-brand-600 hover:underline">
                        Lihat Profil &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- H. Trust & Transparency Section -->
<section id="transparency" class="py-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <div class="lg:col-span-5 space-y-4">
                <span class="text-xs font-bold text-brand-600 uppercase tracking-wider font-mono">Standar Integritas</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-navy font-heading">
                    Bagaimana AppFinder Memverifikasi Software?
                </h2>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Kami meyakini bahwa akses ke software berkualitas adalah hak setiap pengguna, tanpa harus berkompromi dengan keamanan data atau melanggar hak cipta pengembang.
                </p>
                <div class="pt-2">
                    <a href="?page=register" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-600 hover:text-brand-700">
                        <span>Punya rekomendasi aplikasi untuk dikurasi?</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50">
                    <div class="w-7 h-7 rounded bg-brand-50 text-brand-600 flex items-center justify-center font-mono font-bold text-xs mb-3">
                        01
                    </div>
                    <h3 class="text-sm font-bold text-navy mb-1">Verifikasi Tautan Asli</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Semua tautan unduhan diverifikasi langsung ke Google Play Store atau website resmi pengembang. Tidak pernah menautkan ke situs mirror APK mencurigakan.
                    </p>
                </div>

                <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50">
                    <div class="w-7 h-7 rounded bg-brand-50 text-brand-600 flex items-center justify-center font-mono font-bold text-xs mb-3">
                        02
                    </div>
                    <h3 class="text-sm font-bold text-navy mb-1">Audit Usabilitas Gratis</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Aplikasi yang terdaftar harus memiliki fungsi utama yang benar-benar dapat digunakan tanpa harus membayar langganan mandatory.
                    </p>
                </div>

                <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50">
                    <div class="w-7 h-7 rounded bg-brand-50 text-brand-600 flex items-center justify-center font-mono font-bold text-xs mb-3">
                        03
                    </div>
                    <h3 class="text-sm font-bold text-navy mb-1">Transparansi Lisensi</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Kami tidak mencampuradukkan software Open-Source sejati (GPL/MIT) dengan Freeware proprietary. Informasi lisensi disajikan jujur.
                    </p>
                </div>

                <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50">
                    <div class="w-7 h-7 rounded bg-brand-50 text-brand-600 flex items-center justify-center font-mono font-bold text-xs mb-3">
                        04
                    </div>
                    <h3 class="text-sm font-bold text-navy mb-1">Bebas Mod &amp; Malware</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Kebijakan ketat nol toleransi terhadap APK modifikasi ilegal (crack, unlocked paywall), menjaga keamanan perangkat Anda tetap terlindungi.
                    </p>
                </div>

            </div>

        </div>

    </div>
</section>

<!-- Community Contribution CTA -->
<section class="py-16 bg-navy text-white text-center">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl sm:text-3xl font-bold mb-3 font-heading">
            Tahu Software Gratis Berkualitas yang Belum Terdaftar?
        </h2>
        <p class="text-sm text-slate-300 mb-8 max-w-xl mx-auto leading-relaxed">
            Bergabunglah dengan komunitas kurator AppFinder. Bagikan aplikasi open-source dan legal favorit Anda untuk membantu jutaan orang lainnya.
        </p>
        <a 
            href="?page=register" 
            class="inline-block bg-brand-500 hover:bg-brand-600 text-white font-semibold px-6 py-3 rounded-xl text-sm transition-colors shadow-sm"
        >
            Kirimkan Rekomendasi Aplikasi
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
