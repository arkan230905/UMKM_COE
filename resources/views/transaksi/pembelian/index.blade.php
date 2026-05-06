@extends('layouts.app')

@section('title', 'Daftar Pembelian')

@section('content')
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
</style>
@endpush
@push('scripts')
<script>
// Cache busting for journal modal
const journalModalVersion = '2026-04-30-v2';
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
        fetch(`/api/pembelian/${pembelianId}/journal?v=${journalModalVersion}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.journals && data.journals.length > 0) {
                    let totalDebitAmount = 0;
                    let totalCreditAmount = 0;
                    let rows = '';
                    
                    data.journals.forEach(entry => {
                        totalDebitAmount += entry.debit;
                        totalCreditAmount += entry.kredit;
                        
                        const tanggal = entry.tanggal ? new Date(entry.tanggal).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-') : '-';
                        const coaInfo = entry.coa ? 
                            `<span class="badge bg-primary">${entry.coa.nama_akun}</span><br><small class="text-muted">${entry.coa.kode_akun}</small>` : 
                            '<span class="badge bg-secondary">COA tidak ditemukan</span>';
                        const keterangan = entry.keterangan || '-';
                        const debit = entry.debit > 0 ? 'Rp ' + entry.debit.toLocaleString('id-ID') : '-';
                        const kredit = entry.kredit > 0 ? 'Rp ' + entry.kredit.toLocaleString('id-ID') : '-';
                        
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
                    totalDebit.textContent = 'Rp ' + totalDebitAmount.toLocaleString('id-ID');
                    totalCredit.textContent = 'Rp ' + totalCreditAmount.toLocaleString('id-ID');
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

@endpush
@endsection
