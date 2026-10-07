<?php
/**
 * LegalFree — Direktori Software Legal Gratis
 * Single-file PHP app. Tailwind CSS + SQLite.
 *
 * DESIGN RULES:
 *   1. Visual 100% verbatim dari try2.html (Tailwind CDN + Plus Jakarta Sans + Silkscreen)
 *   2. JavaScript SPA architecture preserved (switchPage, wishlist, filters)
 *   3. Only `appsData` array is replaced with PHP/SQLite queries
 *   4. All functions prefixed with lf_ to avoid collisions
 *   5. No SQLite datetime() expressions — dates computed in PHP
 *   6. All user data escaped with lf_e()
 *
 * PHP 7.4+ / SQLite / PDO.
 */

declare(strict_types=1);

// ==========================================================================
// SECTION 1: ERROR HANDLERS
// ==========================================================================

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e): void {
    lf_log_error(get_class($e) . ': ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title>'
       . '<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>'
       . '</head><body class="bg-slate-50 flex items-center justify-center min-h-screen">'
       . '<div class="text-center">'
       . '<h1 class="text-8xl font-extrabold text-rose-500 font-pixel" style="font-family:Silkscreen,monospace">500</h1>'
       . '<p class="mt-4 text-slate-700 font-bold">Something broke. The error has been logged.</p>'
       . '<a href="/" class="inline-block mt-6 px-6 py-3 bg-cyan-500 text-slate-900 font-bold rounded-xl border-2 border-slate-900 shadow-[3px_3px_0px_#1e293b]">Back to index</a>'
       . '</div></body></html>';
    exit;
});

// ==========================================================================
// SECTION 2: CONSTANTS
// ==========================================================================

const DB_FILE  = __DIR__ . '/data/legalfree.db';
const LOG_FILE = __DIR__ . '/debug.log';

const CATEGORIES = [
    'Productivity' => ['name' => 'Productivity', 'icon' => 'fa-briefcase', 'color' => 'amber', 'desc' => 'Office suite, catatan, & utilitas kerja.'],
    'Design'       => ['name' => 'Design & Art', 'icon' => 'fa-palette',   'color' => 'rose',  'desc' => 'Vektor, raster, & desain grafis.'],
    'Developer'    => ['name' => 'Developer Tools', 'icon' => 'fa-code',   'color' => 'cyan',  'desc' => 'Editor, IDE, & utilitas coding.'],
    'Gaming'       => ['name' => 'Gaming & Media', 'icon' => 'fa-gamepad', 'color' => 'indigo','desc' => 'Game engine & pemutar media.'],
];

// ==========================================================================
// SECTION 3: LOGGING
// ==========================================================================

function lf_rid(): string {
    static $id = null;
    return $id ??= bin2hex(random_bytes(4));
}

function lf_log_info(string $msg): void {
    file_put_contents(LOG_FILE, date('Y-m-d H:i:s') . ' [' . lf_rid() . '] INFO  ' . $msg . "\n", FILE_APPEND | LOCK_EX);
}

function lf_log_error(string $msg): void {
    file_put_contents(LOG_FILE, date('Y-m-d H:i:s') . ' [' . lf_rid() . '] ERROR ' . $msg . "\n", FILE_APPEND | LOCK_EX);
}

// ==========================================================================
// SECTION 4: DATABASE
// ==========================================================================

function lf_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA foreign_keys = ON');
    }
    return $pdo;
}

function lf_q(string $sql, array $params = []): PDOStatement {
    $start = microtime(true);
    try {
        $stmt = lf_db()->prepare($sql);
        $stmt->execute(array_values($params));
        $ms = round((microtime(true) - $start) * 1000, 1);
        lf_log_info('SQL ' . $ms . 'ms  ' . preg_replace('/\s+/', ' ', $sql));
        return $stmt;
    } catch (PDOException $e) {
        lf_log_error('SQL FAIL: ' . $e->getMessage() . '  SQL: ' . $sql);
        throw $e;
    }
}

function lf_db_ensure(): void {
    $exists = (int) lf_q("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='apps'")->fetchColumn();
    if ($exists > 0) return;

    lf_log_info('Creating schema + seeding');

    lf_db()->exec("
        CREATE TABLE apps (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            name          TEXT NOT NULL,
            category      TEXT NOT NULL DEFAULT 'Productivity',
            developer     TEXT NOT NULL DEFAULT '',
            rating        REAL NOT NULL DEFAULT 0,
            reviews_count INTEGER NOT NULL DEFAULT 0,
            downloads     TEXT NOT NULL DEFAULT '0',
            license       TEXT NOT NULL DEFAULT 'Unknown',
            is_open_source INTEGER NOT NULL DEFAULT 0,
            no_ads        INTEGER NOT NULL DEFAULT 1,
            platforms     TEXT NOT NULL DEFAULT 'Windows,Mac,Linux',
            size          TEXT NOT NULL DEFAULT '',
            updated       TEXT NOT NULL DEFAULT '',
            website       TEXT NOT NULL DEFAULT '#',
            icon          TEXT NOT NULL DEFAULT '',
            description   TEXT NOT NULL DEFAULT '',
            features      TEXT NOT NULL DEFAULT '',
            screenshots   TEXT NOT NULL DEFAULT '',
            reviews       TEXT NOT NULL DEFAULT '[]',
            featured      INTEGER NOT NULL DEFAULT 0,
            added_at      TEXT NOT NULL DEFAULT ''
        );
        CREATE TABLE submissions (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            name            TEXT NOT NULL,
            tagline         TEXT NOT NULL DEFAULT '',
            description     TEXT NOT NULL DEFAULT '',
            category        TEXT NOT NULL DEFAULT 'Productivity',
            website         TEXT NOT NULL DEFAULT '#',
            submitter_name  TEXT NOT NULL DEFAULT '',
            submitter_email TEXT NOT NULL DEFAULT '',
            notes           TEXT NOT NULL DEFAULT '',
            status          TEXT NOT NULL DEFAULT 'pending',
            created_at      TEXT NOT NULL DEFAULT ''
        );
        CREATE INDEX idx_apps_category ON apps(category);
        CREATE INDEX idx_apps_featured ON apps(featured);
    ");

    foreach (lf_seed_apps() as $a) {
        lf_q(
            'INSERT INTO apps (name, category, developer, rating, reviews_count, downloads, license, is_open_source, no_ads, platforms, size, updated, website, icon, description, features, screenshots, reviews, featured, added_at) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $a['name'], $a['category'], $a['developer'], $a['rating'], $a['reviews_count'],
                $a['downloads'], $a['license'], $a['is_open_source'], $a['no_ads'],
                $a['platforms'], $a['size'], $a['updated'], $a['website'], $a['icon'],
                $a['description'], $a['features'], $a['screenshots'], $a['reviews'],
                $a['featured'], date('Y-m-d H:i:s', strtotime('-' . random_int(1, 30) . ' days')),
            ]
        );
    }
    lf_log_info('Seeded ' . count(lf_seed_apps()) . ' apps');
}

// ==========================================================================
// SECTION 5: SEED DATA
// ==========================================================================

