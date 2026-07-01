@extends('layouts.app')

@section('title', 'Daftar COA')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calculator me-2"></i>COA
        </h2>
        <div class="d-flex gap-2 align-items-end">
            <form method="get" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label small mb-1">Pilih Periode</label>
                    <select name="periode" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 180px;">
                        @foreach($periods as $p)
                            <option value="{{ $p->periode }}" {{ $periode && $periode->periode == $p->periode ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($p->periode.'-01')->isoFormat('MMMM YYYY') }}
                                {{ $p->is_closed ? '✓' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
            <a href="{{ route('master-data.coa.create') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus me-1"></i>Tambah COA
            </a>
        </div>
    </div>

    @if(session('warning_coa'))
        <div class="alert alert-warning alert-dismissible fade show">
            {{ session('warning_coa') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Daftar COA (Chart of Accounts)
                </h5>
                <div style="min-width: 280px;">
                    <input type="text" id="coaSearch" class="form-control form-control-sm"
                           placeholder="Cari nama akun atau kode akun..."
                           style="border-color: #5C4033; box-shadow: 0 0 0 0.15rem rgba(92,64,51,0.15);">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="coaTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">NO</th>
                            <th>Nama Akun</th>
                            <th>Kode Akun</th>
                            <th>Tipe</th>
                            <th class="text-center">Posisi</th>
                            <th class="text-end">Saldo Awal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coas as $key => $coa)
                            @php
                                $tipeAkun = match($coa->tipe_akun) {
                                    'Asset' => 'Aset',
                                    'Liability' => 'Kewajiban',
                                    'Equity', 'Ekuitas' => 'Modal',
                                    'Revenue' => 'Pendapatan',
                                    'Expense', 'Biaya' => 'Beban',
                                    default => $coa->tipe_akun,
                                };

                                $tipeBadgeClass = match($tipeAkun) {
                                    'Aset' => 'bg-success',
                                    'Kewajiban' => 'bg-warning',
                                    'Modal' => 'bg-info',
                                    'Pendapatan' => 'bg-primary',
                                    'Beban' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-calculator text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $coa->nama_akun }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><code>{{ $coa->kode_akun }}</code></td>
                                <td>
                                    <span class="badge {{ $tipeBadgeClass }}">
                                        {{ $tipeAkun }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $posisi = $posisiAkun[$coa->id] ?? 'Unknown';
                                    @endphp
                                    <span class="badge {{ $posisi == 'Debit' ? 'bg-primary' : 'bg-success' }}">
                                        {{ $posisi }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @php
                                        $saldo = $saldoPeriode[$coa->id] ?? 0;
                                        if ($saldo == floor($saldo)) {
                                            echo number_format($saldo, 0, ',', '.');
                                        } else {
                                            echo number_format($saldo, 2, ',', '.');
                                        }
                                    @endphp
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('master-data.coa.edit', $coa->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    const searchInput = document.getElementById('coaSearch');
    const table = document.getElementById('coaTable');

    searchInput.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(function (row) {
            // Kolom Nama Akun = index 1, Kode Akun = index 2
            const namaAkun  = (row.cells[1] ? row.cells[1].textContent : '').toLowerCase();
            const kodeAkun  = (row.cells[2] ? row.cells[2].textContent : '').toLowerCase();

            const match = namaAkun.includes(query) || kodeAkun.includes(query);
            row.style.display = match ? '' : 'none';
        });
    });

    // Styling fokus mengikuti warna tema
    searchInput.addEventListener('focus', function () {
        this.style.borderColor = '#5C4033';
        this.style.boxShadow  = '0 0 0 0.2rem rgba(92,64,51,0.25)';
    });
    searchInput.addEventListener('blur', function () {
        this.style.boxShadow = '0 0 0 0.15rem rgba(92,64,51,0.15)';
    });
})();
</script>
@endsection
