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
                    Pencapaian
                </th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Status
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
            @foreach($getState() as $item)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $item['nama_bulan'] }}
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right font-medium">
                    {{ number_format($item['target'], 0, ',', '.') }} Unit
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                    <span class="font-medium {{ $item['realisasi'] >= $item['target'] ? 'text-success-600 dark:text-success-400' : 'text-gray-900 dark:text-gray-100' }}">
                        {{ number_format($item['realisasi'], 0, ',', '.') }} Unit
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                    <span class="{{ $item['selisih'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }} font-medium">
                        {{ $item['selisih'] >= 0 ? '+' : '' }}{{ number_format($item['selisih'], 0, ',', '.') }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $item['persentase'] >= 100 ? 'bg-success-100 text-success-800 dark:bg-success-400/10 dark:text-success-400' : '' }}
                        {{ $item['persentase'] >= 80 && $item['persentase'] < 100 ? 'bg-info-100 text-info-800 dark:bg-info-400/10 dark:text-info-400' : '' }}
                        {{ $item['persentase'] >= 60 && $item['persentase'] < 80 ? 'bg-warning-100 text-warning-800 dark:bg-warning-400/10 dark:text-warning-400' : '' }}
                        {{ $item['persentase'] < 60 ? 'bg-gray-100 text-gray-800 dark:bg-gray-400/10 dark:text-gray-400' : '' }}
                    ">
                        {{ number_format($item['persentase'], 1) }}%
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    @if($item['status'] === 'Locked')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-400/10 dark:text-gray-400">
                            <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                            Terkunci
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-400/10 dark:text-primary-400">
                            <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            Editable
                        </span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-gray-100">
                    TOTAL
                </td>
                <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-gray-100 text-right">
                    {{ number_format(collect($getState())->sum('target'), 0, ',', '.') }} Unit
                </td>
                <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-gray-100 text-right">
                    {{ number_format(collect($getState())->sum('realisasi'), 0, ',', '.') }} Unit
                </td>
                <td class="px-4 py-3 text-sm font-bold text-right">
                    @php
                        $totalSelisih = collect($getState())->sum('realisasi') - collect($getState())->sum('target');
                    @endphp
                    <span class="{{ $totalSelisih >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        {{ $totalSelisih >= 0 ? '+' : '' }}{{ number_format($totalSelisih, 0, ',', '.') }}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm font-bold text-center">
                    @php
                        $totalTarget = collect($getState())->sum('target');
                        $totalRealisasi = collect($getState())->sum('realisasi');
                        $totalPersentase = $totalTarget > 0 ? ($totalRealisasi / $totalTarget) * 100 : 0;
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $totalPersentase >= 100 ? 'bg-success-100 text-success-800 dark:bg-success-400/10 dark:text-success-400' : 'bg-info-100 text-info-800 dark:bg-info-400/10 dark:text-info-400' }}
                    ">
                        {{ number_format($totalPersentase, 1) }}%
                    </span>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
