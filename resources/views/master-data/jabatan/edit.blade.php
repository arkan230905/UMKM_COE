@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <h2 class="mb-4"><i class="bi bi-briefcase me-2"></i>Edit Jabatan</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <style>
        .jabatan-form .form-label,
        .jabatan-form small { color: #fff !important; }
    </style>
    <div class="card border-0 shadow-sm">
        <div class="card-body jabatan-form">
            <form method="POST" action="{{ route('master-data.jabatan.update', $jabatan->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Jabatan</label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama',$jabatan->nama) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="btkl" {{ old('kategori',$jabatan->kategori)==='btkl' ? 'selected' : '' }}>BTKL</option>
                            <option value="btktl" {{ old('kategori',$jabatan->kategori)==='btktl' ? 'selected' : '' }}>BTKTL</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tunjangan (Rp)</label>
                        <input type="text" name="tunjangan" class="form-control money-input" value="{{ old('tunjangan',$jabatan->tunjangan) }}">
                        <small class="text-white money-hint"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Asuransi (Rp)</label>
                        <input type="text" name="asuransi" class="form-control money-input" value="{{ old('asuransi',$jabatan->asuransi) }}">
                        <small class="text-white money-hint"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gaji Pokok (Rp)</label>
                        <input type="text" name="gaji_pokok" class="form-control money-input" value="{{ old('gaji_pokok',$jabatan->gaji_pokok) }}">
                        <small class="text-white money-hint"></small>
                        <small class="text-white d-block">BTKTL: gaji per bulan. BTKL: isi 0.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tarif Lembur / Jam (Rp)</label>
                        <input type="text" name="tarif_lembur" class="form-control money-input" value="{{ old('tarif_lembur',$jabatan->tarif_lembur) }}">
                        <small class="text-white money-hint"></small>
                        <small class="text-white d-block">BTKL: tarif per jam. BTKTL: isi 0.</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('master-data.jabatan.index') }}" class="btn btn-secondary">Kembali</a>
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
                const commaIndex = v.indexOf(',');
                let rawInt = commaIndex >= 0 ? v.slice(0, commaIndex) : v;
                let rawDec = commaIndex >= 0 ? v.slice(commaIndex + 1) : '';
                rawInt = rawInt.replace(/\D/g, '');
                rawDec = rawDec.replace(/\D/g, '').slice(0, 2);
                let intPart = rawInt.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                if (!rawDec) return intPart;
                if (/^0{1,2}$/.test(rawDec)) return intPart;
                return intPart + ',' + rawDec;
            };
            const toNumber = (formatted) => {
                if (!formatted) return 0;
                let s = String(formatted).trim();
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
                    if (hint) hint.textContent = text ? '(' + text + ')' : '';
                };
                updateHint();
                inp.addEventListener('input', () => {
                    inp.value = formatID(inp.value);
                    updateHint();
                });
                inp.addEventListener('blur', () => { inp.value = formatID(inp.value); updateHint(); });
            });
        })();
    </script>
</div>
@endsection
