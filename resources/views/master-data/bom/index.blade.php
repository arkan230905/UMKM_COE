<select id="produkSelect" class="form-select">
    <option value="">-- Pilih Produk --</option>
    @foreach($produks as $p)
        <option value="{{ $p->id }}">{{ $p->nama_produk }}</option>
    @endforeach
</select>

<div id="bomTable">
    <p class="text-muted">Pilih produk untuk melihat BOM.</p>
</div>

<script>
document.getElementById('produkSelect').addEventListener('change', function() {
    let produkId = this.value;
    let bomTable = document.getElementById('bomTable');

    if(produkId) {
        fetch(`/master-data/bom/view/${produkId}`)
            .then(response => response.text())
            .then(html => bomTable.innerHTML = html)
            .catch(err => {
                bomTable.innerHTML = '<p class="text-danger">Gagal memuat data BOM.</p>';
            });
    } else {
        bomTable.innerHTML = '<p class="text-muted">Pilih produk untuk melihat BOM.</p>';
    }
});
</script>
