@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">Tambah Pembayaran Beban</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('transaksi.pembayaran-beban.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('transaksi.pembayaran-beban.store') }}" method="POST">
                @csrf
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('tanggal') is-invalid @enderror" 
                               id="tanggal" name="tanggal" 
                               value="{{ old('tanggal', date('Y-m-d')) }}" required>
                        @error('tanggal')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="akun_kas_id">Akun Kas <span class="text-danger">*</span></label>
                        <select class="form-control @error('akun_kas_id') is-invalid @enderror" 
                                id="akun_kas_id" name="akun_kas_id" required>
                            <option value="">Pilih Akun Kas</option>
                            @foreach($akunKas as $kas)
                                <option value="{{ $kas->id }}" 
                                    {{ old('akun_kas_id') == $kas->id ? 'selected' : '' }}>
                                    {{ $kas->kode }} - {{ $kas->nama }} ({{ format_rupiah($kas->saldo) }})
                                </option>
                            @endforeach
                        </select>
                        @error('akun_kas_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="akun_beban_id">Akun Beban <span class="text-danger">*</span></label>
                        <select class="form-control @error('akun_beban_id') is-invalid @enderror" 
                                id="akun_beban_id" name="akun_beban_id" required>
                            <option value="">Pilih Akun Beban</option>
                            @foreach($akunBeban as $beban)
                                <option value="{{ $beban->id }}" 
                                    {{ old('akun_beban_id') == $beban->id ? 'selected' : '' }}>
                                    {{ $beban->kode }} - {{ $beban->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('akun_beban_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="jumlah">Jumlah <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" class="form-control @error('jumlah') is-invalid @enderror" 
                                   id="jumlah" name="jumlah" value="{{ old('jumlah') }}" 
                                   min="1" required>
                            @error('jumlah')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                              id="keterangan" name="keterangan" rows="2" required>{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="catatan">Catatan</label>
                    <textarea class="form-control @error('catatan') is-invalid @enderror" 
                              id="catatan" name="catatan" rows="2">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Format input number
        $('#jumlah').on('input', function() {
            let value = $(this).val();
            if (value < 1) {
                $(this).val(1);
            }
        });
    });
</script>
@endpush
