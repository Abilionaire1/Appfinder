    </main>

    <!-- Global Toast Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm pointer-events-none"></div>

    <!-- I. Footer -->
    <footer class="bg-navy-900 text-slate-300 border-t border-slate-800 text-sm mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-10">
                
                <!-- Column 1: Brand & Mission -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-navy-800 border border-slate-700 flex items-center justify-center text-brand-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-white font-heading">
                            appfinder<span class="text-brand-400">.</span>
                        </span>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                        Direktori independen kurasi software legal, open-source, dan freeware berkualitas tinggi. Kami membantu Anda menemukan alternatif perangkat lunak yang aman, transparan, dan bebas dari lisensi bajakan.
                    </p>
                    <div class="flex items-center gap-3 pt-2 text-xs font-mono text-slate-400">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-800/80 border border-slate-700/60 text-slate-300">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span> 100% Verified Sources
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-800/80 border border-slate-700/60 text-slate-300">
                            <span class="w-2 h-2 rounded-full bg-brand-400"></span> No Malware Guarantee
                        </span>
                    </div>
                </div>

                <!-- Column 2: Platform Navigation -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white font-mono mb-4">Navigasi</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="?page=home" class="hover:text-white text-slate-400 transition-colors">Beranda</a></li>
                        <li><a href="?page=explore" class="hover:text-white text-slate-400 transition-colors">Jelajahi Katalog</a></li>
                        <li><a href="?page=explore&filter=open-source" class="hover:text-white text-slate-400 transition-colors">Software Open Source</a></li>
                        <li><a href="?page=explore&filter=freeware" class="hover:text-white text-slate-400 transition-colors">Freeware &amp; Tools Gratis</a></li>
                        <li><a href="?page=dashboard" class="hover:text-white text-slate-400 transition-colors">Daftar Tersimpan</a></li>
                    </ul>
                </div>

                <!-- Column 3: Top Categories -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white font-mono mb-4">Kategori</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="?page=explore&c=7" class="hover:text-white text-slate-400 transition-colors">Design &amp; 3D Creative</a></li>
                        <li><a href="?page=explore&c=4" class="hover:text-white text-slate-400 transition-colors">Development &amp; Coding</a></li>
                        <li><a href="?page=explore&c=2" class="hover:text-white text-slate-400 transition-colors">Office &amp; Productivity</a></li>
                        <li><a href="?page=explore&c=3" class="hover:text-white text-slate-400 transition-colors">Editing &amp; Multimedia</a></li>
                        <li><a href="?page=explore&c=1" class="hover:text-white text-slate-400 transition-colors">Education &amp; Belajar</a></li>
                    </ul>
                </div>

                <!-- Column 4: Standar & Legal -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white font-mono mb-4">Transparansi</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="?page=home#transparency" class="hover:text-white text-slate-400 transition-colors">Prinsip Kurasi</a></li>
                        <li><a href="?page=home#alternatives" class="hover:text-white text-slate-400 transition-colors">Indeks Alternatif</a></li>
                        <li><a href="?page=register" class="hover:text-white text-slate-400 transition-colors">Kontribusi Komunitas</a></li>
                        <li><span class="text-slate-500 cursor-not-allowed">Kebijakan Privasi</span></li>
                        <li><span class="text-slate-500 cursor-not-allowed">Disclaimer Resmi</span></li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Disclaimer & Copyright -->
            <div class="mt-12 pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                <p>
                    &copy; <?= date('Y') ?> AppFinder. Seluruh hak cipta dan merek dagang software yang terdaftar adalah milik masing-masing pengembang resmi.
                </p>
                <p class="font-mono text-[11px] text-slate-400">
                    Bukan bagian dari Google LLC • Bebas APK Modifikasi
                </p>
            </div>
        </div>
    </footer>

    <!-- Minimal Interactivity Script: Bookmark Toggling & Toast System -->
    <script>
        function showToast(message, isSuccess = true) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `px-4 py-3 rounded-lg text-xs font-medium border shadow-lg transition-all duration-300 transform translate-y-2 pointer-events-auto flex items-center gap-2 ${
                isSuccess 
                    ? 'bg-slate-900 text-white border-slate-700 shadow-slate-900/50' 
                    : 'bg-rose-900 text-rose-100 border-rose-700'
            }`;
            toast.innerHTML = `
                <svg class="w-4 h-4 ${isSuccess ? 'text-brand-400' : 'text-rose-400'} flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${isSuccess ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"></path>
                </svg>
                <span>${message}</span>
            `;
            container.appendChild(toast);
            
            // Animate in
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-2');
                toast.classList.add('translate-y-0');
            });
            
            // Auto dismiss
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        async function toggleBookmark(appId, btnElement) {
            try {
                const response = await fetch(`index.php?action=toggle_bookmark&id=${appId}`);
                if (response.status === 401) {
                    showToast('Silakan login terlebih dahulu untuk menyimpan software.', false);
                    return;
                }
                const data = await response.json();
                if (data.status === 'added') {
                    showToast('Software berhasil disimpan ke daftar Anda.');
                    if (btnElement) {
                        btnElement.classList.add('text-brand-600', 'bg-brand-50');
                        btnElement.classList.remove('text-slate-400', 'bg-white');
                        const icon = btnElement.querySelector('svg');
                        if (icon) icon.setAttribute('fill', 'currentColor');
                    }
                } else if (data.status === 'removed') {
                    showToast('Software dihapus dari daftar tersimpan.');
                    if (btnElement) {
                        btnElement.classList.remove('text-brand-600', 'bg-brand-50');
                        btnElement.classList.add('text-slate-400', 'bg-white');
                        const icon = btnElement.querySelector('svg');
                        if (icon) icon.setAttribute('fill', 'none');
                    }
                }
            } catch (err) {
                showToast('Gagal mengubah status simpan.', false);
            }
        }
    </script>
</body>
</html>
