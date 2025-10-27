@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Tambah COA</h2>

    <form action="{{ route('master-data.coa.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="tipe_akun" class="form-label">Tipe Akun</label>
            <select name="tipe_akun" id="tipe_akun" class="form-select" onchange="generateKode()" required>
                <option value="">Pilih tipe</option>
                <option value="Asset">Asset</option>
                <option value="Liability">Liability</option>
                <option value="Equity">Equity</option>
                <option value="Revenue">Revenue</option>
                <option value="Expense">Expense</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="nama_akun" class="form-label">Nama Akun</label>
            <input type="text" name="nama_akun" id="nama_akun" class="form-control" required>
        </div>

        <input type="hidden" name="kode_akun" id="kode_akun">

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<script>
function generateKode() {
    const tipe = document.getElementById('tipe_akun').value;
    if (!tipe) return;

<<<<<<< HEAD
    fetch(`/master-data/coa/generate-kode?tipe=${tipe}`)
=======
    fetch(/master-data/coa/generate-kode?tipe=${tipe})
>>>>>>> 68de30b (pembuatan bop dan satuan)
        .then(res => res.json())
        .then(data => {
            document.getElementById('kode_akun').value = data.kode_akun;
        });
}
</script>
<<<<<<< HEAD
@endsection
=======
@endsection
>>>>>>> 68de30b (pembuatan bop dan satuan)
