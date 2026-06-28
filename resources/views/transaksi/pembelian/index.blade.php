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
        <!-- RETUR TAB - HIDDEN (feature disabled but code preserved) -->
        <li class="nav-item" role="presentation" style="display: none;">
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

        <!-- Retur Tab - HIDDEN (feature disabled but code preserved) -->
        <div class="tab-pane fade {{ request('tab') == 'retur' ? 'show active' : '' }}" 
             id="retur" 
             role="tabpanel" 
             aria-labelledby="retur-tab"
             style="display: none;">
            @include('transaksi.pembelian.partials.retur-content')
        </div>
    </div>
</div>

<!-- Journal Modal -->
<div class="modal fade" id="journalModal" tabindex="-1" aria-labelledby="journalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content rounded-4 shadow-lg border-0">
            <div class="modal-header border-0 pb-2 pt-4 px-4">
                <h4 class="modal-title fw-bold" id="journalModalLabel" style="color: #1F2937;">
                    Jurnal Pembelian
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pt-2">
                <!-- Transaction Info Card -->
                <div class="card border rounded-3 mb-4" id="transactionInfoCard" style="background-color: #ffffff; border-color: #E5E7EB !important; display: none;">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex flex-column">
                                    <small class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 500;">Nomor Pembelian</small>
                                    <span class="fw-bold fs-5" id="nomorPembelian" style="color: #1F2937;">-</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex flex-column">
                                    <small class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 500;">Vendor</small>
                                    <span class="fw-semibold" id="vendorName" style="color: #6B4F3A; font-size: 0.95rem;">-</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex flex-column">
                                    <small class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 500;">Tanggal</small>
                                    <span class="fw-semibold" id="tanggalPembelian" style="color: #1F2937; font-size: 0.95rem;">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Journal Table -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                        <thead style="background-color: #F8F6F3;">
                            <tr>
                                <th class="border-0 py-3 px-3" style="color: #6B4F3A; font-weight: 600; font-size: 0.875rem;">Tanggal</th>
                                <th class="border-0 py-3 px-3" style="color: #6B4F3A; font-weight: 600; font-size: 0.875rem;">Akun</th>
                                <th class="border-0 py-3 px-3" style="color: #6B4F3A; font-weight: 600; font-size: 0.875rem;">Keterangan</th>
                                <th class="border-0 py-3 px-3 text-end" style="color: #6B4F3A; font-weight: 600; font-size: 0.875rem;">Debit</th>
                                <th class="border-0 py-3 px-3 text-end" style="color: #6B4F3A; font-weight: 600; font-size: 0.875rem;">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="journalTableBody" style="background-color: #ffffff;">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5 border-bottom" style="border-color: #E5E7EB !important;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Pilih pembelian untuk melihat jurnal
                                </td>
                            </tr>
                        </tbody>
                        <tfoot style="background-color: #F8F6F3;">
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end py-3 px-3 border-0" style="color: #1F2937; font-size: 0.95rem;">Total:</td>
                                <td class="text-end py-3 px-3 border-0" id="totalDebit" style="color: #1F2937; font-size: 0.95rem;">Rp 0</td>
                                <td class="text-end py-3 px-3 border-0" id="totalCredit" style="color: #1F2937; font-size: 0.95rem;">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-3">
                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Tutup</button>
            </div>
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

    /* Modern Journal Modal Styles */
    #journalModal .modal-content {
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    #journalModal tbody tr {
        border-bottom: 1px solid #E5E7EB;
        transition: background-color 0.15s ease;
    }

    #journalModal tbody tr:hover {
        background-color: #F9FAFB !important;
    }

    #journalModal tbody tr:last-child {
        border-bottom: none;
    }

    #journalModal tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    #journalModal .account-name {
        color: #1F2937;
        font-weight: 600;
        font-size: 0.9rem;
        display: block;
        margin-bottom: 0.25rem;
    }

    #journalModal .account-code {
        color: #6B7280;
        font-size: 0.8rem;
        font-weight: 400;
    }

    /* Sortable column styles */
    .sortable {
        user-select: none;
        position: relative;
    }

    .sortable:hover {
        background-color: #f8f9fa !important;
    }

    .sortable .sort-icon {
        display: inline-block;
        min-width: 15px;
        text-align: center;
    }

    .sortable .sort-icon i {
        font-size: 0.875rem;
    }

    .sortable .sort-icon .fa-sort {
        opacity: 0.3;
    }

    .sortable:hover .sort-icon .fa-sort {
        opacity: 0.6;
    }

    .sortable .sort-icon .fa-sort-up,
    .sortable .sort-icon .fa-sort-down {
        opacity: 1;
    }
