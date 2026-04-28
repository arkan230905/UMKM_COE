{{-- Simple Action Buttons Component (Fallback Version) --}}
@php
    $nextStatuses = [
        'pending' => ['disetujui' => 'Setujui', 'ditolak' => 'Tolak'],
        'disetujui' => ['dikirim' => 'Kirim'],
        'dikirim' => ['selesai' => 'Selesai'],
        'ditolak' => [],
        'selesai' => [],
    ];
    
    $actions = $nextStatuses[$retur->status] ?? [];
    
    $buttonConfig = [
        'disetujui' => ['color' => 'success', 'icon' => 'check', 'action' => 'approve'],
        'ditolak' => ['color' => 'danger', 'icon' => 'times', 'action' => 'reject'],
        'dikirim' => ['color' => 'primary', 'icon' => 'shipping-fast', 'action' => 'send'],
        'selesai' => ['color' => 'info', 'icon' => 'check-double', 'action' => 'complete'],
    ];
@endphp

@if(!empty($actions))
    <div class="btn-group" role="group">
        @foreach($actions as $status => $label)
            @php $config = $buttonConfig[$status]; @endphp
            <form method="POST" action="{{ route('transaksi.retur-pembelian.' . $config['action'], $retur->id) }}" class="d-inline retur-action-form" data-action="{{ $label }}">
                @csrf
                <button type="submit" class="btn btn-{{ $config['color'] }} btn-sm">
                    <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                    {{ $label }}
                </button>
            </form>
        @endforeach
    </div>
@else
    <span class="text-muted">
        <i class="fas fa-lock me-1"></i>
        Tidak ada aksi tersedia
    </span>
@endif