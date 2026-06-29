<div class="space-y-6">
    <div class="text-center mb-4">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Dashboard Target Produksi {{ $tahun }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ringkasan pencapaian target produksi tahun berjalan</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Total Target -->
        <div class="rounded-lg bg-gradient-to-br from-primary-500 to-primary-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-100">Total Target Tahunan</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($summary['total_target'], 0, ',', '.') }}</p>
                    <p class="text-xs text-primary-100 mt-1">Unit</p>
                </div>
                <div class="rounded-full bg-primary-400/30 p-3">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Realisasi -->
        <div class="rounded-lg bg-gradient-to-br from-success-500 to-success-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-success-100">Total Realisasi</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($summary['total_realisasi'], 0, ',', '.') }}</p>
                    <p class="text-xs text-success-100 mt-1">Unit</p>
                </div>
                <div class="rounded-full bg-success-400/30 p-3">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Persentase -->
        <div class="rounded-lg bg-gradient-to-br from-info-500 to-info-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-info-100">Persentase Pencapaian</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($summary['persentase'], 1) }}%</p>
                    <p class="text-xs text-info-100 mt-1">
                        @if($summary['selisih'] >= 0)
                            <span class="text-success-200">▲ {{ number_format($summary['selisih'], 0, ',', '.') }} Unit</span>
                        @else
                            <span class="text-danger-200">▼ {{ number_format(abs($summary['selisih']), 0, ',', '.') }} Unit</span>
                        @endif
                    </p>
                </div>
                <div class="rounded-full bg-info-400/30 p-3">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-primary-100 dark:bg-primary-400/10 p-3">
                    <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah Produk</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $summary['jumlah_produk'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-warning-100 dark:bg-warning-400/10 p-3">
                    <svg class="h-6 w-6 text-warning-600 dark:text-warning-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Bulan yang Dapat Diedit</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $summary['bulan_editable'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress Pencapaian</span>
            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($summary['persentase'], 1) }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
            <div class="h-4 rounded-full transition-all duration-500 {{ $summary['persentase'] >= 100 ? 'bg-success-600' : 'bg-primary-600' }}" 
                 style="width: {{ min($summary['persentase'], 100) }}%">
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            @if($summary['persentase'] >= 100)
                🎉 Target tercapai! Selamat atas pencapaian luar biasa.
            @elseif($summary['persentase'] >= 80)
                📈 Hampir tercapai! Terus pertahankan kinerja.
            @elseif($summary['persentase'] >= 60)
                ⚠️ Perlu peningkatan untuk mencapai target.
            @else
                🔴 Perlu perhatian serius untuk meningkatkan produksi.
            @endif
        </p>
    </div>
</div>
