@extends('layouts.app')

@section('title', 'Tambah Pelunasan Utang')

@push('style')
    <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Tambah Pelunasan Utang</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('transaksi.pelunasan-utang.index') }}">Pelunasan Utang</a></div>
                    <div class="breadcrumb-item">Tambah Data</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <form action="{{ route('transaksi.pelunasan-utang.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label>Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal') is-invalid @enderror" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                @error('tanggal')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Pembelian <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('pembelian_id') is-invalid @enderror" name="pembelian_id" required>
                                    <option value="">Pilih Pembelian</option>
                                    @foreach($pembayarans as $pembayaran)
                                        <option value="{{ $pembayaran->id }}" data-sisa="{{ $pembayaran->sisa_pembayaran }}" {{ old('pembelian_id') == $pembayaran->id ? 'selected' : '' }}>
                                            {{ $pembayaran->kode_pembelian }} - {{ $pembayaran->vendor->nama }} (Sisa: {{ format_rupiah($pembayaran->sisa_pembayaran) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('pembelian_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Akun Kas <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('akun_kas_id') is-invalid @enderror" name="akun_kas_id" required>
                                    <option value="">Pilih Akun Kas</option>
                                    @foreach($akunKas as $akun)
                                        <option value="{{ $akun->id }}" {{ old('akun_kas_id') == $akun->id ? 'selected' : '' }}>
                                            {{ $akun->kode }} - {{ $akun->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('akun_kas_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Jumlah <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            Rp
                                        </div>
                                    </div>
                                    <input type="text" class="form-control currency @error('jumlah') is-invalid @enderror" name="jumlah" id="jumlah" value="{{ old('jumlah') }}" required>
                                    @error('jumlah')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <small class="text-muted">Sisa utang: <span id="sisa-utang">Rp 0</span></small>
                            </div>

                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('transaksi.pelunasan-utang.index') }}" class="btn btn-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('library/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('library/cleave.js/dist/cleave.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize select2
            $('.select2').select2({
                placeholder: 'Pilih salah satu',
                allowClear: true,
                width: '100%'
            });

            // Initialize currency format
            new Cleave('.currency', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.',
                numeralIntegerScale: 15,
                numeralDecimalScale: 0,
                numeralPositiveOnly: true,
                onValueChanged: function(e) {
                    // Auto format the value
                    e.target.value = e.target.rawValue ? e.target.rawValue : '';
                }
            });

            // Update remaining debt when pembelian is selected
            $('select[name="pembelian_id"]').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const sisaUtang = selectedOption.data('sisa') || 0;
                
                // Format and display remaining debt
                const formatter = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0,
                });
                
                $('#sisa-utang').text(formatter.format(sisaUtang));
                
                // Auto fill the amount with remaining debt
                if (sisaUtang > 0) {
                    $('input[name="jumlah"]').val(sisaUtang).trigger('input');
                }
            });

            // Trigger change event on page load if there's a selected pembelian
            @if(old('pembelian_id'))
                $('select[name="pembelian_id"]').trigger('change');
            @endif
        });
    </script>
@endpush
