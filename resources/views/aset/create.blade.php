@extends('layouts.app')

@section('title', 'Tambah Aset')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Tambah Aset</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('aset.store') }}" method="POST" id="asetForm">
                @csrf
                <div class="row g-3">
                    <!-- Basic Information -->
                    <div class="col-12 mb-2"><h6 class="border-bottom pb-2">Informasi Dasar</h6></div>

                    <!-- Jenis Aset -->
                    <div class="col-md-4">
                        <label class="form-label">Jenis Aset *</label>
                        <select name="jenis_aset" id="jenis_aset" class="form-select" required>
                            <option value="">-- Pilih Jenis Aset --</option>
                            <option value="aset-tetap" {{ old('jenis_aset') == 'aset-tetap' ? 'selected' : '' }}>Aset Tetap</option>
                            <option value="aset-tidak-tetap" {{ old('jenis_aset') == 'aset-tidak-tetap' ? 'selected' : '' }}>Aset Tidak Tetap</option>
                            <option value="aset-tidak-berwujud" {{ old('jenis_aset') == 'aset-tidak-berwujud' ? 'selected' : '' }}>Aset Tidak Berwujud</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kategori Aset *</label>
                        <select name="kategori_aset_id" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($jenisAsets as $jenis)
                                <optgroup label="{{ $jenis->nama }}">
                                    @foreach($jenis->kategories as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('kategori_aset_id') == $kategori->id ? 'selected' : '' }}>{{ $kategori->nama_kategori }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nama Aset *</label>
                        <input type="text" name="nama_aset" class="form-control" required value="{{ old('nama_aset') }}">
                    </div>

                    <!-- NEW FIELDS: Jenis Perolehan & Sumber Dana -->
                    <div class="col-12 mb-2 mt-4"><h6 class="border-bottom pb-2">Jenis dan Sumber Perolehan</h6></div>

                    <div class="col-md-4">
                        <label class="form-label">Jenis Perolehan *</label>
                        <select name="jenis_perolehan" id="jenis_perolehan" class="form-select" required>
                            <option value="">-- Pilih Jenis Perolehan --</option>
                            <option value="pembelian_baru" {{ old('jenis_perolehan') == 'pembelian_baru' ? 'selected' : '' }}>Pembelian Baru</option>
                            <option value="saldo_awal" {{ old('jenis_perolehan') == 'saldo_awal' ? 'selected' : '' }}>Saldo Awal (Aset Lama)</option>
                        </select>
                        <div class="form-text">Pembelian Baru: Aset baru yang dibeli. Saldo Awal: Aset yang sudah ada di tahun sebelumnya.</div>
                    </div>

                    <div class="col-md-4" id="container_sumber_dana" style="display: none;">
                        <label class="form-label" id="label_sumber_dana">Sumber Dana Perolehan *</label>
                        <select name="sumber_dana_coa_id" id="sumber_dana_coa_id" class="form-select">
                            <option value="">-- Pilih Sumber Dana --</option>
                            @foreach($coaAsets as $coa)
                                <option value="{{ $coa->id }}" {{ old('sumber_dana_coa_id') == $coa->id ? 'selected' : '' }}>{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Pilih Kas, Bank, Utang, atau sumber dana lainnya.</div>
                    </div>

                    <!-- Perolehan -->
                    <div class="col-12 mb-2 mt-4"><h6 class="border-bottom pb-2">Data Perolehan</h6></div>

                    <div class="col-md-4">
                        <label class="form-label">Harga Perolehan (Rp) *</label>
                        <input type="number" name="harga_perolehan" id="harga_perolehan" class="form-control" min="0" required value="{{ old('harga_perolehan', 0) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Biaya Perolehan (Rp)</label>
                        <input type="number" name="biaya_perolehan" id="biaya_perolehan" class="form-control" min="0" value="{{ old('biaya_perolehan', 0) }}">
                        <div class="form-text">Biaya tambahan seperti ongkir, instalasi</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Total Perolehan (Rp)</label>
                        <input type="number" id="total_perolehan" class="form-control bg-light" readonly value="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Beli *</label>
                        <input type="date" name="tanggal_beli" class="form-control" required value="{{ old('tanggal_beli', date('Y-m-d')) }}">
                    </div>

                    <div class="col-md-4" id="tanggal_mulai_container">
                        <label class="form-label">Tanggal Mulai Penyusutan/Amortisasi *</label>
                        <input type="date" name="tanggal_akuisisi" id="tanggal_akuisisi" class="form-control" value="{{ old('tanggal_akuisisi', date('Y-m-d')) }}">
                    </div>

                    <!-- Group 2: Warning for Aset Tidak Tetap -->
                    <div class="col-12 mt-3" id="warning_aset_tidak_tetap" style="display: none;">
                        <div class="alert alert-warning py-2 mb-0">
                            <i class="fas fa-info-circle me-1"></i> Aset Tidak Tetap dicatat sebagai biaya langsung. Tidak ada penyusutan/amortisasi.
                        </div>
                    </div>

                    <!-- Group 1: Penyusutan/Amortisasi -->
                    <div class="col-12" id="group_penyusutan" style="display: none;">
                        <div class="row g-3 mt-1">
                            <div class="col-12 mb-2"><h6 class="border-bottom pb-2" id="label_section_penyusutan">Data Penyusutan/Amortisasi</h6></div>
                            
                            <div class="col-md-4">
                                <label class="form-label" id="label_metode">Metode *</label>
                                <select name="metode_penyusutan" id="metode_penyusutan" class="form-select">
                                    <option value="">-- Pilih Metode --</option>
                                    <option value="garis_lurus">Garis Lurus</option>
                                    <option value="saldo_menurun" class="opt-tetap">Saldo Menurun</option>
                                    <option value="sum_of_years_digits" class="opt-tetap">Jumlah Angka Tahun</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" id="label_umur">Umur Manfaat/Ekonomis (tahun) *</label>
                                <input type="number" name="umur_manfaat" id="umur_manfaat" class="form-control" min="1" value="{{ old('umur_manfaat') }}">
                            </div>

                            <div class="col-md-4" id="container_residu">
                                <label class="form-label">Nilai Residu/Sisa (Rp)</label>
                                <input type="number" name="nilai_residu" id="nilai_residu" class="form-control" min="0" value="{{ old('nilai_residu', 0) }}">
                                <div class="form-text">Nilai sisa di akhir umur manfaat</div>
                            </div>
                        </div>

                        <!-- Calculation Box -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary" id="calc_title">Hasil Perhitungan</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <small class="text-muted d-block" id="calc_label_tahun">Per Tahun</small>
                                                <strong class="fs-5">Rp <span id="calc_per_tahun">0</span></strong>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block" id="calc_label_bulan">Per Bulan</small>
                                                <strong class="fs-5">Rp <span id="calc_per_bulan">0</span></strong>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Nilai Buku (Akhir Tahun 1)</small>
                                                <strong class="fs-5">Rp <span id="calc_nilai_buku">0</span></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Integration COA -->
                    <div class="col-12 mb-2 mt-4"><h6 class="border-bottom pb-2">Integrasi Akuntansi (COA)</h6></div>

                    <div class="col-md-4">
                        <label class="form-label">Akun Aset / Biaya *</label>
                        <select name="asset_coa_id" class="form-select" required>
                            <option value="">-- Pilih Akun --</option>
                            @foreach($coaAsets as $coa)
                                <option value="{{ $coa->id }}" {{ old('asset_coa_id') == $coa->id ? 'selected' : '' }}>{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 coa-penyusutan">
                        <label class="form-label" id="label_coa_akumulasi">Akun Akumulasi *</label>
                        <select name="accum_depr_coa_id" id="accum_depr_coa_id" class="form-select">
                            <option value="">-- Pilih Akun --</option>
                            @foreach($coaAkumulasi as $coa)
                                <option value="{{ $coa->id }}" {{ old('accum_depr_coa_id') == $coa->id ? 'selected' : '' }}>{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 coa-penyusutan">
                        <label class="form-label" id="label_coa_beban">Akun Beban *</label>
                        <select name="expense_coa_id" id="expense_coa_id" class="form-select">
                            <option value="">-- Pilih Akun --</option>
                            @foreach($coaBeban as $coa)
                                <option value="{{ $coa->id }}" {{ old('expense_coa_id') == $coa->id ? 'selected' : '' }}>{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Keterangan Tambahan</label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Simpan Aset</button>
                    <a class="btn btn-secondary" href="{{ route('master-data.aset.index') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const jenisAset = document.getElementById('jenis_aset');
    const hargaPerolehan = document.getElementById('harga_perolehan');
    const biayaPerolehan = document.getElementById('biaya_perolehan');
    const totalPerolehan = document.getElementById('total_perolehan');
    
    const groupPenyusutan = document.getElementById('group_penyusutan');
    const warningTidakTetap = document.getElementById('warning_aset_tidak_tetap');
    const containerResidu = document.getElementById('container_residu');
    
    const metodePenyusutan = document.getElementById('metode_penyusutan');
    const umurManfaat = document.getElementById('umur_manfaat');
    const nilaiResidu = document.getElementById('nilai_residu');
    
    // Labels
    const labelSection = document.getElementById('label_section_penyusutan');
    const labelMetode = document.getElementById('label_metode');
    const labelUmur = document.getElementById('label_umur');
    const calcTitle = document.getElementById('calc_title');
    const calcLabelTahun = document.getElementById('calc_label_tahun');
    const calcLabelBulan = document.getElementById('calc_label_bulan');
    
    // Calculation outputs
    const calcPerTahun = document.getElementById('calc_per_tahun');
    const calcPerBulan = document.getElementById('calc_per_bulan');
    const calcNilaiBuku = document.getElementById('calc_nilai_buku');
    
    // COA fields
    const coaElements = document.querySelectorAll('.coa-penyusutan');
    const accumDeprCoa = document.getElementById('accum_depr_coa_id');
    const expenseCoa = document.getElementById('expense_coa_id');
    const labelCoaAkumulasi = document.getElementById('label_coa_akumulasi');
    const labelCoaBeban = document.getElementById('label_coa_beban');

    // Tanggal Mulai
    const tglMulaiContainer = document.getElementById('tanggal_mulai_container');
    const tglAkuisisi = document.getElementById('tanggal_akuisisi');

    function formatNumber(num) {
        return Math.round(num).toLocaleString('id-ID');
    }

    function calculateDepreciation() {
        const hrg = parseFloat(hargaPerolehan.value) || 0;
        const bya = parseFloat(biayaPerolehan.value) || 0;
        const total = hrg + bya;
        totalPerolehan.value = total;

        const jenis = jenisAset.value;
        const metode = metodePenyusutan.value;
        const umur = parseFloat(umurManfaat.value) || 0;
        const residu = parseFloat(nilaiResidu.value) || 0;

        let perTahun = 0;
        
        if (jenis === 'aset-tetap' && metode && umur > 0) {
            const depreciableAmount = total - residu;
            if (metode === 'garis_lurus') {
                perTahun = depreciableAmount / umur;
            } else if (metode === 'saldo_menurun') {
                perTahun = total * (2 / umur);
            } else if (metode === 'sum_of_years_digits') {
                const sumOfYears = (umur * (umur + 1)) / 2;
                perTahun = (depreciableAmount * umur) / sumOfYears;
            }
        } else if (jenis === 'aset-tidak-berwujud' && metode && umur > 0) {
            // Amortization usually straight-line and no residual value
            const amortizableAmount = total; 
            perTahun = amortizableAmount / umur;
        }

        const perBulan = perTahun / 12;
        const nBukuAkhirTahun1 = Math.max(0, total - perTahun);

        calcPerTahun.innerText = formatNumber(perTahun);
        calcPerBulan.innerText = formatNumber(perBulan);
        calcNilaiBuku.innerText = formatNumber(nBukuAkhirTahun1);
    }

    function handleJenisAsetChange() {
        const jenis = jenisAset.value;

        if (jenis === 'aset-tetap' || jenis === 'aset-tidak-berwujud') {
            groupPenyusutan.style.display = 'block';
            warningTidakTetap.style.display = 'none';
            tglMulaiContainer.style.display = 'block';
            tglAkuisisi.required = true;
            
            // Require methods and umur
            metodePenyusutan.required = true;
            umurManfaat.required = true;

            // Show COA penyusutan
            coaElements.forEach(el => el.style.display = 'block');
            
            if (jenis === 'aset-tetap') {
                labelSection.innerText = 'Data Penyusutan (Aset Tetap)';
                labelMetode.innerText = 'Metode Penyusutan *';
                labelUmur.innerText = 'Umur Manfaat (tahun) *';
                containerResidu.style.display = 'block';
                
                calcTitle.innerText = 'Hasil Perhitungan Penyusutan';
                calcLabelTahun.innerText = 'Penyusutan Per Tahun';
                calcLabelBulan.innerText = 'Penyusutan Per Bulan';
                
                labelCoaAkumulasi.innerText = 'Akun Akumulasi Penyusutan *';
                labelCoaBeban.innerText = 'Akun Beban Penyusutan *';
                
                // Show options specific to Aset Tetap
                document.querySelectorAll('.opt-tetap').forEach(opt => opt.style.display = 'block');
                
            } else if (jenis === 'aset-tidak-berwujud') {
                labelSection.innerText = 'Data Amortisasi (Aset Tidak Berwujud)';
                labelMetode.innerText = 'Metode Amortisasi *';
                labelUmur.innerText = 'Umur Ekonomis (tahun) *';
                
                // Hide residu
                containerResidu.style.display = 'none';
                nilaiResidu.value = 0;
                
                calcTitle.innerText = 'Hasil Perhitungan Amortisasi';
                calcLabelTahun.innerText = 'Amortisasi Per Tahun';
                calcLabelBulan.innerText = 'Amortisasi Per Bulan';
                
                labelCoaAkumulasi.innerText = 'Akun Akumulasi Amortisasi *';
                labelCoaBeban.innerText = 'Akun Beban Amortisasi *';
                
                // Hide saldo menurun for amortisasi
                document.querySelectorAll('.opt-tetap').forEach(opt => opt.style.display = 'none');
                if (metodePenyusutan.value !== 'garis_lurus') {
                    metodePenyusutan.value = 'garis_lurus';
                }
            }
        } else if (jenis === 'aset-tidak-tetap') {
            groupPenyusutan.style.display = 'none';
            warningTidakTetap.style.display = 'block';
            tglMulaiContainer.style.display = 'none';
            tglAkuisisi.required = false;
            
            // Unrequire methods and umur
            metodePenyusutan.required = false;
            umurManfaat.required = false;
            metodePenyusutan.value = '';
            umurManfaat.value = '';
            nilaiResidu.value = 0;

            // Hide COA penyusutan
            coaElements.forEach(el => el.style.display = 'none');
            accumDeprCoa.value = '';
            expenseCoa.value = '';
        } else {
            // Nothing selected
            groupPenyusutan.style.display = 'none';
            warningTidakTetap.style.display = 'none';
            tglMulaiContainer.style.display = 'none';
            metodePenyusutan.required = false;
            umurManfaat.required = false;
            coaElements.forEach(el => el.style.display = 'none');
        }

        calculateDepreciation();
    }

    // Event Listeners
    jenisAset.addEventListener('change', handleJenisAsetChange);
    hargaPerolehan.addEventListener('input', calculateDepreciation);
    biayaPerolehan.addEventListener('input', calculateDepreciation);
    metodePenyusutan.addEventListener('change', calculateDepreciation);
    umurManfaat.addEventListener('input', calculateDepreciation);
    nilaiResidu.addEventListener('input', calculateDepreciation);

    // Initial calculation and logic setup
    handleJenisAsetChange();

    // Form Validation
    document.getElementById('asetForm').addEventListener('submit', function(e) {
        if (jenisAset.value === 'aset-tetap') {
            const hrg = parseFloat(hargaPerolehan.value) || 0;
            const bya = parseFloat(biayaPerolehan.value) || 0;
            const total = hrg + bya;
            const residu = parseFloat(nilaiResidu.value) || 0;

            if (total < residu) {
                e.preventDefault();
                alert('Nilai Residu tidak boleh lebih dari Total Perolehan!');
                return false;
            }
        }
        
        if ((jenisAset.value === 'aset-tetap' || jenisAset.value === 'aset-tidak-berwujud')) {
            if (!metodePenyusutan.value) {
                e.preventDefault();
                alert('Pilih metode penyusutan/amortisasi!');
                return false;
            }
            if (!accumDeprCoa.value || !expenseCoa.value) {
                e.preventDefault();
                alert('Pilih Akun Akumulasi dan Akun Beban!');
                return false;
            }
        }
    });
});
</script>
@endsection
