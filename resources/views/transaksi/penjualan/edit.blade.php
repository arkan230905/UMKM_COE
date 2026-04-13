@extends('layouts.app')
@section('content')
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
                <label class="form-label">Metode Pembayaran</label>
                <select name="payment_method" id="payment_method_jual" class="form-select" required>
                    <option value="">-- Pilih Metode --</option>
                    <option value="cash" {{ ($penjualan->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="transfer" {{ ($penjualan->payment_method ?? '') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                    <option value="credit" {{ ($penjualan->payment_method ?? '') == 'credit' ? 'selected' : '' }}>Kredit</option>
                </select>
            </div>
            <div class="col-md-3" id="sumber_dana_wrapper_jual">
                <label class="form-label">Terima di</label>
                <select name="sumber_dana" id="sumber_dana_jual" class="form-select">
                    @foreach($kasbank as $kb)
                        <option value="{{ $kb->kode_akun }}">
                            {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
                        </option>
                    @endforeach
                </select>
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
                        <th style="width:6%"><button class="btn btn-success btn-sm" type="button" id="addRowJual">+</button></th>
                    </tr>
                </thead>
                <tbody>
                    @if($penjualan->details->count() > 0)
                        @foreach($penjualan->details as $detail)
                        <tr>
                            <td>
                                <select name="produk_id[]" class="form-select produk-select" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($produks as $p)
                                        <option value="{{ $p->id }}" 
                                                data-price="{{ round($p->harga_jual ?? 0) }}"
                                                data-stok="{{ $p->stok_tersedia ?? 0 }}"
                                                {{ $detail->produk_id == $p->id ? 'selected' : '' }}>
                                            {{ $p->nama_produk ?? $p->nama }} (Stok: {{ number_format($p->stok_tersedia ?? 0, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted stok-info"></small>
                            </td>
                            <td><input type="number" step="1" min="1" name="jumlah[]" class="form-control jumlah" value="{{ $detail->jumlah }}" required></td>
                            <td><input type="text" name="harga_satuan[]" class="form-control harga" value="Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}" readonly required></td>
                            <td><input type="number" step="0.01" min="0" max="100" name="diskon_persen[]" class="form-control diskon" value="{{ $detail->diskon_persen ?? 0 }}"></td>
                            <td><input type="text" class="form-control subtotal" value="Rp {{ number_format($detail->subtotal, 0, ',', '.') }}" readonly></td>
                            <td><button type="button" class="btn btn-danger btn-sm removeRow">-</button></td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>
                                <select name="produk_id[]" class="form-select produk-select" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($produks as $p)
                                        <option value="{{ $p->id }}" 
                                                data-price="{{ round($p->harga_jual ?? 0) }}"
                                                data-stok="{{ $p->stok_tersedia ?? 0 }}"
                                                {{ $penjualan->produk_id == $p->id ? 'selected' : '' }}>
                                            {{ $p->nama_produk ?? $p->nama }} (Stok: {{ number_format($p->stok_tersedia ?? 0, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted stok-info"></small>
                            </td>
                            <td><input type="number" step="1" min="1" name="jumlah[]" class="form-control jumlah" value="{{ $penjualan->jumlah }}" required></td>
                            <td><input type="text" name="harga_satuan[]" class="form-control harga" value="0" readonly required></td>
                            <td><input type="number" step="0.01" min="0" max="100" name="diskon_persen[]" class="form-control diskon" value="0"></td>
                            <td><input type="text" class="form-control subtotal" value="0" readonly></td>
                            <td><button type="button" class="btn btn-danger btn-sm removeRow">-</button></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-3 ms-auto">
                <label class="form-label">Subtotal Produk</label>
                <input type="text" name="subtotal_produk" class="form-control" value="0" readonly>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Biaya Ongkir</label>
                <input type="number" step="0.01" min="0" name="biaya_ongkir" class="form-control" value="0" id="biaya_ongkir">
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya Service</label>
                <input type="number" step="0.01" min="0" name="biaya_service" class="form-control" value="0" id="biaya_service">
            </div>
            <div class="col-md-3">
                <label class="form-label">PPN (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="ppn_persen" class="form-control" value="11" id="ppn_persen">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total PPN</label>
                <input type="text" name="total_ppn" class="form-control" value="0" readonly id="total_ppn">
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4 ms-auto">
                <label class="form-label">Total Final</label>
                <input type="text" name="total" class="form-control" value="0" readonly id="total_final">
            </div>
        </div>

        <div class="text-end mt-4">
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">Batal</a>
            <button class="btn btn-success">Update</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('detailTableJual');
    const addBtn = document.getElementById('addRowJual');
    
    // Show/hide sumber dana based on payment method
    function toggleSumberDana() {
        const paymentMethod = document.getElementById('payment_method_jual').value;
        const sumberDanaWrapper = document.getElementById('sumber_dana_wrapper_jual');
        const sumberDana = document.getElementById('sumber_dana_jual');
        
        if (paymentMethod === 'cash' || paymentMethod === 'transfer') {
            sumberDanaWrapper.style.display = 'block';
            sumberDana.required = true;
            
            // Update options based on payment method
            if (paymentMethod === 'cash') {
                sumberDana.innerHTML = `
                    <option value="1101">Kas (1101)</option>
                `;
            } else if (paymentMethod === 'transfer') {
                sumberDana.innerHTML = `
                    <option value="1102">Kas di Bank (1102)</option>
                `;
            }
        } else {
            sumberDanaWrapper.style.display = 'none';
            sumberDana.required = false;
        }
    }
    
    // Initial toggle
    toggleSumberDana();
    
    // Listen to payment method changes
    document.getElementById('payment_method_jual').addEventListener('change', toggleSumberDana);

    function formatCurrency(value) {
        const roundedValue = Math.round(parseFloat(value) * 1000) / 1000;
        return 'Rp ' + roundedValue.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 3 });
    }
    
    function parseCurrency(formattedValue) {
        return parseFloat(formattedValue.replace(/[^\d]/g, '')) || 0;
    }

    function recalcRow(tr) {
        const q = Math.round(parseFloat(tr.querySelector('.jumlah').value) || 0);
        tr.querySelector('.jumlah').value = q; // Ensure integer display
        const p = parseCurrency(tr.querySelector('.harga').value) || 0;
        const dPct = Math.min(Math.max(parseFloat(tr.querySelector('.diskon').value) || 0, 0), 100);
        const sub = q * p;
        const dNom = sub * (dPct/100.0);
        const line = Math.max(sub - dNom, 0);
        tr.querySelector('.subtotal').value = formatCurrency(line);
    }

    function recalcTotal() {
        let sum = 0;
        table.querySelectorAll('tbody tr').forEach(tr => {
            const val = (tr.querySelector('.subtotal').value || 'Rp 0').replace(/[^\d]/g,'');
            sum += parseFloat(val) || 0;
        });
        
        // Update subtotal produk
        const subtotalProdukInput = document.querySelector('input[name="subtotal_produk"]');
        if (subtotalProdukInput) {
            subtotalProdukInput.value = formatCurrency(sum);
        }
        
        // Get additional costs
        const biayaOngkir = parseFloat(document.getElementById('biaya_ongkir').value) || 0;
        const biayaService = parseFloat(document.getElementById('biaya_service').value) || 0;
        const ppnPersen = parseFloat(document.getElementById('ppn_persen').value) || 0;
        
        // Calculate PPN base (subtotal + ongkir + service)
        const ppnBase = sum + biayaOngkir + biayaService;
        const totalPPN = ppnBase * (ppnPersen / 100);
        
        // Update PPN
        const totalPPNInput = document.getElementById('total_ppn');
        if (totalPPNInput) {
            totalPPNInput.value = formatCurrency(totalPPN);
        }
        
        // Calculate final total
        const finalTotal = sum + biayaOngkir + biayaService + totalPPN;
        
        // Update total
        const totalInput = document.getElementById('total_final');
        if (totalInput) {
            totalInput.value = formatCurrency(finalTotal);
        }
    }

    function setPriceFromSelect(tr) {
        const sel = tr.querySelector('.produk-select');
        const opt = sel.options[sel.selectedIndex];
        const price = parseFloat(opt?.getAttribute('data-price') || '0') || 0;
        const stok = parseFloat(opt?.getAttribute('data-stok') || '0') || 0;
        
        tr.querySelector('.harga').value = formatCurrency(price);
        
        // Update stok info
        const stokInfo = tr.querySelector('.stok-info');
        if (stokInfo && opt.value) {
            stokInfo.textContent = `Stok tersedia: ${stok.toLocaleString()}`;
            stokInfo.style.color = stok > 0 ? '#28a745' : '#dc3545';
        }
        
        // Set max qty to available stock
        const qtyInput = tr.querySelector('.jumlah');
        qtyInput.setAttribute('data-max-stok', stok);
        
        recalcRow(tr); recalcTotal();
    }
    
    function validateStock(tr) {
        const qtyInput = tr.querySelector('.jumlah');
        let qty = parseFloat(qtyInput.value) || 0;
        qty = Math.round(qty); // Round to nearest integer
        qtyInput.value = qty; // Update display
        
        const maxStok = parseFloat(qtyInput.getAttribute('data-max-stok') || '0') || 0;
        
        if (qty > maxStok) {
            alert(`Stok tidak cukup! Stok tersedia: ${maxStok.toLocaleString()}, Anda input: ${qty.toLocaleString()}`);
            qtyInput.value = maxStok;
            qtyInput.style.borderColor = '#dc3545';
            return false;
        } else {
            qtyInput.style.borderColor = '';
            return true;
        }
    }

    addBtn.addEventListener('click', () => {
        const tbody = table.querySelector('tbody');
        const clone = tbody.rows[0].cloneNode(true);
        clone.querySelectorAll('input').forEach(inp => {
            if (inp.classList.contains('jumlah')) inp.value = 1;
            else if (inp.classList.contains('harga')) inp.value = 'Rp 0';
            else if (inp.classList.contains('diskon')) inp.value = 0;
            else if (inp.classList.contains('subtotal')) inp.value = 'Rp 0';
        });
        clone.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
        table.querySelector('tbody').appendChild(clone);
    });

    table.addEventListener('change', (e) => {
        if (e.target && e.target.classList.contains('produk-select')) {
            const tr = e.target.closest('tr');
            setPriceFromSelect(tr);
        }
    });
    table.addEventListener('input', (e) => {
        if (e.target && (e.target.classList.contains('jumlah') || e.target.classList.contains('harga') || e.target.classList.contains('diskon'))) {
            const tr = e.target.closest('tr');
            
            // Validate stock if qty changed
            if (e.target.classList.contains('jumlah')) {
                validateStock(tr);
            }
            
            recalcRow(tr); recalcTotal();
        }
    });
    
    // Listen to additional cost changes
    document.getElementById('biaya_ongkir').addEventListener('input', recalcTotal);
    document.getElementById('biaya_service').addEventListener('input', recalcTotal);
    document.getElementById('ppn_persen').addEventListener('input', recalcTotal);
    table.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('removeRow')) {
            const rows = table.querySelectorAll('tbody tr');
            if (rows.length > 1) e.target.closest('tr').remove();
            recalcTotal();
        }
    });

    // Init all rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        setPriceFromSelect(tr);
        recalcRow(tr);
    });
    recalcTotal();
    
    // Validate before submit
    document.getElementById('form-penjualan').addEventListener('submit', function(e) {
        let hasError = false;
        table.querySelectorAll('tbody tr').forEach(tr => {
            const sel = tr.querySelector('.produk-select');
            if (sel.value) {
                if (!validateStock(tr)) {
                    hasError = true;
                }
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Mohon perbaiki jumlah produk yang melebihi stok tersedia!');
            return false;
        }
    });
});

// Helper function for PHP formatCurrency
function formatCurrency(value) {
    const roundedValue = Math.round(parseFloat(value) * 1000) / 1000;
    return 'Rp ' + roundedValue.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 3 });
}

// Auto-update sumber dana berdasarkan metode pembayaran
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('payment_method_jual');
    const sumberDanaSelect = document.getElementById('sumber_dana_jual');
    
    // Fungsi untuk update sumber dana berdasarkan metode pembayaran
    function updateSumberDana() {
        const paymentMethod = paymentMethodSelect.value;
        let targetKode = '';
        
        // Tentukan kode akun target berdasarkan metode pembayaran
        switch(paymentMethod) {
            case 'cash':
                // Pilih akun Kas (112 atau 113) - prioritaskan 112
                targetKode = '112';
                break;
            case 'transfer':
                // Pilih akun Bank (111)
                targetKode = '111';
                break;
            case 'credit':
                // Pilih akun Piutang (118)
                targetKode = '118';
                break;
        }
        
        // Cari dan pilih option yang sesuai
        if (targetKode) {
            const targetOption = Array.from(sumberDanaSelect.options).find(option => 
                option.value === targetKode
            );
            
            if (targetOption) {
                sumberDanaSelect.value = targetKode;
            } else {
                // Jika kode target tidak ditemukan, coba fallback
                if (paymentMethod === 'cash') {
                    // Coba cari akun kas lainnya (113)
                    const fallbackOption = Array.from(sumberDanaSelect.options).find(option => 
                        option.value === '113'
                    );
                    if (fallbackOption) {
                        sumberDanaSelect.value = '113';
                    }
                }
            }
        }
    }
    
    // Event listener untuk perubahan metode pembayaran
    paymentMethodSelect.addEventListener('change', updateSumberDana);
    
    // Initialize saat pertama kali load hanya jika ada metode yang dipilih
    if (paymentMethodSelect.value) {
        updateSumberDana();
    }
});
</script>
@endsection