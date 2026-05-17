@extends('layouts.pelanggan')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h3 class="mb-4" style="color: #2d3748; font-weight: 700;">
                        <i class="bi bi-bug"></i> Debug Ongkir Calculator
                    </h3>

                    <form id="debugForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 600;">Alamat Tujuan</label>
                            <input type="text" id="alamat" name="alamat" class="form-control" 
                                   placeholder="Masukkan alamat tujuan" 
                                   value="Fakultas Ilmu Terapan, Jalan Sukabirus, Sukacahaya, Dayeuhkolot, Kabupaten Bandung, West Java, Java, 40257, Indonesia"
                                   style="border-radius: 8px;">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight: 600;">Latitude (Optional)</label>
                                <input type="number" id="latitude" name="latitude" class="form-control" 
                                       placeholder="Latitude" step="0.0001"
                                       style="border-radius: 8px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight: 600;">Longitude (Optional)</label>
                                <input type="number" id="longitude" name="longitude" class="form-control" 
                                       placeholder="Longitude" step="0.0001"
                                       style="border-radius: 8px;">
                            </div>
                        </div>

                        <button type="submit" class="btn w-100" style="background: #a66a38; color: white; border-radius: 8px; padding: 0.8rem; font-weight: 600;">
                            <i class="bi bi-play-fill"></i> Test Ongkir
                        </button>
                    </form>

                    <div id="result" style="margin-top: 2rem; display: none;">
                        <hr style="border-color: #eee;">
                        <h5 style="color: #2d3748; font-weight: 700; margin-bottom: 1rem;">Hasil Debug:</h5>
                        
                        <div id="resultContent" style="background: #f8f9fa; border-radius: 8px; padding: 1rem; font-family: monospace; font-size: 0.85rem; overflow-x: auto;">
                        </div>
                    </div>

                    <div id="loading" style="margin-top: 2rem; display: none; text-align: center;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2" style="color: #666;">Menghitung jarak...</p>
                    </div>

                    <div id="error" style="margin-top: 2rem; display: none;">
                        <div class="alert alert-danger" role="alert">
                            <strong>Error:</strong> <span id="errorMessage"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="card border-0 shadow-sm mt-4" style="border-radius: 12px; background: #eef8f1;">
                <div class="card-body p-4">
                    <h6 style="color: #27ae60; font-weight: 700; margin-bottom: 1rem;">
                        <i class="bi bi-info-circle"></i> Cara Menggunakan
                    </h6>
                    <ul style="color: #27ae60; margin-bottom: 0;">
                        <li>Masukkan alamat tujuan atau gunakan default</li>
                        <li>Opsional: Masukkan latitude & longitude untuk akurasi lebih tinggi</li>
                        <li>Klik "Test Ongkir" untuk melihat hasil perhitungan</li>
                        <li>Hasil akan menampilkan koordinat toko, jarak, dan ongkir yang cocok</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('debugForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const alamat = document.getElementById('alamat').value;
    const latitude = document.getElementById('latitude').value;
    const longitude = document.getElementById('longitude').value;
    
    document.getElementById('loading').style.display = 'block';
    document.getElementById('result').style.display = 'none';
    document.getElementById('error').style.display = 'none';
    
    try {
        const response = await fetch('{{ route("pelanggan.checkout.debug-ongkir") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                alamat: alamat,
                latitude: latitude || null,
                longitude: longitude || null,
            })
        });
        
        const data = await response.json();
        document.getElementById('loading').style.display = 'none';
        
        if (data.success) {
            const info = data.debug_info;
            let html = '<strong>📍 Toko (Store):</strong><br>';
            html += `Nama: ${info.store.nama}<br>`;
            html += `Latitude: ${info.store.latitude}<br>`;
            html += `Longitude: ${info.store.longitude}<br><br>`;
            
            html += '<strong>📍 Alamat Tujuan:</strong><br>';
            html += `Alamat: ${info.test_address.alamat}<br>`;
            html += `Latitude: ${info.test_address.latitude}<br>`;
            html += `Longitude: ${info.test_address.longitude}<br><br>`;
            
            html += '<strong>📏 Perhitungan Jarak:</strong><br>';
            html += `Jarak (4 desimal): ${info.distance_calculation.distance_km} km<br>`;
            html += `Jarak (2 desimal): ${info.distance_calculation.distance_rounded} km<br><br>`;
            
            if (info.ongkir_matched) {
                html += '<strong style="color: #27ae60;">✅ Ongkir Cocok:</strong><br>';
                html += `Range: ${info.ongkir_matched.label}<br>`;
                html += `Harga: Rp ${new Intl.NumberFormat('id-ID').format(info.ongkir_matched.harga_ongkir)}<br><br>`;
            } else {
                html += '<strong style="color: #e74c3c;">❌ Ongkir Tidak Cocok</strong><br>';
                html += 'Jarak tidak sesuai dengan setting ongkir apapun<br><br>';
            }
            
            html += '<strong>📋 Semua Setting Ongkir:</strong><br>';
            html += '<table style="width: 100%; border-collapse: collapse; margin-top: 0.5rem;">';
            html += '<tr style="background: #ddd;"><th style="padding: 0.5rem; text-align: left; border: 1px solid #999;">Range</th><th style="padding: 0.5rem; text-align: right; border: 1px solid #999;">Harga</th></tr>';
            
            info.all_ongkir_settings.forEach(setting => {
                const isMatched = info.ongkir_matched && 
                    setting.jarak_min === info.ongkir_matched.jarak_min &&
                    setting.jarak_max === info.ongkir_matched.jarak_max;
                const bgColor = isMatched ? '#c8e6c9' : 'white';
                html += `<tr style="background: ${bgColor};"><td style="padding: 0.5rem; border: 1px solid #ddd;">${setting.label}</td><td style="padding: 0.5rem; text-align: right; border: 1px solid #ddd;">Rp ${new Intl.NumberFormat('id-ID').format(setting.harga_ongkir)}</td></tr>`;
            });
            
            html += '</table>';
            
            document.getElementById('resultContent').innerHTML = html;
            document.getElementById('result').style.display = 'block';
        } else {
            document.getElementById('errorMessage').textContent = data.message || 'Terjadi kesalahan';
            document.getElementById('error').style.display = 'block';
        }
    } catch (err) {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('errorMessage').textContent = err.message;
        document.getElementById('error').style.display = 'block';
    }
});
</script>
@endsection
