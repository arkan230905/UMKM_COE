@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">Detail Pesanan #{{ $order->nomor_order }}</h2>
        <div class="d-flex gap-2">
            @if($order->payment_status === 'pending' && in_array($order->status, ['pending', 'processing']))
            <form action="{{ route('pelanggan.orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.');">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-x-circle"></i> Batalkan Pesanan
                </button>
            </form>
            @endif
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

    @if(session('midtrans_status'))
    @php $midtransStatus = session('midtrans_status'); @endphp
    <div class="alert alert-{{ $midtransStatus['type'] }} alert-dismissible fade show">
        {!! $midtransStatus['message'] !!}
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
                                    <th class="text-center">Qty Dipesan</th>
                                    <th class="text-center">Qty Diretur</th>
                                    <th class="text-center">Qty Sisa</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->produk->nama_produk }}</td>
                                    <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $item->qty }}</td>
                                    <td class="text-center">
                                        @if(($item->qty_returned ?? 0) > 0)
                                            <span class="badge bg-warning text-dark">{{ rtrim(rtrim(number_format($item->qty_returned, 2, ',', '.'), '0'), ',') }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php $remaining = $item->qty_remaining ?? $item->qty; @endphp
                                        <span class="{{ $remaining <= 0 ? 'text-danger fw-semibold' : 'text-dark' }}">
                                            {{ rtrim(rtrim(number_format($remaining, 2, ',', '.'), '0'), ',') }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th colspan="5" class="text-end">Total:</th>
                                    <th>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if(isset($orderReturns) && $orderReturns->isNotEmpty())
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark"><i class="bi bi-arrow-counterclockwise"></i> Riwayat Retur Pesanan Ini</h5>
                    <span class="badge bg-secondary">{{ $orderReturns->count() }} Retur</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Retur</th>
                                    <th>Tanggal</th>
                                    <th>Kompensasi</th>
                                    <th>Status</th>
                                    <th>Nilai Retur</th>
                                    <th>Detail Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderReturns as $retur)
                                <tr>
                                    <td><strong>{{ $retur->nomor_retur }}</strong></td>
                                    <td>{{ optional($retur->tanggal_retur)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $retur->kompensasi === 'uang' ? 'success' : 'info' }} text-uppercase">
                                            {{ ucfirst($retur->kompensasi ?? $retur->tipe_kompensasi) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $retur->status === 'selesai' ? 'success' : ($retur->status === 'diproses' ? 'warning text-dark' : 'secondary') }} text-capitalize">
                                            {{ $retur->status ?? 'draft' }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($retur->calculateTotalNilai(), 0, ',', '.') }}</td>
                                    <td>
                                        <ul class="mb-0 ps-3">
                                            @foreach($retur->details as $detail)
                                                <li>
                                                    {{ $detail->item_nama ?? $detail->produk->nama_produk ?? '-' }}
                                                    <small class="text-muted">x {{ rtrim(rtrim(number_format($detail->qty ?? $detail->qty_retur ?? 0, 2, ',', '.'), '0'), ',') }}</small>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
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
const orderId = @json($order->nomor_order);
const orderShowUrl = @json(route('pelanggan.orders.show', $order->id));
const clientCompleteUrl = @json(route('midtrans.client-complete'));
const csrfToken = @json(csrf_token());

function redirectWithStatus(status) {
    const separator = orderShowUrl.includes('?') ? '&' : '?';
    window.location.href = `${orderShowUrl}${separator}redirect_status=${status}`;
}

function requestMidtransStatus(resultPayload = null, fallbackStatus = null, forceCheck = false) {
    return fetch(clientCompleteUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            order_id: orderId,
            result: resultPayload,
            fallback_status: fallbackStatus,
            force_check: forceCheck
        })
    }).then(response => {
        if (!response.ok) {
            throw new Error('Failed to sync payment status');
        }
        return response.json();
    }).then(data => data.redirect_status || 'pending');
}

function handleStatus(status, forceRedirect = false) {
    if (status === 'success' || status === 'error') {
        redirectWithStatus(status);
        return true;
    }

    if (forceRedirect) {
        redirectWithStatus(status);
    }

    return status === 'success';
}

function pollUntilSettled(attempt = 0, maxAttempts = 12) {
    if (attempt >= maxAttempts) {
        return;
    }

    requestMidtransStatus(null, 'pending', attempt > 0 && attempt % 3 === 0)
        .then(status => {
            const settled = handleStatus(status, false);
            if (!settled) {
                setTimeout(() => pollUntilSettled(attempt + 1, maxAttempts), 5000);
            }
        })
        .catch(() => {
            setTimeout(() => pollUntilSettled(attempt + 1, maxAttempts), 7000);
        });
}

