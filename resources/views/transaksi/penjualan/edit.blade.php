@extends('layouts.app')

@section('title', 'Edit Penjualan')

@section('content')

<script>
// Simple, bulletproof calculation function
function hitungTotal() {
    // Get all subtotals from table rows
    let subtotalProduk = 0;
    document.querySelectorAll('#detailTableJual tbody tr').forEach(function(row) {
        const subtotalEl = row.querySelector('.subtotal');
        if (subtotalEl && subtotalEl.value) {
            const rawValue = subtotalEl.getAttribute('data-raw');
            if (rawValue) {
                subtotalProduk += parseFloat(rawValue);
            } else {
                const numStr = subtotalEl.value.replace(/\./g, '').replace(',', '.');
                subtotalProduk += parseFloat(numStr) || 0;
            }
        }
    });

    // Get ongkir value
    const ongkirEl = document.getElementById('biaya_ongkir');
    let ongkir = 0;
    if (ongkirEl) {
        ongkir = parseFloat(ongkirEl.value) || 0;
        if (ongkir === 0 && ongkirEl.selectedIndex > 0) {
            const opt = ongkirEl.options[ongkirEl.selectedIndex];
            if (opt) ongkir = parseFloat(opt.value) || 0;
        }
    }

    // Get PPN percentage
    const ppnEl = document.getElementById('ppn_persen');
    const ppnPersen = ppnEl ? (parseFloat(ppnEl.value) || 0) : 0;

    // Calculate: PPN = (Subtotal Produk + Biaya Ongkir) × PPN%
    const totalPPN   = Math.round((subtotalProduk + ongkir) * ppnPersen / 100);
    const totalFinal = subtotalProduk + ongkir + totalPPN;

    function fmtIDR(n) { return Math.round(n).toLocaleString('id-ID'); }

    // Update hidden inputs (form submission)
    const hiddenSubtotal = document.getElementById('subtotal_produk_hidden');
    if (hiddenSubtotal) hiddenSubtotal.value = Math.round(subtotalProduk);

    const hiddenPPN = document.getElementById('total_ppn');
    if (hiddenPPN) hiddenPPN.value = Math.round(totalPPN);

    const hiddenTotal = document.getElementById('total_final');
    if (hiddenTotal) hiddenTotal.value = Math.round(totalFinal);

    // Update display spans
    const ds = document.getElementById('display_subtotal_produk');
    if (ds) ds.textContent = fmtIDR(subtotalProduk);

    const dp = document.getElementById('display_total_ppn');
    if (dp) dp.textContent = fmtIDR(totalPPN);

    const dg = document.getElementById('display_biaya_ongkir');
    if (dg) dg.textContent = fmtIDR(ongkir);

    const df = document.getElementById('display_total_final');
    if (df) df.textContent = fmtIDR(totalFinal);
}

// Tambah baris produk (dipanggil langsung dari onclick button)
function tambahBarisProduk() {
    const table = document.getElementById('detailTableJual');
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const firstRow = tbody.rows[0];
    if (!firstRow) return;
    
    const clone = firstRow.cloneNode(true);
    
    // Reset semua input di baris baru
    clone.querySelectorAll('input').forEach(inp => {
        if (inp.classList.contains('jumlah')) {
            inp.value = 1;
            inp.setAttribute('data-max-stok', '0'); // reset stok agar validasi tidak pakai stok baris lama
        } else if (inp.classList.contains('harga')) {
            inp.removeAttribute('readonly');
            inp.value = 0;
            inp.setAttribute('readonly', 'readonly');
            inp.removeAttribute('data-raw');
        } else if (inp.classList.contains('diskon')) {
            inp.value = 0;
        } else if (inp.classList.contains('subtotal')) {
            inp.value = 0;
            inp.removeAttribute('data-raw');
        }
    });
    
    // Reset select
    clone.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
    
    // Reset stok info
    const stokInfo = clone.querySelector('.stok-info');
    if (stokInfo) stokInfo.textContent = '';
    
    tbody.appendChild(clone);
    hitungTotal();
}

// Hapus baris produk (dipanggil langsung dari onclick button)
function hapusBarisProduk(btn) {
    const table = document.getElementById('detailTableJual');
    if (!table) return;
    const rows = table.querySelectorAll('tbody tr');
    if (rows.length <= 1) return; // minimal 1 baris
    const tr = btn.closest('tr');
    if (tr) {
        tr.remove();
        hitungTotal();
    }
}

