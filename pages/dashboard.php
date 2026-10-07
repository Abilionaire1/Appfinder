<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

// Fetch user profile
$stmtUser = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$user = $stmtUser->fetch();

if (!$user) {
    unset($_SESSION['user_id'], $_SESSION['role']);
    header('Location: ?page=login');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

// Fetch bookmarked apps
$stmtBm = $pdo->prepare("
    SELECT a.*, c.name as category_name, b.created_at as bookmarked_at 
    FROM bookmarks b 
    JOIN apps a ON b.app_id = a.id 
    JOIN categories c ON a.category_id = c.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$stmtBm->execute([$_SESSION['user_id']]);
$bookmarkedApps = $stmtBm->fetchAll();
?>

<div class="bg-white border-b border-slate-200 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-navy text-brand-400 flex items-center justify-center font-bold text-lg font-mono">
                    <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-navy font-heading">
                        Akun Saya
                    </h1>
                    <div class="text-xs text-slate-500 mt-0.5 font-mono">
                        <?= htmlspecialchars($user['email'] ?? '') ?> &bull; Bergabung <?= !empty($user['created_at']) ? date('M Y', strtotime($user['created_at'])) : '-' ?>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="?page=explore" class="px-4 py-2 rounded-lg border border-slate-200 hover:border-slate-300 text-xs font-semibold text-slate-700 bg-white transition-colors">
                    Jelajahi Software Baru
                </a>
                <a href="?page=logout" class="px-4 py-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition-colors">
                    Keluar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-navy font-heading">Software Tersimpan (Wishlist)</h2>
            <p class="text-xs text-slate-500">Koleksi perangkat lunak legal yang telah Anda tandai untuk diunduh atau dipelajari.</p>
        </div>
        <span class="text-xs font-mono bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-semibold">
            <?= count($bookmarkedApps) ?> Item
        </span>
    </div>

    <?php if (!empty($bookmarkedApps)): ?>
        <!-- Stack Generator / Winget Bundle Feature -->
        <div class="mb-8 p-6 bg-gradient-to-r from-slate-900 to-navy rounded-2xl shadow-lg border border-slate-800 text-white relative overflow-hidden">
            <!-- Decorative blur -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 bg-brand-500 rounded-full mix-blend-screen filter blur-[80px] opacity-30"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-brand-500/20 text-brand-300 border border-brand-500/30 uppercase tracking-wider mb-3">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Fitur Eksklusif Login
                    </div>
                    <h3 class="text-xl font-bold font-heading mb-1">Stack Bundle Generator</h3>
                    <p class="text-xs text-slate-400 max-w-lg">
                        Selesai install ulang Windows? Salin command CLI di bawah untuk menginstall seluruh software tersimpan secara otomatis tanpa perlu mengunduh installer satu per satu.
                    </p>
                </div>
                
                <div class="bg-slate-950/50 p-1.5 rounded-xl border border-slate-800 w-full md:w-auto">
                    <?php 
                        $wingetApps = [];
                        foreach ($bookmarkedApps as $app) {
                            // Only include desktop software (Windows support) for Winget
                            if (str_contains(strtolower($app['platforms']), 'windows') || str_contains(strtolower($app['platforms']), 'mac') || str_contains(strtolower($app['platforms']), 'linux')) {
                                $wingetApps[] = '"' . str_replace('"', '\"', $app['name']) . '"';
                            }
                        }
                        if (empty($wingetApps)) {
                            $cliCommand = "# Tambahkan software desktop (PC) ke dalam wishlist untuk membuat bundle.";
                        } else {
                            $cliCommand = "winget install -e --accept-source-agreements --accept-package-agreements " . implode(" ", $wingetApps);
                        }
                    ?>
                    <div class="flex items-center gap-2">
                        <input 
                            type="text" 
                            id="winget-command" 
                            readonly 
                            value="<?= htmlspecialchars($cliCommand) ?>" 
                            class="bg-transparent border-none text-[11px] font-mono text-emerald-400 w-full md:w-80 px-3 py-2 focus:ring-0 outline-none"
                        >
                        <button 
                            onclick="copyWingetCommand()"
                            class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 border border-white/5"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            <span id="copy-text">Salin</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <script>
                function copyWingetCommand() {
                    var copyText = document.getElementById("winget-command");
                    copyText.select();
                    copyText.setSelectionRange(0, 99999);
                    navigator.clipboard.writeText(copyText.value);
                    
                    var copyBtnText = document.getElementById("copy-text");
                    copyBtnText.innerText = "Tersalin!";
                    setTimeout(function() {
                        copyBtnText.innerText = "Salin";
                    }, 2000);
                }
            </script>
        </div>
    <?php endif; ?>

    <?php if (empty($bookmarkedApps)): ?>
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
            </div>
            <h3 class="text-base font-bold text-navy mb-1 font-heading">Belum Ada Software Tersimpan</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mb-6">
                Temukan software legal berkualitas di katalog kami dan klik ikon penanda (bookmark) untuk menyimpannya di sini.
            </p>
            <a href="?page=explore" class="inline-block px-5 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold transition-colors shadow-sm">
                Jelajahi Software Sekarang
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($bookmarkedApps as $app): ?>
                <div class="ui-card rounded-xl p-5 flex flex-col justify-between group">
                    <div>
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
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        <?= htmlspecialchars($app['developer']) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Remove from Bookmarks Button -->
                            <button 
                                type="button" 
                                onclick="toggleBookmark(<?= $app['id'] ?>, this); setTimeout(() => location.reload(), 400);"
                                class="p-1.5 rounded-lg border border-slate-200 text-brand-600 bg-brand-50 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-colors"
                                title="Hapus dari tersimpan"
                            >
                                <svg class="w-3.5 h-3.5" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="flex items-center gap-1.5 mb-2.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 font-mono">
                                <?= htmlspecialchars($app['category_name']) ?>
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold <?= $app['is_open_source'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-cyan-50 text-brand-700 border border-brand-200' ?> font-mono">
                                <?= htmlspecialchars($app['license']) ?>
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed mb-3">
                            <?= htmlspecialchars($app['description']) ?>
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="font-mono text-[10px] text-slate-400">
                            Disimpan <?= date('d M Y', strtotime($app['bookmarked_at'])) ?>
                        </span>
                        <div class="flex items-center gap-2">
                            <a 
                                href="?page=detail&id=<?= $app['id'] ?>" 
                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold bg-white hover:bg-slate-50 transition-colors"
                            >
                                Detail
                            </a>
                            <a 
                                href="<?= htmlspecialchars($app['play_store_url']) ?>" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="px-2.5 py-1.5 rounded-lg bg-navy hover:bg-slate-800 text-white text-xs font-semibold transition-colors"
                            >
                                Unduh
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
