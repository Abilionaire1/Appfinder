<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ?page=dashboard');
    exit;
}

$submittedToken = $_POST['csrf_token'] ?? '';
if (!is_string($submittedToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
    http_response_code(403);
    require_once __DIR__ . '/../includes/header.php';
    ?>
    <section class="max-w-2xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-16">
        <div role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-rose-900">
            <h1 class="text-lg font-bold">Permintaan keluar tidak valid</h1>
            <p class="mt-2 text-sm">Muat ulang dashboard lalu konfirmasikan keluar sekali lagi.</p>
            <a href="?page=dashboard" class="mt-5 inline-flex rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700">Kembali ke dashboard</a>
        </div>
    </section>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();
header('Location: index.php?page=home');
exit;
