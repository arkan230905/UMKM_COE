@extends('layouts.app')

@section('content')
<div class="container text-light">
    <h2 class="mb-4 text-white">Tambah Aset Baru</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('master-data.aset.store') }}" method="POST" id="asetForm">
                @csrf
                
                <div class="mb-3">
                    <label for="kode_aset" class="form-label text-white">Kode Aset</label>
                    <input type="text" class="form-control bg-dark text-white" id="kode_aset" name="kode_aset" value="{{ $kodeAset }}" readonly>
                    <small class="text-muted">Kode aset akan otomatis terisi</small>
                </div>
                
                <div class="mb-3">
                    <label for="nama_aset" class="form-label text-white">Nama Aset</label>
                    <input type="text" class="form-control bg-dark text-white @error('nama_aset') is-invalid @enderror" id="nama_aset" name="nama_aset" value="{{ old('nama_aset') }}" required>
                    @error('nama_aset')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jenis_aset" class="form-label text-white">Jenis Aset</label>
                    <select class="form-select bg-dark text-white @error('jenis_aset') is-invalid @enderror" id="jenis_aset" name="jenis_aset" required onchange="updateKategoriOptions()">
                        <option value="" disabled {{ old('jenis_aset') ? '' : 'selected' }}>-- Pilih Jenis Aset --</option>
                        <option value="Aset Tetap" {{ old('jenis_aset') == 'Aset Tetap' ? 'selected' : '' }}>Aset Tetap</option>
                        <option value="Aset Tidak Tetap" {{ old('jenis_aset') == 'Aset Tidak Tetap' ? 'selected' : '' }}>Aset Tidak Tetap</option>
                        <option value="Aset Tak Berwujud" {{ old('jenis_aset') == 'Aset Tak Berwujud' ? 'selected' : '' }}>Aset Tak Berwujud</option>
                    </select>
                    @error('jenis_aset')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="kategori" class="form-label text-white">Kategori</label>
                    <select class="form-select bg-dark text-white @error('kategori') is-invalid @enderror" id="kategori" name="kategori" required>
                        <option value="" disabled selected>-- Pilih Jenis Aset terlebih dahulu --</option>
                    </select>
                    @error('kategori')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <script>
                    const kategoriOptions = {
                        'Aset Tetap': [
                            'Kendaraan Operasional',
                            'Peralatan Kantor',
                            'Peralatan Produksi',
                            'Peralatan Medis',
                            'Peralatan Laboratorium',
                            'Peralatan Konstruksi',
                            'Peralatan IT & Elektronik',
                            'Furniture & Perlengkapan',
                            'Peralatan Listrik',
                            'Peralatan Mekanik',
                            'Alat Berat',
                            'Mesin Pabrik',
                            'Gedung & Bangunan',
                            'Tanah',
                            'Rumah Dinas',
                            'Kapal',
                            'Pesawat Terbang',
                            'Kereta Api',
                            'Peralatan Telekomunikasi',
                            'Peralatan Keamanan',
                            'Peralatan Dapur',
                            'Peralatan Kebersihan',
                            'Peralatan Olahraga',
                            'Peralatan Musik',
                            'Peralatan Fotografi',
                            'Peralatan Studio',
                            'Peralatan Bengkel',
                            'Peralatan Pertanian',
                            'Peralatan Perkebunan',
                            'Peralatan Peternakan',
                            'Peralatan Perikanan',
                            'Peralatan Kehutanan',
                            'Peralatan Tambang',
                            'Peralatan Makanan & Minuman',
                            'Peralatan Kesehatan & Keselamatan'
                        ],
                        'Aset Tidak Tetap': [
                            'Persediaan Barang Dagang',
                            'Bahan Baku',
                            'Barang Dalam Proses',
                            'Barang Jadi',
                            'Konsinyasi',
                            'Barang Promosi',
                            'Perlengkapan Kantor',
                            'Perlengkapan Kebersihan',
                            'Perlengkapan Dapur',
                            'Perlengkapan Maintenance',
                            'Bahan Habis Pakai',
                            'Bahan Kimia',
                            'Suku Cadang',
                            'Kemasan',
                            'Barang Cetakan',
                            'Alat Tulis Kantor',
                            'Bahan Bakar & Pelumas',
                            'Barang Konsinyasi',
                            'Barang Sampel',
                            'Barang Lain-lain'
                        ],
                        'Aset Tak Berwujud': [
                            'Hak Cipta (Copyright)',
                            'Merek Dagang (Trademark)',
                            'Paten',
                            'Hak Desain Industri',
                            'Rahasia Dagang',
                            'Lisensi',
                            'Franchise',
                            'Hak Guna Bangunan',
                            'Hak Pengusahaan Hutan',
                            'Hak Pengusahaan Pertambangan',
                            'Hak Pengusahaan Perairan',
                            'Hak Pengusahaan Perkebunan',
                            'Hak Pengusahaan Perikanan',
                            'Hak Pengusahaan Peternakan',
                            'Hak Pengusahaan Pariwisata',
                            'Hak Pengusahaan Jasa',
                            'Hak Pengusahaan Lainnya',
                            'Goodwill',
                            'Biaya Pendirian',
                            'Biaya Penelitian & Pengembangan',
                            'Biaya Pengembangan Software',
                            'Biaya Lisensi Software',
                            'Biaya Iklan & Promosi',
                            'Biaya Pelatihan',
                            'Biaya Perizinan',
                            'Biaya Operasional Yang Ditangguhkan',
                            'Biaya Pra Operasi',
                            'Biaya Pendahuluan',
                            'Biaya Pengalihan Hak'
                        ]
                    };

                    function updateKategoriOptions() {
                        const jenisAset = document.getElementById('jenis_aset').value;
                        const kategoriSelect = document.getElementById('kategori');
                        
                        // Clear existing options
                        kategoriSelect.innerHTML = '<option value="" disabled selected>-- Pilih Kategori --</option>';
                        
                        if (jenisAset && kategoriOptions[jenisAset]) {
                            // Add options based on selected jenis aset
                            kategoriOptions[jenisAset].forEach(kategori => {
                                const option = document.createElement('option');
                                option.value = kategori;
                                option.textContent = kategori;
                                if (kategori === '{{ old('kategori') }}') {
                                    option.selected = true;
                                }
                                kategoriSelect.appendChild(option);
                            });
                            
                            // Add custom option
                            const customOption = document.createElement('option');
                            customOption.value = 'Lainnya';
                            customOption.textContent = '+ Kategori Lainnya';
                            kategoriSelect.appendChild(customOption);
                        }
                        
                        // Trigger change event to show/hide custom input if needed
                        updateCustomKategoriInput();
                    }
                    
                    function updateCustomKategoriInput() {
                        const kategoriSelect = document.getElementById('kategori');
                        const customInputContainer = document.getElementById('customKategoriContainer');
                        
                        if (kategoriSelect.value === 'Lainnya') {
                            if (!customInputContainer) {
                                const container = document.createElement('div');
                                container.id = 'customKategoriContainer';
                                container.className = 'mt-2';
                                container.innerHTML = `
                                    <input type="text" 
                                           class="form-control bg-dark text-white" 
                                           id="custom_kategori" 
                                           name="kategori" 
                                           placeholder="Masukkan kategori baru" 
                                           value="{{ old('kategori') }}"
                                           required>
                                `;
                                kategoriSelect.parentNode.insertBefore(container, kategoriSelect.nextSibling);
                            } else {
                                customInputContainer.style.display = 'block';
                            }
                            kategoriSelect.removeAttribute('name');
                        } else {
                            if (customInputContainer) {
                                customInputContainer.style.display = 'none';
                            }
                            kategoriSelect.setAttribute('name', 'kategori');
                        }
                    }
                    
                    // Initialize on page load
                    document.addEventListener('DOMContentLoaded', function() {
                        const jenisAset = document.getElementById('jenis_aset');
                        if ('{{ old('jenis_aset') }}') {
                            jenisAset.value = '{{ old('jenis_aset') }}';
                            updateKategoriOptions();
                        }
                        
                        // Add event listener for kategori select
                        document.getElementById('kategori').addEventListener('change', updateCustomKategoriInput);
                    });
                </script>

                <div class="mb-3">
                    <label for="harga_perolehan" class="form-label text-white">Harga Perolehan (Rp)</label>
                    <input type="number" class="form-control bg-dark text-white @error('harga_perolehan') is-invalid @enderror" id="harga_perolehan" name="harga_perolehan" value="{{ old('harga_perolehan') }}" required oninput="hitungResidu()">
                    @error('harga_perolehan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="acquisition_cost" class="form-label text-white">Biaya Perolehan (Rp)</label>
                    <input type="number" class="form-control bg-dark text-white @error('acquisition_cost') is-invalid @enderror" id="acquisition_cost" name="acquisition_cost" value="{{ old('acquisition_cost', old('harga', 0)) }}" oninput="hitungResidu()">
                    @error('acquisition_cost')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="umur_manfaat" class="form-label text-white">Umur Manfaat (tahun)</label>
                    <input type="number" class="form-control bg-dark text-white @error('umur_manfaat') is-invalid @enderror" id="umur_manfaat" name="umur_manfaat" value="{{ old('umur_manfaat', 4) }}" min="1" oninput="hitungResidu()">
                    @error('umur_manfaat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <script>
                // Inisialisasi saat halaman dimuat
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('=== Halaman dimuat ===');
                    
                    // Set nilai default acquisition cost sama dengan harga jika kosong
                    const hargaInput = document.getElementById('harga');
                    const acquisitionCostInput = document.getElementById('acquisition_cost');
                    
                    if (hargaInput && acquisitionCostInput && !acquisitionCostInput.value) {
                        acquisitionCostInput.value = hargaInput.value || 0;
                    }
                    
                    // Jalankan perhitungan awal
                    hitungResidu();
                    
                    console.log('=== Inisialisasi selesai ===');
                });

                // Fungsi untuk menghitung nilai residu dan penyusutan
                function hitungResidu() {
                    try {
                        const harga = parseFloat(document.getElementById('harga_perolehan').value) || 0;
                        const acquisitionCost = harga;
                        const usefulLifeYears = parseFloat(document.getElementById('umur_manfaat').value) || 1;
                        
                        // Hitung nilai residu (5% dari harga perolehan)
                        const residualValue = acquisitionCost * 0.05;
                        const nilaiDisusutkan = acquisitionCost - residualValue;
                        const penyusutanTahunan = nilaiDisusutkan / usefulLifeYears;
                        const penyusutanBulanan = penyusutanTahunan / 12;
                        
                        // Update tampilan
                        document.getElementById('harga_perolehan_display').textContent = 'Rp ' + formatRupiah(acquisitionCost);
                        document.getElementById('nilai_residu_display').textContent = 'Rp ' + formatRupiah(residualValue);
                        document.getElementById('nilai_disusutkan_display').textContent = 'Rp ' + formatRupiah(nilaiDisusutkan);
                        document.getElementById('umur_manfaat_display').textContent = usefulLifeYears;
                        document.getElementById('penyusutan_tahunan_display').textContent = 'Rp ' + formatRupiah(penyusutanTahunan) + ' /tahun';
                        document.getElementById('penyusutan_bulanan_display').textContent = 'Rp ' + formatRupiah(penyusutanBulanan) + ' /bulan';
                        
                        // Update nilai hidden field
                        document.getElementById('residual_value').value = residualValue;
                        
                    } catch (error) {
                        console.error('Error in hitungResidu:', error);
                    }
                }
                
                // Fungsi untuk format rupiah
                function formatRupiah(angka) {
                    return new Intl.NumberFormat('id-ID').format(angka);
                }
                </script>

                <!-- Hidden fields for depreciation calculation -->
                <input type="hidden" id="nilai_residu_hidden" name="residual_value" value="{{ old('residual_value', 0) }}">
                <input type="hidden" id="acquisition_cost" name="acquisition_cost" value="{{ old('acquisition_cost', 0) }}">

                <div class="mb-3">
                    <label for="tanggal_beli" class="form-label text-light">Tanggal Pembelian</label>
                    <input type="date" class="form-control bg-dark text-light @error('tanggal_beli') is-invalid @enderror" id="tanggal_beli" name="tanggal_beli" value="{{ old('tanggal_beli') }}" required>
                    @error('tanggal_beli')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label text-white">Status</label>
                    <select class="form-select bg-dark text-white @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="" disabled selected>-- Pilih Status --</option>
                        <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="disewakan" {{ old('status') == 'disewakan' ? 'selected' : '' }}>Disewakan</option>
                        <option value="dioperasikan" {{ old('status') == 'dioperasikan' ? 'selected' : '' }}>Dioperasikan</option>
                        <option value="dihapus" {{ old('status') == 'dihapus' ? 'selected' : '' }}>Dihapus</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="card border-0 shadow-sm mb-4 bg-dark">
                    <div class="card-header bg-primary text-light">
                        <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Ringkasan Penyusutan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0 table-dark">
                                <tbody>
                                    <tr>
                                        <td class="bg-secondary text-white fw-bold" width="40%">Harga Perolehan</td>
                                        <td class="text-end text-white" id="harga_perolehan_display">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td class="bg-secondary text-white fw-bold">Nilai Residu (5%)</td>
                                        <td class="text-end text-info fw-bold" id="nilai_residu_display">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td class="bg-secondary text-white fw-bold">Nilai yang Disusutkan</td>
                                        <td class="text-end text-white" id="nilai_disusutkan_display">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td class="bg-secondary text-white fw-bold">Umur Manfaat</td>
                                        <td class="text-end text-white"><span id="umur_manfaat_display">0</span> tahun</td>
                                    </tr>
                                    <tr class="bg-success bg-opacity-25">
                                        <td class="fw-bold text-white">Penyusutan Tahunan</td>
                                        <td class="text-end fw-bold text-white" id="penyusutan_tahunan_display">Rp 0 /tahun</td>
                                    </tr>
                                    <tr class="bg-info bg-opacity-25">
                                        <td class="fw-bold text-white">Penyusutan Bulanan</td>
                                        <td class="text-end fw-bold text-white" id="penyusutan_bulanan_display">Rp 0 /bulan</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="depr_start_date" class="form-label text-white">Mulai Penyusutan</label>
                    <input type="date" class="form-control bg-dark text-white @error('depr_start_date') is-invalid @enderror" 
                           id="depr_start_date" name="depr_start_date" 
                           value="{{ old('depr_start_date', date('Y-m-d')) }}" required>
                    @error('depr_start_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('master-data.aset.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
