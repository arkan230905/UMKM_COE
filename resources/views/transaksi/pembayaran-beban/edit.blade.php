@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Edit Pembayaran Beban</h3>
  
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

  <form action="{{ route('transaksi.expense-payment.update', $row->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="mb-3">
      <label class="form-label">Tanggal <span class="text-danger">*</span></label>
      <input type="date" name="tanggal" class="form-control" value="{{ $row->tanggal }}" required>
    </div>
    
    <div class="mb-3">
      <label class="form-label">Beban Operasional <span class="text-danger">*</span></label>
      <select name="beban_operasional_id" id="bebanOperasionalSelect" class="form-select" required>
        <option value="">Pilih Beban Operasional</option>
        @foreach($bebanOperasional as $bo)
          <option value="{{ $bo->id }}" 
                  data-kategori="{{ $bo->kategori }}"
                  data-budget="{{ $bo->budget_bulanan_formatted }}"
                  data-coa-kode="{{ $bo->coa ? $bo->coa->kode_akun : '' }}"
                  data-coa-nama="{{ $bo->coa ? $bo->coa->nama_akun : '' }}"
                  {{ $row->beban_operasional_id == $bo->id ? 'selected' : '' }}>
            {{ $bo->nama_beban }}
          </option>
        @endforeach
      </select>
      <small class="form-text text-muted">Pilih beban yang sudah terdaftar di master data Beban Operasional</small>
    </div>
    
    <!-- Info Otomatis -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label">Kategori</label>
        <input type="text" id="kategoriDisplay" class="form-control" readonly placeholder="Akan muncul otomatis">
      </div>
      <div class="col-md-6">
        <label class="form-label">Budget Bulanan</label>
        <input type="text" id="budgetDisplay" class="form-control" readonly placeholder="Akan muncul otomatis">
      </div>
    </div>
    
    <div class="mb-3">
      <label class="form-label">Akun Beban <span class="text-danger">*</span></label>
      <input type="text" id="akunBebanDisplay" class="form-control" readonly placeholder="Akan muncul otomatis saat memilih Beban Operasional">
      <small class="form-text text-muted">Akun beban diambil otomatis dari Beban Operasional yang dipilih</small>
    </div>
    
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Metode Bayar <span class="text-danger">*</span></label>
        <select name="metode_bayar" class="form-select" required>
          <option value="cash" {{ $row->metode_bayar == 'cash' ? 'selected' : '' }}>Cash</option>
          <option value="bank" {{ $row->metode_bayar == 'bank' ? 'selected' : '' }}>Bank</option>
        </select>
      </div>
      
      <div class="col-md-4">
        <label class="form-label">Akun Kas/Bank <span class="text-danger">*</span></label>
        <select name="coa_kasbank" class="form-select" required>
          <option value="">Pilih Akun Kas/Bank</option>
          @foreach($coaKas as $k)
            <option value="{{ $k->kode_akun }}" {{ $row->coa_kasbank == $k->kode_akun ? 'selected' : '' }}>
              {{ $k->kode_akun }} - {{ $k->nama_akun }}
            </option>
          @endforeach
        </select>
      </div>
      
      <div class="col-md-4">
        <label class="form-label">Nominal Pembayaran <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="nominal_pembayaran" class="form-control" value="{{ $row->nominal_pembayaran }}" required placeholder="0.00">
      </div>
    </div>
    
    <div class="mb-3 mt-3">
      <label class="form-label">Keterangan</label>
      <textarea name="keterangan" class="form-control" rows="2" placeholder="Opsional">{{ $row->keterangan }}</textarea>
    </div>
    
    <button type="submit" class="btn btn-success">Update</button>
    <a href="{{ route('transaksi.pembayaran-beban.index') }}" class="btn btn-secondary">Batal</a>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bebanOperasionalSelect = document.getElementById('bebanOperasionalSelect');
    const kategoriDisplay = document.getElementById('kategoriDisplay');
    const budgetDisplay = document.getElementById('budgetDisplay');
    const akunBebanDisplay = document.getElementById('akunBebanDisplay');
    
    // Initialize with current selection
    const selectedOption = bebanOperasionalSelect.options[bebanOperasionalSelect.selectedIndex];
    if (bebanOperasionalSelect.value) {
        kategoriDisplay.value = selectedOption.dataset.kategori || '';
        budgetDisplay.value = selectedOption.dataset.budget || '';
        
        // Tampilkan akun COA jika ada
        const coaKode = selectedOption.dataset.coa_kode || '';
        const coaNama = selectedOption.dataset.coa_nama || '';
        
        if (coaKode && coaNama) {
            akunBebanDisplay.value = coaKode + ' - ' + coaNama;
        } else {
            akunBebanDisplay.value = 'Akun COA belum diatur untuk Beban Operasional ini';
        }
    }
    
    bebanOperasionalSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            kategoriDisplay.value = selectedOption.dataset.kategori || '';
            budgetDisplay.value = selectedOption.dataset.budget || '';
            
            // Tampilkan akun COA jika ada
            const coaKode = selectedOption.dataset.coa_kode || '';
            const coaNama = selectedOption.dataset.coa_nama || '';
            
            if (coaKode && coaNama) {
                akunBebanDisplay.value = coaKode + ' - ' + coaNama;
            } else {
                akunBebanDisplay.value = 'Akun COA belum diatur untuk Beban Operasional ini';
            }
        } else {
            kategoriDisplay.value = '';
            budgetDisplay.value = '';
            akunBebanDisplay.value = '';
        }
    });
});
</script>
@endsection