// Toggle opsi "Terima di" berdasarkan metode pembayaran
function toggleSumberDana() {
    const paymentMethod = document.getElementById('payment_method_jual').value;
    const sumberDana    = document.getElementById('sumber_dana_jual');
    if (!sumberDana) return;

    const allOptions = sumberDana.querySelectorAll('option');
    let firstVisible = null;

    allOptions.forEach(opt => {
        const tipe = opt.getAttribute('data-tipe'); // 'kas', 'bank', 'piutang'
        let show = true;

        if (paymentMethod === 'cash') {
            show = (tipe === 'kas');
        } else if (paymentMethod === 'transfer') {
            show = (tipe === 'bank');
        } else if (paymentMethod === 'credit') {
            show = (tipe === 'piutang');
        }

        opt.style.display = show ? '' : 'none';
        opt.disabled = !show;
        if (show && !firstVisible) firstVisible = opt;
    });

    // Kalau pilihan saat ini tersembunyi, pilih yang pertama visible
    const currentOpt = sumberDana.options[sumberDana.selectedIndex];
    if (!currentOpt || currentOpt.disabled) {
        if (firstVisible) sumberDana.value = firstVisible.value;
    }
}

// Jalankan saat halaman pertama kali load
document.addEventListener('DOMContentLoaded', function() {
    toggleSumberDana();
});

// Handler barcode input - dipanggil langsung dari oninput di HTML
function handleBarcodeOninput(value) {
    value = value.trim();

    if (!value) {
        document.getElementById('search-results').style.display = 'none';
        return;
    }

    // Hanya proses input numerik
    if (!/^\d+$/.test(value)) {
        document.getElementById('search-results').style.display = 'none';
        return;
    }

    // Input panjang (>= 8 digit) -> proses sebagai barcode lengkap
    if (value.length >= 8) {
        document.getElementById('search-results').style.display = 'none';
        setTimeout(() => {
            const current = document.getElementById('barcode-scanner').value.trim();
            if (current === value) processAutomaticBarcode(value);
        }, 100);
        return;
    }

    // Input pendek (1-7 digit) -> tampilkan search results
    performRealTimeSearch(value);
}
</script>

<style>
/* Enhanced search result styling */
.search-result-item {
    transition: all 0.2s ease;
    border-radius: 4px;
    padding: 8px !important;
    margin: 2px 0;
}

.search-result-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-result-item.selected {
    background-color: #e3f2fd !important;
    border-left: 3px solid #2196f3;
}

/* Barcode highlight styling */
mark.bg-warning {
    background-color: #fff3cd !important;
    color: #856404 !important;
    font-weight: bold;
    padding: 1px 2px;
    border-radius: 2px;
}

/* Search results container */
#search-results {
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Barcode scanner input focus */
#barcode-scanner:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>

