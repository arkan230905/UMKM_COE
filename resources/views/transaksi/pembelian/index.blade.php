@extends('layouts.app')

@section('title', 'Daftar Pembelian')

@push('styles')
<style>
    .nav-tabs .nav-link {
        color: #6c757d;
        border: 1px solid transparent;
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: #e9ecef #e9ecef #dee2e6;
        isolation: isolate;
    }
    
    .nav-tabs .nav-link.active {
        color: #495057;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }
    
    /* Table styling for better layout */
    .nowrap {
        white-space: nowrap;
    }
    
    .table td, .table th {
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Transaksi Pembelian
        </h2>
        <div id="tab-actions">
            <!-- Actions will be dynamically shown based on active tab -->
        </div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="pembelianTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ !request('tab') || request('tab') == 'pembelian' ? 'active' : '' }}" 
                    id="pembelian-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#pembelian" 
                    type="button" 
                    role="tab" 
                    aria-controls="pembelian" 
                    aria-selected="{{ !request('tab') || request('tab') == 'pembelian' ? 'true' : 'false' }}">
                <i class="fas fa-shopping-cart me-2"></i>Daftar Pembelian
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') == 'retur' ? 'active' : '' }}" 
                    id="retur-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#retur" 
                    type="button" 
                    role="tab" 
                    aria-controls="retur" 
                    aria-selected="{{ request('tab') == 'retur' ? 'true' : 'false' }}">
                <i class="fas fa-undo me-2"></i>Retur Pembelian
            </button>
        </li>
    </ul>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tab Content -->
    <div class="tab-content" id="pembelianTabsContent">
        <!-- Pembelian Tab -->
        <div class="tab-pane fade {{ !request('tab') || request('tab') == 'pembelian' ? 'show active' : '' }}" 
             id="pembelian" 
             role="tabpanel" 
             aria-labelledby="pembelian-tab">
            @include('transaksi.pembelian.partials.pembelian-content')
        </div>

        <!-- Retur Tab -->
        <div class="tab-pane fade {{ request('tab') == 'retur' ? 'show active' : '' }}" 
             id="retur" 
             role="tabpanel" 
             aria-labelledby="retur-tab">
            @include('transaksi.pembelian.partials.retur-content')
        </div>
    </div>
</div>

<!-- Journal Modal -->
<div class="modal fade" id="journalModal" tabindex="-1" aria-labelledby="journalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="journalModalLabel">
                    <i class="fas fa-book me-2"></i>Jurnal Pembelian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Tanggal</th>
                                <th>Akun</th>
                                <th>Keterangan</th>
                                <th class="text-end">Debet</th>
                                <th class="text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="journalTableBody">
                            <!-- Journal entries will be loaded here -->
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th class="text-end" id="totalDebit">Rp 0</th>
                                <th class="text-end" id="totalCredit">Rp 0</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Function to load journal data for a specific pembelian
    function loadJournal(pembelianId, nomorPembelian) {
        // Show loading state
        const journalTableBody = document.getElementById('journalTableBody');
        const totalDebit = document.getElementById('totalDebit');
        const totalCredit = document.getElementById('totalCredit');
        
        journalTableBody.innerHTML = '<tr><td colspan="5" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
        totalDebit.textContent = 'Rp 0';
        totalCredit.textContent = 'Rp 0';
        
        // Fetch journal data
        fetch(`/api/pembelian/${pembelianId}/journal`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.journals && data.journals.length > 0) {
                    let totalDebitAmount = 0;
                    let totalCreditAmount = 0;
                    let rows = '';
                    
                    data.journals.forEach(entry => {
                        totalDebitAmount += entry.debit;
                        totalCreditAmount += entry.kredit;
                        
                        const tanggal = entry.tanggal ? new Date(entry.tanggal).toLocaleDateString('id-ID') : '-';
                        const coaInfo = entry.coa ? 
                            `<span class="badge bg-primary">${entry.coa.nama_akun}</span><br><small class="text-muted">${entry.coa.kode_akun}</small>` : 
                            '<span class="badge bg-secondary">COA tidak ditemukan</span>';
                        const keterangan = entry.keterangan || '-';
                        const debit = entry.debit > 0 ? 'Rp ' + entry.debit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
                        const kredit = entry.kredit > 0 ? 'Rp ' + entry.kredit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
                        
                        rows += `
                            <tr>
                                <td>${tanggal}</td>
                                <td>${coaInfo}</td>
                                <td>${keterangan}</td>
                                <td class="text-end">${debit}</td>
                                <td class="text-end">${kredit}</td>
                            </tr>
                        `;
                    });
                    
                    journalTableBody.innerHTML = rows;
                    totalDebit.textContent = 'Rp ' + totalDebitAmount.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    totalCredit.textContent = 'Rp ' + totalCreditAmount.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                } else {
                    journalTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Jurnal belum dibuat untuk pembelian ini
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading journal:', error);
                journalTableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Gagal memuat data jurnal
                        </td>
                    </tr>
                `;
            });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Handle tab switching with URL update and dynamic actions
        const tabButtons = document.querySelectorAll('#pembelianTabs button[data-bs-toggle="tab"]');
        const tabActions = document.getElementById('tab-actions');
        
        // Define actions for each tab
        const tabActionsConfig = {
            'pembelian': `
                <a href="{{ route('transaksi.pembelian.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Pembelian
                </a>
            `,
            'retur': '' // No actions for retur tab
        };
        
        // Function to update actions based on active tab
        function updateTabActions(tabId) {
            tabActions.innerHTML = tabActionsConfig[tabId] || '';
        }
        
        // Set initial actions based on current tab
        const currentTab = '{{ request("tab", "pembelian") }}';
        updateTabActions(currentTab);
        
        // Check if we just created a new retur
        @if(session('new_retur_created') && !session('success'))
        console.log('New retur created session detected');
        console.log('Current tab:', currentTab);
        console.log('Session data:', {
            new_retur_created: {{ session('new_retur_created') ? 'true' : 'false' }},
            new_retur_id: {{ session('new_retur_id') ?? 'null' }}
        });
        
        // Ensure we're on the retur tab
        if (currentTab !== 'retur') {
            // Switch to retur tab automatically
            const returTab = document.querySelector('#retur-tab');
            if (returTab) {
                returTab.click();
            }
        }
        
        // Show success message immediately without forcing reload
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>Retur baru berhasil dibuat dan data telah dimuat!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert alert at the top of the page
        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                alertDiv.remove();
            }, 5000);
        }
        
        // Auto-scroll to new retur row after tab switch
        setTimeout(function() {
            const newReturRow = document.querySelector('tr.table-success');
            if (newReturRow) {
                newReturRow.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                // Add pulse animation
                newReturRow.style.animation = 'pulse 2s ease-in-out';
            }
        }, 1000);
        @endif
        
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function (event) {
                const tabId = event.target.getAttribute('aria-controls');
                const url = new URL(window.location);
                
                if (tabId === 'pembelian') {
                    url.searchParams.delete('tab');
                } else {
                    url.searchParams.set('tab', tabId);
                }
                
                window.history.pushState({}, '', url);
                updateTabActions(tabId);
            });
        });
    });
</script>

<style>
/* Animation for new retur highlight */
@keyframes pulse {
    0% { background-color: #d1e7dd; }
    50% { background-color: #a3d9a4; }
    100% { background-color: #d1e7dd; }
}

.table-success {
    background-color: #d1e7dd !important;
}

/* Fix journal modal table header text color */
#journalModal .table-dark th {
    color: #000 !important;
    background-color: #343a40 !important;
}

/* Fix COA badges in journal modal */
#journalModal .badge {
    font-size: 0.8em;
    margin-bottom: 2px;
}
</style>
@endpush
@endsection
