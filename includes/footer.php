    </main>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm pointer-events-none"></div>

    <!-- Footer -->
    <footer class="bg-stone-900 text-stone-400 border-t border-stone-800 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-10">
                
                <!-- Brand -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-stone-800 border border-stone-700 flex items-center justify-center text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-white font-heading">
                            appfinder<span class="text-brand-500">.</span>
                        </span>
                    </div>
                    <p class="text-stone-500 text-sm leading-relaxed max-w-sm">
                        Direktori independen kurasi software legal, open-source, dan freeware berkualitas tinggi.
                    </p>
                    <div class="flex items-center gap-2 text-xs font-mono">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-stone-800 border border-stone-700 text-stone-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Verified
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-stone-800 border border-stone-700 text-stone-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-400"></span> No Malware
                        </span>
                    </div>
                </div>

                <!-- Nav -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Navigasi</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="?page=home" class="hover:text-white transition-colors">Beranda</a></li>
                        <li><a href="?page=explore" class="hover:text-white transition-colors">Jelajahi Katalog</a></li>
                        <li><a href="?page=explore&filter=open-source" class="hover:text-white transition-colors">Open Source</a></li>
                        <li><a href="?page=dashboard" class="hover:text-white transition-colors">Daftar Tersimpan</a></li>
                    </ul>
                </div>

                <!-- Categories -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Kategori</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="?page=explore&c=7" class="hover:text-white transition-colors">Design & 3D</a></li>
                        <li><a href="?page=explore&c=4" class="hover:text-white transition-colors">Development</a></li>
                        <li><a href="?page=explore&c=2" class="hover:text-white transition-colors">Office</a></li>
                        <li><a href="?page=explore&c=3" class="hover:text-white transition-colors">Multimedia</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Transparansi</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="?page=home#transparency" class="hover:text-white transition-colors">Prinsip Kurasi</a></li>
                        <li><a href="?page=home#alternatives" class="hover:text-white transition-colors">Indeks Alternatif</a></li>
                        <li><a href="?page=register" class="hover:text-white transition-colors">Kontribusi</a></li>
                    </ul>
                </div>

            </div>

            <div class="mt-12 pt-8 border-t border-stone-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-500">
                <p>&copy; <?= date('Y') ?> AppFinder. Hak cipta software milik pengembang masing-masing.</p>
                <p class="font-mono text-[11px]">Bukan bagian dari Google LLC • Bebas APK Modifikasi</p>
            </div>
        </div>
    </footer>

    <!-- JS: Bookmark & Toast -->
    <script>
        (function () {
            const navLinks = Array.from(document.querySelectorAll('[data-desktop-nav]'));
            if (!navLinks.length) return;

            const currentPage = () => new URLSearchParams(window.location.search).get('page') || 'home';
            const setActiveNav = (section = '') => {
                const page = currentPage();
                navLinks.forEach((link) => {
                    const isSectionLink = link.dataset.navSection !== '';
                    const isActive = isSectionLink
                        ? page === 'home' && link.dataset.navSection === section
                        : page === link.dataset.navPage && section === '';
                    link.classList.toggle('nav-pill-active', isActive);
                    link.toggleAttribute('aria-current', isActive);
                });
            };

            const syncNavWithLocation = () => setActiveNav(window.location.hash.slice(1));
            window.addEventListener('hashchange', syncNavWithLocation);
            syncNavWithLocation();

            if (currentPage() === 'home' && 'IntersectionObserver' in window) {
                const sections = ['categories', 'alternatives']
                    .map((id) => document.getElementById(id))
                    .filter(Boolean);
                const observer = new IntersectionObserver((entries) => {
                    const visibleSection = entries
                        .filter((entry) => entry.isIntersecting)
                        .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
                    if (visibleSection) setActiveNav(visibleSection.target.id);
                }, { threshold: 0.35 });
                sections.forEach((section) => observer.observe(section));
            }
        })();

        function showToast(message, isSuccess = true) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `px-4 py-3 rounded-xl text-xs font-medium border shadow-lg transition-all duration-300 transform translate-y-2 pointer-events-auto flex items-center gap-2 ${
                isSuccess 
                    ? 'bg-stone-900 text-white border-stone-700' 
                    : 'bg-rose-900 text-rose-100 border-rose-700'
            }`;
            toast.innerHTML = `
                <svg class="w-4 h-4 ${isSuccess ? 'text-emerald-400' : 'text-rose-400'} flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${isSuccess ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"></path>
                </svg>
                <span>${message}</span>
            `;
            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-2');
                toast.classList.add('translate-y-0');
            });
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        async function toggleBookmark(appId, btnElement) {
            if (btnElement) {
                btnElement.disabled = true;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const response = await fetch('index.php?action=toggle_bookmark', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': csrfToken
                    },
                    body: new URLSearchParams({ id: String(appId) })
                });
                const data = await response.json();

                if (response.status === 401) {
                    showToast('Silakan login terlebih dahulu untuk menyimpan software.', false);
                    return false;
                }

                if (!response.ok) {
                    showToast(data.error || 'Gagal mengubah software tersimpan.', false);
                    return false;
                }

                if (data.status !== 'added' && data.status !== 'removed') {
                    showToast('Respons server tidak dikenali. Coba muat ulang halaman.', false);
                    return false;
                }

                if (data.status === 'added') {
                    showToast('Software berhasil disimpan ke daftar Anda.');
                    if (btnElement) {
                        btnElement.classList.add('text-brand-600', 'bg-brand-50');
                        btnElement.classList.remove('text-stone-400', 'bg-white');
                        const icon = btnElement.querySelector('svg');
                        if (icon) icon.setAttribute('fill', 'currentColor');
                        btnElement.setAttribute('aria-pressed', 'true');
                    }
                } else if (data.status === 'removed') {
                    showToast('Software dihapus dari daftar tersimpan.');
                    if (btnElement) {
                        btnElement.classList.remove('text-brand-600', 'bg-brand-50');
                        btnElement.classList.add('text-stone-400', 'bg-white');
                        const icon = btnElement.querySelector('svg');
                        if (icon) icon.setAttribute('fill', 'none');
                        btnElement.setAttribute('aria-pressed', 'false');
                    }
                }

                return true;
            } catch (err) {
                console.error('Bookmark update failed:', err);
                showToast('Gagal mengubah status simpan.', false);
                return false;
            } finally {
                if (btnElement) {
                    btnElement.disabled = false;
                }
            }
        }
    </script>
</body>
</html>
