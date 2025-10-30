@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <h2>Edit Aset</h2>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Terjadi kesalahan!</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('master-data.aset.update', $aset->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Aset</label>
                    <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $aset->nama) }}" required>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <optgroup label="Aset Lancar">
                            <option value="Kas" {{ old('kategori', $aset->kategori) == 'Kas' ? 'selected' : '' }}>Kas</option>
                            <option value="Bank" {{ old('kategori', $aset->kategori) == 'Bank' ? 'selected' : '' }}>Bank</option>
                            <option value="Piutang Usaha" {{ old('kategori', $aset->kategori) == 'Piutang Usaha' ? 'selected' : '' }}>Piutang Usaha</option>
                            <option value="Piutang Lain-lain" {{ old('kategori', $aset->kategori) == 'Piutang Lain-lain' ? 'selected' : '' }}>Piutang Lain-lain</option>
                            <option value="Persediaan Bahan Baku" {{ old('kategori', $aset->kategori) == 'Persediaan Bahan Baku' ? 'selected' : '' }}>Persediaan Bahan Baku</option>
                            <option value="Persediaan Barang Dagang" {{ old('kategori', $aset->kategori) == 'Persediaan Barang Dagang' ? 'selected' : '' }}>Persediaan Barang Dagang</option>
                            <option value="Uang Muka" {{ old('kategori', $aset->kategori) == 'Uang Muka' ? 'selected' : '' }}>Uang Muka</option>
                            <option value="Beban Dibayar Dimuka" {{ old('kategori', $aset->kategori) == 'Beban Dibayar Dimuka' ? 'selected' : '' }}>Beban Dibayar Dimuka</option>
                        </optgroup>
                        <optgroup label="Aset Tetap">
                            <option value="Tanah" {{ old('kategori', $aset->kategori) == 'Tanah' ? 'selected' : '' }}>Tanah</option>
                            <option value="Bangunan" {{ old('kategori', $aset->kategori) == 'Bangunan' ? 'selected' : '' }}>Bangunan</option>
                            <option value="Kendaraan" {{ old('kategori', $aset->kategori) == 'Kendaraan' ? 'selected' : '' }}>Kendaraan</option>
                            <option value="Mesin" {{ old('kategori', $aset->kategori) == 'Mesin' ? 'selected' : '' }}>Mesin</option>
                            <option value="Peralatan" {{ old('kategori', $aset->kategori) == 'Peralatan' ? 'selected' : '' }}>Peralatan</option>
                            <option value="Peralatan Kantor" {{ old('kategori', $aset->kategori) == 'Peralatan Kantor' ? 'selected' : '' }}>Peralatan Kantor</option>
                            <option value="Peralatan Dapur" {{ old('kategori', $aset->kategori) == 'Peralatan Dapur' ? 'selected' : '' }}>Peralatan Dapur</option>
                            <option value="Komputer & Elektronik" {{ old('kategori', $aset->kategori) == 'Komputer & Elektronik' ? 'selected' : '' }}>Komputer & Elektronik</option>
                            <option value="Furniture" {{ old('kategori', $aset->kategori) == 'Furniture' ? 'selected' : '' }}>Furniture</option>
                        </optgroup>
                        <optgroup label="Aset Tak Berwujud">
                            <option value="Hak Paten" {{ old('kategori', $aset->kategori) == 'Hak Paten' ? 'selected' : '' }}>Hak Paten</option>
                            <option value="Merek Dagang" {{ old('kategori', $aset->kategori) == 'Merek Dagang' ? 'selected' : '' }}>Merek Dagang</option>
                            <option value="Hak Cipta" {{ old('kategori', $aset->kategori) == 'Hak Cipta' ? 'selected' : '' }}>Hak Cipta</option>
                            <option value="Lisensi/Software" {{ old('kategori', $aset->kategori) == 'Lisensi/Software' ? 'selected' : '' }}>Lisensi/Software</option>
                            <option value="Goodwill" {{ old('kategori', $aset->kategori) == 'Goodwill' ? 'selected' : '' }}>Goodwill</option>
                        </optgroup>
                        <optgroup label="Aset Lain-lain">
                            <option value="Deposito Jangka Panjang" {{ old('kategori', $aset->kategori) == 'Deposito Jangka Panjang' ? 'selected' : '' }}>Deposito Jangka Panjang</option>
                            <option value="Investasi Jangka Panjang" {{ old('kategori', $aset->kategori) == 'Investasi Jangka Panjang' ? 'selected' : '' }}>Investasi Jangka Panjang</option>
                            <option value="Aset Dalam Pengerjaan" {{ old('kategori', $aset->kategori) == 'Aset Dalam Pengerjaan' ? 'selected' : '' }}>Aset Dalam Pengerjaan</option>
                        </optgroup>
                    </select>
                    @error('kategori')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jenis_aset" class="form-label">Jenis Aset</label>
                    <select class="form-select @error('jenis_aset') is-invalid @enderror" id="jenis_aset" name="jenis_aset" required>
                        <option value="">-- Pilih Jenis Aset --</option>
                        <option value="Aset Tetap" {{ old('jenis_aset', $aset->jenis_aset) == 'Aset Tetap' ? 'selected' : '' }}>Aset Tetap</option>
                        <option value="Aset Tidak Tetap" {{ old('jenis_aset', $aset->jenis_aset) == 'Aset Tidak Tetap' ? 'selected' : '' }}>Aset Tidak Tetap</option>
                    </select>
                    @error('jenis_aset')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="harga" class="form-label">Harga</label>
                    <input type="number" class="form-control @error('harga') is-invalid @enderror" id="harga" name="harga" value="{{ old('harga', $aset->harga) }}" required>
                    @error('harga')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tanggal_beli" class="form-label">Tanggal Pembelian</label>
                    <input type="date" class="form-control @error('tanggal_beli') is-invalid @enderror" id="tanggal_beli" name="tanggal_beli" value="{{ old('tanggal_beli', optional($aset->tanggal_beli)->format('Y-m-d')) }}" required>
                    @error('tanggal_beli')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="border rounded p-3 mb-3">
                    <h5 class="mb-3">Penyusutan Aset</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Harga Perolehan</label>
                            <input type="number" step="0.01" name="acquisition_cost" class="form-control @error('acquisition_cost') is-invalid @enderror" value="{{ old('acquisition_cost', $aset->acquisition_cost) }}">
                            @error('acquisition_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nilai Residu</label>
                            <input type="number" step="0.01" name="residual_value" class="form-control @error('residual_value') is-invalid @enderror" value="{{ old('residual_value', $aset->residual_value) }}">
                            @error('residual_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Umur Manfaat (tahun)</label>
                            <input type="number" step="1" min="0" name="useful_life_years" class="form-control @error('useful_life_years') is-invalid @enderror" value="{{ old('useful_life_years', $aset->useful_life_years) }}">
                            @error('useful_life_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mulai Penyusutan</label>
                            <input type="date" name="depr_start_date" class="form-control @error('depr_start_date') is-invalid @enderror" value="{{ old('depr_start_date', optional($aset->depr_start_date)->format('Y-m-d')) }}">
                            @error('depr_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Metode</label>
                            <select name="depr_method" class="form-select">
                                <option value="SL" {{ old('depr_method', $aset->depr_method) == 'SL' ? 'selected' : '' }}>Garis Lurus (SL)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                    <a href="{{ route('master-data.aset.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