<div class="container">
    <h3 class="mb-3">Edit Penjualan</h3>

    @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('transaksi.penjualan.update', $penjualan->id) }}" method="POST" id="form-penjualan">
        @csrf
        @method('PATCH')

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ $penjualan->tanggal instanceof \Carbon\Carbon ? $penjualan->tanggal->format('Y-m-d') : $penjualan->tanggal }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Waktu</label>
                <input type="time" name="waktu" class="form-control" value="{{ $penjualan->waktu ?? now()->format('H:i') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Metode Pembayaran</label>
                <select name="payment_method" id="payment_method_jual" class="form-select" required onchange="toggleSumberDana()">
                    <option value="cash" {{ ($penjualan->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="transfer" {{ ($penjualan->payment_method ?? '') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                    <option value="credit" {{ ($penjualan->payment_method ?? '') == 'credit' ? 'selected' : '' }}>Kredit</option>
                </select>
            </div>
            <div class="col-md-3" id="sumber_dana_wrapper_jual">
                <label class="form-label">Terima di</label>
                <select name="sumber_dana" id="sumber_dana_jual" class="form-select">
                    @foreach($kasbank as $kb)
                        @php
                            $kode = $kb->kode_akun;
                            if ($kode === '118' || stripos($kb->nama_akun, 'piutang') !== false) {
                                $tipe = 'piutang';
                            } elseif ($kode === '111' || stripos($kb->nama_akun, 'bank') !== false) {
                                $tipe = 'bank';
                            } else {
                                $tipe = 'kas'; // 112, 113, dll
                            }
                        @endphp
                        <option value="{{ $kb->kode_akun }}" data-tipe="{{ $tipe }}" {{ ($penjualan->sumber_dana ?? '') == $kb->kode_akun ? 'selected' : '' }}>
                            {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Barcode Scanner Input -->
        <div class="card mb-3 border-primary">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-barcode fa-2x text-primary"></i>
                    </div>
                    <div class="col">
                        <label class="form-label mb-1 small text-muted">Scan Barcode Produk</label>
                        <div class="input-group">
                            <input type="text" id="barcode-scanner" class="form-control form-control-lg" 
                                   placeholder="Ketik atau scan barcode..." 
                                   autocomplete="off" autofocus
                                   oninput="handleBarcodeOninput(this.value)">
                            <div class="input-group-text bg-success text-white">
                                <i class="fas fa-wifi me-1"></i>
                                <span id="scan-indicator">Siap Scan</span>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetScannerState()" title="Reset Scanner">
                                <i class="fas fa-refresh"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Pencarian berdasarkan awalan barcode - ketik angka untuk mencari produk yang barcodenya diawali dengan angka tersebut
                        </small>
                        
                        <!-- Real-time search results -->
                        <div id="search-results" class="mt-2" style="display: none;">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white py-1">
                                    <small><i class="fas fa-search me-1"></i>Hasil Pencarian (<span id="search-count">0</span> produk)</small>
                                </div>
                                <div class="card-body p-2" id="search-results-body" style="max-height: 200px; overflow-y: auto;">
                                    <!-- Results will be populated here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5>Detail Penjualan</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="detailTableJual">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Harga/Satuan</th>
                        <th class="text-end">Diskon (%)</th>
                        <th class="text-end">Subtotal</th>
                        <th style="width:6%"><button class="btn btn-success btn-sm" type="button" id="addRowJual" onclick="tambahBarisProduk()">+</button></th>
                    </tr>
                </thead>
                <tbody>
                    @if($penjualan->details->count() > 0)
                        @foreach($penjualan->details as $index => $detail)
                        <tr>
                            <td>
                                <select name="produk_id[]" class="form-select produk-select" required onchange="
                                    const tr = this.closest('tr');
                                    const hargaInput = tr.querySelector('.harga');
                                    const subtotalInput = tr.querySelector('.subtotal');
                                    const qtyInput = tr.querySelector('.jumlah');
                                    const stokInfo = tr.querySelector('.stok-info');
                                    const selectedOption = this.options[this.selectedIndex];
                                    
                                    if (!this.value) {
                                        hargaInput.removeAttribute('readonly');
                                        hargaInput.value = 0;
                                        hargaInput.setAttribute('readonly', 'readonly');
                                        subtotalInput.value = 0;
                                        qtyInput.setAttribute('data-max-stok', '0');
                                        if (stokInfo) stokInfo.textContent = '';
                                        hitungTotal();
                                        return;
                                    }
                                    
                                    const harga = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                                    const stok  = parseFloat(selectedOption.getAttribute('data-stok')  || '0') || 0;
                                    const qty = parseFloat(qtyInput.value) || 1;
                                    const diskon = parseFloat(tr.querySelector('.diskon').value) || 0;
                                    const subtotal = qty * harga * (1 - diskon / 100);
                                    
                                    hargaInput.removeAttribute('readonly');
                                    hargaInput.value = harga.toLocaleString('id-ID');
                                    hargaInput.setAttribute('readonly', 'readonly');
                                    hargaInput.setAttribute('data-raw', harga);
                                    subtotalInput.value = subtotal.toLocaleString('id-ID');
                                    subtotalInput.setAttribute('data-raw', subtotal);
                                    
                                    // Simpan stok ke data-max-stok agar validasi qty bisa membacanya
                                    qtyInput.setAttribute('data-max-stok', stok);
                                    
                                    // Tampilkan info stok di bawah select
                                    if (stokInfo) {
                                        stokInfo.textContent = 'Stok tersedia: ' + stok.toLocaleString('id-ID');
                                        stokInfo.style.color = stok > 0 ? '#28a745' : '#dc3545';
                                    }
                                    
                                    hitungTotal();
                                ">
                                    <option value="">-- Pilih Produk --</option>
                                    <optgroup label="Produk Individual">
                                        @foreach($produks as $p)
                                            <option value="{{ $p->id }}" 
                                                    data-price="{{ $p->harga_jual ?? 0 }}"
                                                    data-stok="{{ $p->stok ?? 0 }}"
                                                    data-type="produk"
                                                    data-nama="{{ $p->nama_produk ?? $p->nama }}"
                                                    {{ $detail->produk_id == $p->id ? 'selected' : '' }}>
                                                {{ $p->nama_produk ?? $p->nama }} (Stok: {{ number_format($p->stok ?? 0, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    @if($paketMenus->count() > 0)
                                    <optgroup label="Paket Menu">
                                        @foreach($paketMenus as $paket)
                                            <option value="paket_{{ $paket->id }}" 
                                                    data-price="{{ round($paket->harga_paket ?? 0) }}"
                                                    data-stok="999"
                                                    data-type="paket"
                                                    data-paket-id="{{ $paket->id }}"
                                                    {{ $detail->produk_id == 'paket_'.$paket->id ? 'selected' : '' }}>
                                                {{ $paket->nama_paket }} - Rp {{ number_format($paket->harga_paket, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    @endif
                                </select>
                                <small class="text-muted stok-info"></small>
                            </td>
                            <td><input type="number" step="1" min="1" name="jumlah[]" class="form-control jumlah" value="{{ $detail->jumlah }}" required onchange="
                                const tr = this.closest('tr');
                                if (!validateStock(tr)) return;
                                const hargaInput = tr.querySelector('.harga');
                                const harga = parseFloat(hargaInput.getAttribute('data-raw') || hargaInput.value.replace(/\./g,'').replace(',','.')) || 0;
                                const qty = parseFloat(this.value) || 1;
                                const diskon = parseFloat(tr.querySelector('.diskon').value) || 0;
                                const subtotal = qty * harga * (1 - diskon / 100);
                                const subtotalInput = tr.querySelector('.subtotal');
                                subtotalInput.value = subtotal.toLocaleString('id-ID');
                                subtotalInput.setAttribute('data-raw', subtotal);
                                hitungTotal();
                            " oninput="
                                const tr = this.closest('tr');
                                const hargaInput = tr.querySelector('.harga');
                                const harga = parseFloat(hargaInput.getAttribute('data-raw') || hargaInput.value.replace(/\./g,'').replace(',','.')) || 0;
                                const qty = parseFloat(this.value) || 1;
                                const diskon = parseFloat(tr.querySelector('.diskon').value) || 0;
                                const subtotal = qty * harga * (1 - diskon / 100);
                                const subtotalInput = tr.querySelector('.subtotal');
                                subtotalInput.value = subtotal.toLocaleString('id-ID');
                                subtotalInput.setAttribute('data-raw', subtotal);
                                hitungTotal();
                            "></td>
                            <td><input type="text" name="harga_satuan[]" class="form-control harga" value="{{ number_format($detail->harga_satuan, 0, ',', '.') }}" readonly required style="background-color: #e9ecef; cursor: not-allowed;" data-raw="{{ $detail->harga_satuan }}"></td>
                            <td><input type="number" step="0.01" min="0" max="100" name="diskon_persen[]" class="form-control diskon" value="{{ $detail->diskon_persen ?? 0 }}" onchange="
                                const tr = this.closest('tr');
                                const hargaInput = tr.querySelector('.harga');
                                const harga = parseFloat(hargaInput.getAttribute('data-raw') || hargaInput.value.replace(/\./g,'').replace(',','.')) || 0;
                                const qty = parseFloat(tr.querySelector('.jumlah').value) || 1;
                                const diskon = parseFloat(this.value) || 0;
                                const subtotal = qty * harga * (1 - diskon / 100);
                                const subtotalInput = tr.querySelector('.subtotal');
                                subtotalInput.value = subtotal.toLocaleString('id-ID');
                                subtotalInput.setAttribute('data-raw', subtotal);
                                hitungTotal();
                            "></td>
                            <td><input type="text" class="form-control subtotal" value="{{ number_format($detail->subtotal, 0, ',', '.') }}" readonly data-raw="{{ $detail->subtotal }}"></td>
                            <td><button type="button" class="btn btn-danger btn-sm removeRow" onclick="hapusBarisProduk(this)">-</button></td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>
                                <select name="produk_id[]" class="form-select produk-select" required onchange="
                                    const tr = this.closest('tr');
                                    const hargaInput = tr.querySelector('.harga');
                                    const subtotalInput = tr.querySelector('.subtotal');
                                    const qtyInput = tr.querySelector('.jumlah');
                                    const stokInfo = tr.querySelector('.stok-info');
                                    const selectedOption = this.options[this.selectedIndex];
                                    
                                    if (!this.value) {
                                        hargaInput.removeAttribute('readonly');
                                        hargaInput.value = 0;
                                        hargaInput.setAttribute('readonly', 'readonly');
                                        subtotalInput.value = 0;
                                        qtyInput.setAttribute('data-max-stok', '0');
                                        if (stokInfo) stokInfo.textContent = '';
                                        hitungTotal();
                                        return;
                                    }
                                    
                                    const harga = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                                    const stok  = parseFloat(selectedOption.getAttribute('data-stok')  || '0') || 0;
                                    const qty = parseFloat(qtyInput.value) || 1;
                                    const diskon = parseFloat(tr.querySelector('.diskon').value) || 0;
                                    const subtotal = qty * harga * (1 - diskon / 100);
                                    
                                    hargaInput.removeAttribute('readonly');
                                    hargaInput.value = harga.toLocaleString('id-ID');
                                    hargaInput.setAttribute('readonly', 'readonly');
                                    hargaInput.setAttribute('data-raw', harga);
                                    subtotalInput.value = subtotal.toLocaleString('id-ID');
                                    subtotalInput.setAttribute('data-raw', subtotal);
                                    
                                    // Simpan stok ke data-max-stok agar validasi qty bisa membacanya
                                    qtyInput.setAttribute('data-max-stok', stok);
                                    
                                    // Tampilkan info stok di bawah select
                                    if (stokInfo) {
                                        stokInfo.textContent = 'Stok tersedia: ' + stok.toLocaleString('id-ID');
                                        stokInfo.style.color = stok > 0 ? '#28a745' : '#dc3545';
                                    }
                                    
                                    hitungTotal();
                                ">
                                    <option value="">-- Pilih Produk --</option>
                                    <optgroup label="Produk Individual">
                                        @foreach($produks as $p)
                                            <option value="{{ $p->id }}" 
                                                    data-price="{{ $p->harga_jual ?? 0 }}"
                                                    data-stok="{{ $p->stok ?? 0 }}"
                                                    data-type="produk"
                                                    data-nama="{{ $p->nama_produk ?? $p->nama }}">
                                                {{ $p->nama_produk ?? $p->nama }} (Stok: {{ number_format($p->stok ?? 0, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    @if($paketMenus->count() > 0)
                                    <optgroup label="Paket Menu">
                                        @foreach($paketMenus as $paket)
                                            <option value="paket_{{ $paket->id }}" 
                                                    data-price="{{ round($paket->harga_paket ?? 0) }}"
                                                    data-stok="999"
                                                    data-type="paket"
                                                    data-paket-id="{{ $paket->id }}">
                                                {{ $paket->nama_paket }} - Rp {{ number_format($paket->harga_paket, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    @endif
                                </select>
                                <small class="text-muted stok-info"></small>
                            </td>
                            <td><input type="number" step="1" min="1" name="jumlah[]" class="form-control jumlah" value="1" required onchange="
                                const tr = this.closest('tr');
                                if (!validateStock(tr)) return;
                                const hargaInput = tr.querySelector('.harga');
                                const harga = parseFloat(hargaInput.getAttribute('data-raw') || hargaInput.value.replace(/\./g,'').replace(',','.')) || 0;
                                const qty = parseFloat(this.value) || 1;
                                const diskon = parseFloat(tr.querySelector('.diskon').value) || 0;
                                const subtotal = qty * harga * (1 - diskon / 100);
                                const subtotalInput = tr.querySelector('.subtotal');
                                subtotalInput.value = subtotal.toLocaleString('id-ID');
                                subtotalInput.setAttribute('data-raw', subtotal);
                                hitungTotal();
                            " oninput="
                                const tr = this.closest('tr');
                                const hargaInput = tr.querySelector('.harga');
                                const harga = parseFloat(hargaInput.getAttribute('data-raw') || hargaInput.value.replace(/\./g,'').replace(',','.')) || 0;
                                const qty = parseFloat(this.value) || 1;
                                const diskon = parseFloat(tr.querySelector('.diskon').value) || 0;
                                const subtotal = qty * harga * (1 - diskon / 100);
                                const subtotalInput = tr.querySelector('.subtotal');
                                subtotalInput.value = subtotal.toLocaleString('id-ID');
                                subtotalInput.setAttribute('data-raw', subtotal);
                                hitungTotal();
                            "></td>
                            <td><input type="text" name="harga_satuan[]" class="form-control harga" value="0" readonly required style="background-color: #e9ecef; cursor: not-allowed;"></td>
                            <td><input type="number" step="0.01" min="0" max="100" name="diskon_persen[]" class="form-control diskon" value="0" onchange="
                                const tr = this.closest('tr');
                                const hargaInput = tr.querySelector('.harga');
                                const harga = parseFloat(hargaInput.getAttribute('data-raw') || hargaInput.value.replace(/\./g,'').replace(',','.')) || 0;
                                const qty = parseFloat(tr.querySelector('.jumlah').value) || 1;
                                const diskon = parseFloat(this.value) || 0;
                                const subtotal = qty * harga * (1 - diskon / 100);
                                const subtotalInput = tr.querySelector('.subtotal');
                                subtotalInput.value = subtotal.toLocaleString('id-ID');
                                subtotalInput.setAttribute('data-raw', subtotal);
                                hitungTotal();
                            "></td>
                            <td><input type="text" class="form-control subtotal" value="0" readonly></td>
                            <td><button type="button" class="btn btn-danger btn-sm removeRow" onclick="hapusBarisProduk(this)">-</button></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Hidden inputs for form submission -->
        <input type="hidden" name="subtotal_produk" id="subtotal_produk_hidden" value="{{ $penjualan->subtotal_produk ?? 0 }}">
        <input type="hidden" name="total_ppn" id="total_ppn" value="{{ $penjualan->total_ppn ?? 0 }}">
        <input type="hidden" name="total" id="total_final" value="{{ $penjualan->total ?? 0 }}">

        <!-- Ringkasan Pembayaran Card -->
        <div class="card mt-4 border-0" style="background:#f8f9fa; border-radius:12px;">
            <div class="card-body px-4 py-3">

                <!-- Row: Subtotal Produk -->
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted">Subtotal Produk</span>
                    <span class="fw-semibold">Rp <span id="display_subtotal_produk">{{ number_format($penjualan->subtotal_produk ?? 0, 0, ',', '.') }}</span></span>
                </div>

                <!-- Row: Biaya Ongkir -->
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted me-2">Biaya Ongkir</span>
                        <select name="biaya_ongkir" id="biaya_ongkir"
                                class="form-select form-select-sm"
                                style="width:220px;"
                                onchange="hitungTotal();">
                            <option value="0" {{ ($penjualan->biaya_ongkir ?? 0) == 0 ? 'selected' : '' }}>Tanpa Ongkir</option>
                            @foreach($ongkirSettings as $ongkir)
                                <option value="{{ (int)$ongkir->harga_ongkir }}" {{ ($penjualan->biaya_ongkir ?? 0) == (int)$ongkir->harga_ongkir ? 'selected' : '' }}>
                                    {{ $ongkir->getJarakLabel() }} - Rp {{ number_format($ongkir->harga_ongkir, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <span class="fw-semibold">Rp <span id="display_biaya_ongkir">{{ number_format($penjualan->biaya_ongkir ?? 0, 0, ',', '.') }}</span></span>
                </div>

                <!-- Row: PPN -->
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-2">PPN (%)</span>
                        <input type="number" name="ppn_persen" id="ppn_persen"
                               class="form-control form-control-sm"
                               style="width:80px;"
                               value="{{ $penjualan->ppn_persen ?? 11 }}" min="0" max="100" step="0.01"
                               oninput="hitungTotal();"
                               onchange="hitungTotal();">
                        <span class="text-muted ms-4">Total PPN</span>
                    </div>
                    <span class="fw-semibold">Rp <span id="display_total_ppn">{{ number_format($penjualan->total_ppn ?? 0, 0, ',', '.') }}</span></span>
                </div>

                <!-- Separator -->
                <hr class="my-2">

                <!-- Row: Total Bayar -->
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="fw-semibold">Total Bayar</span>
                    <span class="fw-bold text-success" style="font-size:1.25rem;">Rp <span id="display_total_final">{{ number_format($penjualan->total ?? 0, 0, ',', '.') }}</span></span>
                </div>

            </div>
        </div>

        <div class="text-end mt-4">
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success">Update</button>
        </div>
    </form>
</div>

<script>
// Product data for barcode lookup
const productData = {
    @foreach($produks as $p)
    @if($p->barcode)
    '{{ trim($p->barcode) }}': {
        id: {{ $p->id }},
        nama: '{{ addslashes($p->nama_produk ?? $p->nama) }}',
        harga: {{ round($p->harga_jual ?? 0) }},
        stok: {{ $p->stok ?? 0 }},
        barcode: '{{ trim($p->barcode) }}'
    },
    @endif
    @endforeach
};

// Create searchable product array for real-time search
const searchableProducts = [
    @foreach($produks as $p)
    {
        id: {{ $p->id }},
        nama: '{{ addslashes($p->nama_produk ?? $p->nama) }}',
        harga: {{ round($p->harga_jual ?? 0) }},
        stok: {{ $p->stok ?? 0 }},
        barcode: '{{ $p->barcode ?? '' }}',
        type: 'produk',
        searchText: '{{ strtolower(addslashes($p->nama_produk ?? $p->nama)) }} {{ $p->barcode ?? '' }}'.toLowerCase()
    },
    @endforeach
    @foreach($paketMenus as $paket)
    {
        id: 'paket_{{ $paket->id }}',
        nama: '{{ addslashes($paket->nama_paket) }}',
        harga: {{ round($paket->harga_paket ?? 0) }},
        stok: 999,
        barcode: '',
        type: 'paket',
        paket_id: {{ $paket->id }},
        searchText: '{{ strtolower(addslashes($paket->nama_paket)) }} paket menu'.toLowerCase()
    },
    @endforeach
];

// Debug: Log productData to console
console.log('Product Data loaded:', Object.keys(productData).length, 'products');
console.log('Searchable Products loaded:', searchableProducts.length, 'products');

// Verify searchableProducts has prices
if (searchableProducts.length > 0) {
    console.log('First product in searchableProducts:', searchableProducts[0]);
    console.log('First product harga:', searchableProducts[0].harga);
}

// Global utility functions (must be outside DOMContentLoaded)
function formatCurrency(value) {
    if (value === null || value === undefined || isNaN(value)) {
        return 'Rp 0';
    }
    const roundedValue = Math.round(parseFloat(value) * 1000) / 1000;
    return 'Rp ' + roundedValue.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 3 });
}

function parseCurrency(formattedValue) {
    if (!formattedValue) return 0;
    return parseFloat(formattedValue.toString().replace(/[^\d]/g, '')) || 0;
}

// SIMPLE function to set price manually
function setPriceManual(option, harga) {
    console.log('setPriceManual called with harga:', harga);
    
    // Find the select element and then the row
    const select = option.parentElement.parentElement;
    const tr = select.closest('tr');
    const hargaInput = tr.querySelector('.harga');
    
    // Remove readonly, set value, add readonly back
    hargaInput.removeAttribute('readonly');
    hargaInput.value = harga;
    hargaInput.setAttribute('readonly', 'readonly');
    
    console.log('Price set to:', hargaInput.value);
    
    // Recalculate
    recalcRow(tr);
    hitungTotal();
}

// Global functions for barcode system
function handleProdukChange(selectElement) {
    const tr = selectElement.closest('tr');
    const hargaInput = tr.querySelector('.harga');
    
    console.log('handleProdukChange called');
    console.log('Selected value:', selectElement.value);
    
    if (!selectElement.value) {
        hargaInput.value = 0;
        recalcRow(tr);
        hitungTotal();
        return;
    }
    
    // Ambil harga dari data-price
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const harga = parseFloat(selectedOption.getAttribute('data-price')) || 0;
    const stok = parseFloat(selectedOption.getAttribute('data-stok')) || 0;
    
    console.log('Harga dari data-price:', harga);
    console.log('Stok dari data-stok:', stok);
    
    // Set harga dengan format currency
    hargaInput.value = formatCurrency(harga);
    hargaInput.setAttribute('data-raw', harga);
    
    // Update stok info
    const stokInfo = tr.querySelector('.stok-info');
    if (stokInfo) {
        stokInfo.textContent = 'Stok tersedia: ' + stok.toLocaleString('id-ID');
        stokInfo.style.color = stok > 0 ? '#28a745' : '#dc3545';
    }
    
    // Set max qty
    const qtyInput = tr.querySelector('.jumlah');
    qtyInput.setAttribute('data-max-stok', stok);
    
    recalcRow(tr);
    hitungTotal();
}

function recalcRow(tr) {
    const qty = parseFloat(tr.querySelector('.jumlah').value) || 0;
    const hargaRaw = tr.querySelector('.harga').getAttribute('data-raw');
    const harga = hargaRaw ? parseFloat(hargaRaw) : parseCurrency(tr.querySelector('.harga').value);
    const diskon = parseFloat(tr.querySelector('.diskon').value) || 0;
    const subtotal = qty * harga * (1 - diskon / 100);
    
    tr.querySelector('.subtotal').value = formatCurrency(subtotal);
    tr.querySelector('.subtotal').setAttribute('data-raw', subtotal);
}

function validateStock(tr) {
    const qtyInput = tr.querySelector('.jumlah');
    const qty = parseFloat(qtyInput.value) || 0;
    const maxStok = parseFloat(qtyInput.getAttribute('data-max-stok')) || 0;
    
    if (qty > maxStok) {
        alert('Stok tidak cukup! Stok tersedia: ' + maxStok.toLocaleString('id-ID') + ', Anda input: ' + qty.toLocaleString('id-ID'));
        qtyInput.value = maxStok;
        return false;
    }
    return true;
}

// Barcode processing functions
function processAutomaticBarcode(barcode) {
    console.log('Processing automatic barcode:', barcode);
    
    const product = productData[barcode];
    if (!product) {
        alert('Produk dengan barcode ' + barcode + ' tidak ditemukan!');
        return;
    }
    
    console.log('Found product:', product);
    
    // Find first empty row or add new row
    const table = document.getElementById('detailTableJual');
    const tbody = table.querySelector('tbody');
    let targetRow = null;
    
    // Look for empty row first
    for (let row of tbody.rows) {
        const select = row.querySelector('.produk-select');
        if (!select.value) {
            targetRow = row;
            break;
        }
    }
    
    // If no empty row, add new one
    if (!targetRow) {
        tambahBarisProduk();
        targetRow = tbody.rows[tbody.rows.length - 1];
    }
    
    // Set product in the row
    const select = targetRow.querySelector('.produk-select');
    select.value = product.id;
    
    // Trigger change event to set price and recalculate
    handleProdukChange(select);
    
    // Clear scanner
    document.getElementById('barcode-scanner').value = '';
    
    // Focus on quantity input
    targetRow.querySelector('.jumlah').focus();
    
    console.log('Barcode processed successfully');
}

function performRealTimeSearch(query) {
    console.log('Performing real-time search for:', query);
    
    const results = searchableProducts.filter(product => 
        product.searchText.includes(query.toLowerCase())
    );
    
    console.log('Search results:', results.length, 'products');
    
    const resultsContainer = document.getElementById('search-results');
    const resultsBody = document.getElementById('search-results-body');
    const searchCount = document.getElementById('search-count');
    
    if (results.length === 0) {
        resultsContainer.style.display = 'none';
        return;
    }
    
    // Update count
    searchCount.textContent = results.length;
    
    // Clear previous results
    resultsBody.innerHTML = '';
    
    // Add results
    results.forEach(product => {
        const div = document.createElement('div');
        div.className = 'search-result-item';
        div.style.cursor = 'pointer';
        div.style.padding = '8px';
        div.style.border = '1px solid #dee2e6';
        div.style.borderRadius = '4px';
        div.style.marginBottom = '4px';
        
        // Highlight matching text
        let displayName = product.nama;
        if (product.barcode && product.barcode.startsWith(query)) {
            displayName += ` <mark class="bg-warning">${product.barcode}</mark>`;
        }
        
        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${displayName}</strong><br>
                    <small class="text-muted">Harga: ${formatCurrency(product.harga)} | Stok: ${product.stok.toLocaleString('id-ID')}</small>
                </div>
                <button class="btn btn-sm btn-primary">Pilih</button>
            </div>
        `;
        
        div.addEventListener('click', () => selectProduct(product));
        resultsBody.appendChild(div);
    });
    
    // Show results
    resultsContainer.style.display = 'block';
}

function selectProduct(product) {
    console.log('Selecting product:', product);
    
    // Hide search results
    document.getElementById('search-results').style.display = 'none';
    
    // Clear scanner
    document.getElementById('barcode-scanner').value = '';
    
    // Find first empty row or add new row
    const table = document.getElementById('detailTableJual');
    const tbody = table.querySelector('tbody');
    let targetRow = null;
    
    // Look for empty row first
    for (let row of tbody.rows) {
        const select = row.querySelector('.produk-select');
        if (!select.value) {
            targetRow = row;
            break;
        }
    }
    
    // If no empty row, add new one
    if (!targetRow) {
        tambahBarisProduk();
        targetRow = tbody.rows[tbody.rows.length - 1];
    }
    
    // Set product in the row
    const select = targetRow.querySelector('.produk-select');
    select.value = product.id;
    
    // Trigger change event to set price and recalculate
    handleProdukChange(select);
    
    // Focus on quantity input
    targetRow.querySelector('.jumlah').focus();
}

function resetScannerState() {
    document.getElementById('barcode-scanner').value = '';
    document.getElementById('search-results').style.display = 'none';
    document.getElementById('scan-indicator').textContent = 'Siap Scan';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Edit page loaded - initializing...');
    
    // Initialize existing rows
    const table = document.getElementById('detailTableJual');
    table.querySelectorAll('tbody tr').forEach(tr => {
        const select = tr.querySelector('.produk-select');
        if (select.value) {
            handleProdukChange(select);
        }
        recalcRow(tr);
    });
    
    // Calculate initial total
    hitungTotal();
    
    console.log('Edit page initialization complete');
});
</script>
@endsection