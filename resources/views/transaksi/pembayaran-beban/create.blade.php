@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Tambah Pembayaran Beban</h3>
  
  @if($errors->any())
    <div class="alert alert-danger">
      <strong>Error!</strong>
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  
  @if(session('error'))
    <div class="alert alert-danger">
      <strong>Error!</strong> {{ session('error') }}
    </div>
  @endif
  
  @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif
  
  <form action="{{ route('transaksi.pembayaran-beban.store') }}" method="POST">@csrf
    <div class="mb-3">
      <label class="form-label">Tanggal <span class="text-danger">*</span></label>
      <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}" required>
      @error('tanggal')
        <div class="text-danger small">{{ $message }}</div>
      @enderror
    </div>
    
    <div class="mb-3">
      <label class="form-label">Beban Operasional <span class="text-danger">*</span></label>
      <select name="beban_operasional_id" id="bebanOperasionalSelect" class="form-select" required>
        <option value="">Pilih Beban Operasional</option>
        @foreach($bebanOperasional as $bo)
          <option value="{{ $bo->id }}" 
                  data-coa-id="{{ $bo->coa ? $bo->coa->id : '' }}"
                  data-coa-kode="{{ $bo->coa ? $bo->coa->kode_akun : '' }}"
                  data-coa-nama="{{ $bo->coa ? $bo->coa->nama_akun : '' }}"
                  data-budget="{{ $bo->budget_bulanan_formatted ?? '' }}"
                  {{ old('beban_operasional_id') == $bo->id ? 'selected' : '' }}>
            {{ $bo->nama_beban }} @if($bo->coa)({{ $bo->coa->kode_akun }})@endif
          </option>
        @endforeach
      </select>
      <small class="form-text text-muted">Pilih beban yang sudah terdaftar di master data Beban Operasional</small>
      @if($bebanOperasional->isEmpty())
        <div class="text-warning small mt-1">
          <strong>Info:</strong> Tidak ada Beban Operasional yang aktif. Silakan tambahkan data terlebih dahulu.
        </div>
      @endif
      @error('beban_operasional_id')
        <div class="text-danger small">{{ $message }}</div>
      @enderror
    </div>
    
    <!-- Info Otomatis -->
    <div class="mb-3">
      <label class="form-label">Budget Bulanan</label>
      <input type="text" id="budgetDisplay" class="form-control" readonly placeholder="Akan muncul otomatis">
    </div>
    
    <div class="mb-3">
      <label class="form-label">Nominal Pembayaran Sesungguhnya <span class="text-danger">*</span></label>
      <input type="text" name="nominal_pembayaran_formatted" class="form-control" value="{{ old('nominal_pembayaran') ? number_format(old('nominal_pembayaran'), 0, '.', '.') : '' }}" required placeholder="Masukkan nominal pembayaran yang sebenarnya">
      <input type="hidden" name="nominal_pembayaran" id="nominal_pembayaran_hidden" value="{{ old('nominal_pembayaran') }}">
      <small class="form-text text-muted">Nominal pembayaran aktual yang dibayarkan (bisa berbeda dengan budget)</small>
      @error('nominal_pembayaran')
        <div class="text-danger small">{{ $message }}</div>
      @enderror
    </div>
    
    <div class="mb-3">
      <label class="form-label">Akun Beban <span class="text-danger">*</span></label>
      <select name="kode_akun_beban" class="form-select" required>
        <option value="">Pilih Akun Beban</option>
        @foreach($coaBebans as $akun)
          <option value="{{ $akun->kode_akun }}" 
                  {{ old('kode_akun_beban') == $akun->kode_akun ? 'selected' : '' }}>
            {{ $akun->kode_akun }} - {{ $akun->nama_akun }}
          </option>
        @endforeach
      </select>
      <small class="form-text text-muted">Akun beban diambil dari tabel COA (kategori Expense)</small>
      @if($coaBebans->isEmpty())
        <div class="text-warning small mt-1">
          <strong>Info:</strong> Tidak ada akun beban dengan kategori Expense di tabel COA.
        </div>
      @endif
      @error('kode_akun_beban')
        <div class="text-danger small">{{ $message }}</div>
      @enderror
    </div>
    
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Metode Bayar <span class="text-danger">*</span></label>
        <select name="metode_bayar" class="form-select" required>
          <option value="cash" {{ old('metode_bayar') == 'cash' ? 'selected' : '' }}>Cash</option>
          <option value="bank" {{ old('metode_bayar') == 'bank' ? 'selected' : '' }}>Bank</option>
        </select>
        @error('metode_bayar')
          <div class="text-danger small">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-6">
        <label class="form-label">Akun Kas/Bank <span class="text-danger">*</span></label>
        <select name="kode_akun_kas" class="form-select" required>
          <option value="">Pilih Akun Kas/Bank</option>
          @foreach($akunKas as $k)
            <option value="{{ $k->kode_akun }}" {{ old('kode_akun_kas') == $k->kode_akun ? 'selected' : '' }}>
              {{ $k->kode_akun }} - {{ $k->nama_akun }}
            </option>
          @endforeach
        </select>
        @error('kode_akun_kas')
          <div class="text-danger small">{{ $message }}</div>
        @enderror
      </div>
    </div>
    
    <div class="mb-3 mt-3">
      <label class="form-label">Catatan</label>
      <textarea name="catatan" class="form-control" rows="2" placeholder="Masukkan catatan pembayaran (opsional)">{{ old('catatan') }}</textarea>
      @error('catatan')
        <div class="text-danger small">{{ $message }}</div>
      @enderror
    </div>
    
    <button type="submit" class="btn btn-success">Simpan</button>
    <a href="{{ route('transaksi.pembayaran-beban.index') }}" class="btn btn-secondary">Batal</a>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bebanOperasionalSelect = document.getElementById('bebanOperasionalSelect');
    const budgetDisplay = document.getElementById('budgetDisplay');
    const nominalPembayaranInput = document.querySelector('input[name="nominal_pembayaran_formatted"]');
    const nominalPembayaranHidden = document.getElementById('nominal_pembayaran_hidden');
    
    // Format number with thousand separators
    function formatNumber(input) {
        // Remove all non-digit characters
        let value = input.value.replace(/\D/g, '');
        
        // Convert to number and format with thousand separators
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
        }
        
        // Update input value
        input.value = value;
        
        // Update hidden field with numeric value
        const numericValue = parseInt(value.replace(/\D/g, '')) || 0;
        nominalPembayaranHidden.value = numericValue;
    }
    
    // Get numeric value from formatted string
    function getNumericValue(formattedString) {
        return parseInt(formattedString.replace(/\D/g, '')) || 0;
    }
    
    // Event listener untuk nominal pembayaran input
    nominalPembayaranInput.addEventListener('input', function() {
        formatNumber(this);
    });
    
    // Event listener untuk nominal pembayaran input (paste event)
    nominalPembayaranInput.addEventListener('paste', function(e) {
        // Wait for paste to complete, then format
        setTimeout(() => {
            formatNumber(this);
        }, 10);
    });
    
    // Event listener untuk nominal pembayaran input (blur event)
    nominalPembayaranInput.addEventListener('blur', function() {
        formatNumber(this);
    });
    
    // Event listener untuk Beban Operasional
    bebanOperasionalSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const budget = selectedOption.dataset.budget;
        
        // Tampilkan budget dari Beban Operasional
        budgetDisplay.value = budget || '';
    });
    
    // Form submission handler
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        console.log('=== FORM SUBMISSION STARTED ===');
        
        // Ensure hidden field is updated before submission
        const currentValue = nominalPembayaranInput.value.replace(/\D/g, '');
        nominalPembayaranHidden.value = currentValue || 0;
        
        // Validate required fields
        const tanggal = document.querySelector('input[name="tanggal"]').value;
        const bebanOperasionalId = document.querySelector('select[name="beban_operasional_id"]').value;
        const kodeAkunBeban = document.querySelector('select[name="kode_akun_beban"]').value;
        const kodeAkunKas = document.querySelector('select[name="kode_akun_kas"]').value;
        const metodeBayar = document.querySelector('select[name="metode_bayar"]').value;
        const keterangan = document.querySelector('textarea[name="keterangan"]').value;
        const nominalPembayaran = nominalPembayaranHidden.value;
        
        console.log('Form data:', {
            tanggal,
            bebanOperasionalId,
            kodeAkunBeban,
            kodeAkunKas,
            metodeBayar,
            keterangan,
            nominalPembayaran
        });
        
        // Check for missing required fields
        const errors = [];
        if (!tanggal) errors.push('Tanggal harus diisi');
        if (!bebanOperasionalId) errors.push('Beban Operasional harus dipilih');
        if (!kodeAkunBeban) errors.push('Akun Beban harus dipilih');
        if (!kodeAkunKas) errors.push('Akun Kas/Bank harus dipilih');
        if (!metodeBayar) errors.push('Metode Bayar harus dipilih');
        if (!keterangan) errors.push('Keterangan harus diisi');
        if (!nominalPembayaran || nominalPembayaran == 0) errors.push('Nominal Pembayaran harus diisi');
        
        if (errors.length > 0) {
            console.error('VALIDATION ERRORS:', errors);
            e.preventDefault(); // Prevent submission only if there are errors
            alert('Mohon lengkapi semua field yang required:\n\n' + errors.join('\n'));
            return false;
        }
        
        console.log('=== VALIDATION PASSED, SUBMITTING FORM ===');
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        
        // Allow form to submit normally if validation passes
        return true;
    });
});
</script>
@endsection
