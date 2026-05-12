{{-- CONTOH IMPLEMENTASI TOMBOL AKSI DINAMIS RETUR PEMBELIAN --}}
{{-- Sesuai dengan permintaan: hanya 1 tombol next step berdasarkan status --}}

<div class="action-buttons">
    {{-- Detail Button --}}
    <a href="{{ route('transaksi.retur-pembelian.show', $retur->id) }}" 
       class="btn btn-sm btn-outline-info" 
       title="Lihat Detail">
        <i class="fas fa-eye"></i>
    </a>

    {{-- TOMBOL AKSI DINAMIS - HANYA 1 TOMBOL NEXT STEP --}}
    @if($retur->jenis_retur == 'tukar_barang')
        {{-- WORKFLOW TUKAR BARANG --}}
        @if($retur->status == 'pending')
            <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Yakin ingin ACC retur ini?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-check me-1"></i>ACC Vendor
                </button>
            </form>
        @elseif($retur->status == 'disetujui')
            <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Yakin ingin kirim barang?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-warning">
                    <i class="fas fa-shipping-fast me-1"></i>Kirim Barang
                </button>
            </form>
        @elseif($retur->status == 'dikirim')
            <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Yakin barang sudah diproses vendor?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-secondary">
                    <i class="fas fa-cogs me-1"></i>Diproses Vendor
                </button>
            </form>
        @elseif($retur->status == 'diproses')
            <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Yakin barang sudah diterima?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-check-circle me-1"></i>Barang Diterima
                </button>
            </form>
        @endif
        {{-- Status 'selesai' = tidak ada tombol --}}
    @endif

    @if($retur->jenis_retur == 'refund')
        {{-- WORKFLOW REFUND --}}
        @if($retur->status == 'pending')
            <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Yakin ingin ACC retur ini?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-check me-1"></i>ACC Vendor
                </button>
            </form>
        @elseif($retur->status == 'disetujui')
            <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Yakin ingin kirim barang?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-warning">
                    <i class="fas fa-shipping-fast me-1"></i>Kirim Barang
                </button>
            </form>
        @elseif($retur->status == 'dikirim')
            <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Yakin vendor sudah terima barang?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-info">
                    <i class="fas fa-handshake me-1"></i>Vendor Terima
                </button>
            </form>
        @elseif($retur->status == 'diterima')
            <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Yakin sudah terima uang refund?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-money-bill-wave me-1"></i>Terima Uang
                </button>
            </form>
        @endif
        {{-- Status 'refund_selesai' = tidak ada tombol --}}
    @endif

    {{-- Delete Button (hanya jika belum selesai) --}}
    @if(!in_array($retur->status, ['selesai', 'refund_selesai']))
        <form action="{{ route('transaksi.retur-pembelian.destroy', $retur->id) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Yakin ingin menghapus retur ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="btn btn-sm btn-outline-danger" 
                    title="Hapus">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endif
</div>

{{-- 
PENJELASAN LOGIKA:

TUKAR BARANG:
- pending → ACC Vendor → disetujui
- disetujui → Kirim Barang → dikirim  
- dikirim → Diproses Vendor → diproses
- diproses → Barang Diterima → selesai
- selesai → tidak ada tombol

REFUND:
- pending → ACC Vendor → disetujui
- disetujui → Kirim Barang → dikirim
- dikirim → Vendor Terima → diterima
- diterima → Terima Uang → refund_selesai
- refund_selesai → tidak ada tombol

FITUR:
✅ Hanya 1 tombol aksi (next step)
✅ Tombol berubah otomatis setelah diklik
✅ Tidak ada tombol jika sudah selesai
✅ Form POST dengan CSRF protection
✅ Konfirmasi sebelum submit
✅ Icon yang sesuai untuk setiap aksi
--}}