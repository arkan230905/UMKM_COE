@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-wallet"></i> Laporan Kas dan Bank</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('laporan.kas-bank.export-pdf', request()->all()) }}" class="btn btn-danger" target="_blank">
                <i class="bi bi-file-pdf"></i> Download PDF
            </a>
            <a href="{{ route('laporan.kas-bank.export-excel', request()->all()) }}" class="btn btn-success">
                <i class="bi bi-file-excel"></i> Download Excel
            </a>
        </div>
    </div>

    <!-- Filter Periode -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.kas-bank') }}" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="setQuickFilter('today')">Hari Ini</button>
                        <button type="button" class="btn btn-secondary" onclick="setQuickFilter('week')">Minggu Ini</button>
                        <button type="button" class="btn btn-secondary" onclick="setQuickFilter('month')">Bulan Ini</button>
                        <button type="button" class="btn btn-secondary" onclick="setQuickFilter('year')">Tahun Ini</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small mb-2">
                        <i class="fas fa-balance-scale me-1"></i>Total Saldo Awal
                    </div>
                    <div class="h5 mb-0 {{ $totalSaldoAwal < 0 ? 'text-danger' : 'text-primary' }}">
                        Rp {{ number_format($totalSaldoAwal, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small mb-2">
                        <i class="fas fa-arrow-down me-1"></i>Total Transaksi Masuk
                    </div>
                    <div class="h5 mb-0 text-success">
                        Rp {{ number_format($totalTransaksiMasuk, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small mb-2">
                        <i class="fas fa-arrow-up me-1"></i>Total Transaksi Keluar
                    </div>
                    <div class="h5 mb-0 text-danger">
                        Rp {{ number_format($totalTransaksiKeluar, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small mb-2">
                        <i class="fas fa-calculator me-1"></i>Total Saldo Akhir
                    </div>
                    <div class="h5 mb-0 {{ $totalKeseluruhan < 0 ? 'text-danger' : 'text-success' }} fw-bold">
                        Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}
                        @if($totalKeseluruhan < 0)
                            <i class="fas fa-exclamation-triangle ms-1" title="Total Saldo Negatif - Perlu Perhatian"></i>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($totalKeseluruhan < 0)
    <!-- Warning Alert for Negative Balance -->
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Perhatian!</strong> Total saldo kas dan bank menunjukkan nilai negatif sebesar 
        <strong>Rp {{ number_format(abs($totalKeseluruhan), 0, ',', '.') }}</strong>. 
        Hal ini menunjukkan bahwa pengeluaran melebihi pemasukan. Silakan periksa transaksi dan pertimbangkan untuk menambah modal atau mengurangi pengeluaran.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Tabel Saldo per Akun -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th class="text-end">Saldo Awal</th>
                            <th class="text-end">Transaksi Masuk</th>
                            <th class="text-end">Transaksi Keluar</th>
                            <th class="text-end">Saldo Akhir</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dataKasBank as $data)
                        <tr class="{{ $data['saldo_akhir'] < 0 ? 'table-warning' : '' }}">
                            <td><strong>{{ $data['kode_akun'] }}</strong></td>
                            <td>{{ $data['nama_akun'] }}</td>
                            <td class="text-end">
                                <span class="{{ $data['saldo_awal'] < 0 ? 'text-danger' : 'text-primary' }}">
                                    Rp {{ number_format($data['saldo_awal'], 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="text-success fw-bold">
                                    <i class="fas fa-arrow-down me-1"></i>
                                    Rp {{ number_format($data['transaksi_masuk'], 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="text-danger fw-bold">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    Rp {{ number_format($data['transaksi_keluar'], 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold {{ $data['saldo_akhir'] < 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($data['saldo_akhir'], 0, ',', '.') }}
                                    @if($data['saldo_akhir'] < 0)
                                        <i class="fas fa-exclamation-triangle ms-1" title="Saldo Negatif"></i>
                                    @endif
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-success" onclick="showDetailMasuk('{{ $data['kode_akun'] }}', '{{ $data['nama_akun'] }}')" title="Detail Transaksi Masuk">
                                        <i class="fas fa-arrow-down"></i> Masuk
                                    </button>
                                    <button class="btn btn-danger" onclick="showDetailKeluar('{{ $data['kode_akun'] }}', '{{ $data['nama_akun'] }}')" title="Detail Transaksi Keluar">
                                        <i class="fas fa-arrow-up"></i> Keluar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data akun kas/bank</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Modal Detail Transaksi Masuk -->
<div class="modal fade" id="modalDetailMasuk" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-arrow-down"></i> Detail Transaksi Masuk - <span id="namaAkunMasuk"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-success">
                            <tr>
                                <th>Tanggal</th>
                                <th>No. Transaksi</th>
                                <th>Jenis</th>
                                <th>Keterangan</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody id="tableDetailMasuk">
                            <tr>
                                <td colspan="5" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi Keluar -->
<div class="modal fade" id="modalDetailKeluar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-arrow-up"></i> Detail Transaksi Keluar - <span id="namaAkunKeluar"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-danger">
                            <tr>
                                <th>Tanggal</th>
                                <th>No. Transaksi</th>
                                <th>Jenis</th>
                                <th>Keterangan</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody id="tableDetailKeluar">
                            <tr>
                                <td colspan="5" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Quick Filter
function setQuickFilter(period) {
    const today = new Date();
    let startDate, endDate;
    
    switch(period) {
        case 'today':
            startDate = endDate = today.toISOString().split('T')[0];
            break;
        case 'week':
            const firstDay = new Date(today.setDate(today.getDate() - today.getDay()));
            startDate = firstDay.toISOString().split('T')[0];
            endDate = new Date().toISOString().split('T')[0];
            break;
        case 'month':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
            break;
        case 'year':
            startDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
            endDate = new Date(today.getFullYear(), 11, 31).toISOString().split('T')[0];
            break;
    }
    
    document.querySelector('input[name="start_date"]').value = startDate;
    document.querySelector('input[name="end_date"]').value = endDate;
    document.getElementById('filterForm').submit();
}

// Show Detail Transaksi Masuk
function showDetailMasuk(coaId, namaAkun) {
    document.getElementById('namaAkunMasuk').textContent = namaAkun;
    
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    
    const url = `/laporan/kas-bank/${coaId}/detail-masuk?start_date=${startDate}&end_date=${endDate}`;
    console.log('Fetching URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            console.log('Data length:', data.length);
            
            let html = '';
            let total = 0;
            
            if (!data || data.length === 0) {
                html = '<tr><td colspan="5" class="text-center">Tidak ada transaksi masuk</td></tr>';
            } else {
                data.forEach(item => {
                    total += parseFloat(item.nominal);
                    html += `
                        <tr>
                            <td>${item.tanggal}</td>
                            <td>${item.nomor_transaksi}</td>
                            <td>${item.jenis}</td>
                            <td>${item.keterangan}</td>
                            <td class="text-end">Rp ${parseInt(item.nominal).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });
                html += `
                    <tr class="table-success fw-bold">
                        <td colspan="4" class="text-end">TOTAL</td>
                        <td class="text-end">Rp ${parseInt(total).toLocaleString('id-ID')}</td>
                    </tr>
                `;
            }
            
            document.getElementById('tableDetailMasuk').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetailMasuk')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tableDetailMasuk').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error: ' + error.message + '</td></tr>';
            new bootstrap.Modal(document.getElementById('modalDetailMasuk')).show();
        });
}

// Show Detail Transaksi Keluar
function showDetailKeluar(coaId, namaAkun) {
    document.getElementById('namaAkunKeluar').textContent = namaAkun;
    
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    
    const url = `/laporan/kas-bank/${coaId}/detail-keluar?start_date=${startDate}&end_date=${endDate}`;
    console.log('Fetching URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            console.log('Data length:', data.length);
            
            let html = '';
            let total = 0;
            
            if (!data || data.length === 0) {
                html = '<tr><td colspan="5" class="text-center">Tidak ada transaksi keluar</td></tr>';
            } else {
                data.forEach(item => {
                    total += parseFloat(item.nominal);
                    html += `
                        <tr>
                            <td>${item.tanggal}</td>
                            <td>${item.nomor_transaksi}</td>
                            <td>${item.jenis}</td>
                            <td>${item.keterangan}</td>
                            <td class="text-end">Rp ${parseInt(item.nominal).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });
                html += `
                    <tr class="table-danger fw-bold">
                        <td colspan="4" class="text-end">TOTAL</td>
                        <td class="text-end">Rp ${parseInt(total).toLocaleString('id-ID')}</td>
                    </tr>
                `;
            }
            
            document.getElementById('tableDetailKeluar').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetailKeluar')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tableDetailKeluar').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error: ' + error.message + '</td></tr>';
            new bootstrap.Modal(document.getElementById('modalDetailKeluar')).show();
        });
}
</script>
@endsection
