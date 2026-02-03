@extends('layouts.app')

@section('title', 'Laporan Stok - Pegawai Gudang')

@push('styles')
<style>
/* Laporan Stok Page */
.table tbody tr:hover {
    background-color: #f8f9fa !important;
}

.table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    font-weight: 600 !important;
    border: none !important;
}

.table td {
    vertical-align: middle !important;
}

.filter-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.card {
    border: none !important;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    border-radius: 10px !important;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    border-radius: 10px 10px 0 0 !important;
    border: none !important;
}

.btn {
    border-radius: 6px !important;
    font-weight: 500 !important;
}

.text-nowrap {
    white-space: nowrap;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-chart-line me-2"></i>Laporan Stok
            </h2>
            <p class="text-muted mb-0">Analisis pergerakan stok berdasarkan periode waktu</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pegawai-gudang.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" action="{{ route('pegawai-gudang.laporan-stok.index') }}" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-bold">Tipe</label>
                    <select name="tipe" class="form-select" onchange="this.form.submit()">
                        <option value="product" {{ $tipe == 'product' ? 'selected' : '' }}>Produk</option>
                        <option value="material" {{ $tipe == 'material' ? 'selected' : '' }}>Bahan Baku</option>
                        <option value="support" {{ $tipe == 'support' ? 'selected' : '' }}>Bahan Pendukung</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Dari Tanggal</label>
                    <input type="text" name="dari_tanggal" class="form-control" 
                           value="{{ \Carbon\Carbon::parse($dariTanggal)->format('d/m/Y') }}" 
                           placeholder="dd/mm/yyyy" id="dariTanggal"
                           maxlength="10" pattern="\d{2}/\d{2}/\d{4}">
                    <small class="text-muted">Format: dd/mm/yyyy</small>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Sampai Tanggal</label>
                    <input type="text" name="sampai_tanggal" class="form-control" 
                           value="{{ \Carbon\Carbon::parse($sampaiTanggal)->format('d/m/Y') }}" 
                           placeholder="dd/mm/yyyy" id="sampaiTanggal"
                           maxlength="10" pattern="\d{2}/\d{2}/\d{4}">
                    <small class="text-muted">Format: dd/mm/yyyy</small>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success w-100" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Cetak
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h5 class="card-title text-primary">Total Item</h5>
                    <h2 class="mb-0">{{ $laporanStok->count() }}</h2>
                    <small class="text-muted">
                        {{ $tipe == 'product' ? 'Produk' : ($tipe == 'material' ? 'Bahan Baku' : 'Bahan Pendukung') }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 class="card-title text-success">Total Masuk</h5>
                    <h2 class="mb-0">{{ number_format($laporanStok->sum('masuk_qty'), 2, ',', '.') }}</h2>
                    <small class="text-muted">Jumlah</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h5 class="card-title text-danger">Total Keluar</h5>
                    <h2 class="mb-0">{{ number_format($laporanStok->sum('keluar_qty'), 2, ',', '.') }}</h2>
                    <small class="text-muted">Jumlah</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h5 class="card-title text-info">Saldo Akhir</h5>
                    <h2 class="mb-0">{{ number_format($laporanStok->sum('saldo_qty'), 2, ',', '.') }}</h2>
                    <small class="text-muted">Jumlah</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Report Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>Ringkasan Stok
                @if($tipe == 'product')
                    Produk
                @elseif($tipe == 'material')
                    Bahan Baku
                @else
                    Bahan Pendukung
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th class="text-end">Masuk (Qty)</th>
                            <th class="text-end">Masuk (Nilai)</th>
                            <th class="text-end">Keluar (Qty)</th>
                            <th class="text-end">Keluar (Nilai)</th>
                            <th class="text-end">Saldo (Qty)</th>
                            <th class="text-end">Saldo (Nilai)</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporanStok as $item)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $item['nama_item'] }}</div>
                                    <small class="text-muted">Satuan: {{ $item['satuan'] }}</small>
                                </td>
                                <td class="text-end">{{ number_format($item['masuk_qty'], 2, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item['masuk_nilai'], 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($item['keluar_qty'], 2, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item['keluar_nilai'], 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">{{ number_format($item['saldo_qty'], 2, ',', '.') }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format($item['saldo_nilai'], 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('pegawai-gudang.laporan-stok.detail', [
                                        'item_type' => $item['item_type'],
                                        'item_id' => $item['item_id'],
                                        'dari_tanggal' => $dariTanggal,
                                        'sampai_tanggal' => $sampaiTanggal
                                    ]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">
                                        Tidak ada pergerakan stok dalam periode ini
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary fw-bold">
                            <td>TOTAL</td>
                            <td class="text-end">{{ number_format($laporanStok->sum('masuk_qty'), 2, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($laporanStok->sum('masuk_nilai'), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($laporanStok->sum('keluar_qty'), 2, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($laporanStok->sum('keluar_nilai'), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($laporanStok->sum('saldo_qty'), 2, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($laporanStok->sum('saldo_nilai'), 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript untuk konversi tanggal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    
    // Auto-format input tanggal
    function formatTanggal(input) {
        let value = input.value.replace(/\D/g, '');
        let formattedValue = '';
        
        if (value.length >= 2) {
            formattedValue = value.substring(0, 2);
            if (value.length > 2) {
                formattedValue += '/' + value.substring(2, 4);
                if (value.length > 4) {
                    formattedValue += '/' + value.substring(4, 8);
                }
            }
        } else {
            formattedValue = value;
        }
        
        input.value = formattedValue;
    }
    
    // Tambah event listener untuk input tanggal
    const dariTanggal = document.getElementById('dariTanggal');
    const sampaiTanggal = document.getElementById('sampaiTanggal');
    
    if (dariTanggal) {
        dariTanggal.addEventListener('input', function() {
            formatTanggal(this);
        });
        
        dariTanggal.addEventListener('keypress', function(e) {
            // Hanya allow angka dan /
            if (!/[0-9\/]/.test(e.key) && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
            }
        });
    }
    
    if (sampaiTanggal) {
        sampaiTanggal.addEventListener('input', function() {
            formatTanggal(this);
        });
        
        sampaiTanggal.addEventListener('keypress', function(e) {
            // Hanya allow angka dan /
            if (!/[0-9\/]/.test(e.key) && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
            }
        });
    }
    
    form.addEventListener('submit', function(e) {
        // Konversi format tanggal dari dd/mm/yyyy ke yyyy-mm-dd
        const dariTanggalValue = dariTanggal.value;
        const sampaiTanggalValue = sampaiTanggal.value;
        
        if (dariTanggalValue) {
            const dariParts = dariTanggalValue.split('/');
            if (dariParts.length === 3) {
                const dariConverted = `${dariParts[2]}-${dariParts[1].padStart(2, '0')}-${dariParts[0].padStart(2, '0')}`;
                dariTanggal.value = dariConverted;
            }
        }
        
        if (sampaiTanggalValue) {
            const sampaiParts = sampaiTanggalValue.split('/');
            if (sampaiParts.length === 3) {
                const sampaiConverted = `${sampaiParts[2]}-${sampaiParts[1].padStart(2, '0')}-${sampaiParts[0].padStart(2, '0')}`;
                sampaiTanggal.value = sampaiConverted;
            }
        }
    });
});
</script>

<!-- Print Styles -->
<style media="print">
    .filter-section,
    .btn,
    .d-flex.justify-content-between {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
    
    .table th {
        background: #f8f9fa !important;
        color: #000 !important;
    }
</style>
@endsection
