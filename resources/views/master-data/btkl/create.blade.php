@extends('layouts.app')

@section('title', 'Tambah BTKL')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-person-gear me-2"></i>Tambah Proses Produksi (BTKL)
        </h2>
        <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('master-data.btkl.store') }}" method="POST">
                @csrf
                
                <div class="row g-3">
                    {{-- Kode Proses --}}
                    <div class="col-md-6">
                        <label for="kode_proses" class="form-label">Kode Proses <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="kode_proses" 
                               id="kode_proses" 
                               class="form-control" 
                               value="{{ old('kode_proses', $nextKode) }}" 
                               readonly>
                    </div>

                    {{-- Nama Proses --}}
                    <div class="col-md-6">
                        <label for="nama_btkl" class="form-label">Nama Proses <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nama_btkl" 
                               id="nama_btkl" 
                               class="form-control @error('nama_btkl') is-invalid @enderror" 
                               value="{{ old('nama_btkl') }}" 
                               placeholder="Contoh: Pengisian Cup Jasuke" 
                               required>
                        @error('nama_btkl')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Jabatan --}}
                    <div class="col-md-6">
                        <label for="jabatan_id" class="form-label">Jabatan BTKL <span class="text-danger">*</span></label>
                        <select name="jabatan_id" 
                                id="jabatan_id" 
                                class="form-select @error('jabatan_id') is-invalid @enderror" 
                                required>
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach($jabatanBtkl as $jabatan)
                                <option value="{{ $jabatan->id }}" {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                    {{ $jabatan->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('jabatan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Satuan --}}
                    <div class="col-md-6">
                        <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
                        <select name="satuan" 
                                id="satuan" 
                                class="form-select @error('satuan') is-invalid @enderror" 
                                required>
                            <option value="">-- Pilih Satuan --</option>
                            @foreach($satuanOptions as $satuan)
                                <option value="{{ $satuan }}" {{ old('satuan') == $satuan ? 'selected' : '' }}>
                                    {{ $satuan }}
                                </option>
                            @endforeach
                        </select>
                        @error('satuan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tarif per Produk --}}
                    <div class="col-md-6">
                        <label class="form-label">Tarif BTKL per Produk <span class="text-info">(Otomatis)</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp/produk</span>
                            <input type="text" 
                                   id="tarif_per_produk_display" 
                                   class="form-control" 
                                   value="0" 
                                   readonly>
                            <input type="hidden" 
                                   name="tarif_per_produk" 
                                   id="tarif_per_produk" 
                                   value="0">
                        </div>
                        
                        <div id="tarifCalculationDisplay" class="mt-2" style="display: none;">
                            <div class="alert alert-info py-2 mb-0">
                                <i class="bi bi-calculator me-2"></i>
                                <span id="tarifCalculationText">Rp 0 x 0 pegawai = Rp 0 per produk</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Jumlah Pegawai (Hidden) --}}
                    <input type="hidden" name="jumlah_pegawai" id="jumlah_pegawai" value="1">

                    {{-- Deskripsi --}}
                    <div class="col-md-12">
                        <label for="deskripsi_proses" class="form-label">Deskripsi Proses</label>
                        <textarea name="deskripsi_proses" 
                                  id="deskripsi_proses" 
                                  class="form-control @error('deskripsi_proses') is-invalid @enderror" 
                                  rows="3" 
                                  placeholder="Jelaskan detail aktivitas proses ini">{{ old('deskripsi_proses') }}</textarea>
                    </div>

                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan Data
                            </button>
                            <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Data pegawai dari controller
const employeeData = @json($employeeData ?? []);

document.addEventListener('DOMContentLoaded', function() {
    const jabatanSelect = document.getElementById('jabatan_id');
    const tarifDisplay = document.getElementById('tarif_per_produk_display');
    const tarifCalculationDisplay = document.getElementById('tarifCalculationDisplay');
    const tarifCalculationText = document.getElementById('tarifCalculationText');

    function updateTarifCalculation(jabatan) {
        if (jabatan) {
            const jumlahPegawai = jabatan.pegawai_count || 1;
            const tarifDasar = jabatan.tarif_produk || jabatan.tarif || 0; 
            const totalTarif = tarifDasar;
            
            // Update display
            tarifDisplay.value = totalTarif.toLocaleString('id-ID');
            
            // Update hidden inputs
            document.getElementById('tarif_per_produk').value = tarifDasar;
            document.getElementById('jumlah_pegawai').value = jumlahPegawai;
            
            tarifCalculationText.textContent = 'Kalkulasi: Rp ' + tarifDasar.toLocaleString('id-ID') + ' per produk × ' + jumlahPegawai + ' pegawai = Rp ' + (tarifDasar * jumlahPegawai).toLocaleString('id-ID') + ' total BTKL';
            tarifCalculationDisplay.style.display = 'block';
        } else {
            tarifDisplay.value = '0';
            document.getElementById('tarif_per_produk').value = 0;
            document.getElementById('jumlah_pegawai').value = 1;
            tarifCalculationDisplay.style.display = 'none';
        }
    }

    jabatanSelect.addEventListener('change', function() {
        const selectedId = parseInt(this.value);
        const jabatan = employeeData.find(j => j.id === selectedId);
        updateTarifCalculation(jabatan);
    });
});
</script>
@endsection