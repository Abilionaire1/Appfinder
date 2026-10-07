<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper to count user bookmarks
$bookmarkCount = 0;
if (isset($_SESSION['user_id']) && isset($pdo)) {
    $stmtBm = $pdo->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id = ?");
    $stmtBm->execute([$_SESSION['user_id']]);
    $bookmarkCount = (int)$stmtBm->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
    <title>AppFinder — Direktori Software Legal & Gratis Berkualitas</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Plus Jakarta Sans (Headings) & Inter (UI/Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Inter"', 'sans-serif'],
                        heading: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfeff',
                            100: '#cffafe',
                            200: '#a5f3fc',
                            300: '#67e8f9',
                            400: '#22d3ee',
                            500: '#06b6d4', // Primary Cyan
                            600: '#0891b2',
                            700: '#0e7490',
                            800: '#155e75',
                            900: '#164e63',
                        },
                        navy: {
                            DEFAULT: '#111827',
                            900: '#0b0f19',
                            800: '#111827',
                            700: '#1f2937',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: #111827;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        /* Subtle Engineering Grid Background */
        .bg-tech-grid {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(226, 232, 240, 0.7) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(226, 232, 240, 0.7) 1px, transparent 1px);
        }
        
        /* Controlled Card Layering (Linear/Raycast inspired) */
        .ui-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .ui-card:hover {
            border-color: #CBD5E1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06), 0 1px 3px rgba(15, 23, 42, 0.04);
            transform: translateY(-1px);
        }
        
        /* Precision Focus Styles */
        input:focus, select:focus, button:focus-visible {
            outline: 2px solid #06B6D4;
            outline-offset: 2px;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased flex flex-col min-h-screen">

    <!-- A. Announcement / Discovery Event Bar -->
    <aside class="bg-navy-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-1.5 sm:gap-4 text-center sm:text-left">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-brand-500/20 text-brand-300 border border-brand-500/30 font-mono uppercase tracking-wider">
                    Kurasi 2026
                </span>
                <span class="text-slate-300">
                    Katalog Software Bebas Lisensi Bajakan &amp; Alternatif Legal untuk Kebutuhan Digital.
                </span>
            </div>
            <div class="flex items-center gap-3 text-slate-400 text-[11px]">
                <span class="inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    Tautan Resmi Terverifikasi
                </span>
            </div>
        </div>
    </aside>

    <!-- B. Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            
            <!-- Brand Identity -->
            <div class="flex items-center gap-8">
                <a href="?page=home" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-lg bg-navy flex items-center justify-center text-brand-400 group-hover:bg-brand-600 group-hover:text-white transition-colors shadow-sm">
                        <!-- Shield Logo -->
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight text-navy font-heading flex items-center gap-1 leading-none">
                            appfinder<span class="text-brand-500">.</span>
                        </span>
                        <span class="block text-[9px] font-semibold text-slate-500 tracking-wider uppercase font-mono mt-0.5">
                            100% Verified Software
                        </span>
                    </div>
                </a>

                <!-- Primary Nav Links -->
                <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-slate-600">
                    <a href="?page=home" class="px-3 py-1.5 rounded-md hover:text-navy hover:bg-slate-100 transition-colors <?= (!isset($_GET['page']) || $_GET['page'] === 'home') ? 'text-brand-600 font-semibold bg-brand-50/50' : '' ?>">
                        Home
                    </a>
                    <a href="?page=explore" class="px-3 py-1.5 rounded-md hover:text-navy hover:bg-slate-100 transition-colors <?= (isset($_GET['page']) && $_GET['page'] === 'explore') ? 'text-brand-600 font-semibold bg-brand-50/50' : '' ?>">
                        Explore Software
                    </a>
                    <a href="?page=home#categories" class="px-3 py-1.5 rounded-md hover:text-navy hover:bg-slate-100 transition-colors">
                        Kategori
                    </a>
                    <a href="?page=home#alternatives" class="px-3 py-1.5 rounded-md hover:text-navy hover:bg-slate-100 transition-colors">
                        Alternatif Legal
                    </a>
                    <a href="?page=home#transparency" class="px-3 py-1.5 rounded-md hover:text-navy hover:bg-slate-100 transition-colors">
                        Standar Kurasi
                    </a>
                </nav>
            </div>

            <!-- Header Right Actions -->
            <div class="flex items-center gap-2.5">
                
                <!-- Quick Search Action -->
                <a href="?page=explore" class="p-2 text-slate-500 hover:text-navy hover:bg-slate-100 rounded-lg transition-colors" title="Cari Software" aria-label="Cari Software">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </a>

                <!-- Saved / Wishlist Button -->
                <a href="?page=dashboard" class="relative p-2 text-slate-500 hover:text-navy hover:bg-slate-100 rounded-lg transition-colors" title="Software Tersimpan" aria-label="Software Tersimpan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                    <?php if ($bookmarkCount > 0): ?>
                        <span class="absolute top-1 right-1 w-4 h-4 bg-brand-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center font-mono">
                            <?= $bookmarkCount ?>
                        </span>
                    <?php endif; ?>
                </a>

                <div class="h-4 w-px bg-slate-200 mx-1"></div>

                <!-- Auth Controls -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="?page=dashboard" class="text-xs font-semibold px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 transition-colors">
                        Akun Saya
                    </a>
                    <a href="?page=logout" class="text-xs font-medium text-slate-500 hover:text-rose-600 px-2 py-1 transition-colors" title="Keluar">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="?page=login" class="text-xs font-semibold text-slate-700 hover:text-navy px-3 py-2 rounded-lg hover:bg-slate-100 transition-colors">
                        Masuk
                    </a>
                    <a href="?page=register" class="text-xs font-semibold text-white bg-brand-500 hover:bg-brand-600 px-3.5 py-2 rounded-lg transition-colors shadow-sm">
                        Daftar
                    </a>
                <?php endif; ?>

            </div>

        </div>
    </header>

    <main class="flex-grow">
