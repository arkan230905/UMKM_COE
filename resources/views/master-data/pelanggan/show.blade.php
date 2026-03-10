@extends('layouts.app')

@section('title', 'Detail Pelanggan')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">
            <i class="bi bi-person-circle"></i> Detail Pelanggan
        </h2>
        <a href="{{ route('master-data.pelanggan.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person"></i> Informasi Pelanggan
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-dark">
                        <tr>
                            <td width="120"><strong>Nama:</strong></td>
                            <td>{{ $pelanggan->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $pelanggan->email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Username:</strong></td>
                            <td>{{ $pelanggan->username }}</td>
                        </tr>
                        <tr>
                            <td><strong>No. Telepon:</strong></td>
                            <td>{{ $pelanggan->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Password:</strong></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span id="passwordDisplay" class="font-monospace">••••••••</span>
                                    <button class="btn btn-sm btn-outline-light" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="copyPassword()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="actualPassword" value="{{ $pelanggan->plain_password ?? $pelanggan->password }}">
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Terdaftar:</strong></td>
                            <td>{{ $pelanggan->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Pesanan:</strong></td>
                            <td><span class="badge bg-info">{{ $pelanggan->orders->count() }} Pesanan</span></td>
                        </tr>
                    </table>

                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('master-data.pelanggan.edit', $pelanggan->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Data
                        </a>
                        <button class="btn btn-danger" onclick="resetPassword()">
                            <i class="fas fa-key"></i> Reset Password
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cart-check"></i> Riwayat Pesanan (10 Terakhir)
                    </h5>
                </div>
                <div class="card-body">
                    @if($pelanggan->orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Nomor Order</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status Pembayaran</th>
                                    <th>Status Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pelanggan->orders as $order)
                                <tr>
                                    <td>
                                        <strong>{{ $order->nomor_order }}</strong>
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if($order->payment_status === 'paid')
                                        <span class="badge bg-success">Lunas</span>
                                        @elseif($order->payment_status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                        @else
                                        <span class="badge bg-danger">Gagal</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->status === 'completed')
                                        <span class="badge bg-success">Selesai</span>
                                        @elseif($order->status === 'processing')
                                        <span class="badge bg-info">Diproses</span>
                                        @elseif($order->status === 'shipped')
                                        <span class="badge bg-primary">Dikirim</span>
                                        @else
                                        <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                        <p class="text-muted mt-2">Belum ada pesanan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
let isPasswordVisible = false;

function togglePassword() {
    const passwordDisplay = document.getElementById('passwordDisplay');
    const passwordToggleIcon = document.getElementById('passwordToggleIcon');
    const actualPassword = document.getElementById('actualPassword').value;
    
    if (isPasswordVisible) {
        // Hide password
        passwordDisplay.textContent = '••••••••';
        passwordToggleIcon.classList.remove('fa-eye-slash');
        passwordToggleIcon.classList.add('fa-eye');
        isPasswordVisible = false;
    } else {
        // Show actual password
        passwordDisplay.textContent = actualPassword;
        passwordToggleIcon.classList.remove('fa-eye');
        passwordToggleIcon.classList.add('fa-eye-slash');
        isPasswordVisible = true;
    }
}

function copyPassword() {
    const actualPassword = document.getElementById('actualPassword').value;
    
    // Copy to clipboard
    navigator.clipboard.writeText(actualPassword).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.remove('btn-outline-info');
        btn.classList.add('btn-success');
        
        setTimeout(function() {
            btn.innerHTML = originalIcon;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-info');
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy password: ', err);
        
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = actualPassword;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        // Show success message
        const btn = event.target.closest('button');
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.remove('btn-outline-info');
        btn.classList.add('btn-success');
        
        setTimeout(function() {
            btn.innerHTML = originalIcon;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-info');
        }, 2000);
    });
}

// Add password reset functionality
function resetPassword() {
    if (confirm('Apakah Anda yakin ingin mereset password pelanggan ini?')) {
        const pelangganId = {{ $pelanggan->id }};
        
        fetch(`/master-data/pelanggan/${pelangganId}/reset-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                password: 'password123', // Default password
                password_confirmation: 'password123'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Password berhasil direset ke: password123');
                location.reload();
            } else {
                alert('Gagal mereset password: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mereset password');
        });
    }
}
</script>
