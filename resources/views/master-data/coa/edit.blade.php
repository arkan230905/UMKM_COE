@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Edit COA</h2>
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('master-data.coa.update',$coa->kode_akun) }}" method="POST">
        @csrf @method('PATCH')

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tipe Akun</label>
                <select name="tipe_akun" id="tipe_akun" class="form-select" required>
                    @php($tipeList=['Asset','Liability','Equity','Revenue','Expense'])
                    @foreach($tipeList as $t)
                        <option value="{{ $t }}" {{ $coa->tipe_akun===$t?'selected':'' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kode Akun</label>
                <input type="text" name="kode_akun" class="form-control" value="{{ $coa->kode_akun }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Saldo Normal</label>
                <select name="saldo_normal" class="form-select">
                    <option value="">-</option>
                    <option value="debit" {{ $coa->saldo_normal==='debit'?'selected':'' }}>Debit</option>
                    <option value="kredit" {{ $coa->saldo_normal==='kredit'?'selected':'' }}>Kredit</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama Akun</label>
                <input type="text" name="nama_akun" class="form-control" value="{{ $coa->nama_akun }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kategori Akun</label>
                <input type="text" name="kategori_akun" class="form-control" value="{{ $coa->kategori_akun }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Akun Induk</label>
                <select name="kode_induk" class="form-select">
                    <option value="">- Tidak Ada -</option>
                    @foreach($coas as $p)
                        <option value="{{ $p->kode_akun }}" {{ $coa->kode_induk===$p->kode_akun?'selected':'' }}>{{ $p->kode_akun }} - {{ $p->nama_akun }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Akun Header?</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_akun_header" value="1" id="is_header" {{ $coa->is_akun_header? 'checked':'' }}>
                    <label class="form-check-label" for="is_header">Ya</label>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Saldo Awal</label>
                <input type="text" id="saldo_awal_view" class="form-control" inputmode="decimal" placeholder="0" value="{{ $coa->saldo_awal ? number_format((float)$coa->saldo_awal,0,',','.') : '' }}">
                <input type="hidden" name="saldo_awal" id="saldo_awal" value="{{ (float)($coa->saldo_awal ?? 0) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Tanggal Saldo Awal</label>
                <input type="date" name="tanggal_saldo_awal" class="form-control" value="{{ $coa->tanggal_saldo_awal }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Posted Saldo Awal?</label>
                <select name="posted_saldo_awal" class="form-select">
                    <option value="0" {{ !$coa->posted_saldo_awal? 'selected':'' }}>Belum</option>
                    <option value="1" {{ $coa->posted_saldo_awal? 'selected':'' }}>Posted</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2">{{ $coa->keterangan }}</textarea>
            </div>
        </div>

        <div class="mt-3">
            <button class="btn btn-success">Update</button>
            <a href="{{ route('master-data.coa.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
    </div>

    <script>
    // Money formatting (IDR) for saldo_awal in edit
    (function(){
        const view = document.getElementById('saldo_awal_view');
        const hidden = document.getElementById('saldo_awal');
        const nf = new Intl.NumberFormat('id-ID');
        const parseIdr = (str)=>{
            if (!str) return 0;
            let s = String(str).replace(/[^0-9,\.]/g,'').replace(/\./g,'').replace(',', '.');
            const num = parseFloat(s);
            return isNaN(num) ? 0 : num;
        };
        view.addEventListener('input', ()=>{
            const raw = view.value; const val = parseIdr(raw);
            hidden.value = val; view.value = raw === '' ? '' : nf.format(val);
            view.selectionStart = view.selectionEnd = view.value.length;
        });
        document.querySelector('form[action="{{ route('master-data.coa.update',$coa->kode_akun) }}"]').addEventListener('submit', ()=>{
            hidden.value = parseIdr(view.value);
        });
    })();
    </script>
@endsection
