<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    <link rel="icon" type="image/svg+xml" href="appfinder.svg">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Inter"', 'system-ui', 'sans-serif'],
                        heading: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                        },
                        surface: '#FFFFFF',
                        muted: '#f5f5f4',
                    },
                    keyframes: {
                        'fade-in-up': {
                            '0%': { opacity: '0', transform: 'translateY(16px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        'marquee': {
                            '0%': { transform: 'translateX(0%)' },
                            '100%': { transform: 'translateX(-50%)' }
                        }
                    },
                    animation: {
                        'fade-in-up': 'fade-in-up 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards',
                        'marquee': 'marquee 25s linear infinite',
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: #FFFFFF;
            color: #1a1a1a;
        }
        .font-heading { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', Georgia, serif; }
        
        .ui-card {
            background: #FFFFFF;
            border: 1px solid #e7e5e4;
            transition: all 0.25s ease;
        }
        .ui-card:hover {
            border-color: #d6d3d1;
            box-shadow: 0 8px 30px rgba(0,0,0,0.04);
            transform: translateY(-2px);
        }

        /* Pill nav active */
        .nav-pill-active {
            background: #FFFFFF;
            border-radius: 9999px;
            color: #1c1917;
            font-weight: 600;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        }

        /* Hide Scrollbar for Horizontal Carousels */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        /* Modern App Card */
        .app-card-landscape {
            background: #FFFFFF;
            border: 1px solid #e7e5e4;
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            scroll-snap-align: start;
        }
        .app-card-landscape:hover {
            border-color: #111827;
            transform: translateY(-2px);
            box-shadow: 0 12px 40px -12px rgba(0,0,0,0.1);
        }
        .app-card-landscape .cover-wrapper {
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
            background: #f5f5f4;
        }
        .app-card-landscape img.cover-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .app-card-landscape:hover img.cover-img {
            transform: scale(1.05);
        }

        /* Marquee pause on hover */
        .marquee-track:hover {
            animation-play-state: paused;
        }
        .mobile-nav-toggle::-webkit-details-marker {
            display: none;
        }
    </style>
</head>
<body class="bg-white text-stone-900 antialiased flex flex-col min-h-screen">

    <!-- Navigation Bar (21st.dev style: centered pill nav) -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between">
                
                <!-- Brand -->
                <a href="?page=home" class="flex items-center gap-2 group shrink-0">
                    <div class="w-8 h-8 rounded-full bg-stone-900 flex items-center justify-center text-white group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <span class="text-lg font-bold tracking-tight text-stone-900 font-heading">appfinder<span class="text-brand-500">.</span></span>
                </a>

                <!-- Center Pill Nav -->
                <nav class="hidden md:flex items-center gap-0.5 text-sm font-medium text-stone-500 bg-stone-100/60 rounded-full px-1 py-1 border border-stone-200/60">
                    <?php
                    $currentPage = $_GET['page'] ?? 'home';
                    $navItems = [
                        ['label' => 'Home', 'page' => 'home'],
                        ['label' => 'Explore', 'page' => 'explore'],
                        ['label' => 'Kategori', 'href' => '?page=home#categories'],
                        ['label' => 'Alternatif', 'href' => '?page=home#alternatives'],
                    ];
                    foreach ($navItems as $nav):
                        $href = $nav['href'] ?? "?page={$nav['page']}";
                        $isActive = isset($nav['page']) && $currentPage === $nav['page'];
                        $section = isset($nav['href']) ? (parse_url($href, PHP_URL_FRAGMENT) ?: '') : '';
                    ?>
                        <a href="<?= $href ?>" data-desktop-nav data-nav-page="<?= htmlspecialchars($nav['page'] ?? 'home', ENT_QUOTES, 'UTF-8') ?>" data-nav-section="<?= htmlspecialchars($section, ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-1.5 rounded-full transition-all text-sm hover:text-stone-900 <?= $isActive ? 'nav-pill-active' : '' ?>">
                            <?= $nav['label'] ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <!-- Right Actions -->
                <div class="flex items-center gap-2 shrink-0">
                    <details class="relative md:hidden">
                        <summary class="mobile-nav-toggle flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-full text-stone-500 transition-colors hover:bg-stone-100 hover:text-stone-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" aria-label="Buka navigasi">
                            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </summary>
                        <nav aria-label="Navigasi seluler" class="absolute right-0 top-11 z-50 w-52 rounded-2xl border border-stone-200 bg-white p-2 shadow-xl shadow-stone-900/10">
                            <a href="?page=home" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-100 hover:text-stone-900">Home</a>
                            <a href="?page=explore" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-100 hover:text-stone-900">Explore</a>
                            <a href="?page=home#categories" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-100 hover:text-stone-900">Kategori</a>
                            <a href="?page=home#alternatives" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-100 hover:text-stone-900">Alternatif</a>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="?page=dashboard" class="mt-1 block rounded-xl bg-stone-900 px-3 py-2.5 text-sm font-semibold text-white">Akun Saya</a>
                            <?php else: ?>
                                <a href="?page=register" class="mt-1 block rounded-xl bg-stone-900 px-3 py-2.5 text-sm font-semibold text-white">Buat akun gratis</a>
                            <?php endif; ?>
                        </nav>
                    </details>
                    <a href="?page=explore" class="p-2 text-stone-400 hover:text-stone-900 rounded-full hover:bg-stone-100 transition-colors" title="Cari">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </a>
                    <a href="?page=dashboard" class="relative p-2 text-stone-400 hover:text-stone-900 rounded-full hover:bg-stone-100 transition-colors" title="Tersimpan">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                        <?php if ($bookmarkCount > 0): ?>
                            <span class="absolute top-0.5 right-0.5 w-4 h-4 bg-brand-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center"><?= $bookmarkCount ?></span>
                        <?php endif; ?>
                    </a>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="?page=dashboard" class="hidden sm:inline-flex text-xs font-semibold px-4 py-2 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-800 transition-colors">
                            Akun Saya
                        </a>
                    <?php else: ?>
                        <a href="?page=register" class="hidden sm:inline-flex items-center gap-2 text-xs font-semibold text-white bg-stone-900 hover:bg-stone-800 pl-5 pr-1.5 py-1.5 rounded-full transition-colors group">
                            <span>Mulai Jelajahi</span>
                            <span class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center group-hover:bg-white/30 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7v10"></path></svg>
                            </span>
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </header>

    <main class="flex-grow">
