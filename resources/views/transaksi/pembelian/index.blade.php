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
