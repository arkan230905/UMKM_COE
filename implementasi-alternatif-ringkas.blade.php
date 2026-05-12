{{-- ========================================= --}}
{{-- IMPLEMENTASI ALTERNATIF YANG LEBIH RINGKAS --}}
{{-- Menggunakan method dari model PurchaseReturn --}}
{{-- ========================================= --}}

<td class="text-center">
    <div class="action-buttons">
        {{-- Tombol Detail --}}
        <a href="{{ route('transaksi.retur-pembelian.show', $retur->id) }}" 
           class="btn btn-sm btn-outline-info" 
           title="Lihat Detail">
            <i class="fas fa-eye"></i>
        </a>

        {{-- TOMBOL AKSI DINAMIS (MENGGUNAKAN METHOD MODEL) --}}
        @if($retur->action_button)
            <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Yakin ingin melanjutkan ke tahap berikutnya?')">
                @csrf
                <button type="submit" 
                        class="btn btn-sm {{ $retur->action_button['class'] }}" 
                        title="{{ $retur->action_button['text'] }}">
                    {{-- Icon berdasarkan status --}}
                    @if($retur->status == 'menunggu_acc')
                        <i class="fas fa-check me-1"></i>
                    @elseif($retur->status == 'disetujui')
                        <i class="fas fa-shipping-fast me-1"></i>
                    @elseif($retur->status == 'dikirim' && $retur->jenis_retur == 'tukar_barang')
                        <i class="fas fa-cogs me-1"></i>
                    @elseif($retur->status == 'dikirim' && $retur->jenis_retur == 'refund')
                        <i class="fas fa-handshake me-1"></i>
                    @elseif($retur->status == 'diproses')
                        <i class="fas fa-check-circle me-1"></i>
                    @elseif($retur->status == 'diterima')
                        <i class="fas fa-money-bill-wave me-1"></i>
                    @endif
                    {{ $retur->action_button['text'] }}
                </button>
            </form>
        @endif

        {{-- Tombol Hapus (hanya jika belum selesai) --}}
        @if(!$retur->is_completed)
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
</td>

{{-- ========================================= --}}
{{-- KEUNTUNGAN IMPLEMENTASI INI: --}}
{{-- ========================================= --}}

{{-- 
✅ LEBIH RINGKAS - Menggunakan method dari model
✅ MAINTAINABLE - Logic ada di model, bukan di view
✅ DRY PRINCIPLE - Tidak ada duplikasi kode
✅ KONSISTEN - Menggunakan method yang sudah ada

METHOD YANG DIGUNAKAN:
- $retur->action_button (dari getActionButtonAttribute)
- $retur->is_completed (dari getIsCompletedAttribute)

LOGIKA TETAP SAMA:
- Hanya 1 tombol next step
- Tombol berubah otomatis
- Tidak ada tombol jika sudah selesai
--}}