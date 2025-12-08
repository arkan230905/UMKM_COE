@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Tambah COA</h2>

    <form action="{{ route('master-data.coa.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tipe Akun</label>
                <select name="tipe_akun" id="tipe_akun" class="form-select" onchange="generateKode()" required>
                    <option value="">Pilih tipe</option>
                    <option value="Asset">Asset</option>
                    <option value="Liability">Liability</option>
                    <option value="Equity">Equity</option>
                    <option value="Revenue">Revenue</option>
                    <option value="Expense">Expense</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kode Akun</label>
                <input type="text" name="kode_akun" id="kode_akun" class="form-control" placeholder="Otomatis saat pilih tipe" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Saldo Normal</label>
                <select name="saldo_normal" class="form-select">
                    <option value="">-</option>
                    <option value="debit">Debit</option>
                    <option value="kredit">Kredit</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama Akun</label>
                <input type="text" name="nama_akun" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kategori Akun</label>
                <input type="text" name="kategori_akun" class="form-control" placeholder="Misal: Aset Lancar">
            </div>

            <div class="col-md-6">
                <label class="form-label">Akun Induk</label>
                <select name="kode_induk" class="form-select">
                    <option value="">- Tidak Ada -</option>
                    @foreach($coas as $p)
                        <option value="{{ $p->kode_akun }}">{{ $p->kode_akun }} - {{ $p->nama_akun }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Akun Header?</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_akun_header" value="1" id="is_header">
                    <label class="form-check-label" for="is_header">Ya</label>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Saldo Awal</label>
                <input type="text" id="saldo_awal_view" class="form-control" inputmode="decimal" placeholder="0">
                <input type="hidden" name="saldo_awal" id="saldo_awal" value="0">
            </div>

            <div class="col-md-4">
                <label class="form-label">Tanggal Saldo Awal</label>
                <input type="date" name="tanggal_saldo_awal" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Posted Saldo Awal?</label>
                <select name="posted_saldo_awal" class="form-select">
                    <option value="0">Belum</option>
                    <option value="1">Posted</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('master-data.coa.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
function generateKode() {
    const tipe = document.getElementById('tipe_akun').value;
    if (!tipe) return;

    fetch(`/master-data/coa/generate-kode?tipe=${tipe}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('kode_akun').value = data.kode_akun;
        });
}

// Money formatting (IDR) for saldo_awal
(function(){
    const view = document.getElementById('saldo_awal_view');
    const hidden = document.getElementById('saldo_awal');
    const nf = new Intl.NumberFormat('id-ID');
    const parseIdr = (str)=>{
        if (!str) return 0;
        // remove spaces, dots thousands, normalize comma to dot
        let s = String(str).replace(/[^0-9,\.]/g,'').replace(/\./g,'').replace(',', '.');
        const num = parseFloat(s);
        return isNaN(num) ? 0 : num;
    };
    const formatView = ()=>{
        const val = parseIdr(view.value);
        hidden.value = val;
        view.value = val === 0 ? '' : nf.format(val);
    };
    // initialize from hidden default
    view.value = hidden.value && hidden.value !== '0' ? nf.format(parseFloat(hidden.value)) : '';
    view.addEventListener('input', ()=>{
        const caretToEnd = document.activeElement === view; // simple approach
        const raw = view.value;
        const val = parseIdr(raw);
        hidden.value = val;
        view.value = raw === '' ? '' : nf.format(val);
        if (caretToEnd) view.selectionStart = view.selectionEnd = view.value.length;
    });
    // ensure hidden value set on submit
    document.querySelector('form[action="{{ route('master-data.coa.store') }}"]').addEventListener('submit', ()=>{
        hidden.value = parseIdr(view.value);
    });
})();
</script>
@endsection