function lf_seed_apps(): array {
    return [
        [
            'name' => 'Blender', 'category' => 'Design', 'developer' => 'Blender Foundation',
            'rating' => 4.9, 'reviews_count' => 1420, 'downloads' => '4.5M+',
            'license' => 'GNU GPLv2', 'is_open_source' => 1, 'no_ads' => 1,
            'platforms' => 'Windows,Mac,Linux', 'size' => '350 MB', 'updated' => 'Agustus 2026',
            'website' => 'https://www.blender.org',
            'icon' => 'https://upload.wikimedia.org/wikipedia/commons/0/0c/Blender_logo_no_text.svg',
            'description' => 'Blender adalah suite pembuatan 3D open-source gratis yang mendukung keseluruhan pipeline 3D—pemodelan, rigging, animasi, simulasi, rendering, dan komposisi.',
            'features' => 'Pemodelan 3D & sculpting kelas profesional||Mesin render Cycles & Eevee super cepat||Dukungan penuh untuk animasi rigging 2D/3D||Editor video bawaan',
            'screenshots' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?auto=format&fit=crop&w=600&q=80||https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=600&q=80',
            'reviews' => '[{"user":"Rian Pratama","rating":5,"date":"2 hari lalu","comment":"Software 3D terbaik tanpa bayar sepeserpun!"}]',
            'featured' => 1,
        ],
        [
            'name' => 'Visual Studio Code', 'category' => 'Developer', 'developer' => 'Microsoft Open Source',
            'rating' => 4.8, 'reviews_count' => 3200, 'downloads' => '12M+',
            'license' => 'MIT License', 'is_open_source' => 1, 'no_ads' => 1,
            'platforms' => 'Windows,Mac,Linux', 'size' => '120 MB', 'updated' => 'September 2026',
            'website' => 'https://code.visualstudio.com',
            'icon' => 'https://upload.wikimedia.org/wikipedia/commons/9/9a/Visual_Studio_Code_1.35_icon.svg',
            'description' => 'Visual Studio Code adalah editor kode sumber ringan namun bertenaga dengan dukungan bawaan untuk JavaScript, TypeScript, dan Node.js.',
            'features' => 'IntelliSense penyelesaian kode cerdas||Debugging langsung dari editor||Ekosistem ekstensi yang sangat kaya||Kontrol Git terintegrasi',
            'screenshots' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=600&q=80||https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80',
            'reviews' => '[{"user":"Budi Santoso","rating":5,"date":"Kemarin","comment":"Editor wajib untuk programmer modern."}]',
            'featured' => 1,
        ],
        [
            'name' => 'VLC Media Player', 'category' => 'Gaming', 'developer' => 'VideoLAN Organization',
            'rating' => 4.7, 'reviews_count' => 2100, 'downloads' => '25M+',
            'license' => 'GNU GPLv2+', 'is_open_source' => 1, 'no_ads' => 1,
            'platforms' => 'Windows,Mac,Linux', 'size' => '40 MB', 'updated' => 'Juli 2026',
            'website' => 'https://www.videolan.org',
            'icon' => 'https://upload.wikimedia.org/wikipedia/commons/e/e6/VLC_Icon_%282012-present%29.svg',
            'description' => 'VLC adalah pemutar multimedia gratis yang memutar hampir semua file multimedia, DVD, CD Audio, dan protokol streaming.',
            'features' => 'Memutar hampir semua format tanpa instalasi codec||Ringan, cepat, dan 100% bebas iklan||Fitur konversi media bawaan',
            'screenshots' => 'https://images.unsplash.com/photo-1512758017271-d7b84c2113f1?auto=format&fit=crop&w=600&q=80',
            'reviews' => '[{"user":"Dewi Lestari","rating":5,"date":"3 hari lalu","comment":"Pemutar video legendaris!"}]',
            'featured' => 1,
        ],
        [
            'name' => 'Godot Engine', 'category' => 'Gaming', 'developer' => 'Godot Foundation',
            'rating' => 4.9, 'reviews_count' => 840, 'downloads' => '3.2M+',
            'license' => 'MIT License', 'is_open_source' => 1, 'no_ads' => 1,
            'platforms' => 'Windows,Mac,Linux', 'size' => '100 MB', 'updated' => 'September 2026',
            'website' => 'https://godotengine.org',
            'icon' => 'https://upload.wikimedia.org/wikipedia/commons/6/6a/Godot_icon_%282014%29.svg',
            'description' => 'Godot adalah game engine 2D dan 3D berfitur lengkap yang menyediakan lingkungan pembuatan game terpadu tanpa royalti.',
            'features' => 'Arsitektur berbasis node intuitif||Bahasa GDScript & C#||Build sangat ringan & cepat',
            'screenshots' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80',
            'reviews' => '[{"user":"Yoga","rating":5,"date":"1 minggu lalu","comment":"Engine terbaik untuk developer indie!"}]',
            'featured' => 1,
        ],
        [
            'name' => 'GIMP', 'category' => 'Design', 'developer' => 'GIMP Team',
            'rating' => 4.6, 'reviews_count' => 1850, 'downloads' => '8.2M+',
            'license' => 'GNU GPLv3', 'is_open_source' => 1, 'no_ads' => 1,
            'platforms' => 'Windows,Mac,Linux', 'size' => '250 MB', 'updated' => 'Agustus 2026',
            'website' => 'https://www.gimp.org',
            'icon' => 'https://upload.wikimedia.org/wikipedia/commons/4/45/The_GIMP_icon_-_gnome.svg',
            'description' => 'GIMP adalah editor gambar gratis yang kuat untuk retouching, komposisi gambar, dan editing grafis umum.',
            'features' => 'Editor raster kelas profesional gratis||Dukungan plugin pihak ketiga||Tools retouching foto lengkap||Mendukung format PSD Photoshop',
            'screenshots' => 'https://images.unsplash.com/photo-1572044162444-ad60f128bdea?auto=format&fit=crop&w=600&q=80',
            'reviews' => '[{"user":"Sari Indah","rating":4,"date":"5 hari lalu","comment":"Alternatif Photoshop yang cukup mumpuni."}]',
            'featured' => 0,
        ],
        [
            'name' => 'OBS Studio', 'category' => 'Gaming', 'developer' => 'OBS Project',
            'rating' => 4.8, 'reviews_count' => 1650, 'downloads' => '6.1M+',
            'license' => 'GNU GPLv2', 'is_open_source' => 1, 'no_ads' => 1,
            'platforms' => 'Windows,Mac,Linux', 'size' => '180 MB', 'updated' => 'September 2026',
            'website' => 'https://obsproject.com',
            'icon' => 'https://upload.wikimedia.org/wikipedia/commons/1/1d/OBS_Studio_Logo.svg',
            'description' => 'OBS Studio adalah software gratis untuk streaming dan recording layar yang digunakan streamer profesional.',
            'features' => 'Recording dan streaming real-time||Multiple scene dengan transisi||Dukungan plugin komunitas||Encoding hardware acceleration',
            'screenshots' => 'https://images.unsplash.com/photo-1626379801357-537572c10c00?auto=format&fit=crop&w=600&q=80',
            'reviews' => '[{"user":"Andi Wijaya","rating":5,"date":"1 minggu lalu","comment":"Wajib untuk content creator!"}]',
            'featured' => 0,
        ],
        [
            'name' => 'LibreOffice', 'category' => 'Productivity', 'developer' => 'The Document Foundation',
            'rating' => 4.5, 'reviews_count' => 980, 'downloads' => '15M+',
            'license' => 'MPL 2.0', 'is_open_source' => 1, 'no_ads' => 1,
            'platforms' => 'Windows,Mac,Linux', 'size' => '320 MB', 'updated' => 'Agustus 2026',
            'website' => 'https://www.libreoffice.org',
            'icon' => 'https://upload.wikimedia.org/wikipedia/commons/4/4d/LibreOffice_Logo.svg',
            'description' => 'LibreOffice adalah suite office gratis dengan Writer, Calc, Impress, Draw, Base, dan Math — alternatif legal Microsoft Office.',
            'features' => 'Writer pengganti Word||Calc pengganti Excel||Impress pengganti PowerPoint||Mendukung format file Microsoft Office',
            'screenshots' => 'https://images.unsplash.com/photo-1633265486064-086b219458ec?auto=format&fit=crop&w=600&q=80',
            'reviews' => '[{"user":"Maya Sari","rating":4,"date":"2 minggu lalu","comment":"Sangat membantu untuk tugas kantor."}]',
            'featured' => 0,
        ],
        [
            'name' => 'Audacity', 'category' => 'Productivity', 'developer' => 'Audacity Team',
            'rating' => 4.6, 'reviews_count' => 1120, 'downloads' => '7.8M+',
            'license' => 'GNU GPLv2', 'is_open_source' => 1, 'no_ads' => 1,
            'platforms' => 'Windows,Mac,Linux', 'size' => '55 MB', 'updated' => 'Juli 2026',
            'website' => 'https://www.audacityteam.org',
            'icon' => 'https://upload.wikimedia.org/wikipedia/commons/c/ca/Audacity_Logo.svg',
            'description' => 'Audacity adalah editor audio gratis untuk recording, mixing, dan editing — populer di kalangan podcaster dan musisi indie.',
            'features' => 'Multi-track recording||Efek audio bawaan (reverb, echo, dll)||Export ke MP3, WAV, OGG||Support plugin VST/LADSPA',
            'screenshots' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?auto=format&fit=crop&w=600&q=80',
            'reviews' => '[{"user":"Rudi Hart","rating":5,"date":"3 minggu lalu","comment":"Tools podcast terbaik gratis!"}]',
            'featured' => 0,
        ],
    ];
}

// ==========================================================================
// SECTION 6: INPUT HELPERS
// ==========================================================================

