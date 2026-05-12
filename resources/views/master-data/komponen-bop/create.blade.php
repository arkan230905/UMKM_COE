@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tambah Komponen BOP</h1>
        <a href="{{ route('master-data.komponen-bop.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('master-data.komponen-bop.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Komponen <span class="text-danger">*</span></label>
                            <input type="text" name="nama_komponen" class="form-control @error('nama_komponen') is-invalid @enderror" 
                                   value="{{ old('nama_komponen') }}" placeholder="Contoh: Listrik, Gas LPG, Penyusutan Mesin" required>
                            @error('nama_komponen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" class="form-control @error('satuan') is-invalid @enderror" 
                                   value="{{ old('satuan') }}" placeholder="Contoh: kWh, kg, m³, jam" required>
                            @error('satuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Tarif per Satuan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="tarif_per_satuan" class="form-control @error('tarif_per_satuan') is-invalid @enderror" 
                                       value="{{ old('tarif_per_satuan', 0) }}" min="0" step="100" required>
                            </div>
                            @error('tarif_per_satuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Contoh Komponen BOP:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Listrik - kWh - Rp 1.500/kWh</li>
                        <li>Gas LPG - kg - Rp 15.000/kg</li>
                        <li>Air PDAM - m³ - Rp 5.000/m³</li>
                        <li>Penyusutan Mesin - jam - Rp 2.000/jam</li>
                        <li>Minyak Goreng - liter - Rp 18.000/liter</li>
                    </ul>
                </div>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master-data.komponen-bop.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
