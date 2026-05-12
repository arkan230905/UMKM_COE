@extends('layouts.app')

@section('title', 'Laporan Kartu Stok')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-clipboard-list me-2"></i>Laporan Kartu Stok
        </h2>
        @if($stockReport)
        <a href="{{ route('laporan.kartu-stok.export', request()->query()) }}" class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i> Export Excel
        </a>
        @endif
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-1">
                <i class="fas fa-filter me-2"></i>Filter Laporan
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.kartu-stok.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Jenis Item</label>
                    <select name="item_type" class="form-select" id="itemTypeSelect">
                        <option value="bahan_baku" {{ $itemType == 'bahan_baku' ? 'selected' : '' }}>Bahan Baku</option>
                        <option value="bahan_pendukung" {{ $itemType == 'bahan_pendukung' ? 'selected' : '' }}>Bahan Pendukung</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Item</label>
                    <select name="item_id" class="form-select" id="itemSelect">
                        <option value="">Pilih Item</option>
                        @if($itemType == 'bahan_baku')
                            @foreach($bahanBakus as $item)
                                <option value="{{ $item->id }}" {{ $itemId == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_bahan }}
                                </option>
                            @endforeach
                        @else
                            @foreach($bahanPendukungs as $item)
                                <option value="{{ $item->id }}" {{ $itemId == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_bahan }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($stockReport && $selectedItem)
        <!-- Stock Report -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    Kartu Stok - {{ $selectedItem->nama_bahan }}
                    @if($startDate && $endDate)
                        ({{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})
                    @endif
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 120px;">Tanggal</th>
                                <th>Keterangan</th>
                                <th class="text-center" style="width: 100px;">Masuk</th>
                                <th class="text-center" style="width: 100px;">Keluar</th>
                                <th class="text-center" style="width: 100px;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($stockReport['saldo_awal'] != 0)
                                <tr class="table-info">
                                    <td class="text-center">-</td>
                                    <td><strong>Saldo Awal</strong></td>
                                    <td class="text-center">-</td>
                                    <td class="text-center">-</td>
                                    <td class="text-center"><strong>{{ number_format($stockReport['saldo_awal'], 2) }}</strong></td>
                                </tr>
                            @endif
                            
                            @forelse($stockReport['entries'] as $entry)
                                <tr>
                                    <td class="text-center">{{ $entry['tanggal'] }}</td>
                                    <td>{{ $entry['keterangan'] }}</td>
                                    <td class="text-center">
                                        @if($entry['qty_masuk'] > 0)
                                            <span class="text-success">{{ number_format($entry['qty_masuk'], 2) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($entry['qty_keluar'] > 0)
                                            <span class="text-danger">{{ number_format($entry['qty_keluar'], 2) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ number_format($entry['saldo'], 2) }}</strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>Tidak ada transaksi dalam periode ini</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            
                            @if(count($stockReport['entries']) > 0)
                                <tr class="table-success">
                                    <td class="text-center"><strong>-</strong></td>
                                    <td><strong>Saldo Akhir</strong></td>
                                    <td class="text-center"><strong>-</strong></td>
                                    <td class="text-center"><strong>-</strong></td>
                                    <td class="text-center"><strong>{{ number_format($stockReport['saldo_akhir'], 2) }}</strong></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Pilih Item untuk Melihat Kartu Stok</h5>
                <p class="text-muted">Silakan pilih jenis item dan item dari dropdown di atas untuk melihat laporan kartu stok.</p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemTypeSelect = document.getElementById('itemTypeSelect');
    const itemSelect = document.getElementById('itemSelect');
    
    // Handle item type change
    itemTypeSelect.addEventListener('change', function() {
        // Clear item selection
        itemSelect.value = '';
        
        // Submit form to reload with new item type
        this.form.submit();
    });
});
</script>
@endpush
@endsection