function lf_e(?string $s): string {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function lf_url(string $path = ''): string {
    static $base = null;
    if ($base === null) {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    }
    return $base . '/' . ltrim($path, '');
}

function lf_post_str(string $key, string $default = ''): string {
    $val = $_POST[$key] ?? $default;
    return is_string($val) ? trim($val) : $default;
}

function lf_post_category(string $key, string $default = 'Productivity'): string {
    $val = $_POST[$key] ?? $default;
    if (!is_string($val)) return $default;
    $val = trim($val);
    return array_key_exists($val, CATEGORIES) ? $val : $default;
}

// ==========================================================================
// SECTION 7: FLASH MESSAGES
// ==========================================================================

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

function lf_flash(string $msg, string $kind = 'info'): void {
    $_SESSION['flash'][] = ['msg' => $msg, 'kind' => $kind];
}

function lf_flash_render(): string {
    if (empty($_SESSION['flash'])) return '';
    $out = '<div style="position:fixed;top:90px;right:18px;z-index:90;max-width:420px">';
    foreach ((array) $_SESSION['flash'] as $f) {
        $bg = $f['kind'] === 'ok' ? '#d1fae5' : ($f['kind'] === 'error' ? '#fee2e2' : '#dbeafe');
        $bd = $f['kind'] === 'ok' ? '#10b981' : ($f['kind'] === 'error' ? '#ef4444' : '#3b82f6');
        $out .= '<div style="background:' . $bg . ';border:2px solid ' . $bd . ';padding:14px 16px;margin-bottom:10px;font-family:Plus Jakarta Sans,sans-serif;font-weight:700;box-shadow:4px 4px 0 #1e293b">' . lf_e($f['msg']) . '</div>';
    }
    unset($_SESSION['flash']);
    $out .= '</div>';
    return $out;
}

// ==========================================================================
// SECTION 8: QUERY HELPERS
// ==========================================================================

function lf_apps_all(): array {
    return lf_q('SELECT * FROM apps ORDER BY rating DESC, reviews_count DESC')->fetchAll();
}

function lf_apps_featured(int $limit = 4): array {
    return lf_q('SELECT * FROM apps WHERE featured = 1 ORDER BY rating DESC LIMIT ?', [$limit])->fetchAll();
}

function lf_app_count(): int {
    return (int) lf_q('SELECT COUNT(*) FROM apps')->fetchColumn();
}

function lf_app_to_json(array $row): array {
    return [
        'id'           => (int) $row['id'],
        'name'         => $row['name'],
        'category'     => $row['category'],
        'developer'    => $row['developer'],
        'rating'       => (float) $row['rating'],
        'reviewsCount' => (int) $row['reviews_count'],
        'downloads'    => $row['downloads'],
        'license'      => $row['license'],
        'isOpenSource' => (bool) $row['is_open_source'],
        'noAds'        => (bool) $row['no_ads'],
        'platforms'    => array_filter(array_map('trim', explode(',', $row['platforms']))),
        'size'         => $row['size'],
        'updated'      => $row['updated'],
        'website'      => $row['website'],
        'icon'         => $row['icon'],
        'description'  => $row['description'],
        'features'     => array_filter(array_map('trim', explode('||', $row['features']))),
        'screenshots'  => array_filter(array_map('trim', explode('||', $row['screenshots']))),
        'reviews'      => json_decode($row['reviews'], true) ?: [],
    ];
}

function lf_submission_add(array $d): int {
    lf_q(
        'INSERT INTO submissions (name, tagline, description, category, website, submitter_name, submitter_email, notes, status, created_at) '
        . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $d['name'], $d['tagline'], $d['description'], $d['category'],
            $d['website'], $d['submitter_name'], $d['submitter_email'],
            $d['notes'], 'pending', date('Y-m-d H:i:s'),
        ]
    );
    return (int) lf_db()->lastInsertId();
}

// ==========================================================================
// SECTION 9: BOOTSTRAP
// ==========================================================================

lf_db_ensure();

$rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
if ($scriptDir !== '' && $scriptDir !== '/' && strpos($rawPath, $scriptDir) === 0) {
    $rawPath = substr($rawPath, strlen($scriptDir));
}
$path = '/' . ltrim($rawPath, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

lf_log_info('REQ ' . $method . ' ' . $path);

// ==========================================================================
// SECTION 10: ROUTING
// ==========================================================================

if ($path === '/smoke-test') {
    lf_route_smoke_test();
    exit;
}

// Handle submit POST (only the form processing; UI is in JS)
if ($path === '/submit' && $method === 'POST') {
    lf_handle_submit_post();
    exit;
}

// Default: render the SPA
lf_route_home();
exit;

// ==========================================================================
// SECTION 11: ROUTE FUNCTIONS
// ==========================================================================

function lf_handle_submit_post(): void {
    $errors = [];

    $name        = lf_post_str('name', '');
    $tagline     = lf_post_str('tagline', '');
    $description = lf_post_str('description', '');
    $category    = lf_post_category('category', 'Productivity');
    $website     = lf_post_str('website', '');
    $notes       = lf_post_str('notes', '');

    if ($name === '') $errors[] = 'Nama aplikasi wajib diisi';
    elseif (mb_strlen($name) > 80) $errors[] = 'Nama maksimal 80 karakter';
    if ($tagline === '') $errors[] = 'Tagline wajib diisi';
    if (mb_strlen($description) < 30) $errors[] = 'Deskripsi minimal 30 karakter';
    if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) $errors[] = 'URL website tidak valid';

    if (!empty($errors)) {
        foreach ($errors as $err) lf_flash($err, 'error');
        $_SESSION['form'] = compact('name', 'tagline', 'description', 'category', 'website', 'notes');
    } else {
        try {
            lf_submission_add([
                'name' => $name, 'tagline' => $tagline, 'description' => $description,
                'category' => $category, 'website' => $website,
                'submitter_name' => '', 'submitter_email' => '', 'notes' => $notes,
            ]);
            lf_flash('Terima kasih! "' . $name . '" masuk antrian moderasi.', 'ok');
            lf_log_info('Submission created: ' . $name);
        } catch (Throwable $e) {
            lf_flash('Gagal menyimpan. Error tercatat.', 'error');
            lf_log_error('Submit failed: ' . $e->getMessage());
        }
    }

    header('Location: ' . lf_url(''));
    exit;
}

