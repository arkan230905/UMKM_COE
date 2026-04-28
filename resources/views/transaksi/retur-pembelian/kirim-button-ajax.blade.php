{{-- Tombol Kirim Barang dengan AJAX --}}
@if($retur->status === 'disetujui')
    <button type="button" 
            class="btn btn-primary btn-sm kirim-barang-btn"
            data-retur-id="{{ $retur->id }}"
            data-csrf="{{ csrf_token() }}">
        <i class="fas fa-shipping-fast me-1"></i>
        Kirim Barang
    </button>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle kirim barang button
    document.querySelectorAll('.kirim-barang-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const returId = this.dataset.returId;
            const csrfToken = this.dataset.csrf;
            
            // Show confirmation
            if (confirm('Yakin ingin mengubah status ke Dikirim?')) {
                // Disable button
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
                
                // Send AJAX request
                fetch(`/transaksi/retur-pembelian/${returId}/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    
                    if (response.ok) {
                        // Success - reload page to show updated status
                        window.location.reload();
                    } else {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan: ' + error.message);
                    
                    // Re-enable button
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-shipping-fast me-1"></i>Kirim Barang';
                });
            }
        });
    });
});
</script>
@endpush