</style>
@endpush
@push('scripts')
<script>
// Cache busting for journal modal
const journalModalVersion = '2026-04-30-v2';
    // Function to load journal data for a specific pembelian
    function loadJournal(pembelianId, nomorPembelian) {
        console.log('Loading journal for pembelian ID:', pembelianId, 'Nomor:', nomorPembelian);
        
        // Show loading state
        const journalTableBody = document.getElementById('journalTableBody');
        const totalDebit = document.getElementById('totalDebit');
        const totalCredit = document.getElementById('totalCredit');
        const transactionInfoCard = document.getElementById('transactionInfoCard');
        
        if (!journalTableBody) {
            console.error('journalTableBody element not found');
            return;
        }
        
        // Hide transaction info during loading
        if (transactionInfoCard) {
            transactionInfoCard.style.display = 'none';
        }
        
        journalTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-5 border-bottom" style="border-color: #E5E7EB !important;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
        totalDebit.textContent = 'Rp 0';
        totalCredit.textContent = 'Rp 0';
        
        // Fetch journal data
        fetch(`/transaksi/api/pembelian/${pembelianId}/journal?v=${journalModalVersion}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                console.log('API Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('API Response data:', data);
                if (data.success && data.journals && data.journals.length > 0) {
                    let totalDebitAmount = 0;
                    let totalCreditAmount = 0;
                    let rows = '';
                    
                    // Update transaction info card
                    if (transactionInfoCard && data.pembelian) {
                        document.getElementById('nomorPembelian').textContent = data.pembelian.nomor_pembelian || nomorPembelian || '-';
                        document.getElementById('vendorName').textContent = data.pembelian.vendor_name || '-';
                        document.getElementById('tanggalPembelian').textContent = data.pembelian.tanggal || '-';
                        transactionInfoCard.style.display = 'block';
                    }
                    
                    data.journals.forEach(entry => {
                        totalDebitAmount += entry.debit;
                        totalCreditAmount += entry.kredit;
                        
                        const tanggal = entry.tanggal ? new Date(entry.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-';
                        
                        // Modern account format without badge
                        const coaInfo = entry.coa ? 
                            `<span class="account-name">${entry.coa.nama_akun}</span><span class="account-code">${entry.coa.kode_akun}</span>` : 
                            '<span class="account-name text-muted">COA tidak ditemukan</span>';
                        
                        const keterangan = entry.keterangan || '-';
                        const debit = entry.debit > 0 ? 'Rp ' + entry.debit.toLocaleString('id-ID') : '-';
                        const kredit = entry.kredit > 0 ? 'Rp ' + entry.kredit.toLocaleString('id-ID') : '-';
                        
                        rows += `
                            <tr style="border-bottom: 1px solid #E5E7EB;">
                                <td class="px-3 py-3" style="color: #6B7280; font-size: 0.875rem;">${tanggal}</td>
                                <td class="px-3 py-3">${coaInfo}</td>
                                <td class="px-3 py-3" style="color: #6B7280; font-size: 0.875rem;">${keterangan}</td>
                                <td class="text-end px-3 py-3" style="color: #1F2937; font-weight: 500; font-size: 0.875rem;">${debit}</td>
                                <td class="text-end px-3 py-3" style="color: #1F2937; font-weight: 500; font-size: 0.875rem;">${kredit}</td>
                            </tr>
                        `;
                    });
                    
                    journalTableBody.innerHTML = rows;
                    totalDebit.textContent = 'Rp ' + totalDebitAmount.toLocaleString('id-ID');
                    totalCredit.textContent = 'Rp ' + totalCreditAmount.toLocaleString('id-ID');
                } else {
                    if (transactionInfoCard) {
                        transactionInfoCard.style.display = 'none';
                    }
                    journalTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5 border-bottom" style="border-color: #E5E7EB !important;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Jurnal belum dibuat untuk pembelian ini
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading journal:', error);
                if (transactionInfoCard) {
                    transactionInfoCard.style.display = 'none';
                }
                journalTableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-danger py-5 border-bottom" style="border-color: #E5E7EB !important;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Gagal memuat data jurnal: ${error.message}
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

        // Handle sortable column clicks for pembelian table
        const sortableHeaders = document.querySelectorAll('.sortable');
        
        sortableHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const sortBy = this.dataset.sort;
                const currentSortBy = '{{ request("sort_by") }}';
                const currentSortOrder = '{{ request("sort_order", "asc") }}';
                
                // Determine new sort order
                let newSortOrder = 'asc';
                if (sortBy === currentSortBy) {
                    // Toggle sort order if clicking the same column
                    newSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
                }
                
                // Build URL with all current filters
                const url = new URL(window.location.href);
                url.searchParams.set('sort_by', sortBy);
                url.searchParams.set('sort_order', newSortOrder);
                
                // Redirect to new URL
                window.location.href = url.toString();
            });
            
            // Add hover effect
            header.style.transition = 'background-color 0.2s';
            header.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            header.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
</script>

@endpush
@endsection
