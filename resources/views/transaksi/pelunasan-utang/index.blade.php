@extends('layouts.app')

@section('title', 'Pelunasan Utang')

@push('styles')
<style>
/* Tab Navigation - Style seperti penjualan */
.nav-tabs-custom {
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 1.5rem;
}

.nav-tabs-custom .tab-btn {
    background: none;
    border: none;
    padding: 1rem 2rem;
    font-size: 0.95rem;
    font-weight: 500;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
    margin-bottom: -1px;
    margin-right: 2rem;
}

.nav-tabs-custom .tab-btn:hover {
    color: #495057;
    border-bottom-color: #d1d5db;
}

.nav-tabs-custom .tab-btn.active {
    color: #8B7355;
    border-bottom-color: #8B7355;
    font-weight: 600;
}

.tab-pane {
    display: none;
}

.tab-pane.show.active {
    display: block;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-hand-holding-usd me-2"></i>Pelunasan Utang
        </h2>
        <div id="tab-actions">
            <!-- Actions will be dynamically shown based on active tab -->
        </div>
    </div>

    <!-- Tab Navigation - Custom Style -->
    <div class="nav-tabs-custom">
        <button class="tab-btn active" data-tab="daftar-utang">
            <i class="fas fa-file-invoice-dollar me-2"></i>Daftar Utang
        </button>
        <button class="tab-btn" data-tab="pelunasan">
            <i class="fas fa-check-circle me-2"></i>Riwayat Pelunasan
        </button>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="pelunasanTabsContent">
        <!-- Daftar Utang Tab -->
        <div class="tab-pane show active" id="daftar-utang" role="tabpanel">
            @include('transaksi.pelunasan-utang.partials.daftar-utang')
        </div>

        <!-- Pelunasan Tab -->
        <div class="tab-pane" id="pelunasan" role="tabpanel">
            @include('transaksi.pelunasan-utang.partials.pelunasan-content')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching logic - TANPA reload halaman
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    const tabActions = document.getElementById('tab-actions');
    
    // Define actions for each tab
    const tabActionsHTML = {
        'daftar-utang': '', // No button for daftar utang
        'pelunasan': '' // No add button - user adds from daftar utang using Lunasi button
    };
    
    // Function to switch tabs
    function switchTab(targetTab) {
        // Update tab buttons
        tabButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.tab === targetTab) {
                btn.classList.add('active');
            }
        });
        
        // Update tab panes
        tabPanes.forEach(pane => {
            pane.classList.remove('show', 'active');
            if (pane.id === targetTab) {
                pane.classList.add('show', 'active');
            }
        });
        
        // Update actions
        tabActions.innerHTML = tabActionsHTML[targetTab] || '';
    }
    
    // Set initial state from URL
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || 'daftar-utang';
    switchTab(initialTab);
    
    // Add click handlers
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            switchTab(targetTab);
            
            // Update URL without reload
            const url = new URL(window.location);
            if (targetTab === 'daftar-utang') {
                url.searchParams.delete('tab');
            } else {
                url.searchParams.set('tab', targetTab);
            }
            window.history.pushState({}, '', url);
        });
    });
});
</script>
@endpush
