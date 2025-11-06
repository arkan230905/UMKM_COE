@extends('layouts.app')

@section('title', 'Tambah Data Pegawai')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">➕ Tambah Data Pegawai</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('master-data.pegawai.store') }}" method="POST" id="pegawai-form">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="nama" class="form-label">Nama Pegawai</label>
                <input type="text" name="nama" id="nama" class="form-control" value="{{ old('nama') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="no_telepon" class="form-label">No. Telepon</label>
                <input type="text" name="no_telepon" id="no_telepon" class="form-control" value="{{ old('no_telepon') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea name="alamat" id="alamat" class="form-control" rows="2" required>{{ old('alamat') }}</textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label for="nama_bank" class="form-label">Nama Bank</label>
                <input type="text" name="nama_bank" id="nama_bank" class="form-control" value="{{ old('nama_bank') }}">
            </div>

            <div class="col-md-6 mb-3">
                <label for="no_rekening" class="form-label">No. Rekening</label>
                <input type="text" name="no_rekening" id="no_rekening" class="form-control" value="{{ old('no_rekening') }}">
            </div>

            <div class="col-md-6 mb-3">
                <label for="jabatan_id" class="form-label">Jabatan</label>
                <select name="jabatan_id" id="jabatan_id" class="form-select" required>
                    <option value="">-- Pilih Jabatan --</option>
                    @foreach($jabatans as $j)
                        <option value="{{ $j->id }}"
                                data-nama="{{ $j->nama }}"
                                data-kategori="{{ $j->kategori }}"
                                data-tunjangan="{{ $j->tunjangan }}"
                                data-asuransi="{{ $j->asuransi }}"
                                data-gaji="{{ $j->gaji }}"
                                data-tarif="{{ $j->tarif }}"
                                {{ old('jabatan_id')==$j->id?'selected':'' }}>
                            {{ $j->nama }} ({{ strtoupper($j->kategori) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Hidden fields auto-filled -->
            <input type="hidden" name="jabatan" id="jabatan" value="{{ old('jabatan') }}">
            <input type="hidden" name="kategori" id="kategori" value="{{ old('kategori') }}">
            <input type="hidden" name="tunjangan" id="tunjangan" value="{{ old('tunjangan') }}">
            <input type="hidden" name="asuransi" id="asuransi" value="{{ old('asuransi') }}">
            <input type="hidden" name="gaji" id="gaji" value="{{ old('gaji') }}">
            <input type="hidden" name="tarif" id="tarif" value="{{ old('tarif') }}">

            <!-- Preview otomatis dari Jabatan -->
            <div class="col-12">
                <div class="alert alert-secondary small" id="preview-box" style="display:none">
                    <div><strong>Kategori:</strong> <span id="pv-kategori">-</span></div>
                    <div><strong>Tunjangan:</strong> Rp <span id="pv-tunjangan">0</span></div>
                    <div><strong>Asuransi:</strong> Rp <span id="pv-asuransi">0</span></div>
                    <div><strong>Gaji (BTKTL/bulan):</strong> Rp <span id="pv-gaji">0</span></div>
                    <div><strong>Tarif / Jam (BTKL):</strong> Rp <span id="pv-tarif">0</span></div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('master-data.pegawai.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<script>
    (function(){
        const fmt = (n)=> new Intl.NumberFormat('id-ID').format(Number(n||0));
        const dd = document.getElementById('jabatan_id');
        const mapFromSelect = () => {
            const opt = dd.options[dd.selectedIndex];
            if (!opt) { document.getElementById('preview-box').style.display='none'; return; }
            const ds = opt.dataset;
            const data = {
                nama: ds.nama || '',
                kategori: ds.kategori || '',
                tunjangan: ds.tunjangan || 0,
                asuransi: ds.asuransi || 0,
                gaji: ds.gaji || 0,
                tarif: ds.tarif || 0,
            };
            // set hidden
            document.getElementById('jabatan').value = data.nama;
            document.getElementById('kategori').value = (data.kategori||'').toUpperCase();
            document.getElementById('tunjangan').value = data.tunjangan;
            document.getElementById('asuransi').value = data.asuransi;
            document.getElementById('gaji').value = data.gaji;
            document.getElementById('tarif').value = data.tarif;
            // preview
            document.getElementById('pv-kategori').textContent = (data.kategori||'').toUpperCase();
            document.getElementById('pv-tunjangan').textContent = fmt(data.tunjangan);
            document.getElementById('pv-asuransi').textContent = fmt(data.asuransi);
            document.getElementById('pv-gaji').textContent = fmt(data.gaji);
            document.getElementById('pv-tarif').textContent = fmt(data.tarif);
            document.getElementById('preview-box').style.display='block';
        };
        dd.addEventListener('change', mapFromSelect);
        if (dd.value) mapFromSelect();
    })();
</script>
@endsection
