<div class="space-y-6">
    <div class="grid grid-cols-2 gap-4">
        <div class="rounded-lg bg-primary-50 p-4 dark:bg-primary-400/10">
            <div class="text-sm font-medium text-primary-600 dark:text-primary-400">Total Target {{ $target->tahun }}</div>
            <div class="mt-1 text-2xl font-bold text-primary-900 dark:text-primary-100">
                {{ number_format($target->total_target_tahunan, 0, ',', '.') }} Unit
            </div>
        </div>

        <div class="rounded-lg bg-success-50 p-4 dark:bg-success-400/10">
            <div class="text-sm font-medium text-success-600 dark:text-success-400">Total Realisasi</div>
            <div class="mt-1 text-2xl font-bold text-success-900 dark:text-success-100">
                {{ number_format($target->total_realisasi, 0, ',', '.') }} Unit ({{ number_format($target->persentase_pencapaian, 1) }}%)
            </div>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900">
        <canvas id="targetChart" height="100"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('targetChart');
    if (!ctx) return;

    const comparison = @json($comparison);
    
    const labels = comparison.map(item => item.nama_bulan);
    const targetData = comparison.map(item => item.target);
    const realisasiData = comparison.map(item => item.realisasi);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Target',
                    data: targetData,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                },
                {
                    label: 'Realisasi',
                    data: realisasiData,
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Grafik Target vs Realisasi Produksi per Bulan'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += new Intl.NumberFormat('id-ID').format(context.parsed.y) + ' Unit';
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            }
        }
    });
});
</script>