const payButton = document.getElementById('pay-button');
if (payButton) {
    payButton.addEventListener('click', function () {
        snap.pay('{{ $order->snap_token }}', {
            onSuccess: function(result){
                requestMidtransStatus(result, 'success').then(status => handleStatus(status, true)).catch(() => redirectWithStatus('pending'));
            },
            onPending: function(result){
                requestMidtransStatus(result, 'pending').then(status => handleStatus(status, true)).catch(() => redirectWithStatus('pending'));
            },
            onError: function(result){
                requestMidtransStatus(result, 'error', true).then(status => handleStatus(status, true)).catch(() => redirectWithStatus('error'));
            },
            onClose: function(){
                window.location.href = orderShowUrl;
            }
        });
    });

    // Auto-poll while order is pending to detect payments made outside current session
    pollUntilSettled();
}
</script>
@endif

@section('review-section')
@if($order->status === 'completed' || $order->payment_status === 'paid')
<div class="card border-0 shadow-sm mb-4 review-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <span class="review-pill">Berikan Penilaian</span>
            <h5 class="mb-0 text-dark mt-2"><i class="bi bi-star"></i> Review Produk</h5>
        </div>
        <div class="text-end">
            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis review-badge">
                <i class="bi bi-lightning-charge"></i> Bantu UMKM berkembang
            </span>
        </div>
    </div>
    <div class="card-body review-body">
        @php
            $existingReview = App\Models\Review::where('order_id', $order->id)
                ->where('user_id', auth()->id())
                ->first();
        @endphp
        
        @if($existingReview)
            <div class="review-highlight">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="review-score">{{ number_format($existingReview->rating, 1) }}</div>
                    <div>
                        <h6 class="mb-1 text-dark">Terima kasih atas penilaianmu!</h6>
                        <div class="review-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star-fill text-{{ $i <= $existingReview->rating ? 'warning' : 'secondary' }}"></i>
                            @endfor
                        </div>
                    </div>
                </div>
                @if($existingReview->comment)
                    <p class="mb-3 text-dark">“{{ $existingReview->comment }}”</p>
                @else
                    <p class="mb-3 text-muted">Belum ada komentar tertulis.</p>
                @endif
                
                <button class="btn btn-warning px-3" data-bs-toggle="modal" data-bs-target="#editReviewModal">
                    <i class="bi bi-pencil"></i> Update Review
                </button>
            </div>
        @else
            <div class="review-highlight review-highlight-empty">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <h6 class="text-dark mb-1">Bantu pembeli lain dengan pengalamanmu</h6>
                        <p class="mb-0 text-muted">Bagikan kualitas produk dan layanan kami untuk mendukung pelaku UMKM.</p>
                    </div>
                    <button class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#reviewModal">
                        <i class="bi bi-star"></i> Berikan Review Sekarang
                    </button>
                </div>
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

.review-card {
    border: none;
    overflow: hidden;
}

.review-pill {
    display: inline-block;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 600;
    color: #0d6efd;
    background: rgba(13, 110, 253, 0.12);
    border-radius: 999px;
    padding: 0.3rem 0.8rem;
}

.review-body {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(255, 193, 7, 0.08));
    border-radius: 0 0 1rem 1rem;
}

.review-highlight {
    background: #fff;
    border-radius: 1rem;
    padding: 1.5rem;
    border: 1px dashed rgba(13, 110, 253, 0.25);
    box-shadow: 0 10px 20px rgba(13, 110, 253, 0.08);
}

.review-highlight-empty {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.15), rgba(255, 193, 7, 0.15));
    border: none;
    box-shadow: none;
    color: #0c2d62;
}

.review-score {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    background: linear-gradient(135deg, #ffc107, #ff8f0c);
    color: #fff;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 8px 20px rgba(255, 193, 7, 0.35);
}

.review-stars i {
    font-size: 1.15rem;
}

.review-badge {
    background: rgba(255, 193, 7, 0.18) !important;
    color: #b58100 !important;
    font-weight: 600;
    padding: 0.45rem 0.9rem;
    font-size: 0.85rem;
}

@media (max-width: 768px) {
    .review-body {
        border-radius: 0 0 1rem 1rem;
        padding: 1.25rem;
    }

    .review-highlight {
        padding: 1.25rem;
    }
}
</style>
@endsection
