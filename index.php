<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Validate session user against DB
if (isset($_SESSION['user_id'])) {
    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmtCheck->execute([$_SESSION['user_id']]);
    if (!$stmtCheck->fetch()) {
        unset($_SESSION['user_id'], $_SESSION['role']);
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Action Handler (e.g. AJAX Bookmark Toggle)
if (isset($_GET['action']) && $_GET['action'] === 'toggle_bookmark') {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $submittedToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($submittedToken) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid security token. Refresh the page and try again.']);
        exit;
    }

    $appIdInput = $_POST['id'] ?? null;
    $appId = is_string($appIdInput)
        ? filter_var($appIdInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
        : false;
    if ($appId === false) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid app ID']);
        exit;
    }

    $userId = (int)$_SESSION['user_id'];

    $appCheck = $pdo->prepare("SELECT id FROM apps WHERE id = ?");
    $appCheck->execute([$appId]);
    if (!$appCheck->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Software not found']);
        exit;
    }

    $insert = $pdo->prepare("
        INSERT INTO bookmarks (user_id, app_id) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE id = id
    ");
    $insert->execute([$userId, $appId]);

    if ($insert->rowCount() === 1) {
        echo json_encode(['status' => 'added']);
    } else {
        $del = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND app_id = ?");
        $del->execute([$userId, $appId]);
        echo json_encode(['status' => 'removed']);
    }
    exit;
}

$page = $_GET['page'] ?? 'home';

$allowed_pages = ['home', 'explore', 'detail', 'login', 'register', 'dashboard', 'logout'];
$admin_pages = ['index', 'apps', 'category'];

if (in_array($page, $allowed_pages) && file_exists(__DIR__ . "/pages/{$page}.php")) {
    require __DIR__ . "/pages/{$page}.php";
} elseif (isset($_GET['admin']) && in_array($_GET['admin'], $admin_pages) && file_exists(__DIR__ . "/pages/admin/{$_GET['admin']}.php")) {
    require __DIR__ . "/pages/admin/{$_GET['admin']}.php";
} else {
    require __DIR__ . "/pages/home.php";
}
