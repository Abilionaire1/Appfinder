<?php
require_once __DIR__ . '/../includes/header.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ?page=dashboard');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, password, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header('Location: ?page=dashboard');
        exit;
    } else {
        $error = 'Email atau password tidak sesuai.';
    }
}
?>

<div class="max-w-md mx-auto my-16 px-4">
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-navy font-heading">Masuk ke AppFinder</h1>
            <p class="text-xs text-slate-500 mt-1">Akses daftar software tersimpan dan rekomendasi aplikasi Anda</p>
        </div>

        <?php if (isset($_GET['registered'])): ?>
            <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 p-3 rounded-lg mb-4 text-xs font-medium">
                Pendaftaran berhasil! Silakan masuk dengan akun baru Anda.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-rose-50 text-rose-700 border border-rose-200 p-3 rounded-lg mb-4 text-xs font-medium">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Email Terdaftar</label>
                <input 
                    type="email" 
                    name="email" 
                    required 
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
                    placeholder="••••••••" 
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-brand-500 hover:bg-brand-600 text-white py-2.5 rounded-xl font-semibold text-xs transition-colors shadow-sm mt-2"
            >
                Masuk
            </button>
        </form>

        <p class="mt-6 text-xs text-slate-500 text-center">
            Belum memiliki akun? <a href="?page=register" class="text-brand-600 hover:underline font-semibold">Daftar sekarang</a>
        </p>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
