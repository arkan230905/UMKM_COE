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
      <div class="col-md-4">
        <label class="form-label">Metode Bayar <span class="text-danger">*</span></label>
        <select name="metode_bayar" class="form-select" required>
          <option value="cash" {{ old('metode_bayar') == 'cash' ? 'selected' : '' }}>Cash</option>
          <option value="bank" {{ old('metode_bayar') == 'bank' ? 'selected' : '' }}>Bank</option>
        </select>
        @error('metode_bayar')
          <div class="text-danger small">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-4">
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
      <div class="col-md-4">
        <label class="form-label">Nominal Pembayaran <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="jumlah" class="form-control" value="{{ old('jumlah') }}" required placeholder="0.00">
        @error('jumlah')
          <div class="text-danger small">{{ $message }}</div>
        @enderror
      </div>
    </div>
    
    <div class="mb-3 mt-3">
      <label class="form-label">Keterangan</label>
      <textarea name="keterangan" class="form-control" rows="2" placeholder="Opsional">{{ old('keterangan') }}</textarea>
      @error('keterangan')
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
    
    // Event listener untuk Beban Operasional
    bebanOperasionalSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const budget = selectedOption.dataset.budget;
        
        // Tampilkan budget dari Beban Operasional
        budgetDisplay.value = budget || '';
    });
});
</script>
@endsection
