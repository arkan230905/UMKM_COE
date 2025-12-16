@extends('layouts.app')

@push('scripts')
<script>
function showMonthlySYDDetail(config) {
    const modal = new bootstrap.Modal(document.getElementById('monthlyDetailModal'));
    const content = document.getElementById('monthlyDetailContent');

    if (!config || !config.months || !config.annual) {
        content.innerHTML = '<p class="text-muted mb-0">Data penyusutan tidak tersedia.</p>';
        modal.show();
        return;
    }

    const monthNames = [
        'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];

    const months = parseInt(config.months, 10) || 0;
    const startMonth = Math.max(1, Math.min(12, parseInt(config.start_month ?? 1, 10)));
    const annual = parseFloat(config.annual) || 0;
    const accumStart = parseFloat(config.accum_start ?? (config.accum_end - annual)) || 0;
    const bookStart = parseFloat(config.book_start ?? (config.book_end + annual)) || 0;
    const yearLabel = config.year_label || config.year || '';

    if (months <= 0 || annual <= 0) {
        content.innerHTML = '<p class="text-muted mb-0">Data penyusutan tidak tersedia.</p>';
        modal.show();
        return;
    }

    const monthlyBase = annual / months;
    let allocated = 0;
    let currentAccum = accumStart;
    let currentBook = bookStart;

    let rows = '';
    for (let i = 0; i < months; i++) {
        const isLast = i === months - 1;
        let amount = isLast ? (annual - allocated) : monthlyBase;
        amount = parseFloat(amount.toFixed(2));

        allocated = parseFloat((allocated + amount).toFixed(2));
        currentAccum = parseFloat((currentAccum + amount).toFixed(2));
        currentBook = parseFloat((currentBook - amount).toFixed(2));

        const monthIndex = startMonth - 1 + i;
        const labelMonth = monthNames[Math.min(monthIndex, 11)] ?? 'Bulan';

        rows += `
            <tr>
                <td>${labelMonth} ${config.year}</td>
                <td class="text-end">Rp ${numberFormat(amount)}</td>
                <td class="text-end">Rp ${numberFormat(currentAccum)}</td>
                <td class="text-end">Rp ${numberFormat(Math.max(currentBook, 0))}</td>
            </tr>
        `;
    }

    rows += `
        <tr class="fw-bold table-secondary">
            <td>Total ${yearLabel}</td>
            <td class="text-end">Rp ${numberFormat(annual)}</td>
            <td class="text-end">Rp ${numberFormat(parseFloat(config.accum_end ?? currentAccum))}</td>
            <td class="text-end">Rp ${numberFormat(parseFloat(config.book_end ?? currentBook))}</td>
        </tr>
    `;

    content.innerHTML = `
        <div class="mb-3">
            <h6>${yearLabel}</h6>
            <p class="mb-1"><strong>Penyusutan per tahun:</strong> Rp ${numberFormat(annual)}</p>
            <p class="mb-0"><strong>Penyusutan per bulan:</strong> Rp ${numberFormat(annual / months)}</p>
        </div>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-end">Beban Penyusutan</th>
                        <th class="text-end">Akumulasi</th>
                        <th class="text-end">Nilai Buku</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>
        </div>
    `;

    modal.show();
}

function showMonthlyDetail(options) {

    const metodePenyusutan = '{{ $aset->metode_penyusutan }}';
    const modal = new bootstrap.Modal(document.getElementById('monthlyDetailModal'));
    const content = document.getElementById('monthlyDetailContent');

    const defaults = {
        year: null,
        annual: 0,
        accumStart: 0,
        bookStart: 0,
        details: null,
    };

    let payload = defaults;

    if (options && typeof options === 'object' && !Array.isArray(options)) {
        payload = { ...defaults, ...options };
    } else {
        // Backward compatibility jika fungsi dipanggil dengan argumen lama
        const [tahun, annual, accumStart, bookStart, monthly_breakdown] = arguments;
        payload = {
            year: tahun ?? null,
            annual: annual ?? 0,
            accumStart: accumStart ?? 0,
            bookStart: bookStart ?? 0,
            details: monthly_breakdown ? { monthly_breakdown } : null,
        };
    }

    const year = payload.year;
    const annual = Number(payload.annual) || 0;
    const accumStart = Number(payload.accumStart) || 0;
    const bookStart = Number(payload.bookStart) || 0;

    const details = payload.details && typeof payload.details === 'object' ? payload.details : null;
    const monthly_breakdown = Array.isArray(details?.monthly_breakdown) ? details.monthly_breakdown : [];

    // Debug: log untuk melihat data yang diterima
    console.log('showMonthlyDetail called with:', {
        year,
        annual,
        accumStart,
        bookStart,
        metodePenyusutan,
        monthlyItems: monthly_breakdown.length,
        details,
    });

    if (monthly_breakdown.length > 0) {
        let rows = '';

        monthly_breakdown.forEach((item, index) => {
            const amount = Number(item.amount ?? 0);
            const accum = Number(item.accum ?? amount);
            const book = Number(item.book ?? 0);

            rows += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${item.label ?? '-'}</td>
                    <td class="text-end">Rp ${numberFormat(amount)}</td>
                    <td class="text-end">Rp ${numberFormat(accum)}</td>
                    <td class="text-end">Rp ${numberFormat(book)}</td>
                </tr>
            `;
        });

        const totals = monthly_breakdown.reduce((carry, item) => {
            const amount = Number(item.amount ?? 0);
            carry.amount += amount;
            carry.accum = Number(item.accum ?? carry.accum);
            carry.book = Number(item.book ?? carry.book);
            return carry;
        }, { amount: 0, accum: accumStart, book: bookStart });

        const summaryAnnual = Number(details?.annual_depreciation ?? totals.amount);
        const summaryAccumStart = Number(details?.accum_start ?? accumStart);
        const summaryAccumEnd = Number(details?.accum_end ?? totals.accum);
        const summaryBookStart = Number(details?.book_start ?? bookStart);
        const summaryBookEnd = Number(details?.book_end ?? totals.book);

        rows += `
            <tr class="fw-bold table-secondary">
                <td colspan="2">Total Tahun ${year}</td>
                <td class="text-end">Rp ${numberFormat(summaryAnnual)}</td>
                <td class="text-end">Rp ${numberFormat(summaryAccumEnd)}</td>
                <td class="text-end">Rp ${numberFormat(summaryBookEnd)}</td>
            </tr>
        `;

        content.innerHTML = `
            <div class="mb-3">
                <h6 class="mb-1">Tahun ${year}</h6>
                <div class="d-flex flex-wrap gap-3">
                    <div><strong>Beban:</strong> Rp ${numberFormat(summaryAnnual)}</div>
                    <div><strong>Akumulasi:</strong> Rp ${numberFormat(summaryAccumEnd)}</div>
                    <div><strong>Nilai Buku:</strong> Rp ${numberFormat(summaryBookEnd)}</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">No</th>
                            <th>Bulan</th>
                            <th class="text-end">Beban Penyusutan</th>
                            <th class="text-end">Akumulasi</th>
                            <th class="text-end">Nilai Buku</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
            </div>
        `;

        modal.show();
        return;
    }

    // Fallback ke logika lama jika tidak ada monthly_breakdown
    const startYear = {{ $aset->tanggal_akuisisi ? \Carbon\Carbon::parse($aset->tanggal_akuisisi)->year : \Carbon\Carbon::parse($aset->tanggal_beli)->year }};
    const startMonth = {{ isset($bulanMulaiDisplay) ? (int)$bulanMulaiDisplay->month : (!empty($aset->bulan_mulai) ? (int)$aset->bulan_mulai : 1) }};
    const umurManfaat = {{ $aset->umur_manfaat ?? 5 }};
    const endYear = startYear + umurManfaat - 1;

    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    let html = `
        <div class="mb-3">
            <h6>Rincian Penyusutan Tahunan</h6>
            <p class="mb-1"><strong>Penyusutan per tahun:</strong> Rp ${numberFormat(penyusutanTahunan)}</p>
            <p class="mb-0"><strong>Umur Manfaat:</strong> ${umurManfaat} tahun</p>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Tahun</th>
                        <th class="text-end">Penyusutan Pertahun</th>
                        <th class="text-end">Akumulasi</th>
                        <th class="text-end">Nilai Buku Akhir</th>
                    </tr>
                </thead>
                <tbody>
    `;

    let currentAccum = accumStart;
    let currentBook = bookStart;
    
    // Tampilkan semua tahun dari startYear sampai endYear
    for (let year = startYear; year <= endYear; year++) {
        let bebanTahunan = annual;
        
        if (year === startYear) {
            // Tahun pertama: sesuai jumlah bulan yang tersisa
            const monthsRemaining = 12 - startMonth + 1;
            bebanTahunan = (annual / 12) * monthsRemaining;
        } else if (year === endYear) {
            // Tahun terakhir: sisa bulan dari tahun pertama
            const monthsRemaining = 12 - startMonth + 1;
            const finalYearMonths = 12 - monthsRemaining;
            bebanTahunan = (annual / 12) * finalYearMonths;
        }
        
        // Update akumulasi dan nilai buku
        currentAccum = currentAccum + bebanTahunan;
        currentBook = currentBook - bebanTahunan;
        
        // Pastikan tidak negatif
        if (currentBook < 0) currentBook = 0;
        
        html += `
            <tr>
                <td>${year}</td>
                <td class="text-end">Rp ${numberFormat(bebanTahunan)}</td>
                <td class="text-end">Rp ${numberFormat(currentAccum)}</td>
                <td class="text-end">Rp ${numberFormat(currentBook)}</td>
            </tr>
        `;
    }

    html += `
                </tbody>
            </table>
        </div>
    `;

    content.innerHTML = html;
    modal.show();
}

function showMonthlyBreakdown(tahun, breakdown) {
    const modal = new bootstrap.Modal(document.getElementById('monthlyDetailModal'));
    const content = document.getElementById('monthlyDetailContent');

    if (!breakdown || !Array.isArray(breakdown)) {
        content.innerHTML = '<p class="text-muted mb-0">Data penyusutan tidak tersedia.</p>';
        modal.show();
        return;
    }

    const totals = breakdown.reduce((carry, item) => {
        const amount = typeof item.amount === 'number' ? item.amount : parseFloat(item.amount);
        carry.amount += isNaN(amount) ? 0 : amount;
        carry.accum = item.accum ?? carry.accum;
        carry.book = item.book ?? carry.book;
        return carry;
    }, { amount: 0, accum: 0, book: 0 });

    let rows = '';
    breakdown.forEach((item, index) => {
        const amount = typeof item.amount === 'number' ? item.amount : parseFloat(item.amount);
        const accum = item.accum ?? (index === 0 ? amount : amount);
        const book = item.book ?? 0;
        rows += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td>${item.label ?? '-'}</td>
                <td class="text-end">Rp ${numberFormat(amount)}</td>
                <td class="text-end">Rp ${numberFormat(accum)}</td>
                <td class="text-end">Rp ${numberFormat(book)}</td>
            </tr>
        `;
    });

    rows += `
        <tr class="fw-bold table-secondary">
            <td colspan="2">Total Tahun ${tahun}</td>
            <td class="text-end">Rp ${numberFormat(totals.amount)}</td>
            <td class="text-end">Rp ${numberFormat(totals.accum)}</td>
            <td class="text-end">Rp ${numberFormat(totals.book)}</td>
        </tr>
    `;

    content.innerHTML = `
        <div class="mb-3">
            <h6>Tahun ${tahun}</h6>
            <p class="mb-0"><strong>Total penyusutan tahun ini:</strong> Rp ${numberFormat(totals.amount)}</p>
        </div>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>Bulan</th>
                        <th class="text-end">Penyusutan Per Bulan</th>
                        <th class="text-end">Akumulasi</th>
                        <th class="text-end">Nilai Buku</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>
        </div>
    `;

    modal.show();
}

function numberFormat(num) {
    return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&.');
}

document.addEventListener('DOMContentLoaded', () => {
    const metodePenyusutan = '{{ $aset->metode_penyusutan }}';
    const hasilPerhitunganCard = document.getElementById('hasil_perhitungan_card');
    const hiddenMethods = ['sum_of_years_digits', 'double_declining_balance'];

    if (hasilPerhitunganCard) {
        if (hiddenMethods.includes(metodePenyusutan)) {
            hasilPerhitunganCard.style.display = 'none';
            console.log('Menyembunyikan hasil perhitungan untuk metode:', metodePenyusutan);
        } else {
            hasilPerhitunganCard.style.display = 'block';
            console.log('Menampilkan hasil perhitungan untuk metode:', metodePenyusutan);
        }
    }

    document.querySelectorAll('.detail-btn').forEach(button => {
        button.addEventListener('click', function() {
            const tahun = this.dataset.tahun;
            const annual = parseFloat(this.dataset.annual ?? '0');
            const accumStart = parseFloat(this.dataset.accumStart ?? '0');
            const bookStart = parseFloat(this.dataset.bookStart ?? '0');

            let details = null;
            const rawMonthly = this.dataset.monthly ?? '';
            if (rawMonthly) {
                try {
                    const decoded = atob(rawMonthly);
                    details = JSON.parse(decoded);
                } catch (e) {
                    console.error('Error parsing monthly detail payload:', e);
                }
            }

            showMonthlyDetail({
                year: tahun,
                annual,
                accumStart,
                bookStart,
                details,
            });
        });
    });
});
</script>
@endpush

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="text-white">Detail Aset</h2>
        </div>
    </div>

    <!-- Informasi Aset -->
    <div class="card mb-4 bg-white">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Aset</h5>
        </div>
        @php
            $startDateDisplay = null;
            $bulanMulaiDisplay = null;

            // Prioritaskan tanggal akuisisi (tanggal mulai penyusutan penuh) jika tersedia
            if (!empty($aset->tanggal_akuisisi)) {
                $startDateDisplay = \Carbon\Carbon::parse($aset->tanggal_akuisisi);
            } elseif (!empty($aset->tanggal_beli)) {
                $startDateDisplay = \Carbon\Carbon::parse($aset->tanggal_beli);
            }

            if ($aset->metode_penyusutan === 'sum_of_years_digits' && !empty($aset->tanggal_perolehan)) {
                $startDateDisplay = \Carbon\Carbon::parse($aset->tanggal_perolehan);
                $bulanMulaiDisplay = $startDateDisplay->copy();
            } elseif ($aset->metode_penyusutan === 'saldo_menurun' && !empty($aset->bulan_mulai)) {
                $referenceYear = $startDateDisplay ? $startDateDisplay->year : now()->year;
                $bulanMulaiDisplay = \Carbon\Carbon::create($referenceYear, (int) $aset->bulan_mulai, 1);

                if (!$startDateDisplay) {
                    $startDateDisplay = $bulanMulaiDisplay->copy();
                }
            }

            if (!$bulanMulaiDisplay && $startDateDisplay) {
                $bulanMulaiDisplay = $startDateDisplay->copy();
            }
            $displayTanggalMulai = $startDateDisplay ?? $bulanMulaiDisplay;
        @endphp
        <div class="card-body bg-white">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%" class="text-dark"><strong>Kode Aset</strong></td>
                            <td class="text-dark">: {{ $aset->kode_aset }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Nama Aset</strong></td>
                            <td class="text-dark">: {{ $aset->nama_aset }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Jenis Aset</strong></td>
                            <td class="text-dark">: {{ $aset->kategori->jenisAset->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Kategori Aset</strong></td>
                            <td class="text-dark">: {{ $aset->kategori->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Tanggal Mulai</strong></td>
                            <td class="text-dark">:
                                @if($displayTanggalMulai)
                                    {{ $displayTanggalMulai->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @if($aset->metode_penyusutan === 'sum_of_years_digits' && $aset->tanggal_perolehan)
                        <tr>
                            <td class="text-dark"><strong>Tanggal Perolehan</strong></td>
                            <td class="text-dark">: {{ \Carbon\Carbon::parse($aset->tanggal_perolehan)->format('d/m/Y') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%" class="text-dark"><strong>Harga Perolehan</strong></td>
                            <td class="text-dark">: Rp {{ number_format($aset->harga_perolehan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Biaya Perolehan</strong></td>
                            <td class="text-dark">: Rp {{ number_format($aset->biaya_perolehan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Total Perolehan</strong></td>
                            <td class="text-dark"><strong>: Rp {{ number_format($totalPerolehan, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Nilai Residu</strong></td>
                            <td class="text-dark">: Rp {{ number_format($aset->nilai_residu, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Umur Manfaat</strong></td>
                            <td class="text-dark">: {{ $aset->umur_manfaat }} tahun</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Metode Penyusutan</strong></td>
                            <td class="text-dark">: {{ ucwords(str_replace('_', ' ', $aset->metode_penyusutan)) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
    <!-- Hasil Perhitungan Penyusutan -->
    <div class="card mb-4 bg-white" id="hasil_perhitungan_card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Hasil Perhitungan Penyusutan</h5>
        </div>
        <div class="card-body bg-white">
            <div class="row text-center">
                <div class="col-md-6 mb-3">
                    <div class="card bg-info bg-opacity-10 border border-info">
                        <div class="card-body">
                            <h6 class="text-dark">Penyusutan Per Bulan</h6>
                            <h3 class="text-dark mb-0">Rp {{ number_format($penyusutanPerBulan, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card bg-success bg-opacity-10 border border-success">
                        <div class="card-body">
                            <h6 class="text-dark">Penyusutan Per Tahun</h6>
                            <h3 class="text-dark mb-0">Rp {{ number_format($penyusutanPerTahun, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Penyusutan Per Tahun -->
    <div class="card mb-4 bg-white">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Jadwal Penyusutan Per Tahun</h5>
        </div>
        <div class="card-body bg-white">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">TAHUN</th>
                            <th class="text-end">PENYUSUTAN PERTAHUN</th>
                            <th class="text-end">AKUMULASI</th>
                            <th class="text-end">NILAI BUKU</th>
                            <th class="text-center">RINCIAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($depreciationData as $index => $row)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">
                                    @if($row->tahun_label)
                                        {{ $row->tahun_label }}
                                    @else
                                        {{ $row->tahun }}
                                        @if(isset($row->months_in_period) && $row->months_in_period < 12)
                                            ({{ $row->months_in_period }} bulan)
                                        @endif
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($row->beban_penyusutan, 2, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($row->akumulasi_penyusutan, 2, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($row->nilai_buku_akhir, 2, ',', '.') }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary text-white detail-btn"
                                            data-tahun="{{ $row->tahun }}"
                                            data-annual="{{ $row->beban_penyusutan }}"
                                            data-accum-start="{{ max(0, (float)$row->akumulasi_penyusutan - (float)$row->beban_penyusutan) }}"
                                            data-book-start="{{ (float)$row->nilai_buku_akhir + (float)$row->beban_penyusutan }}"
                                            data-monthly="{{ base64_encode(json_encode($row->monthly_details ?? null)) }}">
                                            <i class="fas fa-info-circle"></i> Detail
                                        </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada jadwal penyusutan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="mb-4">
        <a href="{{ route('master-data.aset.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <a href="{{ route('master-data.aset.edit', $aset->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
    </div>
</div>

@endsection

<!-- Modal Detail Per Bulan -->
<div class="modal fade" id="monthlyDetailModal" tabindex="-1" aria-labelledby="monthlyDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="monthlyDetailModalLabel">Rincian Penyusutan Per Bulan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="monthlyDetailContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
