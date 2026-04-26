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
</style>
@endpush
@endsection
