<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-lg bg-primary-50 p-4 dark:bg-primary-400/10">
            <div class="text-sm font-medium text-primary-600 dark:text-primary-400">Total Target</div>
            <div class="mt-1 text-2xl font-bold text-primary-900 dark:text-primary-100">
                {{ number_format($target->total_target_tahunan, 0, ',', '.') }}
            </div>
            <div class="text-xs text-primary-600 dark:text-primary-400">Unit</div>
        </div>

        <div class="rounded-lg bg-success-50 p-4 dark:bg-success-400/10">
            <div class="text-sm font-medium text-success-600 dark:text-success-400">Realisasi</div>
            <div class="mt-1 text-2xl font-bold text-success-900 dark:text-success-100">
                {{ number_format($target->total_realisasi, 0, ',', '.') }}
            </div>
            <div class="text-xs text-success-600 dark:text-success-400">Unit</div>
        </div>

        <div class="rounded-lg bg-info-50 p-4 dark:bg-info-400/10">
            <div class="text-sm font-medium text-info-600 dark:text-info-400">Pencapaian</div>
            <div class="mt-1 text-2xl font-bold text-info-900 dark:text-info-100">
                {{ number_format($target->persentase_pencapaian, 1) }}%
            </div>
            <div class="text-xs text-info-600 dark:text-info-400">Persentase</div>
        </div>

        <div class="rounded-lg {{ $target->selisih >= 0 ? 'bg-success-50 dark:bg-success-400/10' : 'bg-danger-50 dark:bg-danger-400/10' }} p-4">
            <div class="text-sm font-medium {{ $target->selisih >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                Selisih
            </div>
            <div class="mt-1 text-2xl font-bold {{ $target->selisih >= 0 ? 'text-success-900 dark:text-success-100' : 'text-danger-900 dark:text-danger-100' }}">
                {{ number_format($target->selisih, 0, ',', '.') }}
            </div>
            <div class="text-xs {{ $target->selisih >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                Unit
            </div>
        </div>
    </div>

    <!-- Monthly Table -->
    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Bulan
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Target
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Realisasi
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Selisih
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        %
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Status
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                @foreach($comparison as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $item['nama_bulan'] }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">
                        {{ number_format($item['target'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">
                        {{ number_format($item['realisasi'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                        <span class="{{ $item['selisih'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }} font-medium">
                            {{ number_format($item['selisih'], 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $item['persentase'] >= 100 ? 'bg-success-100 text-success-800 dark:bg-success-400/10 dark:text-success-400' : '' }}
                            {{ $item['persentase'] >= 80 && $item['persentase'] < 100 ? 'bg-info-100 text-info-800 dark:bg-info-400/10 dark:text-info-400' : '' }}
                            {{ $item['persentase'] >= 60 && $item['persentase'] < 80 ? 'bg-warning-100 text-warning-800 dark:bg-warning-400/10 dark:text-warning-400' : '' }}
                            {{ $item['persentase'] < 60 ? 'bg-danger-100 text-danger-800 dark:bg-danger-400/10 dark:text-danger-400' : '' }}
                        ">
                            {{ number_format($item['persentase'], 1) }}%
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $item['status'] === 'Locked' ? 'bg-gray-100 text-gray-800 dark:bg-gray-400/10 dark:text-gray-400' : 'bg-primary-100 text-primary-800 dark:bg-primary-400/10 dark:text-primary-400' }}
                        ">
                            {{ $item['status'] === 'Locked' ? '🔒 Terkunci' : '✏️ Editable' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
