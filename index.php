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

// Action Handler (e.g. AJAX Bookmark Toggle)
if (isset($_GET['action']) && $_GET['action'] === 'toggle_bookmark') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $appId = (int)($_GET['id'] ?? 0);
    $userId = (int)$_SESSION['user_id'];
    
    if ($appId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid app ID']);
        exit;
    }
    
    // Check if already bookmarked
    $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND app_id = ?");
    $stmt->execute([$userId, $appId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $del = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND app_id = ?");
        $del->execute([$userId, $appId]);
        echo json_encode(['status' => 'removed']);
    } else {
        $ins = $pdo->prepare("INSERT INTO bookmarks (user_id, app_id) VALUES (?, ?)");
        $ins->execute([$userId, $appId]);
        echo json_encode(['status' => 'added']);
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
