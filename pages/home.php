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

// Category Icon SVGs
$categoryMeta = [
    'Education' => '<path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>',
    'Office' => '<path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/>',
    'Editing' => '<path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/><path d="m15 5 3 3"/>',
    'Coding' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
    'Streaming' => '<path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.934a.5.5 0 0 0-.777-.416L16 11"/><rect width="14" height="12" x="2" y="6" rx="2"/>',
    'Entertainment' => '<line x1="6" x2="10" y1="11" y2="11"/><line x1="8" x2="8" y1="9" y2="13"/><line x1="15" x2="15.01" y1="12" y2="12"/><line x1="18" x2="18.01" y1="10" y2="10"/><path d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59c-.006.052-.01.101-.017.152C2.604 9.416 2 14.456 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.414-1.414A2 2 0 0 1 9.828 16h4.344a2 2 0 0 1 1.414.586L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.545-.604-6.584-.685-7.258-.007-.05-.011-.1-.017-.151A4 4 0 0 0 17.32 5z"/>',
    'Art' => '<circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.563-2.512 5.563-5.563C22 6.5 17.5 2 12 2z"/>',
    'Music' => '<path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>',
    'Books' => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
    'Utility' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M8 3v4M16 3v4M8 17v4M16 17v4M3 8h4M17 8h4M3 16h4M17 16h4"/><rect width="6" height="6" x="9" y="9" rx="1"/>',
    'Quran' => '<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/><path d="M10 2v8l3-2 3 2V2"/>'
];

