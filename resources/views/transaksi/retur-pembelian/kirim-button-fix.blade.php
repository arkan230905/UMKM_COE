{{-- Tombol Kirim Barang yang Benar --}}
@if($retur->status === 'disetujui')
    <form method="POST" action="{{ route('transaksi.retur-pembelian.send', $retur->id) }}" class="d-inline">
        @csrf
        <button type="submit" 
                class="btn btn-primary btn-sm"
                onclick="return confirm('Yakin ingin mengubah status ke Dikirim?')">
            <i class="fas fa-shipping-fast me-1"></i>
            Kirim Barang
        </button>
    </form>
@endif