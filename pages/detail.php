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
                        <span class="font-mono text-slate-500">v<?= htmlspecialchars($app['version']) ?></span>
                        <span>&bull;</span>
                        <div class="flex items-center gap-1 font-semibold text-slate-800">
                            <svg class="w-3.5 h-3.5 text-amber-500 fill-amber-500" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <span><?= number_format((float)$app['rating'], 1) ?></span>
                            <span class="text-slate-400 font-normal">(<?= number_format($app['reviews_count']) ?> ulasan)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-3">
                <button 
                    type="button" 
                    onclick="toggleBookmark(<?= $app['id'] ?>, this)"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 hover:border-slate-400 text-xs font-semibold flex items-center gap-2 transition-colors <?= $isBookmarked ? 'text-brand-600 bg-brand-50 border-brand-200' : 'text-slate-700 bg-white' ?>"
                >
                    <svg class="w-4 h-4" fill="<?= $isBookmarked ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                    <span><?= $isBookmarked ? 'Tersimpan' : 'Simpan' ?></span>
                </button>

                <a 
                    href="<?= htmlspecialchars($app['play_store_url']) ?>" 
                    target="_blank" 
                    rel="noopener noreferrer" 
                    class="px-5 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold flex items-center gap-2 transition-colors shadow-sm"
                >
                    <span>Buka Tautan Resmi</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
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
            
            <!-- Metadata Box -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 font-mono pb-3 border-b border-slate-100">
                    Spesifikasi Software
                </h3>

                <div class="space-y-3 text-xs">
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
                        <span class="text-slate-500">Tipe Lisensi</span>
                        <span class="font-semibold <?= $app['is_open_source'] ? 'text-emerald-600' : 'text-slate-700' ?>">
                            <?= $app['is_open_source'] ? 'Open-Source' : 'Freeware' ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Total Unduhan</span>
                        <span class="font-mono font-semibold text-navy"><?= htmlspecialchars($app['download_count']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Pengembang</span>
                        <span class="font-semibold text-navy"><?= htmlspecialchars($app['developer']) ?></span>
                    </div>
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

                <div class="pt-2">
                    <a 
                        href="<?= htmlspecialchars($app['play_store_url']) ?>" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        class="w-full bg-navy hover:bg-slate-800 text-white text-xs font-semibold py-2.5 rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-sm"
                    >
                        <span>Unduh di Play Store</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                </div>
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
                                        <?= htmlspecialchars($r['license']) ?> &bull; ★ <?= number_format((float)$r['rating'], 1) ?>
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
