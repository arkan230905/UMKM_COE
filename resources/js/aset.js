document.addEventListener('DOMContentLoaded', function(){
  const kategori = document.getElementById('kategori');
  const jenis = document.getElementById('jenis_aset');
  if (!kategori || !jenis) return;
  // Map each kategori to one of the two existing options in the form
  const map = {
    // Aset Lancar -> Aset Tidak Tetap
    'Kas':'Aset Tidak Tetap','Bank':'Aset Tidak Tetap','Piutang Usaha':'Aset Tidak Tetap','Piutang Lain-lain':'Aset Tidak Tetap','Persediaan Bahan Baku':'Aset Tidak Tetap','Persediaan Barang Dagang':'Aset Tidak Tetap','Uang Muka':'Aset Tidak Tetap','Beban Dibayar Dimuka':'Aset Tidak Tetap',
    // Aset Tetap -> Aset Tetap
    'Tanah':'Aset Tetap','Bangunan':'Aset Tetap','Kendaraan':'Aset Tetap','Mesin':'Aset Tetap','Peralatan':'Aset Tetap','Peralatan Kantor':'Aset Tetap','Peralatan Dapur':'Aset Tetap','Komputer & Elektronik':'Aset Tetap','Furniture':'Aset Tetap',
    // Aset Tak Berwujud -> Aset Tidak Tetap
    'Hak Paten':'Aset Tidak Tetap','Merek Dagang':'Aset Tidak Tetap','Hak Cipta':'Aset Tidak Tetap','Lisensi/Software':'Aset Tidak Tetap','Goodwill':'Aset Tidak Tetap',
    // Aset Lain-lain -> Aset Tidak Tetap
    'Deposito Jangka Panjang':'Aset Tidak Tetap','Investasi Jangka Panjang':'Aset Tidak Tetap','Aset Dalam Pengerjaan':'Aset Tidak Tetap'
  };
  function syncJenis(){
    const val = kategori.value;
    const target = map[val] || '';
    if(!target) return;
    for (const opt of jenis.options){ if(opt.value === target){ jenis.value = target; break; } }
  }
  kategori.addEventListener('change', syncJenis);
  syncJenis();
});
