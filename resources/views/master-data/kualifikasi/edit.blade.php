@extends('layouts.app')

@section('title', 'Edit Kualifikasi Tenaga Kerja')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-briefcase me-2"></i>Edit Kualifikasi Tenaga Kerja
        </h2>
        <a href="{{ route('master-data.kualifikasi-tenaga-kerja.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-edit me-2"></i>Edit Kualifikasi: {{ $jabatan->nama_kualifikasi }}
            </h5>
        </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body jabatan-form">
            <form method="POST" action="{{ route('master-data.kualifikasi-tenaga-kerja.update', $jabatan) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Kualifikasi</label>
                        <input type="text" name="nama_kualifikasi" class="form-control" value="{{ old('nama_kualifikasi',$jabatan->nama_kualifikasi) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="btkl" {{ old('kategori',$jabatan->kategori)==='btkl' ? 'selected' : '' }}>BTKL</option>
                            <option value="btktl" {{ old('kategori',$jabatan->kategori)==='btktl' ? 'selected' : '' }}>BTKTL</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tunjangan Jabatan (Rp)</label>
                        <input type="text" name="tunjangan" class="form-control money-input" value="{{ old('tunjangan',$jabatan->tunjangan) }}">
                        <small class="text-dark money-hint"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tunjangan Transport (Rp)</label>
                        <input type="text" name="tunjangan_transport" class="form-control money-input" value="{{ old('tunjangan_transport',$jabatan->tunjangan_transport) }}">
                        <small class="text-dark money-hint"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tunjangan Konsumsi (Rp)</label>
                        <input type="text" name="tunjangan_konsumsi" class="form-control money-input" value="{{ old('tunjangan_konsumsi',$jabatan->tunjangan_konsumsi) }}">
                        <small class="text-dark money-hint"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Asuransi (Rp)</label>
                        <input type="text" name="asuransi" class="form-control money-input" value="{{ old('asuransi',$jabatan->asuransi) }}">
                        <small class="text-dark money-hint"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gaji Pokok (Rp)</label>
                        <input type="text" name="gaji" id="input-gaji-pokok" class="form-control money-input" value="{{ old('gaji',$jabatan->gaji_pokok) }}">
                        <small class="text-dark money-hint"></small>
                        
                        <div id="produk-container" class="mt-3 d-none">
                            <label class="form-label">Produk <span class="text-danger">*</span></label>
                            <select name="produk_id" id="select-produk" class="form-select">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($produks as $p)
                                    <option value="{{ $p->id }}" {{ old('produk_id', $jabatan->produk_id) == $p->id ? 'selected' : '' }}>{{ $p->nama_produk }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="periode-container" class="mt-3 d-none">
                            <label class="form-label">Periode Target <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <select name="target_bulan" id="select-bulan" class="form-select">
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ \App\Models\TargetProduksiDetail::getMonthName($m) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select name="target_tahun" id="select-tahun" class="form-select">
                                        @foreach(range(now()->year - 2, now()->year + 2) as $y)
                                            <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div id="target-produksi-container" class="mt-3 d-none">
                            <label class="form-label text-primary mb-1" style="font-size: 0.85rem;" id="label-target-produksi"><i class="fas fa-bullseye me-1"></i>Target Produksi/Bulan (pcs)</label>
                            <input type="number" name="target_produksi" id="input-target-produksi" class="form-control" value="{{ old('target_produksi', $jabatan->target_produksi ?? 0) }}" min="0" readonly style="background-color: #e9ecef;">
                            <div id="target-warning" class="alert alert-warning d-none mt-2" style="padding: 0.5rem 0.75rem;">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <strong>Target produksi belum diatur untuk produk ini di bulan/tahun ini.</strong>
                                <br>
                                <small>Silakan buat target produksi terlebih dahulu di <a href="{{ route('master-data.target-produksi.index') }}" target="_blank" class="alert-link">Master Data > Target Produksi</a></small>
                            </div>
                            <small id="target-loading" class="text-info d-none"><i class="fas fa-spinner fa-spin"></i> Mengambil data target...</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tarif/Produk (Rp)</label>
                        <input type="text" name="tarif" id="input-tarif-produk" class="form-control money-input" value="{{ old('tarif',$jabatan->tarif) }}" readonly style="background-color: #e9ecef;">
                        <small class="text-dark money-hint"></small>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" id="btn-simpan" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('master-data.kualifikasi-tenaga-kerja.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        (function(){
            const formatID = (val) => {
                if (val === null || val === undefined) return '';
                let v = String(val).replace(/[^0-9,.]/g, '');
                if (!v) return '';
                const commaIndex = v.indexOf(',');
                const dotIndex = v.indexOf('.');

                let rawInt;
                let rawDec;

                if (commaIndex >= 0) {
                    rawInt = v.slice(0, commaIndex);
                    rawDec = v.slice(commaIndex + 1);
                } else if (dotIndex >= 0 && v.indexOf('.', dotIndex + 1) === -1) {
                    const decCandidate = v.slice(dotIndex + 1);
                    if (decCandidate.length > 0 && decCandidate.length <= 2) {
                        rawInt = v.slice(0, dotIndex);
                        rawDec = decCandidate;
                    } else {
                        rawInt = v;
                        rawDec = '';
                    }
                } else {
                    rawInt = v;
                    rawDec = '';
                }
                rawInt = rawInt.replace(/\D/g, '');
                rawDec = rawDec.replace(/\D/g, '').slice(0, 2);
                let intPart = rawInt.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                if (!rawDec) return intPart;
                if (/^0{1,2}$/.test(rawDec)) return intPart;
                return intPart + ',' + rawDec;
            };
            const toNumber = (formatted) => {
                if (!formatted) return 0;
                let s = String(formatted).trim();
                if (s.includes(',')) {
                    s = s.replace(/\./g,'').replace(',', '.');
                } else if (s.includes('.') && s.indexOf('.') === s.lastIndexOf('.')) {
                    const dec = s.split('.')[1] ?? '';
                    if (dec.length > 0 && dec.length <= 2) {
                        // keep dot as decimal separator
                    } else {
                        s = s.replace(/\./g, '');
                    }
                } else {
                    s = s.replace(/\./g, '');
                }
                let n = parseFloat(s);
                return isNaN(n) ? 0 : n;
            };
            const compactID = (n) => {
                const u = [
                    {v:1e12, s:' triliun'},
                    {v:1e9, s:' miliar'},
                    {v:1e6, s:' juta'},
                    {v:1e3, s:' ribu'},
                ];
                for (const it of u) {
                    if (n >= it.v) {
                        const val = (n / it.v).toFixed(2).replace(/\.00$/,'');
                        return val + it.s;
                    }
                }
                return '';
            };
            const inputs = document.querySelectorAll('.money-input');
            inputs.forEach((inp) => {
                inp.value = formatID(inp.value);
                const hint = inp.parentElement.querySelector('.money-hint');
                const updateHint = () => {
                    const num = toNumber(inp.value);
                    const text = compactID(num);
                    if (hint) hint.textContent = text ? '(' + text + ')' : '';
                };
                updateHint();
                inp.addEventListener('input', () => {
                    inp.value = formatID(inp.value);
                    updateHint();
                });
                inp.addEventListener('blur', () => { inp.value = formatID(inp.value); updateHint(); });
            });

            // Auto-hitung Tarif/Produk
            const selectKategori = document.querySelector('select[name="kategori"]');
            const inputGaji = document.getElementById('input-gaji-pokok');
            const inputTarif = document.getElementById('input-tarif-produk');
            const inputTarget = document.getElementById('input-target-produksi');
            const labelTarget = document.getElementById('label-target-produksi');
            const containerTarget = document.getElementById('target-produksi-container');
            const containerProduk = document.getElementById('produk-container');
            const containerPeriode = document.getElementById('periode-container');
            const selectProduk = document.getElementById('select-produk');
            const selectBulan = document.getElementById('select-bulan');
            const selectTahun = document.getElementById('select-tahun');
            const btnSimpan = document.getElementById('btn-simpan');
            const targetWarning = document.getElementById('target-warning');
            const targetLoading = document.getElementById('target-loading');
            
            const calculateTarif = () => {
                if (selectKategori.value !== 'btkl') return;
                
                const gaji = toNumber(inputGaji.value);
                const target = parseInt(inputTarget.value) || 0;
                
                if (gaji > 0 && target > 0) {
                    const tarif = Math.round(gaji / target);
                    inputTarif.value = formatID(tarif);
                } else {
                    inputTarif.value = formatID(0);
                }
                inputTarif.dispatchEvent(new Event('input'));
            };
            
            const fetchTargetProduksi = async () => {
                const produkId = selectProduk.value;
                const bulan = selectBulan.value;
                const tahun = selectTahun.value;
                const bulanText = selectBulan.options[selectBulan.selectedIndex].text;
                
                labelTarget.innerHTML = `<i class="fas fa-bullseye me-1"></i>Target Produksi/Bulan (pcs) - Periode: ${bulanText} ${tahun}`;

                if (!produkId) {
                    inputTarget.value = 0;
                    calculateTarif();
                    targetWarning.classList.add('d-none');
                    btnSimpan.disabled = true;
                    return;
                }
                
                btnSimpan.disabled = true;
                targetLoading.classList.remove('d-none');
                targetWarning.classList.add('d-none');
                
                try {
                    const response = await fetch(`/master-data/api/kualifikasi/target-produksi/${produkId}?bulan=${bulan}&tahun=${tahun}`);
                    const result = await response.json();
                    
                    if (result.success && result.target > 0) {
                        inputTarget.value = result.target;
                        targetWarning.classList.add('d-none');
                        btnSimpan.disabled = false;
                    } else {
                        inputTarget.value = 0;
                        targetWarning.classList.remove('d-none');
                        btnSimpan.disabled = true;
                    }
                    calculateTarif();
                } catch (error) {
                    console.error('Error fetching target produksi:', error);
                    inputTarget.value = 0;
                    targetWarning.classList.remove('d-none');
                    btnSimpan.disabled = true;
                    calculateTarif();
                } finally {
                    targetLoading.classList.add('d-none');
                }
            };

            const toggleAutoHitung = () => {
                if (selectKategori.value === 'btkl') {
                    containerProduk.classList.remove('d-none');
                    selectProduk.required = true;
                    containerPeriode.classList.remove('d-none');
                    containerTarget.classList.remove('d-none');
                    
                    if(selectProduk.value && inputTarget.value == 0) {
                        fetchTargetProduksi();
                    } else if (!selectProduk.value) {
                        btnSimpan.disabled = true;
                    } else if (inputTarget.value == 0) {
                        targetWarning.classList.remove('d-none');
                        btnSimpan.disabled = true;
                    }
                } else {
                    containerProduk.classList.add('d-none');
                    selectProduk.required = false;
                    containerPeriode.classList.add('d-none');
                    containerTarget.classList.add('d-none');
                    inputTarget.required = false;
                    inputTarget.value = 0;
                    inputTarif.value = formatID(0);
                    inputTarif.dispatchEvent(new Event('input'));
                    btnSimpan.disabled = false;
                }
            };

            selectKategori.addEventListener('change', toggleAutoHitung);
            selectProduk.addEventListener('change', fetchTargetProduksi);
            selectBulan.addEventListener('change', fetchTargetProduksi);
            selectTahun.addEventListener('change', fetchTargetProduksi);
            inputGaji.addEventListener('input', calculateTarif);
            
            // Initial check
            toggleAutoHitung();
        })();
    </script>
</div>
@endsection
