@extends('layouts.app')

@section('title', 'Tambah Pembayaran Beban')

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
      <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
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
                  data-budget="{{ $bo->budget_bulanan ?? 0 }}"
                  {{ old('beban_operasional_id') == $bo->id ? 'selected' : '' }}>
            {{ $bo->nama_beban }}
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
      <label class="form-label">Nominal Pembayaran <span class="text-danger">*</span></label>
      <input type="text" name="nominal_pembayaran_formatted" class="form-control" value="{{ old('nominal_pembayaran') ? number_format(old('nominal_pembayaran'), 0, '.', '.') : '' }}" required placeholder="Masukkan nominal pembayaran">
      <input type="hidden" name="nominal_pembayaran" id="nominal_pembayaran_hidden" value="{{ old('nominal_pembayaran') }}">
      <small class="form-text text-muted">Nominal pembayaran aktual yang dibayarkan</small>
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
    
    <div class="mb-3">
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
        let value = input.value.replace(/\D/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
        }
        input.value = value;
        const numericValue = parseInt(value.replace(/\D/g, '')) || 0;
        nominalPembayaranHidden.value = numericValue;
    }
    
    // Event listener untuk nominal pembayaran input
    nominalPembayaranInput.addEventListener('input', function() {
        formatNumber(this);
    });
    
    nominalPembayaranInput.addEventListener('paste', function(e) {
        setTimeout(() => {
            formatNumber(this);
        }, 10);
    });
    
    nominalPembayaranInput.addEventListener('blur', function() {
        formatNumber(this);
    });
    
    // Event listener untuk Beban Operasional
    bebanOperasionalSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const budget = selectedOption.dataset.budget;
        budgetDisplay.value = budget ? 'Rp ' + parseInt(budget).toLocaleString('id-ID') : '';
    });
    
    // Form submission handler
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const currentValue = nominalPembayaranInput.value.replace(/\D/g, '');
        nominalPembayaranHidden.value = currentValue || 0;
        
        const tanggal = document.querySelector('input[name="tanggal"]').value;
        const bebanOperasionalId = bebanOperasionalSelect.value;
        const kodeAkunBeban = document.querySelector('select[name="kode_akun_beban"]').value;
        const nominalPembayaran = nominalPembayaranHidden.value;
        
        const errors = [];
        if (!tanggal) errors.push('Tanggal harus diisi');
        if (!bebanOperasionalId) errors.push('Beban Operasional harus dipilih');
        if (!kodeAkunBeban) errors.push('Akun Beban harus dipilih');
        if (!nominalPembayaran || nominalPembayaran == 0) errors.push('Nominal Pembayaran harus diisi');
        
        if (errors.length > 0) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang required:\n\n' + errors.join('\n'));
            return false;
        }
        
        return true;
    });
});
</script>
@endsection
