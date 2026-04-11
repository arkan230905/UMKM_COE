{{-- ========================================= --}}
{{-- IMPLEMENTASI TOMBOL AKSI DINAMIS RETUR PEMBELIAN --}}
{{-- ========================================= --}}

{{-- BAGIAN KOLOM AKSI DI TABEL --}}
<td class="text-center">
    <div class="action-buttons">
        {{-- Tombol Detail --}}
        <a href="{{ route('transaksi.retur-pembelian.show', $retur->id) }}" 
           class="btn btn-sm btn-outline-info" 
           title="Lihat Detail">
            <i class="fas fa-eye"></i>
        </a>

        {{-- TOMBOL AKSI DINAMIS BERDASARKAN JENIS RETUR DAN STATUS --}}
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

        {{-- Tombol Hapus (hanya jika belum selesai) --}}
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
</td>

{{-- ========================================= --}}
{{-- PENJELASAN LOGIKA WORKFLOW --}}
{{-- ========================================= --}}

{{-- 
TUKAR BARANG (jenis_retur = 'tukar_barang'):
1. pending → [ACC Vendor] → disetujui
2. disetujui → [Kirim Barang] → dikirim  
3. dikirim → [Diproses Vendor] → diproses
4. diproses → [Barang Diterima] → selesai
5. selesai → tidak ada tombol

REFUND (jenis_retur = 'refund'):
1. pending → [ACC Vendor] → disetujui
2. disetujui → [Kirim Barang] → dikirim
3. dikirim → [Vendor Terima] → diterima
4. diterima → [Terima Uang] → refund_selesai
5. refund_selesai → tidak ada tombol

FITUR YANG DIIMPLEMENTASIKAN:
✅ Hanya 1 tombol aksi per baris (next step)
✅ Tombol berubah otomatis setelah status diupdate
✅ Tidak ada tombol jika status sudah final
✅ Form POST dengan CSRF protection
✅ Konfirmasi JavaScript sebelum submit
✅ Icon yang sesuai untuk setiap aksi
✅ Warna tombol yang berbeda untuk setiap tahap
✅ Tombol hapus hanya muncul jika belum selesai

ROUTE YANG DIGUNAKAN:
- POST: route('transaksi.retur-pembelian.update-status', $retur->id)
- DELETE: route('transaksi.retur-pembelian.destroy', $retur->id)

CONTROLLER METHOD:
- ReturController@updateStatus (sudah ada)
- ReturController@destroyPembelian (sudah ada)
--}}

{{-- ========================================= --}}
{{-- CONTOH IMPLEMENTASI ALTERNATIF (LEBIH RINGKAS) --}}
{{-- ========================================= --}}

{{-- Jika ingin menggunakan method dari model PurchaseReturn --}}
<td class="text-center">
    <div class="action-buttons">
        {{-- Detail Button --}}
        <a href="{{ route('transaksi.retur-pembelian.show', $retur->id) }}" 
           class="btn btn-sm btn-outline-info">
            <i class="fas fa-eye"></i>
        </a>

        {{-- Dynamic Action Button (menggunakan method dari model) --}}
        @if($retur->action_button)
            <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Yakin ingin melanjutkan ke tahap berikutnya?')">
                @csrf
                <button type="submit" 
                        class="btn btn-sm {{ $retur->action_button['class'] }}">
                    {{ $retur->action_button['text'] }}
                </button>
            </form>
        @endif

        {{-- Delete Button --}}
        @if(!$retur->is_completed)
            <form action="{{ route('transaksi.retur-pembelian.destroy', $retur->id) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Yakin ingin menghapus retur ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endif
    </div>
</td>