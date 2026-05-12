{{-- resources/views/components/dashboard-card.blade.php --}}
<div class="col-md-3 mb-3">
    <div class="card shadow-sm border-0 rounded-3 text-white" style="background: linear-gradient(135deg, #6B73FF 0%, #000DFF 100%);">
        <div class="card-body">
            <h6 class="fw-bold">{{ $title }}</h6>
            <h3 class="fw-bold mt-2">{{ $count }}</h3>
            @if(isset($icon))
                <i class="bi {{ $icon }} fs-2 float-end"></i>
            @endif
        </div>
    </div>
</div>
