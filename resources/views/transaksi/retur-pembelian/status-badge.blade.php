{{-- Status Badge Component --}}
@php
    $statusConfig = [
        'pending' => ['label' => 'Menunggu Persetujuan', 'icon' => 'clock'],
        'disetujui' => ['label' => 'Disetujui', 'icon' => 'check-circle'],
        'ditolak' => ['label' => 'Ditolak', 'icon' => 'times-circle'],
        'dikirim' => ['label' => 'Dikirim', 'icon' => 'shipping-fast'],
        'selesai' => ['label' => 'Selesai', 'icon' => 'check-double'],
    ];
    
    $config = $statusConfig[$status] ?? ['label' => ucfirst($status), 'icon' => 'question'];
@endphp

<span>
    <i class="fas fa-{{ $config['icon'] }} me-1"></i>{{ $config['label'] }}
</span>