@extends('layouts.app')

@section('title', 'Tambah Kualifikasi Tenaga Kerja')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-briefcase me-2"></i>Tambah Kualifikasi Tenaga Kerja
        </h2>
        <a href="{{ route('master-data.kualifikasi-tenaga-kerja.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-plus me-2"></i>Form Kualifikasi Tenaga Kerja Baru
            </h5>
        </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body jabatan-form">
            <form method="POST" action="{{ route('master-data.kualifikasi-tenaga-kerja.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Jabatan</label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" placeholder="cth: Operator Produksi" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="btkl" {{ old('kategori')==='btkl' ? 'selected' : '' }}>BTKL</option>
                            <option value="btktl" {{ old('kategori')==='btktl' ? 'selected' : '' }}>BTKTL</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tunjangan (Rp)</label>
                        <input type="text" name="tunjangan" class="form-control money-input" value="{{ old('tunjangan',0) }}">
                        <small class="money-hint"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Asuransi (Rp)</label>
                        <input type="text" name="asuransi" class="form-control money-input" value="{{ old('asuransi',0) }}">
                        <small class="money-hint"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gaji Pokok (Rp)</label>
                        <input type="text" name="gaji_pokok" class="form-control money-input" value="{{ old('gaji_pokok',0) }}">
                        <small class="money-hint"></small>
                        <small class="d-block">BTKTL: gaji per bulan. BTKL: isi 0.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tarif/Jam (Rp)</label>
                        <input type="text" name="tarif_lembur" class="form-control money-input" value="{{ old('tarif_lembur',0) }}">
                        <small class="money-hint"></small>
                        <small class="d-block">BTKL: tarif per jam. BTKTL: isi 0.</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">Simpan</button>
                    <a href="{{ route('master-data.kualifikasi-tenaga-kerja.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        (function(){
            const formatID = (val) => {
                if (val === null || val === undefined) return '';
                let v = String(val).replace(/[^0-9,.]/g, '');
                if (!v) return '';
                // Treat the first comma as decimal separator, ignore dots when parsing
                const commaIndex = v.indexOf(',');
                let rawInt = commaIndex >= 0 ? v.slice(0, commaIndex) : v;
                let rawDec = commaIndex >= 0 ? v.slice(commaIndex + 1) : '';
                // remove all non-digits from int/dec; ignore dots entirely (they are visual only)
                rawInt = rawInt.replace(/\D/g, '');
                rawDec = rawDec.replace(/\D/g, '').slice(0, 2);
                // format integer part with thousands '.'
                let intPart = rawInt.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                if (!rawDec) return intPart;
                if (/^0{1,2}$/.test(rawDec)) return intPart; // drop .00 style
                return intPart + ',' + rawDec;
            };
            const toNumber = (formatted) => {
                if (!formatted) return 0;
                let s = String(formatted).trim();
                // Treat '.' as thousands and ',' as decimal
                s = s.replace(/\./g,'').replace(',', '.');
                let n = parseFloat(s);
                return isNaN(n) ? 0 : n;
            };
            const compactID = (n) => {
                const u = [
                    {v:1e12, s:' triliun'},
                    {v:1e9, s:' miliar'},
                    {v:1e6, s:' juta'},
                    {v:1e3, s:' ribu'},
                ];
                for (const it of u) {
                    if (n >= it.v) {
                        const val = (n / it.v).toFixed(2).replace(/\.00$/,'');
                        return val + it.s;
                    }
                }
                return '';
            };
            const inputs = document.querySelectorAll('.money-input');
            inputs.forEach((inp) => {
                inp.value = formatID(inp.value);
                const hint = inp.parentElement.querySelector('.money-hint');
                const updateHint = () => {
                    const num = toNumber(inp.value);
                    const text = compactID(num);
                    if (hint) hint.textContent = text ? '('+text+')' : '';
                };
                updateHint();
                inp.addEventListener('input', () => {
                    const start = inp.selectionStart;
                    inp.value = formatID(inp.value);
                    updateHint();
                });
                inp.addEventListener('blur', () => { inp.value = formatID(inp.value); updateHint(); });
            });
        })();
    </script>
</div>
@endsection
