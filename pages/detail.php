<?php
require_once __DIR__ . '/../includes/header.php';

$appId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name 
    FROM apps a 
    JOIN categories c ON a.category_id = c.id 
    WHERE a.id = ?
");
$stmt->execute([$appId]);
$app = $stmt->fetch();

if (!$app) {
    echo '<div class="max-w-4xl mx-auto px-4 py-20 text-center">
            <h1 class="text-2xl font-bold text-navy mb-2">Software Tidak Ditemukan</h1>
            <p class="text-xs text-slate-500 mb-6">Aplikasi yang Anda cari mungkin telah dipindahkan atau belum terdaftar.</p>
            <a href="?page=explore" class="px-4 py-2 bg-navy text-white text-xs font-semibold rounded-lg">Kembali ke Katalog</a>
          </div>';
require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Check bookmark state
$isBookmarked = false;
if (isset($_SESSION['user_id'])) {
    $stmtBm = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND app_id = ?");
    $stmtBm->execute([$_SESSION['user_id'], $app['id']]);
    $isBookmarked = (bool)$stmtBm->fetch();
}

// Related apps in the same category
$stmtRelated = $pdo->prepare("
    SELECT a.*, c.name as category_name 
    FROM apps a 
    JOIN categories c ON a.category_id = c.id 
    WHERE a.category_id = ? AND a.id != ? 
    ORDER BY a.rating DESC 
    LIMIT 3
");
$stmtRelated->execute([$app['category_id'], $app['id']]);
$relatedApps = $stmtRelated->fetchAll();
?>

<!-- Detail Header & Breadcrumb -->
<div class="bg-white border-b border-slate-200 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs font-mono text-slate-500 mb-6">
            <a href="?page=home" class="hover:text-brand-600">Home</a>
            <span>/</span>
            <a href="?page=explore" class="hover:text-brand-600">Explore</a>
            <span>/</span>
            <a href="?page=explore&c=<?= $app['category_id'] ?>" class="hover:text-brand-600"><?= htmlspecialchars($app['category_name']) ?></a>
            <span>/</span>
            <span class="text-slate-800 font-semibold truncate"><?= htmlspecialchars($app['name']) ?></span>
        </nav>

        <!-- Product Identity Row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            
            <div class="flex items-start sm:items-center gap-5">
                <img 
                    src="<?= htmlspecialchars($app['icon']) ?>" 
                    alt="<?= htmlspecialchars($app['name']) ?> logo" 
                    class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-contain p-2 border border-slate-200 bg-white shadow-sm flex-shrink-0"
                    onerror="this.src='https://placehold.co/80x80/f1f5f9/475569?text=App'"
                >
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-navy font-heading">
                            <?= htmlspecialchars($app['name']) ?>
                        </h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[11px] font-mono font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            Verified Legal
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600">
                        <span>Oleh <strong class="text-navy font-semibold"><?= htmlspecialchars($app['developer']) ?></strong></span>
                        <span>&bull;</span>
                        <?php if ($app['version'] !== '—'): ?>
                            <span class="font-mono text-slate-500">v<?= htmlspecialchars($app['version']) ?></span>
                            <span>&bull;</span>
                        <?php endif; ?>
                        <?php if ((float)$app['rating'] > 0): ?>
                            <div class="flex items-center gap-1 font-semibold text-slate-800">
                                <svg class="w-3.5 h-3.5 text-amber-500 fill-amber-500" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <span><?= number_format((float)$app['rating'], 1) ?></span>
                                <span class="text-slate-400 font-normal">(<?= number_format($app['reviews_count']) ?> ulasan)</span>
                            </div>
                        <?php else: ?>
                            <span class="text-slate-500">Belum ada ulasan komunitas</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-3">
                <button 
                    type="button" 
                    onclick="toggleBookmark(<?= $app['id'] ?>, this)"
                    aria-pressed="<?= $isBookmarked ? 'true' : 'false' ?>"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 hover:border-slate-400 text-xs font-semibold flex items-center gap-2 transition-colors <?= $isBookmarked ? 'text-brand-600 bg-brand-50 border-brand-200' : 'text-slate-700 bg-white' ?>"
                >
                    <svg class="w-4 h-4" fill="<?= $isBookmarked ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                    <span><?= $isBookmarked ? 'Tersimpan' : 'Simpan' ?></span>
                </button>

                <a 
                    id="header-download-btn"
                    href="<?= htmlspecialchars($app['play_store_url']) ?>" 
                    target="_blank" 
                    rel="noopener noreferrer" 
                    class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-brand-600 text-white text-xs font-semibold flex items-center gap-2 transition-all shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    <span id="header-download-label">Unduh Sekarang</span>
                </a>
            </div>

        </div>

    </div>
</div>

<!-- Main Content Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Main Editorial Review & Comparison -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Section 1: Overview -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
                <h2 class="text-lg font-bold text-navy font-heading mb-4">Tentang Software</h2>
                <p class="text-sm text-slate-700 leading-relaxed font-normal">
                    <?= nl2br(htmlspecialchars($app['description'])) ?>
                </p>

                <!-- Platform Support Matrix -->
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono mb-3">Dukungan Platform</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php 
                        $plats = explode(',', $app['platforms']);
                        foreach ($plats as $p): 
                            $trimP = trim($p);
                        ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-800 font-mono">
                                <?= htmlspecialchars($trimP) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Section 2: Alternative Comparison -->
            <?php if (!empty($app['alternative_to'])): ?>
                <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                        <h2 class="text-base font-bold text-navy font-heading">
                            Alternatif Legal untuk Software Berbayar
                        </h2>
                    </div>
                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                        Aplikasi ini direkomendasikan sebagai pengganti bebas biaya untuk aplikasi proprietary berikut:
                    </p>
                    
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-xs font-mono text-slate-400 uppercase tracking-wider">Software Komersial:</span>
                            <div class="text-sm font-semibold text-rose-700 mt-0.5 line-through decoration-slate-400">
                                <?= htmlspecialchars($app['alternative_to']) ?>
                            </div>
                        </div>
                        <div class="sm:text-right">
                            <span class="text-xs font-mono text-slate-400 uppercase tracking-wider">Alternatif Gratis:</span>
                            <div class="text-sm font-bold text-emerald-700 mt-0.5">
                                <?= htmlspecialchars($app['name']) ?> (<?= htmlspecialchars($app['license']) ?>)
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Section 3: Licensing & Verification Audit -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
                <h2 class="text-base font-bold text-navy font-heading mb-4">
                    Audit Lisensi &amp; Keamanan Sumber
                </h2>
                
                <div class="space-y-4 text-xs">
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5 border border-emerald-100/60">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <strong class="text-navy font-semibold block mb-0.5">Tautan Asli Play Store Terverifikasi</strong>
                            <p class="text-slate-600">Software ini didistribusikan melalui kanal resmi tanpa modifikasi file APK, menjaga integritas kode dan keaslian tanda tangan digital pengembang.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center flex-shrink-0 mt-0.5 border border-brand-100/60">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <strong class="text-navy font-semibold block mb-0.5">
                                <?= $app['is_open_source'] ? 'Lisensi Kode Sumber Terbuka (Open Source)' : 'Model Lisensi Freeware Legal' ?>
                            </strong>
                            <p class="text-slate-600">
                                Menggunakan skema lisensi <strong class="text-navy"><?= htmlspecialchars($app['license']) ?></strong>.
                                <?= $app['is_open_source'] ? 'Kode sumber dapat diaudit oleh publik dan bebas dipakai untuk tujuan komersial maupun pribadi sesuai ketentuan lisensi terkait.' : 'Dapat diunduh dan digunakan tanpa kewajiban biaya langganan untuk fungsionalitas intinya.' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right 1 Col: Technical Specs & Related Apps -->
        <div class="space-y-6">
            
            <!-- Smart Download Panel -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 font-mono">
                        Unduh Software
                    </h3>
                    <span id="detected-device-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-brand-50 text-brand-600 border border-brand-100 font-mono hidden">
                        📍 Auto-detect
                    </span>
                </div>

                <?php
                // Build platform download options from app data
                $platforms = array_map('trim', explode(',', $app['platforms']));
                $hasPlayStore  = !empty($app['play_store_url']);
                $hasWebsite    = !empty($app['website_url']);

                // Map platform strings to structured data
                $platformMap = [];
                foreach ($platforms as $p) {
                    $pl = strtolower($p);
                    if (str_contains($pl, 'android') && $hasPlayStore) {
                        $platformMap['android'] = ['label' => 'Android', 'icon' => 'android', 'url' => $app['play_store_url'], 'badge' => 'Play Store'];
                    }
                    if (str_contains($pl, 'ios') && $hasWebsite) {
                        $platformMap['ios'] = ['label' => 'iOS', 'icon' => 'ios', 'url' => $app['website_url'], 'badge' => 'App Store'];
                    }
                    if (str_contains($pl, 'windows') && $hasWebsite) {
                        $platformMap['windows'] = ['label' => 'Windows', 'icon' => 'windows', 'url' => $app['website_url'], 'badge' => 'Web Resmi'];
                    }
                    if (str_contains($pl, 'macos') || str_contains($pl, 'mac')) {
                        $platformMap['mac'] = ['label' => 'macOS', 'icon' => 'mac', 'url' => $hasWebsite ? $app['website_url'] : $app['play_store_url'], 'badge' => 'Web Resmi'];
                    }
                    if (str_contains($pl, 'linux') && $hasWebsite) {
                        $platformMap['linux'] = ['label' => 'Linux', 'icon' => 'linux', 'url' => $app['website_url'], 'badge' => 'Web Resmi'];
                    }
                    if (str_contains($pl, 'web') && $hasWebsite) {
                        $platformMap['web'] = ['label' => 'Web App', 'icon' => 'web', 'url' => $app['website_url'], 'badge' => 'Buka di Browser'];
                    }
                }
                // Fallback if nothing matched
                if (empty($platformMap) && $hasPlayStore) {
                    $platformMap['android'] = ['label' => 'Play Store', 'icon' => 'android', 'url' => $app['play_store_url'], 'badge' => 'Play Store'];
                }
                ?>

                <!-- Platform Tabs -->
                <div class="flex flex-wrap gap-1.5 mb-4" id="platform-tabs">
                    <?php foreach ($platformMap as $key => $plat): ?>
                        <button 
                            type="button"
                            data-platform="<?= $key ?>"
                            data-url="<?= htmlspecialchars($plat['url']) ?>"
                            data-label="<?= htmlspecialchars($plat['label']) ?>"
                            data-badge="<?= htmlspecialchars($plat['badge']) ?>"
                            onclick="selectPlatform(this)"
                            class="platform-tab flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all
                                   border-slate-200 bg-slate-50 text-slate-600 hover:border-slate-400 hover:text-slate-900"
                        >
                            <?php
                            $icons = [
                                'android' => '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.523 15.341a.93.93 0 01-.928.928.93.93 0 01-.929-.928V9.91a.93.93 0 01.929-.929.93.93 0 01.928.929v5.432zm-10.118 0a.93.93 0 01-.929.928.93.93 0 01-.928-.928V9.91a.93.93 0 01.928-.929.93.93 0 01.929.929v5.432zM7.58 5.63l-.98-1.796a.204.204 0 01.079-.277.204.204 0 01.277.08l.993 1.82A6.2 6.2 0 0112 4.8a6.2 6.2 0 014.051 1.657l.993-1.82a.204.204 0 01.277-.08.204.204 0 01.079.277L16.42 5.63A5.93 5.93 0 0119.2 10.8H4.8A5.93 5.93 0 017.58 5.63zm2.887.917a.484.484 0 00.484-.484.484.484 0 00-.484-.484.484.484 0 00-.484.484c0 .267.217.484.484.484zm3.066 0a.484.484 0 00.484-.484.484.484 0 00-.484-.484.484.484 0 00-.484.484c0 .267.217.484.484.484zM4.2 10.8h15.6v7.2a1.2 1.2 0 01-1.2 1.2h-1.2v2.4a1.2 1.2 0 01-1.2 1.2 1.2 1.2 0 01-1.2-1.2V19.2H9v2.4a1.2 1.2 0 01-1.2 1.2 1.2 1.2 0 01-1.2-1.2V19.2H5.4A1.2 1.2 0 014.2 18V10.8z"/></svg>',
                                'ios'     => '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>',
                                'windows' => '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.9-1.801"/></svg>',
                                'mac'     => '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>',
                                'linux'   => '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.504 0c-.155 0-.315.008-.48.021-4.226.333-3.105 4.807-3.17 6.298-.076 1.092-.3 1.953-1.05 3.02-.885 1.051-2.127 2.75-2.716 4.521-.278.832-.41 1.684-.287 2.489a.424.424 0 00-.11.135c-.26.268-.45.6-.663.839-.199.199-.485.267-.797.4-.313.136-.658.269-.864.68-.09.189-.136.394-.132.602 0 .199.027.4.055.536.058.399.116.728.04.97-.249.68-.28 1.145-.106 1.484.174.334.535.47.94.601.81.2 1.91.135 2.774.6.926.466 1.866.67 2.616.47.526-.116.97-.464 1.208-.946.587-.003 1.23-.269 2.26-.334.699-.058 1.574.267 2.577.2.025.134.063.198.114.333l.003.003c.391.778 1.113 1.132 1.884 1.071.771-.06 1.592-.536 2.257-1.306.631-.765 1.solar-1.929 1.479-2.526-.743.901-1.805 1.627-2.847 1.625-.857-.012-1.681-.309-2.168-.975-.087-.12-.17-.267-.245-.417-.23.017-.454.016-.676-.006-.335-.033-.64-.123-.858-.306a.5.5 0 01-.167-.375c0-.19.058-.398.142-.577.249-.547.655-.863.877-.948.022-.01.042-.02.06-.031.13-.057.254-.13.361-.226.109-.092.202-.206.279-.335.075-.13.132-.272.167-.423.036-.15.053-.312.053-.478V14.3c-.001-.233-.036-.429-.096-.6.032-.016.063-.034.092-.054.225-.157.42-.37.564-.627a2.54 2.54 0 00.277-.86c.044-.295.04-.607-.028-.912-.01-.043-.023-.085-.038-.126.073-.036.143-.077.208-.123.229-.16.406-.38.534-.64.128-.261.204-.557.226-.878.023-.323-.002-.669-.084-1.02-.16-.705-.507-1.378-.884-1.832.013-.019.024-.04.034-.063.13-.271.189-.572.176-.867-.013-.294-.098-.578-.246-.833a2.165 2.165 0 00-.629-.677c-.274-.177-.61-.28-.97-.28-.362 0-.713.106-1.01.301a2.19 2.19 0 00-.668.768c-.16.306-.24.65-.24 1.007 0 .268.046.539.136.8-.405.346-.786.734-1.113 1.15-.265.333-.479.685-.64 1.05-.162.367-.265.746-.313 1.136-.048.389-.043.788.012 1.183.055.395.161.786.311 1.162.07.179.15.356.235.528a2.31 2.31 0 00-.39-.12c-.226-.043-.454-.043-.666.009a1.2 1.2 0 00-.535.295 1.022 1.022 0 00-.291.508 1.36 1.36 0 00-.02.564c.043.23.14.457.273.664.274.424.638.75 1.044.941a5.5 5.5 0 00.29.12l.044.017c-.012.04-.023.084-.03.13-.014.074-.019.152-.015.23.005.077.02.153.045.224.045.142.121.268.22.374a.967.967 0 00.368.228 1.5 1.5 0 00.455.071c.157 0 .317-.025.47-.073.153-.048.3-.12.43-.213.248-.185.432-.44.517-.733.084-.293.067-.609-.048-.882l-.002-.003a.45.45 0 00-.044-.078c.047-.03.093-.061.138-.094.268-.194.497-.437.672-.712a2.25 2.25 0 00.3-.937c.035-.336.004-.682-.093-1.004a2.8 2.8 0 00-.447-.9c.038-.005.076-.013.114-.024.194-.059.374-.162.527-.302.152-.141.274-.315.36-.513a1.7 1.7 0 00.122-.625c0-.22-.04-.437-.119-.643a1.8 1.8 0 00-.352-.548 1.9 1.9 0 00-.562-.38 1.8 1.8 0 00-.706-.137h-.003z"/></svg>',
                                'web'     => '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
                            ];
                            echo $icons[$key] ?? '';
                            ?>
                            <span><?= htmlspecialchars($plat['label']) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Active Platform Download Info -->
                <div id="download-info" class="bg-slate-50 rounded-xl p-4 border border-slate-200 mb-3 text-xs hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-slate-800" id="dl-platform-name">—</span>
                        <span class="font-mono text-[10px] text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200" id="dl-source-badge">—</span>
                    </div>
                    <p class="text-slate-500 text-[11px]" id="dl-hint">Pilih platform di atas untuk melihat tautan unduh</p>
                </div>

                <a
                    id="download-action-btn"
                    href="#"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-full bg-slate-900 hover:bg-brand-600 text-white text-xs font-bold py-3 rounded-xl transition-all flex items-center justify-center gap-2 shadow-sm opacity-50 pointer-events-none"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    <span id="download-btn-label">Pilih Platform Dulu</span>
                </a>

                <p class="text-center text-[10px] text-slate-400 mt-2 font-mono">
                    <?= $app['version'] !== '—' ? 'v' . htmlspecialchars($app['version']) . ' • ' : '' ?><?= htmlspecialchars($app['license']) ?>
                </p>
            </div>

            <!-- Auto-detect Script -->
            <script>
            (function() {
                // Map platform keys from PHP to JS-detectable platforms
                const ua = navigator.userAgent.toLowerCase();
                let detectedKey = null;

                if (/android/.test(ua))                    detectedKey = 'android';
                else if (/iphone|ipad|ipod/.test(ua))     detectedKey = 'ios';
                else if (/windows/.test(ua))               detectedKey = 'windows';
                else if (/mac/.test(ua))                   detectedKey = 'mac';
                else if (/linux/.test(ua))                 detectedKey = 'linux';

                const deviceLabels = {
                    android: 'Android terdeteksi',
                    ios:     'iOS terdeteksi',
                    windows: 'Windows terdeteksi',
                    mac:     'macOS terdeteksi',
                    linux:   'Linux terdeteksi',
                };

                window.selectPlatform = function(btn) {
                    // Reset all tabs
                    document.querySelectorAll('.platform-tab').forEach(t => {
                        t.classList.remove('bg-slate-900', 'text-white', 'border-slate-900');
                        t.classList.add('bg-slate-50', 'text-slate-600', 'border-slate-200');
                    });
                    // Activate selected
                    btn.classList.remove('bg-slate-50', 'text-slate-600', 'border-slate-200');
                    btn.classList.add('bg-slate-900', 'text-white', 'border-slate-900');

                    const url    = btn.dataset.url;
                    const label  = btn.dataset.label;
                    const badge  = btn.dataset.badge;

                    // Update info box
                    document.getElementById('dl-platform-name').textContent = label;
                    document.getElementById('dl-source-badge').textContent  = badge;
                    document.getElementById('dl-hint').textContent          = 'Tautan terverifikasi langsung ke sumber resmi pengembang.';
                    document.getElementById('download-info').classList.remove('hidden');

                    // Update download button
                    const dlBtn = document.getElementById('download-action-btn');
                    dlBtn.href  = url;
                    dlBtn.classList.remove('opacity-50', 'pointer-events-none');
                    document.getElementById('download-btn-label').textContent = 'Unduh untuk ' + label;

                    // Update header button too
                    const hBtn = document.getElementById('header-download-btn');
                    if (hBtn) {
                        hBtn.href = url;
                        document.getElementById('header-download-label').textContent = 'Unduh untuk ' + label;
                    }
                };

                // Auto-select on page load
                window.addEventListener('DOMContentLoaded', function() {
                    const badge = document.getElementById('detected-device-badge');

                    if (detectedKey && deviceLabels[detectedKey]) {
                        badge.textContent = '📍 ' + deviceLabels[detectedKey];
                        badge.classList.remove('hidden');
                    }

                    const targetTab = document.querySelector(`.platform-tab[data-platform="${detectedKey}"]`);
                    if (targetTab) {
                        selectPlatform(targetTab);
                    } else {
                        // Fallback: auto-select the first tab available
                        const firstTab = document.querySelector('.platform-tab');
                        if (firstTab) selectPlatform(firstTab);
                    }
                });
            })();
            </script>

            <!-- Software Specs Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3 text-xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 font-mono pb-3 border-b border-slate-100">
                    Spesifikasi Software
                </h3>
                <div class="flex justify-between">
                    <span class="text-slate-500">Kategori</span>
                    <a href="?page=explore&c=<?= $app['category_id'] ?>" class="font-semibold text-brand-600 hover:underline">
                        <?= htmlspecialchars($app['category_name']) ?>
                    </a>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Lisensi</span>
                    <span class="font-mono font-semibold text-navy"><?= htmlspecialchars($app['license']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Tipe</span>
                    <span class="font-semibold <?= $app['is_open_source'] ? 'text-emerald-600' : 'text-slate-700' ?>">
                        <?= $app['is_open_source'] ? 'Open-Source' : 'Freeware' ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Unduhan</span>
                    <span class="font-mono font-semibold text-navy"><?= htmlspecialchars((string)($app['download_count'] ?? 'Belum tersedia')) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Pengembang</span>
                    <span class="font-semibold text-navy truncate max-w-[140px] text-right"><?= htmlspecialchars($app['developer']) ?></span>
                </div>
                <?php if ($app['min_ram'] !== null || $app['recommended_ram'] !== null): ?>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">RAM</span>
                        <span class="font-semibold text-navy text-right">
                            <?= $app['min_ram'] !== null ? htmlspecialchars((string)$app['min_ram']) . ' GB min.' : 'Tidak tersedia' ?>
                            <?= $app['recommended_ram'] !== null ? ' / ' . htmlspecialchars((string)$app['recommended_ram']) . ' GB disarankan' : '' ?>
                        </span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($app['min_os'])): ?>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">OS minimum</span>
                        <span class="font-semibold text-navy text-right"><?= htmlspecialchars($app['min_os']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($app['winget_id'])): ?>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Winget</span>
                        <code class="font-mono font-semibold text-navy text-right break-all"><?= htmlspecialchars($app['winget_id']) ?></code>
                    </div>
                <?php endif; ?>
                <?php if (!empty($app['website_url'])): ?>
                    <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-slate-500">Website Resmi</span>
                        <a href="<?= htmlspecialchars($app['website_url']) ?>" target="_blank" rel="noopener noreferrer" class="text-brand-600 hover:underline font-semibold flex items-center gap-1">
                            <span>Kunjungi</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Related Applications in Category -->
            <?php if (!empty($relatedApps)): ?>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 font-mono pb-2 border-b border-slate-100">
                        Software Terkait Lainnya
                    </h3>

                    <div class="space-y-3">
                        <?php foreach ($relatedApps as $r): ?>
                            <a href="?page=detail&id=<?= $r['id'] ?>" class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors group">
                                <img 
                                    src="<?= htmlspecialchars($r['icon']) ?>" 
                                    alt="<?= htmlspecialchars($r['name']) ?>" 
                                    class="w-9 h-9 rounded-lg object-contain p-1 border border-slate-200 bg-white"
                                    onerror="this.src='https://placehold.co/36x36/f1f5f9/475569?text=App'"
                                >
                                <div class="flex-grow min-w-0">
                                    <h4 class="text-xs font-bold text-navy group-hover:text-brand-600 truncate transition-colors">
                                        <?= htmlspecialchars($r['name']) ?>
                                    </h4>
                                    <div class="text-[10px] text-slate-500 font-mono">
                                        <?= htmlspecialchars($r['license']) ?>
                                        <?php if ((float)$r['rating'] > 0): ?>
                                            &bull; ★ <?= number_format((float)$r['rating'], 1) ?>
                                        <?php else: ?>
                                            &bull; Belum dinilai
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-brand-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
