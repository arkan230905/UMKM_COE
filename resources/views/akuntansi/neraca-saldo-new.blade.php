@extends('layouts.app')

@section('title', 'Neraca Saldo')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">📊 Neraca Saldo</h3>
            <p class="text-muted mb-0">
                <i class="bi bi-info-circle"></i> 
                Data diambil langsung dari Buku Besar (Journal Lines)
            </p>
        </div>
        <div class="d-flex gap-2">
            <form method="get" class="d-flex gap-2 align-items-end" id="periodForm">
                <div>
                    <label class="form-label fw-bold">Bulan</label>
                    <select name="bulan" class="form-select" style="min-width: 150px;" id="bulanSelect">
                        <option value="01" {{ $bulan == '01' ? 'selected' : '' }}>Januari</option>
                        <option value="02" {{ $bulan == '02' ? 'selected' : '' }}>Februari</option>
                        <option value="03" {{ $bulan == '03' ? 'selected' : '' }}>Maret</option>
                        <option value="04" {{ $bulan == '04' ? 'selected' : '' }}>April</option>
                        <option value="05" {{ $bulan == '05' ? 'selected' : '' }}>Mei</option>
                        <option value="06" {{ $bulan == '06' ? 'selected' : '' }}>Juni</option>
                        <option value="07" {{ $bulan == '07' ? 'selected' : '' }}>Juli</option>
                        <option value="08" {{ $bulan == '08' ? 'selected' : '' }}>Agustus</option>
                        <option value="09" {{ $bulan == '09' ? 'selected' : '' }}>September</option>
                        <option value="10" {{ $bulan == '10' ? 'selected' : '' }}>Oktober</option>
                        <option value="11" {{ $bulan == '11' ? 'selected' : '' }}>November</option>
                        <option value="12" {{ $bulan == '12' ? 'selected' : '' }}>Desember</option>
                    </select>
                </div>
                <div>
                    <label class="form-label fw-bold">Tahun</label>
                    <input type="number" name="tahun" class="form-control" value="{{ $tahun }}" 
                           style="min-width: 100px;" min="2020" max="2030" id="tahunInput">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" id="loadBtn">
                        <i class="bi bi-search"></i> Tampilkan
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-secondary" id="refreshBtn">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </form>
            <div>
                <a href="{{ route('akuntansi.neraca-saldo.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" 
                   class="btn btn-danger" target="_blank" id="pdfBtn">
                    <i class="bi bi-file-pdf"></i> Cetak PDF
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="text-center py-4" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Menghitung neraca saldo...</p>
    </div>

    <!-- Main Content -->
    <div id="mainContent">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">💰 Total Debit</h6>
                                <h4 class="mb-0 fw-bold">Rp {{ number_format($neracaSaldoData['total_debit'], 0, ',', '.') }}</h4>
                                <small class="opacity-75">Aset & Beban</small>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-arrow-up-circle fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">💳 Total Kredit</h6>
                                <h4 class="mb-0 fw-bold">Rp {{ number_format($neracaSaldoData['total_kredit'], 0, ',', '.') }}</h4>
                                <small class="opacity-75">Kewajiban, Modal & Pendapatan</small>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-arrow-down-circle fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card {{ $neracaSaldoData['is_balanced'] ? 'bg-success' : 'bg-warning' }} text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">⚖️ Status Keseimbangan</h6>
                                <h5 class="mb-0 fw-bold">
                                    @if($neracaSaldoData['is_balanced'])
                                        ✅ SEIMBANG
                                    @else
                                        ⚠️ TIDAK SEIMBANG
                                    @endif
                                </h5>
                                <small class="opacity-75">
                                    @if($neracaSaldoData['is_balanced'])
                                        Debit = Kredit
                                    @else
                                        Selisih: Rp {{ number_format(abs($neracaSaldoData['total_debit'] - $neracaSaldoData['total_kredit']), 0, ',', '.') }}
                                    @endif
                                </small>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-{{ $neracaSaldoData['is_balanced'] ? 'check-circle' : 'exclamation-triangle' }} fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">📊 Total Akun Aktif</h6>
                                <h4 class="mb-0 fw-bold">{{ count($neracaSaldoData['accounts']) }}</h4>
                                <small class="opacity-75">Akun dengan saldo/aktivitas</small>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-list-ul fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="card shadow">
            <div class="card-header bg-brown-gradient">
                <div class="d-flex justify-content-between align-items-center text-white">
                    <div>
                        <h5 class="mb-1">
                            <i class="bi bi-table"></i> NERACA SALDO
                        </h5>
                        <small>
                            Periode: {{ \Carbon\Carbon::parse($tahun . '-' . $bulan . '-01')->isoFormat('MMMM YYYY') }}
                        </small>
                    </div>
                    <div class="text-end">
                        <small>Saldo akhir per akun</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="trialBalanceTable">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th class="text-center py-3" style="width: 5%">
                                    <i class="bi bi-hash"></i> No
                                </th>
                                <th class="py-3" style="width: 15%">
                                    <i class="bi bi-code-square"></i> Kode Akun
                                </th>
                                <th class="py-3" style="width: 40%">
                                    <i class="bi bi-bookmark"></i> Nama Akun
                                </th>
                                <th class="text-end py-3" style="width: 20%">
                                    <i class="bi bi-arrow-up-circle text-primary"></i> Debit
                                </th>
                                <th class="text-end py-3" style="width: 20%">
                                    <i class="bi bi-arrow-down-circle text-success"></i> Kredit
                                </th>
                            </tr>
                        </thead>
                        <tbody id="trialBalanceBody">
                            @php $no = 1; @endphp
                            @if(count($neracaSaldoData['accounts']) > 0)
                                @foreach($neracaSaldoData['accounts'] as $account)
                                    <tr class="border-bottom">
                                        <td class="text-center fw-bold text-muted">{{ $no++ }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $account['kode_akun'] }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $account['nama_akun'] }}</strong>
                                                @if(isset($account['tipe_akun']))
                                                    <br><small class="text-muted">{{ $account['tipe_akun'] }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            @if($account['debit'] > 0)
                                                <span class="fw-bold text-primary">
                                                    Rp {{ number_format($account['debit'], 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($account['kredit'] > 0)
                                                <span class="fw-bold text-success">
                                                    Rp {{ number_format($account['kredit'], 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        <h5>Tidak ada data transaksi</h5>
                                        <p>Belum ada transaksi untuk periode {{ \Carbon\Carbon::parse($tahun . '-' . $bulan . '-01')->isoFormat('MMMM YYYY') }}</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        @if(count($neracaSaldoData['accounts']) > 0)
                            <tfoot class="table-dark" id="trialBalanceFooter">
                                <tr class="border-top-3">
                                    <th colspan="3" class="text-end py-3">
                                        <i class="bi bi-calculator"></i> <strong>TOTAL</strong>
                                    </th>
                                    <th class="text-end py-3">
                                        <span class="fs-5 fw-bold">
                                            Rp {{ number_format($neracaSaldoData['total_debit'], 0, ',', '.') }}
                                        </span>
                                    </th>
                                    <th class="text-end py-3">
                                        <span class="fs-5 fw-bold">
                                            Rp {{ number_format($neracaSaldoData['total_kredit'], 0, ',', '.') }}
                                        </span>
                                    </th>
                                </tr>
                                <tr class="bg-{{ $neracaSaldoData['is_balanced'] ? 'success' : 'warning' }}">
                                    <th colspan="3" class="text-end py-3 text-white">
                                        <i class="bi bi-{{ $neracaSaldoData['is_balanced'] ? 'check-circle' : 'exclamation-triangle' }}"></i>
                                        STATUS KESEIMBANGAN:
                                    </th>
                                    <th colspan="2" class="text-center py-3 text-white">
                                        @if($neracaSaldoData['is_balanced'])
                                            <i class="bi bi-check-circle fs-4"></i>
                                            <strong class="fs-5">BALANCED</strong>
                                            <br><small>Total Debit = Total Kredit</small>
                                        @else
                                            <i class="bi bi-exclamation-triangle fs-4"></i>
                                            <strong class="fs-5">TIDAK SEIMBANG</strong>
                                            <br>
                                            <small>
                                                Selisih: Rp {{ number_format(abs($neracaSaldoData['total_debit'] - $neracaSaldoData['total_kredit']), 0, ',', '.') }}
                                            </small>
                                        @endif
                                    </th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi dan Penjelasan -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle"></i> Tentang Neraca Saldo</h6>
                <ul class="mb-0 small">
                    <li><strong>Sumber Data:</strong> Diambil langsung dari Buku Besar (journal_lines)</li>
                    <li><strong>Logika:</strong> Saldo akhir = Saldo awal ± Mutasi periode</li>
                    <li><strong>Keseimbangan:</strong> Total Debit harus = Total Kredit</li>
                    <li><strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-success">
                <h6><i class="bi bi-check-circle"></i> Normal Balance Akun</h6>
                <div class="row small">
                    <div class="col-6">
                        <strong>Saldo Normal Debit:</strong>
                        <ul class="mb-0">
                            <li>Aset (1xx)</li>
                            <li>Beban (5xx, 6xx)</li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <strong>Saldo Normal Kredit:</strong>
                        <ul class="mb-0">
                            <li>Kewajiban (2xx)</li>
                            <li>Modal (3xx)</li>
                            <li>Pendapatan (4xx)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$neracaSaldoData['is_balanced'])
        <div class="alert alert-warning">
            <h6><i class="bi bi-exclamation-triangle"></i> Neraca Saldo Tidak Seimbang</h6>
            <p class="mb-2">
                Total Debit (Rp {{ number_format($neracaSaldoData['total_debit'], 0, ',', '.') }}) 
                tidak sama dengan Total Kredit (Rp {{ number_format($neracaSaldoData['total_kredit'], 0, ',', '.') }}).
            </p>
            <p class="mb-0 small">
                <strong>Kemungkinan penyebab:</strong>
                Kesalahan input jurnal, transaksi yang belum diposting, atau ada jurnal yang tidak seimbang.
                Silakan periksa kembali jurnal-jurnal pada periode ini.
            </p>
        </div>
    @endif
</div>

<style>
.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.bg-brown-gradient {
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 50%, #CD853F 100%) !important;
}

.border-top-3 {
    border-top: 3px solid #dee2e6 !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,.075);
    transition: background-color 0.15s ease-in-out;
}

.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.badge {
    font-size: 0.75em;
    font-weight: 600;
}

.fs-2 {
    font-size: 2rem !important;
}

.shadow {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.table-dark th {
    background-color: #6F4E37 !important;
    border-color: #8B4513 !important;
    color: #fff !important;
}

.btn {
    font-weight: 500;
    letter-spacing: 0.5px;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

@media print {
    .no-print {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
    
    .bg-gradient {
        background: #6c757d !important;
        color: white !important;
    }
}

/* Loading animation */
.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodForm = document.getElementById('periodForm');
    const loadBtn = document.getElementById('loadBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const bulanSelect = document.getElementById('bulanSelect');
    const tahunInput = document.getElementById('tahunInput');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const mainContent = document.getElementById('mainContent');
    const pdfBtn = document.getElementById('pdfBtn');

    // Function to show loading
    function showLoading() {
        loadingIndicator.style.display = 'block';
        mainContent.style.display = 'none';
        loadBtn.disabled = true;
        refreshBtn.disabled = true;
    }

    // Function to hide loading
    function hideLoading() {
        loadingIndicator.style.display = 'none';
        mainContent.style.display = 'block';
        loadBtn.disabled = false;
        refreshBtn.disabled = false;
    }

    // Function to update PDF link
    function updatePdfLink() {
        const bulan = bulanSelect.value;
        const tahun = tahunInput.value;
        const newUrl = `{{ route('akuntansi.neraca-saldo.pdf') }}?bulan=${bulan}&tahun=${tahun}`;
        pdfBtn.href = newUrl;
    }

    // Function to load data via AJAX
    function loadTrialBalance() {
        const bulan = bulanSelect.value;
        const tahun = tahunInput.value;

        showLoading();

        fetch(`{{ route('akuntansi.neraca-saldo.api') }}?bulan=${bulan}&tahun=${tahun}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateTable(data.data);
                    updatePdfLink();
                    
                    // Update page title safely
                    const headerElement = document.querySelector('.card-header h5');
                    if (headerElement) {
                        const periodText = new Date(tahun, bulan - 1).toLocaleDateString('id-ID', { 
                            month: 'long', 
                            year: 'numeric' 
                        });
                        const periodElement = headerElement.parentElement.querySelector('small');
                        if (periodElement) {
                            periodElement.textContent = `Periode: ${periodText}`;
                        }
                    }
                    
                    // Show success message
                    showNotification('success', 'Data berhasil diperbarui!');
                } else {
                    throw new Error(data.message || 'Gagal memuat data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Terjadi kesalahan saat memuat data: ' + error.message);
            })
            .finally(() => {
                hideLoading();
            });
    }

    // Function to show notifications
    function showNotification(type, message) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification-alert');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create new notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show notification-alert`;
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 4000);
    }

    // Function to update table with new data
    function updateTable(data) {
        const tbody = document.getElementById('trialBalanceBody');
        const tfoot = document.getElementById('trialBalanceFooter');

        if (!tbody || !tfoot) {
            console.error('Table elements not found');
            return;
        }

        // Clear existing rows
        tbody.innerHTML = '';

        // Check if there's data
        if (!data.accounts || data.accounts.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <h5>Tidak ada data transaksi</h5>
                        <p>Belum ada transaksi untuk periode yang dipilih</p>
                    </td>
                </tr>
            `;
            tfoot.innerHTML = '';
            return;
        }

        // Add new rows
        data.accounts.forEach((account, index) => {
            const row = document.createElement('tr');
            row.className = 'border-bottom';
            row.innerHTML = `
                <td class="text-center fw-bold text-muted">${index + 1}</td>
                <td>
                    <span class="badge bg-secondary">${account.kode_akun}</span>
                </td>
                <td>
                    <div>
                        <strong>${account.nama_akun}</strong>
                        ${account.tipe_akun ? `<br><small class="text-muted">${account.tipe_akun}</small>` : ''}
                    </div>
                </td>
                <td class="text-end">
                    ${account.debit > 0 ? 
                        `<span class="fw-bold text-primary">Rp ${new Intl.NumberFormat('id-ID').format(account.debit)}</span>` : 
                        '<span class="text-muted">-</span>'
                    }
                </td>
                <td class="text-end">
                    ${account.kredit > 0 ? 
                        `<span class="fw-bold text-success">Rp ${new Intl.NumberFormat('id-ID').format(account.kredit)}</span>` : 
                        '<span class="text-muted">-</span>'
                    }
                </td>
            `;
            tbody.appendChild(row);
        });

        // Update footer
        const balanceStatus = data.is_balanced ? 
            '<i class="bi bi-check-circle fs-4"></i><strong class="fs-5">BALANCED</strong><br><small>Total Debit = Total Kredit</small>' :
            `<i class="bi bi-exclamation-triangle fs-4"></i><strong class="fs-5">TIDAK SEIMBANG</strong><br>
             <small>Selisih: Rp ${new Intl.NumberFormat('id-ID').format(Math.abs(data.total_debit - data.total_kredit))}</small>`;

        tfoot.innerHTML = `
            <tr class="border-top-3">
                <th colspan="3" class="text-end py-3">
                    <i class="bi bi-calculator"></i> <strong>TOTAL</strong>
                </th>
                <th class="text-end py-3">
                    <span class="fs-5 fw-bold">
                        Rp ${new Intl.NumberFormat('id-ID').format(data.total_debit)}
                    </span>
                </th>
                <th class="text-end py-3">
                    <span class="fs-5 fw-bold">
                        Rp ${new Intl.NumberFormat('id-ID').format(data.total_kredit)}
                    </span>
                </th>
            </tr>
            <tr class="bg-${data.is_balanced ? 'success' : 'warning'}">
                <th colspan="3" class="text-end py-3 text-white">
                    <i class="bi bi-${data.is_balanced ? 'check-circle' : 'exclamation-triangle'}"></i>
                    STATUS KESEIMBANGAN:
                </th>
                <th colspan="2" class="text-center py-3 text-white">
                    ${balanceStatus}
                </th>
            </tr>
        `;

        // Update summary cards if they exist
        updateSummaryCards(data);
    }

    // Function to update summary cards
    function updateSummaryCards(data) {
        // Update Total Debit card
        const debitCard = document.querySelector('.card.bg-primary h4');
        if (debitCard) {
            debitCard.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(data.total_debit)}`;
        }

        // Update Total Kredit card
        const kreditCard = document.querySelector('.card.bg-success h4');
        if (kreditCard) {
            kreditCard.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(data.total_kredit)}`;
        }

        // Update Status card
        const statusCard = document.querySelector('.card.bg-success h5, .card.bg-warning h5');
        if (statusCard) {
            const parentCard = statusCard.closest('.card');
            if (parentCard) {
                parentCard.className = `card ${data.is_balanced ? 'bg-success' : 'bg-warning'} text-white`;
                statusCard.innerHTML = `
                    <i class="bi bi-${data.is_balanced ? 'check-circle' : 'exclamation-triangle'}"></i> 
                    ${data.is_balanced ? 'BALANCED' : 'TIDAK SEIMBANG'}
                `;
            }
        }

        // Update Total Akun card
        const akunCard = document.querySelector('.card.bg-info h4');
        if (akunCard) {
            akunCard.textContent = data.accounts ? data.accounts.length : 0;
        }
    }

    // Event listeners
    refreshBtn.addEventListener('click', function(e) {
        e.preventDefault();
        loadTrialBalance();
    });

    // Auto-update PDF link when period changes
    bulanSelect.addEventListener('change', updatePdfLink);
    tahunInput.addEventListener('input', updatePdfLink);

    // Form submission with AJAX
    periodForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadTrialBalance();
        
        // Update URL without page reload
        const bulan = bulanSelect.value;
        const tahun = tahunInput.value;
        const newUrl = `{{ route('akuntansi.neraca-saldo-temp') }}?bulan=${bulan}&tahun=${tahun}`;
        window.history.pushState({}, '', newUrl);
    });
});
</script>
@endsection