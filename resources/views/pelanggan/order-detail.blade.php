@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">Detail Pesanan #{{ $order->nomor_order }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('pelanggan.returns.create', ['order_id' => $order->id]) }}" class="btn btn-outline-warning">
                <i class="bi bi-arrow-counterclockwise"></i> Ajukan Retur
            </a>
            <a href="{{ route('pelanggan.orders') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-dark"><i class="bi bi-info-circle"></i> Informasi Pesanan</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="200"><strong>Nomor Pesanan:</strong></td>
                            <td>{{ $order->nomor_order }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Pesanan:</strong></td>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status Pesanan:</strong></td>
                            <td>{!! $order->status_badge !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Status Pembayaran:</strong></td>
                            <td>
                                <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Metode Pembayaran:</strong></td>
                            <td>{{ $order->payment_method_label }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Pembayaran:</strong></td>
                            <td class="fw-bold fs-5 text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        @if($order->paid_at)
                        <tr>
                            <td><strong>Dibayar Pada:</strong></td>
                            <td>{{ $order->paid_at->format('d M Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>

                    @if($order->payment_status === 'pending' && $order->snap_token)
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Pesanan Anda menunggu pembayaran
                    </div>
                    <button id="pay-button" class="btn btn-success w-100 py-3">
                        <i class="bi bi-credit-card"></i> Bayar Sekarang
                    </button>
                    @endif

                    @if($order->payment_status === 'paid')
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Pembayaran berhasil! Pesanan Anda sedang diproses.
                    </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-dark"><i class="bi bi-box-seam"></i> Item Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->produk->nama_produk }}</td>
                                    <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-dark"><i class="bi bi-geo-alt"></i> Data Pengiriman</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong class="text-dark">Nama Penerima:</strong><br>
                        <span class="text-dark">{{ $order->nama_penerima }}</span>
                    </p>
                    <p class="mb-2">
                        <strong class="text-dark">Alamat:</strong><br>
                        <span class="text-dark">{{ $order->alamat_pengiriman }}</span>
                    </p>
                    <p class="mb-2">
                        <strong class="text-dark">Telepon:</strong><br>
                        <span class="text-dark">{{ $order->telepon_penerima }}</span>
                    </p>
                    @if($order->catatan)
                    <p class="mb-0">
                        <strong class="text-dark">Catatan:</strong><br>
                        <span class="text-dark">{{ $order->catatan }}</span>
                    </p>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-dark"><i class="bi bi-clock-history"></i> Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <i class="bi bi-check-circle text-success"></i>
                            <span class="text-dark">Pesanan Dibuat</span>
                            <small class="text-muted d-block">{{ $order->created_at->format('d M Y H:i') }}</small>
                        </div>
                        @if($order->paid_at)
                        <div class="timeline-item">
                            <i class="bi bi-check-circle text-success"></i>
                            <span class="text-dark">Pembayaran Berhasil</span>
                            <small class="text-muted d-block">{{ $order->paid_at->format('d M Y H:i') }}</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($order->payment_status === 'pending' && $order->snap_token)
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
document.getElementById('pay-button').addEventListener('click', function () {
    snap.pay('{{ $order->snap_token }}', {
        onSuccess: function(result){
            alert('Pembayaran berhasil!');
            window.location.reload();
        },
        onPending: function(result){
            alert('Menunggu pembayaran Anda');
            window.location.reload();
        },
        onError: function(result){
            alert('Pembayaran gagal! Silakan coba lagi.');
        },
        onClose: function(){
            alert('Anda menutup popup pembayaran');
        }
    });
});
</script>
@endif

@section('review-section')
@if($order->status === 'completed' || $order->payment_status === 'paid')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0 text-dark"><i class="bi bi-star"></i> Review Produk</h5>
    </div>
    <div class="card-body">
        @php
            $existingReview = App\Models\Review::where('order_id', $order->id)
                ->where('user_id', auth()->id())
                ->first();
        @endphp
        
        @if($existingReview)
            <div class="alert alert-info">
                <h6>Review Anda:</h6>
                <div class="mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star-fill text-{{ $i <= $existingReview->rating ? 'warning' : 'secondary' }}"></i>
                    @endfor
                    <span class="ms-2">{{ $existingReview->rating }}/5</span>
                </div>
                @if($existingReview->comment)
                    <p class="mb-2">{{ $existingReview->comment }}</p>
                @endif
                
                <!-- Edit Review Button -->
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editReviewModal">
                    <i class="bi bi-pencil"></i> Edit Review
                </button>
            </div>
        @else
            <div class="alert alert-light">
                <p class="mb-3">Belum memberikan review? Berikan review untuk produk ini:</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    <i class="bi bi-star"></i> Berikan Review
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Review Modal -->
@if(!$existingReview)
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Berikan Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pelanggan.reviews.store') }}" method="POST">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="rating-input">
                            @for($i = 1; $i <= 5; $i++)
                                <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" class="d-none" required>
                                <label for="star{{ $i }}" class="bi bi-star rating-star" data-rating="{{ $i }}"></label>
                            @endfor
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="comment" class="form-label">Komentar (opsional)</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Edit Review Modal -->
@if($existingReview)
<div class="modal fade" id="editReviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pelanggan.reviews.update', $existingReview->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="rating-input">
                            @for($i = 1; $i <= 5; $i++)
                                <input type="radio" id="editstar{{ $i }}" name="rating" value="{{ $i }}" class="d-none" required {{ $i == $existingReview->rating ? 'checked' : '' }}>
                                <label for="editstar{{ $i }}" class="bi bi-star rating-star" data-rating="{{ $i }}"></label>
                            @endfor
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editcomment" class="form-label">Komentar (opsional)</label>
                        <textarea class="form-control" id="editcomment" name="comment" rows="3" maxlength="1000">{{ $existingReview->comment }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Rating stars functionality
    const ratingStars = document.querySelectorAll('.rating-star');
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            
            // Uncheck all inputs first
            ratingInputs.forEach(input => input.checked = false);
            
            // Check the selected input
            const selectedInput = document.querySelector(`input[name="rating"][value="${rating}"]`);
            if (selectedInput) {
                selectedInput.checked = true;
            }
            
            // Update star colors
            updateStarColors(rating);
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            updateStarColors(rating);
        });
    });
    
    // Reset colors when leaving rating area
    document.querySelector('.rating-input')?.addEventListener('mouseleave', function() {
        const checkedInput = document.querySelector('input[name="rating"]:checked');
        const rating = checkedInput ? parseInt(checkedInput.value) : 0;
        updateStarColors(rating);
    });
    
    function updateStarColors(selectedRating) {
        ratingStars.forEach((star, index) => {
            const starRating = parseInt(star.dataset.rating);
            if (starRating <= selectedRating) {
                star.classList.remove('bi-star');
                star.classList.add('bi-star-fill', 'text-warning');
            } else {
                star.classList.remove('bi-star-fill', 'text-warning');
                star.classList.add('bi-star');
            }
        });
    }
    
    // Initialize with checked rating
    const checkedInput = document.querySelector('input[name="rating"]:checked');
    if (checkedInput) {
        updateStarColors(parseInt(checkedInput.value));
    }
});
    
    // Show success notification for review
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('review_success')) {
        const rating = urlParams.get('rating') || '';
        showNotification(`Berhasil me-rating produk dengan ${rating} bintang! Terima kasih atas review Anda.`, 'success');
    }
    
    function showNotification(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});
</script>
@endsection

<style>
.timeline-item {
    padding-left: 30px;
    position: relative;
    padding-bottom: 15px;
}
.timeline-item i {
    position: absolute;
    left: 0;
    top: 0;
}

/* Rating Stars */
.rating-input {
    display: flex;
    gap: 5px;
}

.rating-star {
    font-size: 24px;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.rating-star:hover {
    color: #ffc107;
}

input[type="radio"]:checked + .rating-star {
    color: #ffc107;
}

.rating-display {
    display: flex;
    gap: 2px;
}

.rating-display .bi {
    font-size: 16px;
}
</style>
@endsection
