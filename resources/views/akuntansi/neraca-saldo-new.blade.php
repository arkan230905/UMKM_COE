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
        <!-- Company Header -->
        <div class="mb-4">
            <div class="p-4 bg-white rounded-3 shadow-sm" style="width: 100%;">
                <div class="text-center">
                    <h4 class="fw-bold mb-2">PT MANUFAKTUR COE</h4>
                    <p class="text-muted mb-2 small">Laporan Keuangan {{ \Carbon\Carbon::parse($tahun . '-' . $bulan . '-01')->isoFormat('MMMM YYYY') }}</p>
                    <h5 class="fw-bold text-dark mb-0">Neraca Saldo</h5>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="trialBalanceTable" style="border: 2px solid #8B7355;">
                        <thead>
                            <tr style="background-color: #f8f9fa; border-bottom: 2px solid #8B7355;">
                                <th class="text-center py-3" style="width: 5%; border-right: 1px solid #8B7355; font-weight: 600;">
                                    No
                                </th>
                                <th class="py-3" style="width: 50%; border-right: 1px solid #8B7355; font-weight: 600;">
                                    AKUN
                                </th>
                                <th class="text-center py-3" style="width: 22.5%; border-right: 1px solid #8B7355; font-weight: 600;">
                                    DEBIT (RP)
                                </th>
                                <th class="text-center py-3" style="width: 22.5%; font-weight: 600;">
                                    KREDIT (RP)
                                </th>
                            </tr>
                        </thead>
                        <tbody id="trialBalanceBody">
                            @php $no = 1; @endphp
                            @if(count($neracaSaldoData['accounts']) > 0)
                                @foreach($neracaSaldoData['accounts'] as $account)
                                    <tr style="border-bottom: 1px solid #dee2e6;">
                                        <td class="text-center" style="border-right: 1px solid #8B7355; padding: 12px 8px; font-weight: 600;">
                                            {{ $no++ }}
                                        </td>
                                        <td style="border-right: 1px solid #dee2e6; padding: 12px 15px;">
                                            <div>
                                                <strong>{{ $account['kode_akun'] }} - {{ $account['nama_akun'] }}</strong>
                                                @if(isset($account['tipe_akun']))
                                                    <br><small class="text-muted">{{ $account['tipe_akun'] }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end" style="border-right: 1px solid #dee2e6; padding: 12px 15px;">
                                            @if($account['debit'] > 0)
                                                <strong>{{ number_format($account['debit'], 0, ',', '.') }}</strong>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-end" style="padding: 12px 15px;">
                                            @if($account['kredit'] > 0)
                                                <strong>{{ number_format($account['kredit'], 0, ',', '.') }}</strong>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        <h5>Tidak ada data transaksi</h5>
                                        <p>Belum ada transaksi untuk periode {{ \Carbon\Carbon::parse($tahun . '-' . $bulan . '-01')->isoFormat('MMMM YYYY') }}</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        @if(count($neracaSaldoData['accounts']) > 0)
                            <tfoot>
                                <tr style="background-color: #f8f9fa; border-top: 2px solid #8B7355; border-bottom: 2px solid #8B7355;">
                                    <th class="text-center py-3" style="border-right: 1px solid #8B7355; font-weight: 600; padding: 15px;">
                                        
                                    </th>
                                    <th class="text-end py-3" style="border-right: 1px solid #8B7355; font-weight: 600; padding: 15px;">
                                        <strong>TOTAL</strong>
                                    </th>
                                    <th class="text-end py-3" style="border-right: 1px solid #8B7355; font-weight: 600; padding: 15px;">
                                        <strong>{{ number_format($neracaSaldoData['total_debit'], 0, ',', '.') }}</strong>
                                    </th>
                                    <th class="text-end py-3" style="font-weight: 600; padding: 15px;">
                                        <strong>{{ number_format($neracaSaldoData['total_kredit'], 0, ',', '.') }}</strong>
                                    </th>
                                </tr>
                                <tr style="background-color: {{ $neracaSaldoData['is_balanced'] ? '#d4edda' : '#fff3cd' }}; border-bottom: 2px solid #8B7355;">
                                    <th class="text-center py-3" style="border-right: 1px solid #8B7355; font-weight: 600; padding: 15px;">
                                        
                                    </th>
                                    <th class="text-end py-3" style="border-right: 1px solid #8B7355; font-weight: 600; padding: 15px;">
                                        <strong>STATUS KESEIMBANGAN:</strong>
                                    </th>
                                    <th colspan="2" class="text-center py-3" style="font-weight: 600; padding: 15px;">
                                        @if($neracaSaldoData['is_balanced'])
                                            <span class="text-success">
                                                <i class="bi bi-check-circle"></i>
                                                <strong>SEIMBANG</strong>
                                            </span>
                                            <br><small class="text-muted">Total Debit = Total Kredit</small>
                                        @else
                                            <span class="text-warning">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                <strong>TIDAK SEIMBANG</strong>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                Selisih: {{ number_format(abs($neracaSaldoData['total_debit'] - $neracaSaldoData['total_kredit']), 0, ',', '.') }}
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

    <!-- Summary Information -->
    <div class="row mt-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-primary">
                        <i class="bi bi-info-circle"></i> Informasi Neraca Saldo
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="mb-0 small text-muted">
                                <li><strong>Sumber Data:</strong> Buku Besar (journal_lines)</li>
                                <li><strong>Perhitungan:</strong> Saldo akhir = Saldo awal + Mutasi periode</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="mb-0 small text-muted">
                                <li><strong>Prinsip:</strong> Total Debit harus sama dengan Total Kredit</li>
                                <li><strong>Periode:</strong> {{ \Carbon\Carbon::parse($tahun . '-' . $bulan . '-01')->isoFormat('MMMM YYYY') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-success">
                        <i class="bi bi-calculator"></i> Ringkasan
                    </h6>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total Debit:</span>
                            <strong class="text-primary">Rp {{ number_format($neracaSaldoData['total_debit'], 0, ',', '.') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total Kredit:</span>
                            <strong class="text-success">Rp {{ number_format($neracaSaldoData['total_kredit'], 0, ',', '.') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Status:</span>
                            <span class="badge bg-{{ $neracaSaldoData['is_balanced'] ? 'success' : 'warning' }}">
                                {{ $neracaSaldoData['is_balanced'] ? 'SEIMBANG' : 'TIDAK SEIMBANG' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$neracaSaldoData['is_balanced'])
        <div class="alert alert-warning shadow-sm mt-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
                <div>
                    <h6 class="mb-1">⚠️ Neraca Saldo Tidak Seimbang</h6>
                    <p class="mb-0 small">
                        <strong>Kemungkinan penyebab:</strong> Ada kesalahan input jurnal, akun yang tidak seimbang, atau transaksi yang belum lengkap.
                    </p>
                </div>
            </div>
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

.bg-sidebar-brown {
    background: linear-gradient(135deg, #8B7355 0%, #A0845C 50%, #B8956B 100%) !important;
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

.table-bordered {
    border: 2px solid #8B7355 !important;
}

.table-bordered th,
.table-bordered td {
    border: 1px solid #dee2e6 !important;
    vertical-align: middle;
}

.table thead th {
    border-bottom: 2px solid #8B7355 !important;
    background-color: #f8f9fa !important;
    font-weight: 600 !important;
    color: #495057 !important;
}

.table tfoot th {
    border-top: 2px solid #8B7355 !important;
    background-color: #f8f9fa !important;
    font-weight: 600 !important;
}

/* Remove hover effect for cleaner look */
.table-hover tbody tr:hover {
    background-color: transparent;
}

/* Professional spacing */
.table th,
.table td {
    padding: 12px 15px;
    line-height: 1.4;
}

/* Clean borders */
.table tbody tr {
    border-bottom: 1px solid #dee2e6;
}

.table tbody tr:last-child {
    border-bottom: 2px solid #8B7355;
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
        const tfoot = document.querySelector('tfoot');

        if (!tbody) {
            console.error('Table elements not found');
            return;
        }

        // Clear existing rows
        tbody.innerHTML = '';

        // Check if there's data
        if (!data.accounts || data.accounts.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <h5>Tidak ada data transaksi</h5>
                        <p>Belum ada transaksi untuk periode yang dipilih</p>
                    </td>
                </tr>
            `;
            if (tfoot) tfoot.style.display = 'none';
            return;
        }

        // Add new rows
        data.accounts.forEach((account, index) => {
            const row = document.createElement('tr');
            row.style.borderBottom = '1px solid #dee2e6';
            row.innerHTML = `
                <td class="text-center" style="border-right: 1px solid #8B7355; padding: 12px 8px; font-weight: 600;">
                    ${index + 1}
                </td>
                <td style="border-right: 1px solid #dee2e6; padding: 12px 15px;">
                    <div>
                        <strong>${account.kode_akun} - ${account.nama_akun}</strong>
                        ${account.tipe_akun ? `<br><small class="text-muted">${account.tipe_akun}</small>` : ''}
                    </div>
                </td>
                <td class="text-end" style="border-right: 1px solid #dee2e6; padding: 12px 15px;">
                    ${account.debit > 0 ? 
                        `<strong>${new Intl.NumberFormat('id-ID').format(account.debit)}</strong>` : 
                        '<span class="text-muted">0</span>'
                    }
                </td>
                <td class="text-end" style="padding: 12px 15px;">
                    ${account.kredit > 0 ? 
                        `<strong>${new Intl.NumberFormat('id-ID').format(account.kredit)}</strong>` : 
                        '<span class="text-muted">0</span>'
                    }
                </td>
            `;
            tbody.appendChild(row);
        });

        // Update footer
        if (tfoot) {
            tfoot.style.display = '';
            const balanceStatus = data.is_balanced ? 
                '<span class="text-success"><i class="bi bi-check-circle"></i><strong>SEIMBANG</strong></span><br><small class="text-muted">Total Debit = Total Kredit</small>' :
                `<span class="text-warning"><i class="bi bi-exclamation-triangle"></i><strong>TIDAK SEIMBANG</strong></span><br>
                 <small class="text-muted">Selisih: ${new Intl.NumberFormat('id-ID').format(Math.abs(data.total_debit - data.total_kredit))}</small>`;

            tfoot.innerHTML = `
                <tr style="background-color: #f8f9fa; border-top: 2px solid #8B7355; border-bottom: 2px solid #8B7355;">
                    <th class="text-center py-3" style="border-right: 1px solid #8B7355; font-weight: 600; padding: 15px;">
                        
                    </th>
                    <th class="text-end py-3" style="border-right: 1px solid #8B7355; font-weight: 600; padding: 15px;">
                        <strong>TOTAL</strong>
                    </th>
                    <th class="text-end py-3" style="border-right: 1px solid #8B7355; font-weight: 600; padding: 15px;">
                        <strong>${new Intl.NumberFormat('id-ID').format(data.total_debit)}</strong>
                    </th>
                    <th class="text-end py-3" style="font-weight: 600; padding: 15px;">
                        <strong>${new Intl.NumberFormat('id-ID').format(data.total_kredit)}</strong>
                    </th>
                </tr>
                <tr style="background-color: ${data.is_balanced ? '#d4edda' : '#fff3cd'}; border-bottom: 2px solid #8B7355;">
                    <th class="text-center py-3" style="border-right: 1px solid #8B7355; font-weight: 600; padding: 15px;">
                        
                    </th>
                    <th class="text-end py-3" style="border-right: 1px solid #8B7355; font-weight: 600; padding: 15px;">
                        <strong>STATUS KESEIMBANGAN:</strong>
                    </th>
                    <th colspan="2" class="text-center py-3" style="font-weight: 600; padding: 15px;">
                        ${balanceStatus}
                    </th>
                </tr>
            `;
        }

        // Update summary cards if they exist
        updateSummaryCards(data);
    }

    // Function to update summary cards
    function updateSummaryCards(data) {
        // Update ringkasan di card kanan bawah
        const summaryCard = document.querySelector('.col-md-4 .card-body');
        if (summaryCard) {
            const debitElement = summaryCard.querySelector('.text-primary');
            const kreditElement = summaryCard.querySelector('.text-success');
            const statusElement = summaryCard.querySelector('.badge');
            
            if (debitElement) {
                debitElement.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(data.total_debit)}`;
            }
            
            if (kreditElement) {
                kreditElement.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(data.total_kredit)}`;
            }
            
            if (statusElement) {
                statusElement.className = `badge bg-${data.is_balanced ? 'success' : 'warning'}`;
                statusElement.textContent = data.is_balanced ? 'SEIMBANG' : 'TIDAK SEIMBANG';
            }
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