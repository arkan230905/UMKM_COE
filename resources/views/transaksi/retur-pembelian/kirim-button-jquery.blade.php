{{-- Tombol Kirim Barang dengan jQuery --}}
@if($retur->status === 'disetujui')
    <button type="button" 
            class="btn btn-primary btn-sm"
            id="kirim-barang-{{ $retur->id }}"
            data-retur-id="{{ $retur->id }}">
        <i class="fas fa-shipping-fast me-1"></i>
        Kirim Barang
    </button>
@endif

@push('scripts')
<script>
$(document).ready(function() {
    // Handle kirim barang button
    $('[id^="kirim-barang-"]').on('click', function() {
        const returId = $(this).data('retur-id');
        const button = $(this);
        
        // Show confirmation
        if (confirm('Yakin ingin mengubah status ke Dikirim?')) {
            // Disable button
            button.prop('disabled', true);
            button.html('<i class="fas fa-spinner fa-spin me-1"></i>Memproses...');
            
            // Send AJAX request
            $.ajax({
                url: `/transaksi/retur-pembelian/${returId}/send`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Success:', response);
                    
                    // Show success message if available
                    if (response.message) {
                        alert(response.message);
                    }
                    
                    // Reload page to show updated status
                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr.responseText);
                    
                    let errorMessage = 'Terjadi kesalahan';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    alert(errorMessage);
                    
                    // Re-enable button
                    button.prop('disabled', false);
                    button.html('<i class="fas fa-shipping-fast me-1"></i>Kirim Barang');
                }
            });
        }
    });
});
</script>
@endpush