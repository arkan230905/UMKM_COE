@extends('layouts.app')

@section('title', 'Edit Data Perusahaan')

@section('content')
<style>
    .theme-brown { color: #5C3D2E !important; }
    .theme-brown-light { color: #8A6B48 !important; }
    .bg-theme-brown { background-color: #5C3D2E !important; }
    .bg-theme-brown-light { background-color: #8A6B48 !important; }
    .btn-theme {
        background-color: #5C3D2E;
        color: white;
        border: none;
    }
    .btn-theme:hover {
        background-color: #4a3125;
        color: white;
    }
    .btn-theme-outline {
        color: #5C3D2E;
        border: 1px solid #5C3D2E;
        background-color: transparent;
    }
    .btn-theme-outline:hover {
        background-color: rgba(92, 61, 46, 0.05);
        color: #5C3D2E;
    }
    .form-control:focus {
        border-color: #8A6B48;
        box-shadow: 0 0 0 0.25rem rgba(138, 107, 72, 0.25);
    }
</style>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #5C3D2E 0%, #8A6B48 100%); border-radius: 15px;">
                <div class="card-body p-4 text-white d-flex align-items-center">
                    <div class="bg-white p-3 rounded-circle shadow-sm me-4 d-flex align-items-center justify-content-center theme-brown" style="width: 60px; height: 60px;">
                        <i class="fas fa-building fa-xl"></i>
                    </div>
                    <div>
                        <h3 class="mb-1 text-white fw-bold">Edit Data Perusahaan</h3>
                        <p class="mb-0 opacity-75"><i class="fas fa-edit me-2"></i>Perbarui informasi profil perusahaan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0" style="border-radius: 12px;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <form method="POST" action="/tentang-perusahaan">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label for="nama" class="form-label fw-bold theme-brown">Nama Perusahaan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-font text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="nama" name="nama" value="{{ old('nama', $dataPerusahaan->nama) }}" required placeholder="Masukkan nama perusahaan">
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="alamat" class="form-label fw-bold theme-brown">Alamat Lengkap (Beserta RT/RW dan Patokan)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 align-items-start pt-2"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                    <textarea class="form-control border-start-0 ps-0" id="alamat" name="alamat" rows="3" required placeholder="Contoh: Gedung FIT Telkom University, RT 01/RW 02, dekat gerbang utama">{{ old('alamat', $dataPerusahaan->alamat) }}</textarea>
                                </div>
                                <div class="form-text mt-2"><a href="#map_search" onclick="document.getElementById('map_search').focus(); return false;" class="text-theme text-decoration-none"><i class="fas fa-search-location me-1"></i> Gunakan pencarian peta di bawah untuk mencari lokasi otomatis, lalu lengkapi dengan RT/RW/Patokan secara manual</a></div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold theme-brown">Email Resmi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="{{ old('email', $dataPerusahaan->email) }}" required placeholder="email@perusahaan.com">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="telepon" class="form-label fw-bold theme-brown">Nomor Telepon</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone-alt text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="telepon" name="telepon" value="{{ old('telepon', $dataPerusahaan->telepon) }}" required placeholder="Contoh: 021-12345678">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold theme-brown">Lokasi Perusahaan untuk Perhitungan Ongkir <span class="text-danger">*</span></label>
                                <div class="position-relative mb-2">
                                    <input type="text" id="map_search" class="form-control" placeholder="Cari lokasi perusahaan di peta..." style="border-radius: 8px; padding-right: 2.5rem;">
                                    <i class="fas fa-search position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%);"></i>
                                    <div id="map-suggestions" class="position-absolute w-100 bg-white border rounded shadow-sm mt-1" style="z-index: 9999; display: none; max-height: 200px; overflow-y: auto;"></div>
                                </div>
                                <div id="map" style="height: 300px; border-radius: 8px; border: 1px solid #dee2e6;" class="mb-2"></div>
                                <div class="text-muted small mb-2"><i class="fas fa-info-circle me-1"></i> Titik lokasi ini digunakan untuk menghitung ongkir ke pelanggan secara lebih akurat. Seret atau klik peta untuk menempatkan lokasi perusahaan.</div>
                                
                                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $dataPerusahaan->latitude) }}">
                                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $dataPerusahaan->longitude) }}">
                                
                                <div class="form-check mt-3" style="background: #fdf5eb; border: 1px solid #f0e6d2; padding: 1rem 1rem 1rem 2.5rem; border-radius: 8px;">
                                    <input class="form-check-input" type="checkbox" id="konfirmasi_map" style="cursor: pointer; width: 1.2rem; height: 1.2rem; margin-top: 0.1rem;">
                                    <label class="form-check-label fw-bold" for="konfirmasi_map" style="font-size: 0.9rem; color: #8b5a2b; cursor: pointer;">
                                        Saya sudah memastikan titik lokasi perusahaan di peta sudah benar. Titik ini akan digunakan untuk menghitung ongkir pelanggan.
                                    </label>
                                </div>
                                @if(empty($dataPerusahaan->latitude) || empty($dataPerusahaan->longitude))
                                    <div class="alert alert-warning py-2 mb-0" style="font-size: 0.85rem;">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Titik lokasi belum diatur. Mohon atur lokasi perusahaan agar perhitungan ongkir pelanggan dapat berfungsi.
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mt-5 d-flex gap-2">
                            <button type="submit" class="btn btn-theme px-4 py-2 fw-bold" style="border-radius: 8px;">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="/tentang-perusahaan/detail" class="btn btn-theme-outline px-4 py-2 fw-bold" style="border-radius: 8px;">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px; background-color: #FAFAF8;">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-white p-4 rounded-circle shadow-sm d-inline-block mb-3 theme-brown" style="width: 80px; height: 80px;">
                            <i class="fas fa-info-circle fa-2x mt-1"></i>
                        </div>
                        <h5 class="fw-bold theme-brown">Informasi Penting</h5>
                    </div>
                    
                    <ul class="list-unstyled text-muted small">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Pastikan <strong>Nama Perusahaan</strong> diisi dengan benar karena akan tampil pada kop surat atau laporan PDF.</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span><strong>Alamat Lengkap</strong> akan digunakan sebagai referensi pengiriman dan kontak surat menyurat.</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span><strong>Lokasi Perusahaan</strong> sangat penting untuk menghitung jarak pengiriman (ongkir) pesanan pelanggan secara otomatis.</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span><strong>Email</strong> dan <strong>Telepon</strong> adalah saluran utama yang bisa dihubungi oleh pihak luar.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const latInput = document.getElementById('latitude');
        const lonInput = document.getElementById('longitude');
        const alamatInput = document.getElementById('alamat');
        const searchInput = document.getElementById('map_search');
        const suggestionsList = document.getElementById('map-suggestions');
        const konfirmasiCheckbox = document.getElementById('konfirmasi_map');
        
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!konfirmasiCheckbox.checked) {
                e.preventDefault();
                alert('Silakan pastikan titik lokasi perusahaan di peta sudah benar dan centang kotak konfirmasi sebelum menyimpan.');
            }
        });
        
        let timeoutId;
        
        // Default to saved location, or Bandung if empty
        let defaultLat = latInput.value ? parseFloat(latInput.value) : -6.914744;
        let defaultLon = lonInput.value ? parseFloat(lonInput.value) : 107.609810;
        
        const map = L.map('map').setView([defaultLat, defaultLon], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let marker = L.marker([defaultLat, defaultLon], {draggable: true}).addTo(map);

        function formatNominatimAddress(addr, placeName = null) {
            if (!addr) return '';
            let parts = [];
            
            // Nama Tempat (POI)
            let tempat = placeName || addr.amenity || addr.building || addr.shop || addr.office || addr.tourism || '';
            
            // Nama Jalan & Nomor
            let jalan = addr.road || addr.pedestrian || addr.street || addr.path || '';
            let nomor = addr.house_number || '';
            let jalanLengkap = jalan;
            if (jalan && nomor) {
                jalanLengkap = jalan + ' No. ' + nomor;
            } else if (!jalan && addr.hamlet) {
                // Seringkali jalan tidak ada tapi hamlet/dusun ada
                jalanLengkap = addr.hamlet;
            }
            
            if (tempat && tempat !== jalanLengkap) parts.push(tempat);
            if (jalanLengkap && jalanLengkap !== tempat) parts.push(jalanLengkap);
            
            // Kelurahan / Desa
            let kelurahan = addr.village || addr.suburb || addr.neighbourhood || addr.residential || '';
            if (kelurahan && kelurahan !== jalanLengkap && kelurahan !== tempat) parts.push(kelurahan);
            
            // Kecamatan
            let kecamatan = addr.city_district || addr.district || addr.subdistrict || '';
            if (kecamatan && kecamatan !== kelurahan) parts.push(kecamatan);
            
            // Kota / Kabupaten
            let kota = addr.city || addr.town || addr.municipality || addr.county || '';
            if (kota && kota !== kecamatan) parts.push(kota);
            
            // Provinsi & Kode Pos
            let provinsi = addr.state || addr.region || addr.province || '';
            let kodepos = addr.postcode || '';
            let provPos = provinsi;
            if (provinsi && kodepos) {
                provPos = provinsi + ' ' + kodepos;
            } else if (kodepos) {
                provPos = kodepos;
            }
            if (provPos) parts.push(provPos);
            
            // Negara
            let negara = addr.country || 'Indonesia';
            if (negara) parts.push(negara);
            
            // Gabungkan dengan koma
            return parts.join(', ');
        }

        function reverseGeocode(lat, lon, predefinedAddress = null) {
            if (predefinedAddress) {
                alamatInput.value = predefinedAddress;
                konfirmasiCheckbox.checked = false;
                return;
            }
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.address) {
                        const formatted = formatNominatimAddress(data.address, data.name || null) || data.display_name;
                        if (formatted) alamatInput.value = formatted;
                        konfirmasiCheckbox.checked = false;
                    }
                })
                .catch(err => console.error("Geocoding error:", err));
        }

        function updateLocation(lat, lng, fetchAddress = true, predefinedAddress = null) {
            const newLatLng = new L.LatLng(lat, lng);
            marker.setLatLng(newLatLng);
            map.panTo(newLatLng);
            latInput.value = lat;
            lonInput.value = lng;
            
            if (fetchAddress || predefinedAddress) {
                reverseGeocode(lat, lng, predefinedAddress);
            }
            
            konfirmasiCheckbox.checked = false;
        }

        // On Marker Drag
        marker.on('dragend', function(e) {
            const position = marker.getLatLng();
            updateLocation(position.lat, position.lng, true);
        });

        // On Map Click
        map.on('click', function(e) {
            updateLocation(e.latlng.lat, e.latlng.lng, true);
        });

        // Search Autocomplete
        searchInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const query = this.value.trim();

            if (query.length < 3) {
                suggestionsList.style.display = 'none';
                return;
            }

            timeoutId = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=15&addressdetails=1&dedupe=0`)
                    .then(response => response.json())
                    .then(data => {
                        suggestionsList.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'p-2 border-bottom';
                                div.style.cssText = 'cursor: pointer; font-size: 0.85rem;';
                                
                                // Ambil nama tempat dari display_name
                                let placeName = item.name || item.display_name.split(',')[0];
                                const formatted = item.address ? formatNominatimAddress(item.address, placeName) : item.display_name;
                                div.innerHTML = `<i class="fas fa-map-marker-alt text-muted me-2"></i> ${formatted}`;
                                
                                div.addEventListener('click', function() {
                                    searchInput.value = '';
                                    suggestionsList.style.display = 'none';
                                    updateLocation(item.lat, item.lon, false, formatted);
                                });
                                suggestionsList.appendChild(div);
                            });
                            suggestionsList.style.display = 'block';
                        } else {
                            const div = document.createElement('div');
                            div.className = 'p-2 text-muted text-center';
                            div.style.cssText = 'font-size: 0.85rem;';
                            div.innerText = 'Alamat tidak ditemukan. Coba gunakan kata kunci yang lebih lengkap.';
                            suggestionsList.appendChild(div);
                            suggestionsList.style.display = 'block';
                        }
                    })
                    .catch(err => console.error("Search error:", err));
            }, 400); // 400ms debounce
        });

        document.addEventListener('click', function(e) {
            if (e.target !== searchInput && !suggestionsList.contains(e.target)) {
                suggestionsList.style.display = 'none';
            }
        });
        
        // Form Validation before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!latInput.value || !lonInput.value) {
                e.preventDefault();
                alert('Silakan pilih lokasi perusahaan di peta terlebih dahulu.');
            }
            if (!alamatInput.value.trim()) {
                e.preventDefault();
                alert('Alamat lengkap wajib diisi.');
                alamatInput.focus();
            }
        });

        // Wait a bit for layout to render then invalidate map size
        setTimeout(() => {
            map.invalidateSize();
        }, 500);
    });
</script>
@endpush
@endsection
