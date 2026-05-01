<div class="sidebar">

    {{-- ===== USER PROFILE ===== --}}
    <div class="sb-profile">
        <div class="sb-avatar">
            @if(Auth::check() && Auth::user()->profile_photo)
                <img src="{{ asset('storage/profile-photos/'.Auth::user()->profile_photo) }}"
                     alt="Profile"
                     style="width:100%;height:100%;object-fit:cover;border-radius:50%;"
                     onerror="this.style.display='none'">
            @else
                <i class="fas fa-user"></i>
            @endif
        </div>
        <div class="sb-name">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</div>
        <div class="sb-role">{{ Auth::check() ? ucfirst(Auth::user()->role) : '' }}</div>
    </div>

    {{-- ===== NAV ===== --}}
    <div class="sb-nav">

        @if(Auth::check() && Auth::user()->role === 'pegawai')
        {{-- PEGAWAI --}}
        <a href="{{ route('pegawai.dashboard') }}" class="sb-link {{ request()->is('pegawai/dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i><span>Dashboard</span>
        </a>
        <div class="sb-section">PRESENSI</div>
        <a href="{{ route('pegawai.presensi.absen-wajah') }}" class="sb-link {{ request()->is('pegawai/presensi/absen-wajah') ? 'active' : '' }}">
            <i class="fas fa-camera"></i><span>Absen Wajah</span>
        </a>
        <a href="{{ route('pegawai.riwayat-presensi') }}" class="sb-link {{ request()->is('pegawai/riwayat-presensi') ? 'active' : '' }}">
            <i class="fas fa-history"></i><span>Riwayat Presensi</span>
        </a>
        <div class="sb-section">PENGGAJIAN</div>
        <a href="{{ route('pegawai.slip-gaji.index') }}" class="sb-link {{ request()->is('pegawai/slip-gaji*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i><span>Slip Gaji</span>
        </a>

        @else
        {{-- OWNER / ADMIN --}}

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" class="sb-link {{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i><span>Dashboard</span>
        </a>

        {{-- MENU UTAMA label --}}
        <div class="sb-section">MENU UTAMA</div>

        {{-- Master Data --}}
        <button class="sb-collapse {{ request()->is('master-data*') ? 'open' : '' }}" data-target="menu-master">
            <i class="fas fa-database"></i>
            <span>Master Data</span>
            <i class="fas fa-chevron-down sb-arrow"></i>
        </button>
        <div class="sb-submenu {{ request()->is('master-data*') ? 'show' : '' }}" id="menu-master">
            <a href="{{ route('master-data.coa.index') }}"                          class="sb-sub {{ request()->is('master-data/coa*') ? 'active' : '' }}">COA</a>
            <a href="{{ route('master-data.aset.index') }}"                         class="sb-sub {{ request()->is('master-data/aset*') ? 'active' : '' }}">Aset</a>
            <a href="{{ route('master-data.satuan.dashboard') }}"                   class="sb-sub {{ request()->is('master-data/satuan*') ? 'active' : '' }}">Satuan</a>
            <a href="{{ route('master-data.kualifikasi-tenaga-kerja.index') }}"     class="sb-sub {{ request()->is('master-data/kualifikasi*') ? 'active' : '' }}">Kualifikasi TK</a>
            <a href="{{ route('master-data.pegawai.index') }}"                      class="sb-sub {{ request()->is('master-data/pegawai*') ? 'active' : '' }}">Pegawai</a>
            <a href="{{ route('master-data.vendor.index') }}"                       class="sb-sub {{ request()->is('master-data/vendor*') ? 'active' : '' }}">Vendor</a>
            <a href="{{ route('master-data.pelanggan.index') }}"                    class="sb-sub {{ request()->is('master-data/pelanggan*') ? 'active' : '' }}">Pelanggan</a>
            <a href="{{ route('master-data.bahan-baku.index') }}"                   class="sb-sub {{ request()->is('master-data/bahan-baku*') ? 'active' : '' }}">Bahan Baku</a>
            <a href="{{ route('master-data.bahan-pendukung.index') }}"              class="sb-sub {{ request()->is('master-data/bahan-pendukung*') ? 'active' : '' }}">Bahan Pendukung</a>
            <a href="{{ route('master-data.produk.index') }}"                       class="sb-sub {{ request()->is('master-data/produk*') ? 'active' : '' }}">Produk</a>
            <a href="{{ route('master-data.biaya-bahan.index') }}"                  class="sb-sub {{ request()->is('master-data/biaya-bahan*') ? 'active' : '' }}">Biaya Bahan Baku</a>
            <a href="{{ route('master-data.btkl.index') }}"                         class="sb-sub {{ request()->is('master-data/btkl*') ? 'active' : '' }}">BTKL</a>
            <a href="{{ route('master-data.bop.index') }}"                          class="sb-sub {{ request()->is('master-data/bop*') ? 'active' : '' }}">BOP</a>
            <a href="{{ route('master-data.harga-pokok-produksi.index') }}"         class="sb-sub {{ request()->is('master-data/harga-pokok*') ? 'active' : '' }}">Harga Pokok Produksi</a>
        </div>

        {{-- Transaksi --}}
        <button class="sb-collapse {{ request()->is('transaksi*') ? 'open' : '' }}" data-target="menu-transaksi">
            <i class="fas fa-exchange-alt"></i>
            <span>Transaksi</span>
            <i class="fas fa-chevron-down sb-arrow"></i>
        </button>
        <div class="sb-submenu {{ request()->is('transaksi*') ? 'show' : '' }}" id="menu-transaksi">
            <a href="{{ route('transaksi.produksi.index') }}"        class="sb-sub {{ request()->is('transaksi/produksi*') ? 'active' : '' }}">Produksi</a>
            <a href="{{ route('transaksi.pembelian.index') }}"       class="sb-sub {{ request()->is('transaksi/pembelian*') ? 'active' : '' }}">Pembelian</a>
            <a href="{{ route('transaksi.penjualan.index') }}"       class="sb-sub {{ request()->is('transaksi/penjualan*') ? 'active' : '' }}">Penjualan</a>
            <a href="{{ route('transaksi.presensi.index') }}"        class="sb-sub {{ request()->is('transaksi/presensi*') ? 'active' : '' }}">Presensi</a>
            <a href="{{ route('transaksi.penggajian.index') }}"      class="sb-sub {{ request()->is('transaksi/penggajian*') ? 'active' : '' }}">Penggajian</a>
            <a href="{{ route('transaksi.pembayaran-beban.index') }}" class="sb-sub {{ request()->is('transaksi/pembayaran-beban*') ? 'active' : '' }}">Pembayaran Beban</a>
            <a href="{{ route('transaksi.pelunasan-utang.index') }}"  class="sb-sub {{ request()->is('transaksi/pelunasan-utang*') ? 'active' : '' }}">Pelunasan Utang</a>
        </div>

        {{-- Laporan --}}
        <button class="sb-collapse {{ request()->is('laporan*') || request()->is('akuntansi*') ? 'open' : '' }}" data-target="menu-laporan">
            <i class="fas fa-chart-bar"></i>
            <span>Laporan</span>
            <i class="fas fa-chevron-down sb-arrow"></i>
        </button>
        <div class="sb-submenu {{ request()->is('laporan*') || request()->is('akuntansi*') ? 'show' : '' }}" id="menu-laporan">
            <a href="{{ route('laporan.pembelian.index') }}"             class="sb-sub {{ request()->is('laporan/pembelian*') ? 'active' : '' }}">Laporan Pembelian</a>
            <a href="{{ route('laporan.stok') }}"                        class="sb-sub {{ request()->is('laporan/stok*') ? 'active' : '' }}">Laporan Stok</a>
            <a href="{{ route('laporan.penjualan') }}"                   class="sb-sub {{ request()->is('laporan/penjualan*') ? 'active' : '' }}">Laporan Penjualan</a>
            <a href="{{ route('laporan.penggajian') }}"                  class="sb-sub {{ request()->is('laporan/penggajian*') ? 'active' : '' }}">Laporan Penggajian</a>
            <a href="{{ route('laporan.pembayaran-beban') }}"            class="sb-sub {{ request()->is('laporan/pembayaran-beban*') ? 'active' : '' }}">Laporan Pembayaran Beban</a>
            <a href="{{ route('laporan.pelunasan-utang') }}"             class="sb-sub {{ request()->is('laporan/pelunasan-utang*') ? 'active' : '' }}">Laporan Pelunasan Utang</a>
            <a href="{{ route('laporan.kas-bank') }}"                    class="sb-sub {{ request()->is('laporan/kas-bank*') ? 'active' : '' }}">Laporan Kas &amp; Bank</a>
            <a href="{{ route('akuntansi.jurnal-umum') }}"               class="sb-sub {{ request()->is('akuntansi/jurnal-umum*') ? 'active' : '' }}">Jurnal Umum</a>
            <a href="{{ route('akuntansi.buku-besar') }}"                class="sb-sub {{ request()->is('akuntansi/buku-besar*') ? 'active' : '' }}">Buku Besar</a>
            <a href="{{ route('akuntansi.neraca-saldo-temp') }}"         class="sb-sub {{ request()->is('akuntansi/neraca-saldo*') ? 'active' : '' }}">Neraca Saldo</a>
            <a href="{{ route('akuntansi.laporan-posisi-keuangan') }}"   class="sb-sub {{ request()->is('akuntansi/laporan-posisi-keuangan') ? 'active' : '' }}">Posisi Keuangan</a>
            <a href="{{ route('akuntansi.laba-rugi') }}"                 class="sb-sub {{ request()->is('akuntansi/laba-rugi*') ? 'active' : '' }}">Laba Rugi</a>
        </div>

        {{-- Pengaturan --}}
        <button class="sb-collapse {{ request()->is('tentang-perusahaan*') || request()->is('profile*') ? 'open' : '' }}" data-target="menu-pengaturan">
            <i class="fas fa-cog"></i>
            <span>Pengaturan</span>
            <i class="fas fa-chevron-down sb-arrow"></i>
        </button>
        <div class="sb-submenu {{ request()->is('tentang-perusahaan*') || request()->is('profile*') ? 'show' : '' }}" id="menu-pengaturan">
            @if(auth()->check() && auth()->user()->role === 'owner')
            <a href="/tentang-perusahaan/detail" class="sb-sub {{ request()->is('tentang-perusahaan*') ? 'active' : '' }}">Tentang Perusahaan</a>
            @endif
            <a href="{{ route('profil-admin') }}" class="sb-sub {{ request()->is('profile*') ? 'active' : '' }}">Profil</a>
        </div>

        {{-- Catalog --}}
        <button class="sb-collapse {{ request()->is('kelola-catalog*') ? 'open' : '' }}" data-target="menu-catalog">
            <i class="fas fa-store"></i>
            <span>Catalog</span>
            <i class="fas fa-chevron-down sb-arrow"></i>
        </button>
        <div class="sb-submenu {{ request()->is('kelola-catalog*') ? 'show' : '' }}" id="menu-catalog">
            <a href="{{ route('kelola-catalog.index') }}" class="sb-sub {{ request()->is('kelola-catalog*') ? 'active' : '' }}">Kelola Catalog</a>
        </div>

        @endif

        {{-- Logout --}}
        <div class="sb-logout-wrap">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sb-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>

    </div>{{-- end sb-nav --}}

    {{-- ===== FOOTER ===== --}}
    <div class="sb-footer">
        <div class="sb-footer-title">Informasi Sistem</div>
        <div class="sb-footer-row">
            <i class="fas fa-circle-info"></i>
            <div>
                <div class="sb-footer-label">Versi Aplikasi</div>
                <div class="sb-footer-val">v1.0.0</div>
            </div>
        </div>
        <div class="sb-footer-row">
            <i class="fas fa-database"></i>
            <div>
                <div class="sb-footer-label">Database</div>
                <div class="sb-footer-db">
                    <span class="sb-db-dot"></span> Terhubung
                </div>
            </div>
        </div>
        <div class="sb-footer-copy">© 2026 SIMCOST<br>All rights reserved.</div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.sb-collapse').forEach(function (btn) {
        var targetId = btn.getAttribute('data-target');
        var menu     = document.getElementById(targetId);

        btn.addEventListener('click', function () {
            var isOpen = btn.classList.contains('open');
            // tutup semua
            document.querySelectorAll('.sb-collapse').forEach(function(b){
                b.classList.remove('open');
            });
            document.querySelectorAll('.sb-submenu').forEach(function(m){
                m.classList.remove('show');
            });
            // buka yang diklik jika sebelumnya tertutup
            if (!isOpen) {
                btn.classList.add('open');
                if (menu) menu.classList.add('show');
            }
        });
    });
});
</script>
