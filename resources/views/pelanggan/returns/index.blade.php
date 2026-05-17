@extends('layouts.pelanggan')

@section('content')
<div style="background: white; padding: 1.5rem 1rem;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: #2d3748; margin: 0;">🔄 Retur Saya</h2>
            <p style="color: #999; margin: 0.3rem 0 0 0; font-size: 0.7rem;">Kelola semua pengajuan retur Anda</p>
        </div>

        @if($returs->isEmpty())
        <!-- Empty State -->
        <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0; text-align: center; padding: 2rem 1rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.8rem;">📭</div>
            <h4 style="color: #999; font-size: 0.8rem; margin-bottom: 0.5rem;">Belum Ada Retur</h4>
            <p style="color: #bbb; font-size: 0.65rem; margin-bottom: 1rem;">Anda belum memiliki pengajuan retur. Jika ada produk yang bermasalah, silakan ajukan retur.</p>
            <a href="{{ route('pelanggan.returns.create') }}" style="display: inline-block; padding: 0.5rem 1.2rem; background: #8b6f47; color: white; border: none; border-radius: 50px; font-weight: 700; text-decoration: none; font-size: 0.7rem;">🔄 Ajukan Retur</a>
        </div>
        @else
        <!-- Retur List -->
        <div style="display: flex; flex-direction: column; gap: 0.8rem;">
            @foreach($returs as $retur)
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0;">
                <div style="padding: 1rem;">
                    <!-- Retur Header Row -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; align-items: center; margin-bottom: 0.8rem;">
                        <div>
                            <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Kode Retur</div>
                            <h6 style="font-size: 0.75rem; font-weight: 800; color: #2d3748; margin: 0;">{{ $retur->memo }}</h6>
                            <small style="font-size: 0.55rem; color: #999;">{{ $retur->created_at->format('d M Y') }}</small>
                        </div>
                        <div>
                            <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Referensi Pesanan</div>
                            <h6 style="font-size: 0.75rem; font-weight: 800; color: #8b6f47; margin: 0;">#{{ optional(App\Models\Order::find($retur->ref_id))->nomor_order ?? '-' }}</h6>
                        </div>
                        <div>
                            <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Kompensasi</div>
                            <div style="font-size: 0.65rem; color: #2d3748; font-weight: 600;">{{ ucfirst($retur->kompensasi) }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Status</div>
                            <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.6rem; font-weight: 700; background: {{ $retur->status === 'approved' ? '#d4edda' : ($retur->status === 'rejected' ? '#f8d7da' : '#fff3cd') }}; color: {{ $retur->status === 'approved' ? '#155724' : ($retur->status === 'rejected' ? '#721c24' : '#856404') }};">
                                {{ ucfirst($retur->status ?? 'draft') }}
                            </span>
                        </div>
                        <div>
                            <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Total</div>
                            <div style="font-size: 0.75rem; color: #2d3748; font-weight: 800;">Rp {{ number_format($retur->jumlah, 0, ',', '.') }}</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; padding-top: 0.8rem; border-top: 1px solid #f0f0f0;">
                        <button onclick="openDetailModal({{ $retur->id }})" style="padding: 0.4rem 0.8rem; background: #8b6f47; color: white; border: none; border-radius: 6px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.6rem; cursor: pointer;">
                            👁️ Detail
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($returs->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 1.5rem;">
            {{ $returs->links() }}
        </div>
        @endif
        @endif
    </div>
</div>


<!-- Detail Modals -->
@foreach($returs as $retur)
<div id="detailModal{{ $retur->id }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 1.5rem; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h5 style="font-size: 0.9rem; font-weight: 800; color: #2d3748; margin: 0;">Detail Retur: {{ $retur->memo }}</h5>
            <button type="button" onclick="closeDetailModal({{ $retur->id }})" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">×</button>
        </div>

        <!-- Retur Info -->
        <div style="margin-bottom: 1rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; margin-bottom: 0.8rem;">
                <div>
                    <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Tanggal</div>
                    <div style="font-size: 0.7rem; color: #2d3748; font-weight: 600;">{{ $retur->created_at->format('d M Y') }}</div>
                </div>
                <div>
                    <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Referensi</div>
                    <div style="font-size: 0.7rem; color: #2d3748; font-weight: 600;">#{{ optional(App\Models\Order::find($retur->ref_id))->nomor_order ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Kompensasi</div>
                    <div style="font-size: 0.7rem; color: #2d3748; font-weight: 600;">{{ ucfirst($retur->kompensasi) }}</div>
                </div>
                <div>
                    <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Status</div>
                    <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.6rem; font-weight: 700; background: {{ $retur->status === 'approved' ? '#d4edda' : ($retur->status === 'rejected' ? '#f8d7da' : '#fff3cd') }}; color: {{ $retur->status === 'approved' ? '#155724' : ($retur->status === 'rejected' ? '#721c24' : '#856404') }};">
                        {{ ucfirst($retur->status ?? 'draft') }}
                    </span>
                </div>
            </div>

            @if($retur->alasan)
            <div style="margin-top: 0.8rem;">
                <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Alasan</div>
                <div style="font-size: 0.7rem; color: #2d3748;">{{ $retur->alasan }}</div>
            </div>
            @endif
        </div>

        <!-- Items Table -->
        <div style="margin-bottom: 1rem;">
            <h6 style="font-size: 0.7rem; font-weight: 800; color: #2d3748; margin: 0 0 0.5rem 0;">Item yang Diretur</h6>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.65rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #f0f0f0;">
                            <th style="text-align: left; padding: 0.4rem; font-weight: 700; color: #2d3748;">Produk</th>
                            <th style="text-align: right; padding: 0.4rem; font-weight: 700; color: #2d3748;">Qty</th>
                            <th style="text-align: right; padding: 0.4rem; font-weight: 700; color: #2d3748;">Harga</th>
                            <th style="text-align: right; padding: 0.4rem; font-weight: 700; color: #2d3748;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($retur->details as $detail)
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 0.4rem; color: #2d3748;">{{ optional($detail->produk)->nama_produk ?? '-' }}</td>
                            <td style="padding: 0.4rem; text-align: right; color: #2d3748;">{{ $detail->qty }}</td>
                            <td style="padding: 0.4rem; text-align: right; color: #8b6f47; font-weight: 600;">Rp {{ number_format($detail->harga_satuan_asal, 0, ',', '.') }}</td>
                            <td style="padding: 0.4rem; text-align: right; color: #2d3748; font-weight: 600;">Rp {{ number_format($detail->qty * $detail->harga_satuan_asal, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td colspan="4" style="padding: 0.4rem; text-align: center; color: #999;">Tidak ada item</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid #f0f0f0;">
                            <th colspan="3" style="text-align: right; padding: 0.4rem; font-weight: 700; color: #2d3748;">Total:</th>
                            <th style="text-align: right; padding: 0.4rem; font-weight: 800; color: #8b6f47;">Rp {{ number_format($retur->jumlah, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button type="button" onclick="closeDetailModal({{ $retur->id }})" style="padding: 0.5rem 1rem; background: #e0e0e0; color: #2d3748; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.7rem;">Tutup</button>
        </div>
    </div>
</div>
@endforeach

<script>
function openDetailModal(returId) {
    document.getElementById('detailModal' + returId).style.display = 'flex';
}

function closeDetailModal(returId) {
    document.getElementById('detailModal' + returId).style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.id && event.target.id.startsWith('detailModal')) {
        event.target.style.display = 'none';
    }
});
</script>

@endsection
