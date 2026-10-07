<?php
require_once __DIR__ . '/../includes/header.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ?page=dashboard');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $error = 'Email atau username sudah terdaftar.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
            if ($stmt->execute([$username, $email, $hash])) {
                header('Location: ?page=login&registered=1');
                exit;
            } else {
                $error = 'Terjadi kesalahan sistem saat mendaftar.';
            }
        }
    }
}
?>

<div class="max-w-md mx-auto my-16 px-4">
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-navy font-heading">Daftar Akun AppFinder</h1>
            <p class="text-xs text-slate-500 mt-1">Simpan software favorit Anda dan berkontribusi mengurasi aplikasi</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-rose-50 text-rose-700 border border-rose-200 p-3 rounded-lg mb-4 text-xs font-medium">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Username</label>
                <input 
                    type="text" 
                    name="username" 
                    required 
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    placeholder="nama_pengguna" 
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm"
                >
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Email</label>
                <input 
                    type="email" 
                    name="email" 
                    required 
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    placeholder="nama@email.com" 
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm"
                >
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    required 
                    placeholder="Minimal 6 karakter" 
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-brand-500 hover:bg-brand-600 text-white py-2.5 rounded-xl font-semibold text-xs transition-colors shadow-sm mt-2"
            >
                Daftar Akun
            </button>
        </form>

        <p class="mt-6 text-xs text-slate-500 text-center">
            Sudah memiliki akun? <a href="?page=login" class="text-brand-600 hover:underline font-semibold">Masuk di sini</a>
        </p>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