// Fetch featured apps
$stmtFeatured = $pdo->query("
    SELECT a.*, c.name as category_name 
    FROM apps a 
    JOIN categories c ON a.category_id = c.id 
    WHERE a.featured = 1 
    ORDER BY a.rating DESC, a.reviews_count DESC 
    LIMIT 6
");
$featuredApps = $stmtFeatured->fetchAll();

// Curated free or open-source replacements for commercial software.
$stmtAlternatives = $pdo->query("
    SELECT a.*, c.name AS category_name
    FROM apps a
    JOIN categories c ON c.id = a.category_id
    WHERE TRIM(COALESCE(a.alternative_to, '')) <> ''
      AND (a.is_free = 1 OR a.is_open_source = 1)
    ORDER BY a.is_open_source DESC, a.featured DESC, a.rating DESC, a.reviews_count DESC
    LIMIT 3
");
$alternativeApps = $stmtAlternatives->fetchAll();

// Fetch bookmark IDs
$userBookmarks = [];
if (isset($_SESSION['user_id'])) {
    $stmtUserBm = $pdo->prepare("SELECT app_id FROM bookmarks WHERE user_id = ?");
    $stmtUserBm->execute([$_SESSION['user_id']]);
    $userBookmarks = $stmtUserBm->fetchAll(PDO::FETCH_COLUMN);
}

// Total apps
$totalApps = array_sum(array_column($categories, 'app_count'));
?>

<!-- ============================================= -->
<!-- HERO SECTION                                  -->
<!-- ============================================= -->
<section class="relative pt-14 pb-16 md:pt-20 md:pb-24 overflow-hidden">
    
    <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-stone-50/80 to-transparent pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center relative z-10">
        
        <h1>
            <span class="block text-4xl sm:text-5xl md:text-6xl font-bold text-stone-900 tracking-tight leading-[1.1] font-heading">
                Temukan software terbaik
            </span>
            <span class="block text-4xl sm:text-5xl md:text-6xl tracking-tight leading-[1.1] mt-1">
                <span class="font-heading font-bold text-stone-900">dengan </span><span class="font-serif italic text-brand-600">kurasi terpercaya</span>
            </span>
        </h1>

        <p class="text-stone-500 text-base sm:text-lg max-w-2xl mx-auto mt-5 leading-relaxed">
            Di AppFinder, kami membantu Anda menemukan software legal, open-source, dan gratis berkualitas tinggi tanpa kompromi keamanan atau jebakan pembajakan.
        </p>

        <form action="index.php" method="GET" class="max-w-3xl mx-auto mt-8 bg-white rounded-2xl border border-stone-200 p-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shadow-sm focus-within:border-stone-400 focus-within:shadow-md transition-all">
            <input type="hidden" name="page" value="explore">
            <label class="flex min-w-0 flex-1 items-center gap-3 px-3">
                <svg class="w-5 h-5 text-stone-400 flex-shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <span class="sr-only">Cari software</span>
                <input type="search" name="q" placeholder="Cari software, kategori, atau alternatif..." class="h-11 w-full min-w-0 bg-transparent text-sm text-stone-800 placeholder:text-stone-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 rounded-md" autocomplete="off">
            </label>
            <div class="flex flex-col sm:flex-row gap-2">
                <label class="sr-only" for="hero-category">Pilih kategori</label>
                <select id="hero-category" name="c" class="h-11 w-full sm:w-44 rounded-xl border border-stone-200 bg-stone-50 px-3 text-sm text-stone-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                    <option value="">Semua kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="h-11 rounded-xl bg-stone-900 px-6 text-sm font-semibold text-white transition-colors hover:bg-stone-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                    Cari software
                </button>
            </div>
        </form>

        <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-xs text-stone-500">
            <span class="mr-1">Populer:</span>
            <?php foreach (['Blender', 'LibreOffice', 'VLC', 'GIMP'] as $popularSearch): ?>
                <a href="?page=explore&amp;q=<?= urlencode($popularSearch) ?>" class="rounded-full border border-stone-200 bg-white/80 px-3 py-1.5 font-medium text-stone-600 transition-colors hover:border-stone-400 hover:text-stone-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                    <?= htmlspecialchars($popularSearch) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="mt-7 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs font-medium text-stone-500">
            <span><?= number_format($totalApps) ?> software pilihan</span>
            <span class="hidden sm:inline text-stone-300" aria-hidden="true">/</span>
            <span><?= count($categories) ?> kategori</span>
            <span class="hidden sm:inline text-stone-300" aria-hidden="true">/</span>
            <span>Legal, gratis, dan open-source</span>
        </div>

    </div>
</section>

<!-- ============================================= -->
<!-- MARQUEE BRAND SLIDER                          -->
<!-- ============================================= -->
<section class="py-10 border-t border-stone-100 bg-white overflow-hidden">
    <div class="max-w-5xl mx-auto px-4">
        <div class="flex items-center justify-center mb-6">
            <div class="h-px bg-stone-200 flex-1"></div>
            <span class="px-4 text-xs text-stone-400 font-medium whitespace-nowrap">Software open-source & legal populer yang terkurasi</span>
            <div class="h-px bg-stone-200 flex-1"></div>
        </div>
    </div>
    <div class="relative">
        <div class="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
        <div class="absolute right-0 top-0 bottom-0 w-24 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>
        <div class="flex animate-marquee marquee-track" style="width: max-content; animation-play-state: running;">
            <?php 
            $brands = ['Blender', 'LibreOffice', 'GIMP', 'VLC Player', 'VS Code', 'Audacity', 'OBS Studio', 'Firefox', 'Inkscape', 'Krita'];
            $brandIcons = [
                'Blender' => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>',
                'LibreOffice' => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 6h8M8 10h8M8 14h5"/>',
                'GIMP' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>',
                'VLC Player' => '<polygon points="5 3 19 12 5 21 5 3"/>',
                'VS Code' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
                'Audacity' => '<path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>',
                'OBS Studio' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/><line x1="21.17" y1="8" x2="12" y2="8"/><line x1="3.95" y1="6.06" x2="8.54" y2="14"/><line x1="10.88" y1="21.94" x2="15.46" y2="14"/>',
                'Firefox' => '<circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0110 10"/>',
                'Inkscape' => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/>',
                'Krita' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4z"/>'
            ];
            // Duplicate for seamless loop
            for ($loop = 0; $loop < 2; $loop++):
                foreach ($brands as $brand): ?>
                    <div class="flex items-center gap-2.5 mx-8 sm:mx-12 shrink-0">
                        <svg class="w-5 h-5 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <?= $brandIcons[$brand] ?? '<circle cx="12" cy="12" r="10"/>' ?>
                        </svg>
                        <span class="text-sm font-semibold text-stone-400 whitespace-nowrap"><?= $brand ?></span>
                    </div>
            <?php endforeach; endfor; ?>
        </div>
    </div>
</section>

<!-- ============================================= -->
<!-- EXPLORE BY CATEGORY                           -->
<!-- ============================================= -->
<section id="categories" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-semibold text-brand-600 uppercase tracking-wider">Direktori Lengkap</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-stone-900 mt-1 font-heading tracking-tight">
                    Jelajahi berdasarkan <span class="font-serif italic text-brand-600">kategori</span>
                </h2>
            </div>
            <a href="?page=explore" class="text-sm font-semibold text-stone-500 hover:text-stone-900 flex items-center gap-1 transition-colors">
                Semua Software (<?= $totalApps ?>) →
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            <?php foreach ($categories as $cat): ?>
                <?php $svg = $categoryMeta[$cat['name']] ?? '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>'; ?>
                <a href="?page=explore&c=<?= $cat['id'] ?>" class="ui-card rounded-2xl p-5 flex flex-col justify-between group">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-10 h-10 rounded-xl bg-stone-100 group-hover:bg-brand-50 flex items-center justify-center text-stone-600 group-hover:text-brand-600 transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?= $svg ?></svg>
                        </div>
                        <span class="text-xs font-semibold text-stone-400 group-hover:text-brand-500 font-mono transition-colors">
                            <?= $cat['app_count'] ?>
                        </span>
                    </div>
                    <h3 class="text-sm font-semibold text-stone-800 group-hover:text-brand-600 transition-colors"><?= htmlspecialchars($cat['name']) ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================= -->
<!-- FEATURED PROMO BANNER (App Store Style)       -->
<!-- ============================================= -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative w-full h-[400px] rounded-3xl overflow-hidden bg-stone-900 group">
            <!-- Background Image Placeholder (Fallback) -->
            <img src="https://images.unsplash.com/photo-1607799279861-4dddf8b60dd4?q=80&w=2070&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-50 transition-opacity duration-700" alt="Featured Promo">
            
            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-stone-900 via-stone-900/80 to-transparent"></div>
            
            <!-- Content -->
            <div class="absolute inset-0 flex flex-col justify-center p-10 md:p-16 max-w-2xl">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/20 text-brand-300 text-xs font-semibold font-mono uppercase tracking-wider w-fit mb-4 border border-brand-500/30">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"></path></svg>
                    Sorotan Utama
                </span>
                <h2 class="text-4xl md:text-5xl font-extrabold text-white font-heading leading-tight mb-4 tracking-tight">
                    Software Produksi <br> Kelas Profesional
                </h2>
                <p class="text-stone-300 text-base md:text-lg mb-8 leading-relaxed max-w-lg">
                    Tingkatkan kualitas karya Anda dengan alternatif open-source terbaik untuk editing video, desain 3D, dan audio tanpa biaya lisensi tahunan.
                </p>
                <a href="?page=explore&c=3" class="inline-flex items-center justify-center gap-2 bg-white text-stone-900 hover:bg-stone-100 font-bold px-8 py-3 rounded-full text-sm transition-colors w-fit">
                    Jelajahi Kategori
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================= -->
<!-- FEATURED CAROUSEL (Horizontal Scroll)         -->
<!-- ============================================= -->
<section class="py-10 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-end justify-between mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 font-heading tracking-tight">
                    Disarankan untuk Anda
                </h2>
                <p class="text-sm text-stone-500 mt-1">Pilihan editor berdasarkan performa dan rating terbaik.</p>
            </div>
            <div class="hidden sm:flex gap-2">
                <button onclick="document.getElementById('featured-carousel').scrollBy({left: -300, behavior: 'smooth'})" class="w-10 h-10 rounded-full border border-stone-200 flex items-center justify-center text-stone-400 hover:text-stone-900 hover:bg-stone-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button onclick="document.getElementById('featured-carousel').scrollBy({left: 300, behavior: 'smooth'})" class="w-10 h-10 rounded-full border border-stone-200 flex items-center justify-center text-stone-400 hover:text-stone-900 hover:bg-stone-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>

        <!-- Horizontal Scroll Container -->
        <div id="featured-carousel" class="flex gap-5 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-8 -mx-4 px-4 sm:mx-0 sm:px-0">
            <?php foreach ($featuredApps as $index => $app): ?>
                <?php 
                    $isBookmarked = in_array($app['id'], $userBookmarks); 
                    // Generate pseudo-random image for landscape cover based on ID
                    $coverImages = [
                        'https://images.unsplash.com/photo-1618477388954-7852f32655ec?auto=format&fit=crop&q=80&w=800&h=450',
                        'https://images.unsplash.com/photo-1542831371-29b0f74f9713?auto=format&fit=crop&q=80&w=800&h=450',
                        'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&q=80&w=800&h=450',
                        'https://images.unsplash.com/photo-1558655146-d09347e92766?auto=format&fit=crop&q=80&w=800&h=450',
                        'https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&q=80&w=800&h=450',
                        'https://images.unsplash.com/photo-1627398240411-8d141e6c2717?auto=format&fit=crop&q=80&w=800&h=450'
                    ];
                    $coverImage = $coverImages[$index % count($coverImages)];
                ?>
                <a href="?page=detail&id=<?= $app['id'] ?>" class="app-card-landscape w-[280px] sm:w-[320px] md:w-[360px] group">
                    <!-- Cover Image -->
                    <div class="cover-wrapper">
                        <img src="<?= $coverImage ?>" alt="Cover <?= htmlspecialchars($app['name']) ?>" class="cover-img">
                        <!-- Overlapping App Icon -->
                        <div class="absolute -bottom-4 left-4 p-1 bg-white rounded-xl shadow-sm z-10 border border-stone-100">
                            <img src="<?= htmlspecialchars($app['icon']) ?>" alt="Icon" class="w-10 h-10 rounded-lg object-contain bg-stone-50" onerror="this.src='https://placehold.co/40x40/f5f5f4/78716c?text=App'">
                        </div>
                    </div>
                    
                    <!-- Content Info -->
                    <div class="p-5 pt-6 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h3 class="font-bold text-stone-900 group-hover:text-brand-600 transition-colors truncate">
                                    <?= htmlspecialchars($app['name']) ?>
                                </h3>
                                <div class="flex items-center gap-1 font-semibold text-stone-700 shrink-0">
                                    <svg class="w-3.5 h-3.5 text-amber-500 fill-amber-500" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    <span class="text-sm"><?= number_format((float)$app['rating'], 1) ?></span>
                                </div>
                            </div>
                            <p class="text-xs text-stone-500 mb-3 truncate"><?= htmlspecialchars($app['developer']) ?> &bull; <?= htmlspecialchars($app['category_name']) ?></p>
                        </div>
                        
                        <p class="text-xs text-stone-600 line-clamp-2 leading-relaxed mb-4">
                            <?= htmlspecialchars($app['description']) ?>
                        </p>
                        
                        <!-- Badges -->
                        <div class="flex items-center gap-1.5 mt-auto">
                            <?php if ($app['is_open_source']): ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 font-mono">Open Source</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-stone-100 text-stone-600 font-mono"><?= htmlspecialchars($app['license']) ?></span>
                            <?php endif; ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-stone-100 text-stone-600 font-mono truncate max-w-[100px]"><?= htmlspecialchars($app['platforms']) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================= -->
<!-- ALTERNATIVES SECTION                          -->
<!-- ============================================= -->
<section id="alternatives" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="max-w-3xl mx-auto text-center mb-14">
            <span class="text-xs font-semibold text-brand-600 uppercase tracking-wider">Bebas Biaya Berlangganan</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-stone-900 mt-2 font-heading tracking-tight">
                Alternatif legal untuk<br><span class="font-serif italic text-brand-600">software komersial</span>
            </h2>
            <p class="text-sm text-stone-500 mt-3 max-w-xl mx-auto">
                Tidak perlu langganan bulanan mahal. Berikut padanan open-source yang siap menggantikan workflow Anda.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($alternativeApps as $app): ?>
                <?php $commercialAlternatives = array_values(array_filter(array_map('trim', explode(',', $app['alternative_to'])))); ?>
                <div class="ui-card rounded-2xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="text-[10px] font-bold text-rose-500 uppercase tracking-wider mb-2 font-mono">Alternatif legal untuk</div>
                        <div class="flex items-center gap-3 pb-4 border-b border-stone-100">
                            <span class="text-sm text-stone-400 line-through truncate"><?= htmlspecialchars($commercialAlternatives[0] ?? $app['alternative_to']) ?></span>
                            <span class="text-stone-300" aria-hidden="true">&rarr;</span>
                            <span class="text-base font-bold text-stone-900 truncate"><?= htmlspecialchars($app['name']) ?></span>
                        </div>
                        <p class="text-xs text-stone-500 mt-4 leading-relaxed"><?= htmlspecialchars($app['description']) ?></p>
                    </div>
                    <div class="mt-5 pt-4 border-t border-stone-100 flex items-center justify-between text-xs">
                        <span class="font-mono text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full text-[10px] font-semibold border border-emerald-200"><?= htmlspecialchars($app['license']) ?></span>
                        <a href="?page=detail&id=<?= (int)$app['id'] ?>" class="font-semibold text-stone-600 hover:text-brand-600 transition-colors">Lihat profil &rarr;</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (empty($alternativeApps)): ?>
            <p class="mt-6 text-center text-sm text-stone-500">Alternatif software akan muncul setelah data katalog tersedia.</p>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================= -->
<!-- TRANSPARENCY SECTION                          -->
<!-- ============================================= -->
<section id="transparency" class="py-20 bg-stone-50/50 border-y border-stone-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
            <div class="lg:col-span-5 space-y-5">
                <span class="text-xs font-semibold text-brand-600 uppercase tracking-wider">Standar Integritas</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-stone-900 font-heading tracking-tight">
                    Bagaimana kami<br><span class="font-serif italic text-brand-600">memverifikasi</span> software?
                </h2>
                <p class="text-sm text-stone-500 leading-relaxed">
                    Akses ke software berkualitas adalah hak setiap pengguna, tanpa kompromi keamanan data atau melanggar hak cipta pengembang.
                </p>
                <a href="?page=register" class="inline-flex items-center gap-2 bg-stone-900 hover:bg-stone-800 text-white font-semibold pl-5 pr-2 py-2 rounded-full text-xs transition-colors group">
                    <span>Buat akun untuk bookmark</span>
                    <span class="w-6 h-6 rounded-full bg-white/15 flex items-center justify-center group-hover:bg-white/25 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7v10"></path></svg>
                    </span>
                </a>
            </div>
            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <?php
                $steps = [
                    ['num' => '01', 'title' => 'Verifikasi Tautan Asli', 'desc' => 'Semua tautan diverifikasi langsung ke Google Play Store atau website resmi pengembang.'],
                    ['num' => '02', 'title' => 'Audit Usabilitas Gratis', 'desc' => 'Aplikasi harus memiliki fungsi utama yang benar-benar dapat digunakan secara gratis.'],
                    ['num' => '03', 'title' => 'Transparansi Lisensi', 'desc' => 'Open-Source sejati (GPL/MIT) tidak dicampuradukkan dengan Freeware proprietary.'],
                    ['num' => '04', 'title' => 'Bebas Malware', 'desc' => 'Kebijakan nol toleransi terhadap APK modifikasi ilegal (crack, unlocked paywall).'],
                ];
                foreach ($steps as $step): ?>
                    <div class="p-6 rounded-2xl border border-stone-200 bg-white hover:border-brand-200 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center font-mono font-bold text-xs mb-4"><?= $step['num'] ?></div>
                        <h3 class="text-sm font-bold text-stone-900 mb-2"><?= $step['title'] ?></h3>
                        <p class="text-xs text-stone-500 leading-relaxed"><?= $step['desc'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ============================================= -->
<!-- CTA SECTION                                   -->
<!-- ============================================= -->
<section class="py-24 bg-stone-900 text-white text-center relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-brand-900/20 via-transparent to-transparent pointer-events-none"></div>
    <div class="max-w-3xl mx-auto px-4 relative z-10">
        <h2 class="text-4xl sm:text-5xl font-bold mb-5 font-heading tracking-tight">
            Temukan software gratis <span class="font-serif italic text-brand-400">berkualitas.</span>
        </h2>
        <p class="text-base text-stone-400 mb-10 max-w-xl mx-auto leading-relaxed">
            Simpan software pilihan Anda dan temukan alternatif legal untuk aplikasi berbayar yang biasa digunakan.
        </p>
        <a href="?page=register" class="inline-flex items-center gap-2 bg-white text-stone-900 hover:bg-stone-100 font-bold pl-7 pr-2.5 py-2.5 rounded-full text-sm transition-colors group">
            <span>Buat akun gratis</span>
            <span class="w-8 h-8 rounded-full bg-stone-900 text-white flex items-center justify-center group-hover:bg-stone-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7v10"></path></svg>
            </span>
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