function lf_route_smoke_test(): void {
    $results = [];
    $allOk = true;

    try {
        $n = lf_app_count();
        $ok = $n >= 1;
        $results[] = ['Apps count', $ok, $n . ' apps'];
        if (!$ok) $allOk = false;
    } catch (Throwable $e) {
        $results[] = ['Apps count', false, $e->getMessage()];
        $allOk = false;
    }

    try {
        $n = (int) lf_q('SELECT COUNT(*) FROM submissions')->fetchColumn();
        $results[] = ['Submissions table', true, $n . ' rows'];
    } catch (Throwable $e) {
        $results[] = ['Submissions table', false, $e->getMessage()];
        $allOk = false;
    }

    try {
        $valid = array_keys(CATEGORIES);
        $placeholders = implode(',', array_fill(0, count($valid), '?'));
        $bad = lf_q('SELECT name FROM apps WHERE category NOT IN (' . $placeholders . ')', $valid)->fetchAll();
        $ok = empty($bad);
        $results[] = ['All apps valid category', $ok, $ok ? 'all valid' : 'invalid: ' . json_encode($bad)];
        if (!$ok) $allOk = false;
    } catch (Throwable $e) {
        $results[] = ['All apps valid category', false, $e->getMessage()];
        $allOk = false;
    }

    try {
        $f = lf_apps_featured(4);
        $ok = count($f) >= 1;
        $results[] = ['featured(4)', $ok, count($f) . ' featured'];
        if (!$ok) $allOk = false;
    } catch (Throwable $e) {
        $results[] = ['featured(4)', false, $e->getMessage()];
        $allOk = false;
    }

    try {
        $apps = lf_apps_all();
        // Verify JSON decode works on reviews
        foreach (array_slice($apps, 0, 3) as $a) {
            json_decode($a['reviews'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Invalid JSON in reviews for app ' . $a['name']);
            }
        }
        $results[] = ['Reviews JSON valid', true, 'checked 3 apps'];
    } catch (Throwable $e) {
        $results[] = ['Reviews JSON valid', false, $e->getMessage()];
        $allOk = false;
    }

    try {
        $wrote = file_put_contents(LOG_FILE, date('Y-m-d H:i:s') . ' [SMOKE] test' . "\n", FILE_APPEND | LOCK_EX);
        $ok = $wrote !== false;
        $results[] = ['Log writable', $ok, $ok ? LOG_FILE : 'failed'];
        if (!$ok) $allOk = false;
    } catch (Throwable $e) {
        $results[] = ['Log writable', false, $e->getMessage()];
        $allOk = false;
    }

    $status = $allOk ? 'ALL PASS' : 'FAILURES DETECTED';
    $color  = $allOk ? 'emerald' : 'rose';

    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Smoke Test — LegalFree</title>'
       . '<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>'
       . '<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">'
       . '</head><body class="bg-slate-50 min-h-screen p-8 font-sans" style="font-family:Plus Jakarta Sans,sans-serif">'
       . '<div class="max-w-4xl mx-auto">'
       . '<h1 class="text-3xl font-extrabold text-' . $color . '-600" style="font-family:Silkscreen,monospace">' . lf_e($status) . '</h1>'
       . '<p class="text-slate-600 text-sm mt-2 font-bold">Smoke test ran at ' . date('Y-m-d H:i:s') . ' · request ID ' . lf_e(lf_rid()) . '</p>'
       . '<table class="w-full mt-6 bg-white border-2 border-slate-900 shadow-[4px_4px_0px_#1e293b]">'
       . '<thead><tr class="bg-slate-900 text-white text-xs uppercase">'
       . '<th class="p-3 text-left">Test</th><th class="p-3 text-left">Result</th><th class="p-3 text-left">Detail</th>'
       . '</tr></thead><tbody>';
    foreach ($results as $r) {
        [$name, $ok, $detail] = $r;
        $icon = $ok ? 'OK' : 'FAIL';
        $cls  = $ok ? 'text-emerald-600' : 'text-rose-600';
        echo '<tr class="border-t border-slate-200">'
           . '<td class="p-3 font-bold">' . lf_e($name) . '</td>'
           . '<td class="p-3 font-bold ' . $cls . '">' . $icon . '</td>'
           . '<td class="p-3 text-xs text-slate-600"><code>' . lf_e($detail) . '</code></td>'
           . '</tr>';
    }
    echo '</tbody></table>'
       . '<p class="mt-6 text-xs text-slate-500">Log file: <code>' . lf_e(LOG_FILE) . '</code></p>'
       . '</div></body></html>';
}

function lf_route_home(): void {
    $apps = lf_apps_all();
    $appsJson = json_encode(array_map('lf_app_to_json', $apps), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $totalApps = count($apps);
    $totalDownloads = '50K+'; // static for now
    $flash = lf_flash_render();

    // Page title
    $pageTitle = 'LegalFree - Direktori & Penjelajah Software Legal Gratis';

    // ========================================================================
    // OUTPUT THE HTML (verbatim structure from try2.html)
    // ========================================================================
    ?><!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= lf_e($pageTitle) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-pixel { font-family: 'Silkscreen', cursive; }
        .bg-grid-pattern {
            background-size: 28px 28px;
            background-image:
                linear-gradient(to right, rgba(203, 213, 225, 0.4) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(203, 213, 225, 0.4) 1px, transparent 1px);
        }
        .card-layered {
            background: #ffffff;
            border: 2px solid #1e293b;
            box-shadow: 4px 4px 0px #1e293b;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-layered:hover {
            transform: translate(-2px, -2px);
            box-shadow: 7px 7px 0px #1e293b;
        }
        .tier-starter { background: linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%); border-color: #f43f5e; }
        .tier-bronze  { background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); border-color: #64748b; }
        .tier-silver  { background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%); border-color: #eab308; }
        .tier-gold    { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-color: #0284c7; }
        .tier-diamond { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); border-color: #9333ea; }
        .tier-master  { background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%); border-color: #ea580c; }
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(3deg); }
        }
        .animate-float { animation: floatSlow 4s ease-in-out infinite; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-cyan-500 selection:text-white flex flex-col min-h-screen">

    <!-- Top Gamified Event Bar -->
    <div class="bg-amber-400 text-slate-900 text-xs font-bold py-2 px-4 border-b-2 border-slate-900">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="bg-slate-900 text-amber-400 px-2 py-0.5 rounded text-[10px] font-pixel uppercase">Event Spesial</span>
                <span><i class="fa-solid fa-trophy text-amber-900 mr-1"></i> Penjelajah Software Legal Bulan Ini: Kumpulkan Badge & Buka Fitur Pro!</span>
            </div>
            <div class="hidden md:flex items-center gap-2 font-pixel text-[11px]">
                <i class="fa-regular fa-clock"></i> Sisa Waktu: 12 Hari 08 Jam
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b-2 border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="#" onclick="switchPage('home'); return false;" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-xl bg-cyan-500 border-2 border-slate-900 flex items-center justify-center text-white shadow-[3px_3px_0px_#1e293b] group-hover:translate-x-0.5 group-hover:translate-y-0.5 transition-transform">
                    <i class="fa-solid fa-shield-cat text-xl text-slate-900"></i>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-slate-900 font-pixel">Legal<span class="text-cyan-600">Free</span></span>
                    <span class="block text-[10px] font-bold text-emerald-600 uppercase tracking-wider">100% Verified Software</span>
                </div>
            </a>
            <nav class="hidden md:flex items-center gap-8 font-bold text-sm text-slate-700">
                <a href="#" onclick="switchPage('home'); return false;" class="hover:text-cyan-600 transition-colors">Beranda</a>
                <a href="#" onclick="switchPage('catalog'); return false;" class="hover:text-cyan-600 transition-colors">Jelajahi Aplikasi</a>
                <a href="#milestones" class="hover:text-cyan-600 transition-colors flex items-center gap-1.5 text-amber-600">
                    <i class="fa-solid fa-medal"></i> Rewards Milestone
                </a>
            </nav>
            <div class="flex items-center gap-3">
                <button onclick="toggleWishlistModal()" class="relative p-2.5 rounded-xl border-2 border-slate-900 bg-white hover:bg-slate-100 shadow-[2px_2px_0px_#1e293b] transition-all" title="Wishlist">
                    <i class="fa-solid fa-bookmark text-slate-800"></i>
                    <span id="wishlist-badge" class="absolute -top-2 -right-2 w-5 h-5 bg-rose-500 text-white text-[10px] font-bold rounded-full border border-slate-900 flex items-center justify-center hidden">0</span>
                </button>
                <button onclick="switchPage('catalog')" class="hidden sm:inline-flex items-center gap-2 bg-cyan-500 text-slate-900 border-2 border-slate-900 px-5 py-2.5 rounded-xl text-sm font-bold shadow-[3px_3px_0px_#1e293b] hover:translate-x-0.5 hover:translate-y-0.5 transition-all">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari Software
                </button>
            </div>
        </div>
    </header>

    <main class="flex-grow">

        <!-- ================= PAGE 1: HOMEPAGE ================= -->
        <div id="page-home" class="page-view">
            <section class="relative bg-grid-pattern pt-16 pb-20 border-b-2 border-slate-900 overflow-hidden">
                <div class="hidden lg:block absolute top-12 left-16 text-cyan-500 text-3xl animate-float opacity-80">
                    <i class="fa-solid fa-cube"></i>
                </div>
                <div class="hidden lg:block absolute top-28 left-40 text-amber-400 text-2xl animate-float opacity-90" style="animation-delay: 1s;">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div class="hidden lg:block absolute top-16 right-20 text-rose-500 text-3xl animate-float opacity-80" style="animation-delay: 1.5s;">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <div class="hidden lg:block absolute top-36 right-44 text-indigo-500 text-2xl animate-float opacity-90" style="animation-delay: 0.5s;">
                    <i class="fa-solid fa-bolt"></i>
                </div>

                <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center relative z-10">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border-2 border-slate-900 text-slate-900 text-xs font-bold mb-6 shadow-[2px_2px_0px_#1e293b]">
                        <i class="fa-solid fa-shield-check text-emerald-600"></i> Bebas Malware & Tanpa Lisensi Pembajakan
                    </div>

                    <h2 class="text-4xl sm:text-6xl font-extrabold font-pixel text-slate-900 tracking-wider mb-2">
                        <?= number_format($totalApps * 1000 + 2000) ?>+
                    </h2>
                    <p class="text-xs sm:text-sm font-bold uppercase tracking-widest text-cyan-700 mb-6 font-pixel">Aplikasi Legal & Gratis Siap Diunduh</p>

                    <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight mb-6">
                        Temukan Software Original Tanpa Bayar
                    </h1>
                    <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto mb-8 font-medium">
                        Direktori kurasi perangkat lunak open-source, freeware, dan alternatif legal gratis pengganti software berbayar untuk produktivitas Anda.
                    </p>

                    <div class="max-w-3xl mx-auto bg-white p-3 rounded-2xl border-2 border-slate-900 shadow-[5px_5px_0px_#1e293b] flex flex-col sm:flex-row items-center gap-3">
                        <div class="flex items-center gap-3 px-3 w-full flex-grow">
                            <i class="fa-solid fa-magnifying-glass text-slate-400 text-lg"></i>
                            <input type="text" id="home-search-input" placeholder="Cari nama software (cth: Blender, VS Code, GIMP)..." class="w-full py-2 bg-transparent text-slate-800 focus:outline-none text-sm font-medium">
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto border-t sm:border-t-0 sm:border-l border-slate-200 pt-2 sm:pt-0 sm:pl-3">
                            <select id="home-category-filter" class="bg-slate-100 border border-slate-300 text-slate-800 text-sm font-semibold rounded-xl px-3 py-2.5 focus:outline-none">
                                <option value="">Semua Kategori</option>
                                <?php foreach (CATEGORIES as $slug => $c): ?>
                                    <option value="<?= lf_e($slug) ?>"><?= lf_e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button onclick="handleHeroSearch()" class="bg-cyan-500 hover:bg-cyan-600 text-slate-900 border-2 border-slate-900 px-6 py-2.5 rounded-xl font-bold text-sm transition-all shadow-[2px_2px_0px_#1e293b] whitespace-nowrap w-full sm:w-auto">
                                Cari
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-2 mt-6 text-xs text-slate-600">
                        <span class="font-bold">Populer:</span>
                        <button onclick="quickSearch('Blender')" class="px-3 py-1 bg-white border border-slate-300 rounded-lg hover:border-slate-900 font-semibold transition-colors">Blender</button>
                        <button onclick="quickSearch('VS Code')" class="px-3 py-1 bg-white border border-slate-300 rounded-lg hover:border-slate-900 font-semibold transition-colors">VS Code</button>
                        <button onclick="quickSearch('VLC')" class="px-3 py-1 bg-white border border-slate-300 rounded-lg hover:border-slate-900 font-semibold transition-colors">VLC Player</button>
                        <button onclick="quickSearch('Godot')" class="px-3 py-1 bg-white border border-slate-300 rounded-lg hover:border-slate-900 font-semibold transition-colors">Godot Engine</button>
                    </div>
                </div>
            </section>

            <!-- MILESTONES -->
            <section id="milestones" class="py-16 bg-white border-b-2 border-slate-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-2xl mx-auto mb-12">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Reward Milestone Penjelajah</h2>
                        <p class="text-slate-600 text-sm mt-2 font-medium">Selesaikan aktivitas eksplorasi setiap bulan untuk membuka peti hadiah dan badge kontributor legal.</p>
                    </div>

                    <div class="max-w-4xl mx-auto mb-12 bg-slate-50 p-6 rounded-2xl border-2 border-slate-900 shadow-[4px_4px_0px_#1e293b]">
                        <div class="flex justify-between items-center mb-4">
                            <span class="font-bold text-slate-900 text-sm"><i class="fa-solid fa-chart-line text-cyan-600 mr-2"></i> Progress Milestone Anda</span>
                            <span class="text-xs font-pixel text-cyan-600 bg-cyan-50 px-2 py-1 rounded border border-cyan-200">Level 2 / 6 Unlocked</span>
                        </div>
                        <div class="relative flex items-center justify-between my-6 px-4">
                            <div class="absolute left-6 right-6 top-1/2 -translate-y-1/2 h-3 bg-slate-200 rounded-full border border-slate-900 z-0"></div>
                            <div class="absolute left-6 w-1/3 top-1/2 -translate-y-1/2 h-3 bg-cyan-500 rounded-full border border-slate-900 z-0"></div>
                            <div class="relative z-10 w-8 h-8 rounded-full bg-cyan-400 border-2 border-slate-900 flex items-center justify-center font-bold text-slate-900 text-xs shadow-[2px_2px_0px_#1e293b]">1</div>
                            <div class="relative z-10 w-8 h-8 rounded-full bg-cyan-400 border-2 border-slate-900 flex items-center justify-center font-bold text-slate-900 text-xs shadow-[2px_2px_0px_#1e293b]">2</div>
                            <div class="relative z-10 w-8 h-8 rounded-full bg-amber-300 border-2 border-slate-900 flex items-center justify-center font-bold text-slate-900 text-xs shadow-[2px_2px_0px_#1e293b]">3</div>
                            <div class="relative z-10 w-8 h-8 rounded-full bg-white border-2 border-slate-900 flex items-center justify-center font-bold text-slate-400 text-xs">4</div>
                            <div class="relative z-10 w-8 h-8 rounded-full bg-white border-2 border-slate-900 flex items-center justify-center font-bold text-slate-400 text-xs">5</div>
                            <div class="relative z-10 w-8 h-8 rounded-full bg-white border-2 border-slate-900 flex items-center justify-center font-bold text-slate-400 text-xs">6</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div class="tier-starter p-4 rounded-2xl border-2 card-layered flex flex-col justify-between items-center text-center relative overflow-hidden">
                            <span class="absolute top-2 right-2 text-[9px] font-pixel bg-emerald-500 text-white px-1.5 py-0.5 rounded border border-slate-900">UNLOCKED</span>
                            <div class="my-3 text-4xl text-rose-600"><i class="fa-solid fa-box-open"></i></div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Starter Chest</h4>
                                <p class="text-[10px] text-slate-600 mt-1 font-medium">Unduh 1 Aplikasi Legal</p>
                            </div>
                        </div>
                        <div class="tier-bronze p-4 rounded-2xl border-2 card-layered flex flex-col justify-between items-center text-center relative overflow-hidden">
                            <span class="absolute top-2 right-2 text-[9px] font-pixel bg-emerald-500 text-white px-1.5 py-0.5 rounded border border-slate-900">UNLOCKED</span>
                            <div class="my-3 text-4xl text-slate-600"><i class="fa-solid fa-shield-halved"></i></div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Bronze Badge</h4>
                                <p class="text-[10px] text-slate-600 mt-1 font-medium">Beri 3 Ulasan Ulasan</p>
                            </div>
                        </div>
                        <div class="tier-silver p-4 rounded-2xl border-2 card-layered flex flex-col justify-between items-center text-center relative overflow-hidden">
                            <span class="absolute top-2 right-2 text-[9px] font-pixel bg-amber-500 text-slate-900 px-1.5 py-0.5 rounded border border-slate-900">PROSES</span>
                            <div class="my-3 text-4xl text-amber-600"><i class="fa-solid fa-award"></i></div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Silver Chest</h4>
                                <p class="text-[10px] text-slate-600 mt-1 font-medium">Simpan 5 Wishlist</p>
                            </div>
                        </div>
                        <div class="tier-gold p-4 rounded-2xl border-2 card-layered flex flex-col justify-between items-center text-center relative overflow-hidden opacity-75">
                            <span class="absolute top-2 right-2 text-[9px] font-pixel bg-slate-400 text-white px-1.5 py-0.5 rounded border border-slate-900">LOCKED</span>
                            <div class="my-3 text-4xl text-cyan-600"><i class="fa-solid fa-gem"></i></div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Gold Chest</h4>
                                <p class="text-[10px] text-slate-600 mt-1 font-medium">Bagikan 10 Software</p>
                            </div>
                        </div>
                        <div class="tier-diamond p-4 rounded-2xl border-2 card-layered flex flex-col justify-between items-center text-center relative overflow-hidden opacity-75">
                            <span class="absolute top-2 right-2 text-[9px] font-pixel bg-slate-400 text-white px-1.5 py-0.5 rounded border border-slate-900">LOCKED</span>
                            <div class="my-3 text-4xl text-purple-600"><i class="fa-solid fa-crown"></i></div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Diamond Chest</h4>
                                <p class="text-[10px] text-slate-600 mt-1 font-medium">20 Aplikasi Terverifikasi</p>
                            </div>
                        </div>
                        <div class="tier-master p-4 rounded-2xl border-2 card-layered flex flex-col justify-between items-center text-center relative overflow-hidden opacity-75">
                            <span class="absolute top-2 right-2 text-[9px] font-pixel bg-slate-400 text-white px-1.5 py-0.5 rounded border border-slate-900">LOCKED</span>
                            <div class="my-3 text-4xl text-orange-600"><i class="fa-solid fa-dragon"></i></div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Master Chest</h4>
                                <p class="text-[10px] text-slate-600 mt-1 font-medium">Top 1% Kontributor</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- KATEGORI -->
            <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-end mb-10">
                    <div>
                        <span class="text-cyan-600 font-extrabold text-xs uppercase tracking-wider font-pixel">Jelajahi Berdasarkan Sektor</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Kategori Pilihan</h2>
                    </div>
                    <button onclick="switchPage('catalog')" class="text-sm font-bold text-slate-900 hover:text-cyan-600 flex items-center gap-1">
                        Lihat Semua <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach (CATEGORIES as $slug => $c): ?>
                        <div onclick="filterByCategory('<?= lf_e($slug) ?>')" class="card-layered p-6 rounded-2xl cursor-pointer">
                            <div class="w-12 h-12 rounded-xl bg-<?= lf_e($c['color']) ?>-100 border-2 border-slate-900 flex items-center justify-center text-slate-900 text-xl mb-4 font-bold">
                                <i class="fa-solid <?= lf_e($c['icon']) ?>"></i>
                            </div>
                            <h3 class="font-bold text-slate-900 text-lg mb-1"><?= lf_e($c['name']) ?></h3>
                            <p class="text-xs text-slate-600 mb-4 font-medium"><?= lf_e($c['desc']) ?></p>
                            <span class="text-xs font-bold text-cyan-600 flex items-center gap-1">Jelajahi <i class="fa-solid fa-arrow-right text-[10px]"></i></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- TRENDING -->
            <section class="py-16 bg-slate-900 text-white border-t-2 border-slate-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-end mb-10">
                        <div>
                            <span class="text-amber-400 font-extrabold text-xs uppercase tracking-wider font-pixel">Trending Minggu Ini</span>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-1">Top Pilihan Editor</h2>
                        </div>
                        <button onclick="switchPage('catalog')" class="text-sm font-bold text-amber-400 hover:text-amber-300 flex items-center gap-1">
                            Lihat Semua <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                    <div id="trending-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
                </div>
            </section>

            <!-- CTA SUBMIT -->
            <section class="py-16 bg-cyan-50 border-t-2 border-slate-900">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border-2 border-slate-900 text-slate-900 text-xs font-bold mb-6 shadow-[2px_2px_0px_#1e293b]">
                        <i class="fa-solid fa-hand-holding-heart text-rose-500"></i> Kontribusi Komunitas
                    </div>
                    <h2 class="text-3xl font-extrabold text-slate-900 mb-4">Punya Software Legal Gratis yang Ingin Direkomendasikan?</h2>
                    <p class="text-slate-600 mb-8 max-w-2xl mx-auto">Bantu kami memperluas direktori ini dengan software open-source atau freeware favorit Anda. Setiap submission akan diverifikasi sebelum dipublikasikan.</p>
                    <button onclick="switchPage('submit')" class="inline-flex items-center gap-2 bg-slate-900 text-amber-400 border-2 border-slate-900 px-8 py-3.5 rounded-xl font-bold shadow-[4px_4px_0px_#0e7490] hover:translate-x-1 hover:translate-y-1 transition-all">
                        <i class="fa-solid fa-paper-plane"></i> Submit Software Sekarang
                    </button>
                </div>
            </section>
        </div>

        <!-- ================= PAGE 2: CATALOG ================= -->
        <div id="page-catalog" class="page-view hidden">
            <section class="bg-slate-900 text-white py-12 border-b-2 border-slate-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <span class="text-amber-400 font-extrabold text-xs uppercase tracking-wider font-pixel">Katalog Lengkap</span>
                    <h1 class="text-3xl sm:text-4xl font-extrabold mt-1">Jelajahi Semua Aplikasi Legal</h1>
                    <p class="text-slate-300 text-sm mt-2 font-medium">Filter berdasarkan kategori, platform, atau lisensi untuk menemukan software gratis yang sesuai kebutuhan Anda.</p>
                </div>
            </section>

            <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Filter Sidebar -->
                    <aside class="lg:col-span-1">
                        <div class="bg-white p-5 rounded-2xl border-2 border-slate-900 shadow-[4px_4px_0px_#1e293b] sticky top-24">
                            <h3 class="font-extrabold text-slate-900 mb-4 flex items-center gap-2"><i class="fa-solid fa-filter text-cyan-600"></i> Filter Pencarian</h3>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Kunci</label>
                                <input type="text" id="filter-search" oninput="applyFilters()" placeholder="Cari software..." class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-sm font-medium focus:border-cyan-500 focus:outline-none">
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategori</label>
                                <select id="filter-category" onchange="applyFilters()" class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-sm font-semibold focus:border-cyan-500 focus:outline-none">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach (CATEGORIES as $slug => $c): ?>
                                        <option value="<?= lf_e($slug) ?>"><?= lf_e($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-700 mb-2">Platform</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                                        <input type="checkbox" name="platform" value="Windows" onchange="applyFilters()" class="w-4 h-4 accent-cyan-500"> Windows
                                    </label>
                                    <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                                        <input type="checkbox" name="platform" value="Mac" onchange="applyFilters()" class="w-4 h-4 accent-cyan-500"> Mac
                                    </label>
                                    <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                                        <input type="checkbox" name="platform" value="Linux" onchange="applyFilters()" class="w-4 h-4 accent-cyan-500"> Linux
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-700 mb-2">Lisensi & Fitur</label>
                                <label class="flex items-center gap-2 text-sm font-medium cursor-pointer mb-2">
                                    <input type="checkbox" id="filter-opensource" onchange="applyFilters()" class="w-4 h-4 accent-cyan-500"> Open Source Only
                                </label>
                                <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                                    <input type="checkbox" id="filter-noads" onchange="applyFilters()" class="w-4 h-4 accent-cyan-500"> Tanpa Iklan
                                </label>
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Urutkan</label>
                                <select id="sort-by" onchange="applyFilters()" class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-sm font-semibold focus:border-cyan-500 focus:outline-none">
                                    <option value="popular">Paling Populer</option>
                                    <option value="rating">Rating Tertinggi</option>
                                    <option value="name">Nama (A-Z)</option>
                                </select>
                            </div>

                            <button onclick="resetFilters()" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-900 border-2 border-slate-900 py-2 rounded-xl font-bold text-xs transition-colors">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Reset Filter
                            </button>
                        </div>
                    </aside>

                    <!-- Catalog Grid -->
                    <div class="lg:col-span-3">
                        <div class="flex justify-between items-center mb-6">
                            <span id="result-count" class="text-sm font-bold text-slate-700"></span>
                        </div>
                        <div id="catalog-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
                    </div>
                </div>
            </section>
        </div>

        <!-- ================= PAGE 3: DETAIL ================= -->
        <div id="page-detail" class="page-view hidden">
            <section class="bg-slate-900 text-white py-10 border-b-2 border-slate-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <button onclick="switchPage('catalog')" class="text-amber-400 text-xs font-bold hover:text-amber-300 flex items-center gap-1 mb-4">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog
                    </button>
                    <div class="flex flex-col sm:flex-row items-start gap-6">
                        <img id="detail-icon" src="" alt="App Icon" class="w-20 h-20 rounded-2xl object-contain bg-white p-2 border-2 border-slate-900 shadow-[3px_3px_0px_#000]">
                        <div class="flex-grow">
                            <h1 id="detail-name" class="text-3xl sm:text-4xl font-extrabold"></h1>
                            <p id="detail-developer" class="text-slate-300 text-sm mt-1 font-medium"></p>
                            <div class="flex flex-wrap items-center gap-4 mt-3 text-sm">
                                <span class="flex items-center gap-1 text-amber-400 font-bold"><i class="fa-solid fa-star"></i> <span id="detail-rating"></span> <span class="text-slate-400 text-xs font-medium">(<span id="detail-reviews-count"></span> ulasan)</span></span>
                                <span class="flex items-center gap-1 text-cyan-400 font-bold"><i class="fa-solid fa-download"></i> <span id="detail-downloads"></span></span>
                                <span id="detail-license-badge" class="px-2 py-0.5 bg-emerald-500 text-slate-900 text-xs font-bold rounded border border-slate-900"></span>
                            </div>
                        </div>
                        <div class="flex gap-2 w-full sm:w-auto">
                            <button onclick="toggleWishlistCurrent()" class="p-3 rounded-xl border-2 border-slate-900 bg-white text-slate-900 hover:bg-slate-100 transition-colors" title="Wishlist">
                                <i class="fa-solid fa-bookmark"></i>
                            </button>
                            <a id="detail-download-btn" href="#" target="_blank" class="flex-grow sm:flex-grow-0 inline-flex items-center justify-center gap-2 bg-cyan-500 text-slate-900 border-2 border-slate-900 px-6 py-3 rounded-xl font-bold text-sm shadow-[3px_3px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 transition-all whitespace-nowrap">
                                <i class="fa-solid fa-download"></i> Kunjungi Website
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                        <h2 class="text-xl font-extrabold text-slate-900 mb-3">Tentang Aplikasi</h2>
                        <p id="detail-description" class="text-slate-700 text-sm leading-relaxed font-medium mb-8"></p>

                        <h2 class="text-xl font-extrabold text-slate-900 mb-3">Fitur Utama</h2>
                        <ul id="detail-features" class="space-y-2 mb-8 text-sm font-medium text-slate-700"></ul>

                        <h2 class="text-xl font-extrabold text-slate-900 mb-3">Screenshot</h2>
                        <div id="detail-screenshots" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8"></div>

                        <h2 class="text-xl font-extrabold text-slate-900 mb-3">Ulasan Pengguna</h2>
                        <div id="detail-reviews" class="space-y-3"></div>
                    </div>

                    <aside>
                        <div class="bg-white p-5 rounded-2xl border-2 border-slate-900 shadow-[4px_4px_0px_#1e293b] sticky top-24">
                            <h3 class="font-extrabold text-slate-900 mb-4 flex items-center gap-2"><i class="fa-solid fa-circle-info text-cyan-600"></i> Informasi Teknis</h3>
                            <ul class="space-y-3 text-sm">
                                <li class="flex justify-between items-center pb-3 border-b border-slate-200">
                                    <span class="text-slate-600 font-bold">Lisensi</span>
                                    <span id="detail-license" class="font-bold text-slate-900 text-right"></span>
                                </li>
                                <li class="flex justify-between items-center pb-3 border-b border-slate-200">
                                    <span class="text-slate-600 font-bold">Ukuran</span>
                                    <span id="detail-size" class="font-bold text-slate-900"></span>
                                </li>
                                <li class="flex justify-between items-center pb-3 border-b border-slate-200">
                                    <span class="text-slate-600 font-bold">Update</span>
                                    <span id="detail-updated" class="font-bold text-slate-900"></span>
                                </li>
                                <li class="flex justify-between items-start pb-3 border-b border-slate-200">
                                    <span class="text-slate-600 font-bold">Platform</span>
                                    <span id="detail-platforms" class="flex flex-wrap gap-1 justify-end max-w-[60%]"></span>
                                </li>
                                <li class="flex justify-between items-center">
                                    <span class="text-slate-600 font-bold">Website</span>
                                    <a id="detail-website" href="#" target="_blank" class="font-bold text-cyan-600 text-xs truncate max-w-[60%]"></a>
                                </li>
                            </ul>
                        </div>
                    </aside>
                </div>
            </section>
        </div>

        <!-- ================= PAGE 4: SUBMIT ================= -->
        <div id="page-submit" class="page-view hidden">
            <section class="bg-slate-900 text-white py-12 border-b-2 border-slate-900">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <span class="text-amber-400 font-extrabold text-xs uppercase tracking-wider font-pixel">Kontribusi Komunitas</span>
                    <h1 class="text-3xl sm:text-4xl font-extrabold mt-1">Submit Software Legal Gratis</h1>
                    <p class="text-slate-300 text-sm mt-2 font-medium">Bantu direktori kami berkembang. Semua submission diverifikasi sebelum dipublikasikan.</p>
                </div>
            </section>

            <section class="py-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <form method="POST" action="<?= lf_url('submit') ?>" class="bg-white p-6 sm:p-8 rounded-2xl border-2 border-slate-900 shadow-[4px_4px_0px_#1e293b] space-y-5">

                    <div>
                        <label for="f-name" class="block text-xs font-bold text-slate-700 mb-1.5">Nama Aplikasi *</label>
                        <input type="text" id="f-name" name="name" required maxlength="80" value="<?= lf_e($_SESSION['form']['name'] ?? '') ?>" class="w-full px-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm font-medium focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="f-tagline" class="block text-xs font-bold text-slate-700 mb-1.5">Tagline Singkat *</label>
                        <input type="text" id="f-tagline" name="tagline" required maxlength="160" placeholder="cth: Editor gambar gratis pengganti Photoshop" value="<?= lf_e($_SESSION['form']['tagline'] ?? '') ?>" class="w-full px-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm font-medium focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="f-desc" class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi Lengkap * (min. 30 karakter)</label>
                        <textarea id="f-desc" name="description" required minlength="30" maxlength="2000" rows="5" placeholder="Jelaskan kegunaan, fitur utama, dan kenapa software ini legal & gratis..." class="w-full px-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm font-medium focus:border-cyan-500 focus:outline-none resize-y"><?= lf_e($_SESSION['form']['description'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="f-cat" class="block text-xs font-bold text-slate-700 mb-1.5">Kategori *</label>
                            <select id="f-cat" name="category" required class="w-full px-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-cyan-500 focus:outline-none">
                                <?php foreach (CATEGORIES as $slug => $c): ?>
                                    <option value="<?= lf_e($slug) ?>" <?= (($_SESSION['form']['category'] ?? 'Productivity') === $slug) ? 'selected' : '' ?>><?= lf_e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="f-website" class="block text-xs font-bold text-slate-700 mb-1.5">Website Resmi</label>
                            <input type="url" id="f-website" name="website" placeholder="https://example.org" value="<?= lf_e($_SESSION['form']['website'] ?? '') ?>" class="w-full px-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm font-medium focus:border-cyan-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="f-notes" class="block text-xs font-bold text-slate-700 mb-1.5">Catatan untuk Moderator (opsional)</label>
                        <textarea id="f-notes" name="notes" maxlength="1000" rows="3" placeholder="Lisensi, link download resmi, atau info lain..." class="w-full px-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm font-medium focus:border-cyan-500 focus:outline-none resize-y"><?= lf_e($_SESSION['form']['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-grow sm:flex-grow-0 inline-flex items-center justify-center gap-2 bg-cyan-500 text-slate-900 border-2 border-slate-900 px-8 py-3 rounded-xl font-bold text-sm shadow-[3px_3px_0px_#1e293b] hover:translate-x-0.5 hover:translate-y-0.5 transition-all">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Submission
                        </button>
                        <button type="button" onclick="switchPage('home')" class="bg-white text-slate-700 border-2 border-slate-300 px-6 py-3 rounded-xl font-bold text-sm hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </section>
        </div>

    </main>

    <!-- FOOTER -->
    <footer class="bg-slate-900 text-slate-300 py-12 border-t-2 border-slate-900 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-8 mb-8">
                <div class="sm:col-span-2">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 rounded-xl bg-cyan-500 border-2 border-slate-900 flex items-center justify-center text-white">
                            <i class="fa-solid fa-shield-cat text-slate-900"></i>
                        </div>
                        <span class="text-lg font-extrabold text-white font-pixel">Legal<span class="text-cyan-400">Free</span></span>
                    </div>
                    <p class="text-sm text-slate-400 max-w-md font-medium">Direktori software legal gratis — open-source, freeware, dan alternatif pengganti software berbayar. 100% bebas malware & pembajakan.</p>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-3 text-sm">Kategori</h4>
                    <ul class="space-y-2 text-xs">
                        <?php foreach (CATEGORIES as $slug => $c): ?>
                            <li><a href="#" onclick="filterByCategory('<?= lf_e($slug) ?>'); return false;" class="hover:text-cyan-400 transition-colors"><?= lf_e($c['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-3 text-sm">Tautan</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#" onclick="switchPage('home'); return false;" class="hover:text-cyan-400 transition-colors">Beranda</a></li>
                        <li><a href="#" onclick="switchPage('catalog'); return false;" class="hover:text-cyan-400 transition-colors">Katalog</a></li>
                        <li><a href="#" onclick="switchPage('submit'); return false;" class="hover:text-cyan-400 transition-colors">Submit Software</a></li>
                        <li><a href="<?= lf_url('smoke-test') ?>" class="hover:text-cyan-400 transition-colors">Smoke Test</a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-6 border-t border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs">
                <p class="text-slate-500">© <?= date('Y') ?> LegalFree. Dibangun dengan PHP & SQLite. Tanpa tracking.</p>
                <p class="text-slate-500 font-pixel">100% Verified Software</p>
            </div>
        </div>
    </footer>

    <!-- WISHLIST MODAL -->
    <div id="wishlist-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50">
        <div class="bg-white max-w-md w-full rounded-2xl border-2 border-slate-900 shadow-[6px_6px_0px_#1e293b]">
            <div class="flex justify-between items-center p-4 border-b-2 border-slate-900">
                <h3 class="font-extrabold text-slate-900 flex items-center gap-2"><i class="fa-solid fa-bookmark text-cyan-600"></i> Wishlist (<span id="wishlist-count-modal">0</span>)</h3>
                <button onclick="toggleWishlistModal()" class="text-slate-400 hover:text-slate-900 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div id="wishlist-items" class="p-4 max-h-80 overflow-y-auto"></div>
        </div>
    </div>

    <?= $flash ?>

    <script>
        // Apps data injected from PHP/SQLite
        const appsData = <?= $appsJson ?>;

        let wishlist = JSON.parse(localStorage.getItem('legalfree_wishlist') || '[]');
        let currentAppId = null;

        document.addEventListener('DOMContentLoaded', () => {
            renderTrending();
            renderCatalog(appsData);
            updateWishlistUI();
        });

        function switchPage(pageId) {
            document.querySelectorAll('.page-view').forEach(el => el.classList.add('hidden'));
            const page = document.getElementById('page-' + pageId);
            if (page) {
                page.classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                if (pageId === 'catalog') applyFilters();
            }
        }

        function renderTrending() {
            const container = document.getElementById('trending-grid');
            if (!container) return;
            container.innerHTML = appsData.slice(0, 3).map(app => createCardHTML(app, true)).join('');
        }

        function createCardHTML(app, darkMode = false) {
            const isWishlisted = wishlist.includes(app.id);
            return `
                <div class="card-layered p-6 rounded-2xl flex flex-col justify-between group ${darkMode ? 'bg-slate-800 border-slate-900 text-white' : ''}">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <img src="${app.icon}" alt="${app.name}" class="w-14 h-14 rounded-xl object-contain bg-white p-2 border-2 border-slate-900 shadow-[2px_2px_0px_#1e293b]" onerror="this.style.display='none'">
                            <button onclick="event.stopPropagation(); toggleWishlist(${app.id})" class="p-2 rounded-xl border-2 border-slate-900 ${isWishlisted ? 'bg-cyan-100 text-cyan-600' : (darkMode ? 'bg-slate-700 text-slate-400' : 'bg-white text-slate-400')} hover:text-cyan-600 transition-colors">
                                <i class="fa-solid fa-bookmark"></i>
                            </button>
                        </div>
                        <h3 onclick="openDetail(${app.id})" class="font-extrabold ${darkMode ? 'text-white' : 'text-slate-900'} text-lg cursor-pointer hover:text-cyan-600 transition-colors mb-1">${app.name}</h3>
                        <p class="text-xs font-bold text-cyan-600 mb-2">${app.category} • <span class="${darkMode ? 'text-slate-400' : 'text-slate-500'}">${app.developer}</span></p>
                        <p class="${darkMode ? 'text-slate-300' : 'text-slate-600'} text-xs font-medium line-clamp-2 mb-4">${app.description}</p>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold ${darkMode ? 'text-slate-300' : 'text-slate-600'} pt-3 border-t ${darkMode ? 'border-slate-700' : 'border-slate-200'} mb-3">
                            <span class="text-amber-500"><i class="fa-solid fa-star"></i> ${app.rating}</span>
                            <span><i class="fa-solid fa-download text-cyan-600"></i> ${app.downloads}</span>
                        </div>
                        <button onclick="openDetail(${app.id})" class="w-full bg-slate-900 hover:bg-cyan-600 text-white py-2 rounded-xl font-bold text-xs shadow-[2px_2px_0px_#1e293b] transition-all">
                            Detail & Unduh
                        </button>
                    </div>
                </div>
            `;
        }

        function renderCatalog(data) {
            const container = document.getElementById('catalog-grid');
            const countEl = document.getElementById('result-count');
            if (!container) return;
            if (countEl) countEl.innerText = `Menampilkan ${data.length} aplikasi`;
            if (data.length === 0) {
                container.innerHTML = `<div class="col-span-2 text-center py-12 text-slate-500 font-bold">Tidak ada aplikasi yang cocok.</div>`;
                return;
            }
            container.innerHTML = data.map(app => createCardHTML(app)).join('');
        }

        function applyFilters() {
            const searchVal = (document.getElementById('filter-search')?.value || '').toLowerCase();
            const catVal = document.getElementById('filter-category')?.value || '';
            const openSourceOnly = document.getElementById('filter-opensource')?.checked || false;
            const noAdsOnly = document.getElementById('filter-noads')?.checked || false;
            const sortBy = document.getElementById('sort-by')?.value || 'popular';
            const selectedPlatforms = Array.from(document.querySelectorAll('input[name="platform"]:checked')).map(cb => cb.value);

            let filtered = appsData.filter(app => {
                let matchSearch = app.name.toLowerCase().includes(searchVal) || app.description.toLowerCase().includes(searchVal);
                let matchCat = catVal === "" || app.category === catVal;
                let matchOpenSource = !openSourceOnly || app.isOpenSource;
                let matchNoAds = !noAdsOnly || app.noAds;
                let matchPlatform = selectedPlatforms.length === 0 || selectedPlatforms.some(p => app.platforms.includes(p));
                return matchSearch && matchCat && matchOpenSource && matchNoAds && matchPlatform;
            });

            if (sortBy === 'popular') filtered.sort((a, b) => b.reviewsCount - a.reviewsCount);
            else if (sortBy === 'rating') filtered.sort((a, b) => b.rating - a.rating);
            else if (sortBy === 'name') filtered.sort((a, b) => a.name.localeCompare(b.name));

            renderCatalog(filtered);
        }

        function resetFilters() {
            const search = document.getElementById('filter-search');
            const cat = document.getElementById('filter-category');
            const os = document.getElementById('filter-opensource');
            const na = document.getElementById('filter-noads');
            if (search) search.value = '';
            if (cat) cat.value = '';
            if (os) os.checked = false;
            if (na) na.checked = false;
            document.querySelectorAll('input[name="platform"]').forEach(cb => cb.checked = false);
            applyFilters();
        }

        function filterByCategory(cat) {
            switchPage('catalog');
            setTimeout(() => {
                const catEl = document.getElementById('filter-category');
                if (catEl) catEl.value = cat;
                applyFilters();
            }, 50);
        }

        function quickSearch(keyword) {
            switchPage('catalog');
            setTimeout(() => {
                const searchEl = document.getElementById('filter-search');
                if (searchEl) searchEl.value = keyword;
                applyFilters();
            }, 50);
        }

        function handleHeroSearch() {
            const query = document.getElementById('home-search-input').value;
            const cat = document.getElementById('home-category-filter').value;
            switchPage('catalog');
            setTimeout(() => {
                const searchEl = document.getElementById('filter-search');
                const catEl = document.getElementById('filter-category');
                if (searchEl) searchEl.value = query;
                if (catEl) catEl.value = cat;
                applyFilters();
            }, 50);
        }

        function openDetail(id) {
            const app = appsData.find(a => a.id === id);
            if (!app) return;
            currentAppId = id;

            document.getElementById('detail-icon').src = app.icon;
            document.getElementById('detail-name').innerText = app.name;
            document.getElementById('detail-developer').innerText = `Developer: ${app.developer}`;
            document.getElementById('detail-rating').innerText = app.rating;
            document.getElementById('detail-reviews-count').innerText = app.reviewsCount;
            document.getElementById('detail-downloads').innerText = app.downloads;
            document.getElementById('detail-license-badge').innerText = app.license;
            document.getElementById('detail-description').innerText = app.description;
            document.getElementById('detail-download-btn').href = app.website;
            document.getElementById('detail-license').innerText = app.license;
            document.getElementById('detail-size').innerText = app.size;
            document.getElementById('detail-updated').innerText = app.updated;
            const websiteEl = document.getElementById('detail-website');
            websiteEl.innerText = app.website;
            websiteEl.href = app.website;

            document.getElementById('detail-features').innerHTML = app.features.map(f => `<li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600 font-bold"></i> ${f}</li>`).join('');
            document.getElementById('detail-platforms').innerHTML = app.platforms.map(p => `<span class="px-2.5 py-1 bg-slate-100 border border-slate-900 text-slate-800 text-xs font-bold rounded-lg">${p}</span>`).join('');
            document.getElementById('detail-screenshots').innerHTML = app.screenshots.map(s => `<img src="${s}" class="rounded-xl border-2 border-slate-900 object-cover h-36 w-full" onerror="this.style.display='none'">`).join('');
            document.getElementById('detail-reviews').innerHTML = app.reviews.map(r => `
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-slate-900 text-xs">${r.user}</span>
                        <span class="text-[10px] text-slate-400">${r.date}</span>
                    </div>
                    <div class="text-amber-500 text-xs mb-1">${'<i class="fa-solid fa-star"></i>'.repeat(r.rating)}</div>
                    <p class="text-xs text-slate-600 font-medium">${r.comment}</p>
                </div>
            `).join('');

            switchPage('detail');
        }

        function toggleWishlist(id) {
            const index = wishlist.indexOf(id);
            if (index > -1) wishlist.splice(index, 1);
            else wishlist.push(id);
            localStorage.setItem('legalfree_wishlist', JSON.stringify(wishlist));
            updateWishlistUI();
            renderTrending();
            applyFilters();
        }

        function toggleWishlistCurrent() {
            if (currentAppId !== null) toggleWishlist(currentAppId);
        }

        function updateWishlistUI() {
            const badge = document.getElementById('wishlist-badge');
            const container = document.getElementById('wishlist-items');
            const countModal = document.getElementById('wishlist-count-modal');
            if (countModal) countModal.innerText = wishlist.length;

            if (wishlist.length > 0) {
                if (badge) {
                    badge.innerText = wishlist.length;
                    badge.classList.remove('hidden');
                }
            } else {
                if (badge) badge.classList.add('hidden');
            }

            if (container) {
                if (wishlist.length === 0) {
                    container.innerHTML = `<div class="text-center py-12 text-slate-400 text-xs font-bold">Wishlist Anda kosong.</div>`;
                } else {
                    const wishApps = appsData.filter(a => wishlist.includes(a.id));
                    container.innerHTML = wishApps.map(app => `
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-300 mb-2">
                            <div class="flex items-center gap-3 cursor-pointer" onclick="toggleWishlistModal(); openDetail(${app.id})">
                                <img src="${app.icon}" class="w-10 h-10 rounded-lg object-contain bg-white p-1 border border-slate-300" onerror="this.style.display='none'">
                                <div>
                                    <p class="font-bold text-slate-900 text-xs">${app.name}</p>
                                    <p class="text-[10px] text-slate-500">${app.category}</p>
                                </div>
                            </div>
                            <button onclick="toggleWishlist(${app.id})" class="text-rose-500 hover:text-rose-700 text-xs font-bold">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    `).join('');
                }
            }
        }

        function toggleWishlistModal() {
            const modal = document.getElementById('wishlist-modal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
    </script>
</body>
</html>
<?php
}
