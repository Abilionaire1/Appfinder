<?php
if (isset($_SESSION['user_id'])) {
    header('Location: ?page=dashboard');
    exit;
}

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailInput = $_POST['email'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    $email = is_string($emailInput) ? trim($emailInput) : '';
    $password = is_string($passwordInput) ? $passwordInput : '';

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

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <a href="?page=home" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-500">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"></path></svg>
        Kembali ke beranda
    </a>

    <div class="mt-5 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-[0_20px_70px_-40px_rgba(28,25,23,0.3)]">
        <div class="grid lg:grid-cols-2">
            <section class="px-6 py-9 sm:px-10 sm:py-12 lg:px-14 lg:py-14">
                <a href="?page=home" class="mb-10 inline-flex items-center gap-2.5" aria-label="AppFinder, beranda">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-stone-900 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016Z"></path></svg>
                    </span>
                    <span class="text-lg font-bold tracking-tight text-stone-900 font-heading">appfinder<span class="text-brand-500">.</span></span>
                </a>

                <div class="mb-8">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-brand-700">Selamat datang kembali</p>
                    <h1 class="text-3xl font-extrabold tracking-tight text-stone-900 font-heading sm:text-4xl">Masuk ke akun Anda</h1>
                    <p class="mt-3 max-w-sm text-sm leading-6 text-slate-600">Lanjutkan mengelola software pilihan dan koleksi yang Anda simpan.</p>
                </div>

                <?php if (isset($_GET['registered'])): ?>
                <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        Pendaftaran berhasil. Silakan masuk dengan akun baru Anda.
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <div>
                        <label for="login-email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                        <input
                            id="login-email"
                            type="email"
                            name="email"
                            autocomplete="username"
                            required
                            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="nama@email.com"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                        >
                    </div>

                    <div>
                        <label for="login-password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                        <input
                            id="login-password"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            placeholder="Masukkan password"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                        >
                    </div>

                    <button
                        type="submit"
                        class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-stone-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-stone-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                    >
                        Masuk ke akun
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7 7 7-7 7"></path></svg>
                    </button>
                </form>

                <p class="mt-7 text-center text-sm text-slate-600">
                    Belum punya akun?
                    <a href="?page=register" class="font-semibold text-brand-700 underline-offset-4 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500">Daftar sekarang</a>
                </p>
            </section>

            <aside class="relative isolate overflow-hidden border-t border-stone-200 bg-stone-50 px-6 py-10 sm:px-10 sm:py-12 lg:border-l lg:border-t-0 lg:px-12 lg:py-14">
                <div class="pointer-events-none absolute inset-0 -z-10 opacity-60" aria-hidden="true" style="background-image: linear-gradient(to right, rgba(214,211,209,.45) 1px, transparent 1px), linear-gradient(to bottom, rgba(214,211,209,.45) 1px, transparent 1px); background-size: 32px 32px; mask-image: linear-gradient(to bottom right, black, transparent 85%);"></div>
                <div class="pointer-events-none absolute -right-16 -top-16 -z-10 h-56 w-56 rounded-full bg-brand-100/70 blur-3xl" aria-hidden="true"></div>

                <div class="flex h-full flex-col justify-center">
                    <div class="mb-8 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-brand-200 bg-white text-brand-700 shadow-sm">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v18m9-9H3m15.364-6.364-12.728 12.728m12.728 0L5.636 5.636"></path></svg>
                    </div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-brand-700">Temukan pilihan yang tepat</p>
                    <h2 class="max-w-md text-3xl font-extrabold leading-tight tracking-tight text-stone-900 font-heading sm:text-4xl">Software bagus, tanpa biaya yang tidak perlu.</h2>
                    <p class="mt-4 max-w-md text-sm leading-6 text-slate-600">Temukan software legal, gratis, dan open-source yang dikurasi untuk berbagai kebutuhan.</p>

                    <div class="mt-9 space-y-4">
                        <div class="flex items-start gap-3 rounded-2xl border border-stone-200/80 bg-white/85 p-4 shadow-sm">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"></path></svg>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-stone-900">Legal dan terkurasi</p>
                                <p class="mt-1 text-xs leading-5 text-slate-600">Jelajahi alternatif yang jelas sumber dan lisensinya.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 rounded-2xl border border-stone-200/80 bg-white/85 p-4 shadow-sm">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16l-7-3.5L5 21V5Z"></path></svg>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-stone-900">Simpan untuk nanti</p>
                                <p class="mt-1 text-xs leading-5 text-slate-600">Kumpulkan software favorit dalam satu dashboard.</p>
                            </div>
                        </div>
                    </div>

                    <p class="mt-8 text-xs font-medium text-slate-500">Pilihan lebih bijak untuk setiap perangkat.</p>
                </div>
            </aside>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
