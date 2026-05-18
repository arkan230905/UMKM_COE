@extends('layouts.pelanggan')

@section('content')
<div style="background: white; padding: 1.5rem 1rem;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h2 style="font-size: 1.3rem; font-weight: 800; color: #2d3748; margin: 0;">📦 Pesanan Saya</h2>
                <p style="color: #999; margin: 0.3rem 0 0 0; font-size: 0.7rem;">Kelola dan pantau semua pesanan Anda</p>
            </div>
            <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/dashboard") }}" style="padding: 0.5rem 1.2rem; background: #8b6f47; color: white; border: none; border-radius: 50px; font-weight: 700; text-decoration: none; font-size: 0.7rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                ← Kembali Belanja
            </a>
        </div>

        @if($orders->isEmpty())
        <!-- Empty State -->
        <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0; text-align: center; padding: 2rem 1rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.8rem;">📭</div>
            <h4 style="color: #999; font-size: 0.8rem; margin-bottom: 0.5rem;">Belum Ada Pesanan</h4>
            <p style="color: #bbb; font-size: 0.65rem; margin-bottom: 1rem;">Anda belum memiliki pesanan. Mulai belanja sekarang!</p>
            <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/dashboard") }}" style="display: inline-block; padding: 0.5rem 1.2rem; background: #8b6f47; color: white; border: none; border-radius: 50px; font-weight: 700; text-decoration: none; font-size: 0.7rem;">🛍️ Mulai Belanja</a>
        </div>
        @else
        <!-- Orders List -->
        <div style="display: flex; flex-direction: column; gap: 0.8rem;">
            @foreach($orders as $order)
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0;">
                <div style="padding: 1rem;">
                    <!-- Order Header Row -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; align-items: center; margin-bottom: 0.8rem;">
                        <div>
                            <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Nomor Pesanan</div>
                            <h6 style="font-size: 0.75rem; font-weight: 800; color: #2d3748; margin: 0;">{{ $order->nomor_order }}</h6>
                            <small style="font-size: 0.55rem; color: #999;">{{ $order->created_at->format('d M Y') }}</small>
                        </div>
                        <div>
                            <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Total</div>
                            <h6 style="font-size: 0.75rem; font-weight: 800; color: #8b6f47; margin: 0;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h6>
                        </div>
                        <div>
                            <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Status Pesanan</div>
                            <div style="font-size: 0.65rem;">{!! $order->status_badge !!}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Pembayaran</div>
                            <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.6rem; font-weight: 700; background: {{ $order->payment_status === 'paid' ? '#d4edda' : ($order->payment_status === 'failed' ? '#f8d7da' : '#fff3cd') }}; color: {{ $order->payment_status === 'paid' ? '#155724' : ($order->payment_status === 'failed' ? '#721c24' : '#856404') }};">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                        <div>
                            <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Metode</div>
                            <div style="font-size: 0.65rem; color: #2d3748; font-weight: 600;">{{ $order->payment_method_label }}</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; padding-top: 0.8rem; border-top: 1px solid #f0f0f0;">
                        <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/orders/" . $order->id) }}" style="padding: 0.4rem 0.8rem; background: #8b6f47; color: white; border: none; border-radius: 6px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.6rem; cursor: pointer;">
                            👁️ Detail
                        </a>
                        @if($order->payment_status === 'pending')
                        <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/orders/" . $order->id) }}" style="padding: 0.4rem 0.8rem; background: #10b981; color: white; border: none; border-radius: 6px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.6rem; cursor: pointer;">
                            💳 Bayar
                        </a>
                        @endif
                        @if($order->status === 'completed' || $order->payment_status === 'paid')
                        <button onclick="openReviewModal({{ $order->id }})" style="padding: 0.4rem 0.8rem; background: #f59e0b; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.6rem;">
                            ⭐ Review
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($orders->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 1.5rem;">
            {{ $orders->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

<!-- Review Modals -->
@foreach($orders as $order)
@if($order->status === 'completed' || $order->payment_status === 'paid')
<div id="reviewModal{{ $order->id }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 1.5rem; max-width: 400px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/reviews") }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            
            <!-- Modal Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h5 style="font-size: 0.9rem; font-weight: 800; color: #2d3748; margin: 0;">Beri Review</h5>
                <button type="button" onclick="closeReviewModal({{ $order->id }})" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">×</button>
            </div>

            <!-- Rating Section -->
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #2d3748; margin-bottom: 0.5rem;">Rating</label>
                <div style="display: flex; gap: 0.4rem;">
                    @for($i = 1; $i <= 5; $i++)
                    <input type="radio" id="rating{{ $i }}_{{ $order->id }}" name="rating" value="{{ $i }}" required style="display: none;">
                    <label for="rating{{ $i }}_{{ $order->id }}" style="padding: 0.4rem 0.8rem; background: #f0f0f0; color: #999; border: 2px solid transparent; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 0.7rem; transition: all 0.2s; display: inline-block;">⭐ {{ $i }}</label>
                    @endfor
                </div>
            </div>

            <!-- Comment Section -->
            <div style="margin-bottom: 1rem;">
                <label for="review_{{ $order->id }}" style="display: block; font-size: 0.7rem; font-weight: 700; color: #2d3748; margin-bottom: 0.5rem;">Komentar (opsional)</label>
                <textarea id="review_{{ $order->id }}" name="comment" rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.7rem; font-family: inherit; resize: vertical;"></textarea>
            </div>

            <!-- Modal Footer -->
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" onclick="closeReviewModal({{ $order->id }})" style="padding: 0.5rem 1rem; background: #e0e0e0; color: #2d3748; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.7rem;">Batal</button>
                <button type="submit" style="padding: 0.5rem 1rem; background: #8b6f47; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.7rem;">Kirim Review</button>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach

<script>
function openReviewModal(orderId) {
    document.getElementById('reviewModal' + orderId).style.display = 'flex';
}

function closeReviewModal(orderId) {
    document.getElementById('reviewModal' + orderId).style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.id && event.target.id.startsWith('reviewModal')) {
        event.target.style.display = 'none';
    }
});

// Add visual feedback for rating selection
document.addEventListener('change', function(event) {
    if (event.target.name === 'rating') {
        const orderId = event.target.id.split('_')[1];
        const labels = document.querySelectorAll(`label[for^="rating"][for$="_${orderId}"]`);
        const selectedValue = parseInt(event.target.value);
        
        labels.forEach((label, index) => {
            if (index < selectedValue) {
                label.style.background = '#fbbf24';
                label.style.color = '#78350f';
                label.style.borderColor = '#f59e0b';
            } else {
                label.style.background = '#f0f0f0';
                label.style.color = '#999';
                label.style.borderColor = 'transparent';
            }
        });
    }
});
</script>

@endsection
