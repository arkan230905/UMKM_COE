@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Tambah COA</h2>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('master-data.coa.store') }}" method="POST" id="coaForm">
        @csrf
        <input type="hidden" name="auto_generate_kode" id="auto_generate_kode" value="0">

        <div class="row g-3">
            {{-- Akun Induk --}}
            <div class="col-md-8">
                <label class="form-label fw-bold">Akun Induk <small class="text-muted">(pilih untuk generate kode otomatis)</small></label>
                <select name="parent_coa_id" id="parent_coa_id" class="form-select">
                    <option value="">-- Tanpa Induk (input kode manual) --</option>
                    @foreach($parentCoas as $p)
                        <option value="{{ $p->id }}"
                            data-kode="{{ $p->kode_akun }}"
                            data-tipe="{{ $p->tipe_akun }}"
                            data-kategori="{{ $p->kategori_akun }}"
                            data-saldo-normal="{{ $p->saldo_normal }}"
                            {{ old('parent_coa_id') == $p->id ? 'selected' : '' }}>
                            {{ str_repeat('—', strlen($p->kode_akun) - 1) }} {{ $p->kode_akun }} - {{ $p->nama_akun }} ({{ $p->tipe_akun }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="button" class="btn btn-outline-primary w-100" id="btnGenerate" disabled onclick="generateChildKode()">
                    <i class="bi bi-lightning-charge"></i> Generate Kode Anak
                </button>
            </div>

            {{-- Info hasil generate --}}
            <div class="col-md-12" id="generateInfo" style="display:none;">
                <div class="alert alert-info mb-0 py-2">
                    <strong>Kode otomatis:</strong> <span id="infoKode"></span>
                    — Anak dari <span id="infoParent"></span>
                </div>
            </div>

            {{-- Tipe Akun --}}
            <div class="col-md-4">
                <label class="form-label">Tipe Akun</label>
                <select name="tipe_akun" id="tipe_akun" class="form-select" required>
                    <option value="">Pilih tipe</option>
                    <option value="Asset" {{ old('tipe_akun') == 'Asset' ? 'selected' : '' }}>Asset</option>
                    <option value="Liability" {{ old('tipe_akun') == 'Liability' ? 'selected' : '' }}>Liability</option>
                    <option value="Equity" {{ old('tipe_akun') == 'Equity' ? 'selected' : '' }}>Equity</option>
                    <option value="Revenue" {{ old('tipe_akun') == 'Revenue' ? 'selected' : '' }}>Revenue</option>
                    <option value="Expense" {{ old('tipe_akun') == 'Expense' ? 'selected' : '' }}>Expense</option>
                    <option value="Beban" {{ old('tipe_akun') == 'Beban' ? 'selected' : '' }}>Beban</option>
                </select>
            </div>

            {{-- Kode Akun --}}
            <div class="col-md-4">
                <label class="form-label">Kode Akun</label>
                <input type="text" name="kode_akun" id="kode_akun" class="form-control" value="{{ old('kode_akun') }}" placeholder="Pilih induk lalu klik Generate, atau isi manual" required>
            </div>

            {{-- Saldo Normal --}}
            <div class="col-md-4">
                <label class="form-label">Saldo Normal</label>
                <select name="saldo_normal" id="saldo_normal" class="form-select">
                    <option value="">-</option>
                    <option value="debit" {{ old('saldo_normal') == 'debit' ? 'selected' : '' }}>Debit</option>
                    <option value="kredit" {{ old('saldo_normal') == 'kredit' ? 'selected' : '' }}>Kredit</option>
                </select>
            </div>

            {{-- Nama Akun --}}
            <div class="col-md-6">
                <label class="form-label">Nama Akun</label>
                <input type="text" name="nama_akun" class="form-control" value="{{ old('nama_akun') }}" required>
            </div>

            {{-- Kategori Akun --}}
            <div class="col-md-6">
                <label class="form-label">Kategori Akun</label>
                <input type="text" name="kategori_akun" id="kategori_akun" class="form-control" value="{{ old('kategori_akun') }}" placeholder="Misal: Kas & Bank, Persediaan">
            </div>

            {{-- Saldo Awal --}}
            <div class="col-md-6">
                <label class="form-label">Saldo Awal</label>
                <input type="text" id="saldo_awal_view" class="form-control" inputmode="decimal" placeholder="0">
                <input type="hidden" name="saldo_awal" id="saldo_awal" value="0">
            </div>

            {{-- Tanggal Saldo Awal --}}
            <div class="col-md-3">
                <label class="form-label">Tanggal Saldo Awal</label>
                <input type="date" name="tanggal_saldo_awal" class="form-control" value="{{ old('tanggal_saldo_awal') }}">
            </div>

            {{-- Posted Saldo Awal --}}
            <div class="col-md-3">
                <label class="form-label">Posted Saldo Awal?</label>
                <select name="posted_saldo_awal" class="form-select">
                    <option value="0">Belum</option>
                    <option value="1">Posted</option>
                </select>
            </div>

            {{-- Keterangan --}}
            <div class="col-md-12">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('master-data.coa.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
// Enable/disable generate button based on parent selection
document.getElementById('parent_coa_id').addEventListener('change', function() {
    const btn = document.getElementById('btnGenerate');
    const sel = this;
    btn.disabled = !sel.value;

    if (sel.value) {
        const opt = sel.options[sel.selectedIndex];
        // Auto-fill tipe_akun, kategori_akun, saldo_normal dari parent
        const tipe = opt.getAttribute('data-tipe');
        const kategori = opt.getAttribute('data-kategori');
        const saldoNormal = opt.getAttribute('data-saldo-normal');

        if (tipe) document.getElementById('tipe_akun').value = tipe;
        if (kategori) document.getElementById('kategori_akun').value = kategori;
        if (saldoNormal) document.getElementById('saldo_normal').value = saldoNormal;
    } else {
        // Reset jika tidak ada parent
        document.getElementById('generateInfo').style.display = 'none';
        document.getElementById('auto_generate_kode').value = '0';
        document.getElementById('kode_akun').readOnly = false;
        document.getElementById('kode_akun').value = '';
    }
});

function generateChildKode() {
    const parentId = document.getElementById('parent_coa_id').value;
    if (!parentId) return;

    const btn = document.getElementById('btnGenerate');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';

    fetch(`{{ url('/master-data/coa/generate-child-kode') }}?parent_coa_id=${parentId}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            // Set kode akun
            document.getElementById('kode_akun').value = data.kode_akun;
            document.getElementById('kode_akun').readOnly = true;
            document.getElementById('auto_generate_kode').value = '1';

            // Set tipe, kategori, saldo_normal dari parent
            if (data.parent_tipe) document.getElementById('tipe_akun').value = data.parent_tipe;
            if (data.parent_kategori) document.getElementById('kategori_akun').value = data.parent_kategori;
            if (data.parent_saldo_normal) document.getElementById('saldo_normal').value = data.parent_saldo_normal;

            // Show info
            document.getElementById('infoKode').textContent = data.kode_akun;
            document.getElementById('infoParent').textContent = data.parent_kode + ' - ' + data.parent_nama;
            document.getElementById('generateInfo').style.display = 'block';
        })
        .catch(err => {
            alert('Gagal generate kode: ' + err.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lightning-charge"></i> Generate Kode Anak';
        });
}

// Money formatting (IDR) for saldo_awal
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
    view.value = hidden.value && hidden.value !== '0' ? nf.format(parseFloat(hidden.value)) : '';
    view.addEventListener('input', ()=>{
        const raw = view.value;
        const val = parseIdr(raw);
        hidden.value = val;
        view.value = raw === '' ? '' : nf.format(val);
        view.selectionStart = view.selectionEnd = view.value.length;
    });
    document.getElementById('coaForm').addEventListener('submit', ()=>{
        hidden.value = parseIdr(view.value);
    });
})();
</script>
@endsection
