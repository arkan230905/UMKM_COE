<script>
document.addEventListener('DOMContentLoaded', function () {
    function updateRow(row) {
        const select = row.querySelector('.bahanSelect');
        const satuanCell = row.querySelector('.satuanCell');
        const satuanResep = row.querySelector('.satuanSelect');

        const satuan = select.selectedOptions[0]?.dataset.satuan || '';
        // Tampilkan info satuan bahan di placeholder jika belum dipilih
        if (satuanResep && (!satuanResep.value || satuanResep.value === '')) {
            satuanResep.options[0].textContent = `(ikuti satuan bahan)`;
        }
        if (satuanCell) satuanCell.textContent = satuan;
    }

    function wireRow(row) {
        const bahanSel = row.querySelector('.bahanSelect');
        const qtyInput = row.querySelector('.jumlahInput');
        const removeBtn = row.querySelector('.removeRow');
        if (bahanSel) bahanSel.addEventListener('change', () => updateRow(row));
        if (qtyInput) qtyInput.addEventListener('input', () => updateRow(row));
        if (removeBtn) removeBtn.addEventListener('click', () => row.remove());
        updateRow(row);
    }

    document.querySelectorAll('#bomTable tbody tr').forEach(row => wireRow(row));

    const addBtn = document.getElementById('addRow');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            const tbody = document.querySelector('#bomTable tbody');
            const first = tbody.querySelector('tr');
            const clone = first.cloneNode(true);
            // reset values
            const bahanSel = clone.querySelector('.bahanSelect');
            if (bahanSel) bahanSel.selectedIndex = 0;
            const qtyInput = clone.querySelector('.jumlahInput');
            if (qtyInput) qtyInput.value = 1;
            const satuanResep = clone.querySelector('.satuanSelect');
            if (satuanResep) satuanResep.selectedIndex = 0;
            tbody.appendChild(clone);
            wireRow(clone);
        });
    }

    // Client-side guard: prevent submit if any selected bahan has harga 0
    const form = document.querySelector('form[action*="master-data/\x62\x6f\x6d"]');
    if (form) {
        form.addEventListener('submit', function (e) {
            const zeroPriced = [];
            document.querySelectorAll('#bomTable tbody tr').forEach(row => {
                const opt = row.querySelector('.bahanSelect')?.selectedOptions[0];
                if (!opt) return;
                const harga = parseFloat(opt.dataset.harga || '0');
                const nama = opt.textContent?.trim() || 'Bahan';
                if (!isNaN(harga) && harga <= 0) zeroPriced.push(nama);
            });
            if (zeroPriced.length > 0) {
                e.preventDefault();
                alert('Bahan baku berikut belum pernah dibeli (harga belum ada):\n- ' + zeroPriced.join('\n- '));
            }
        });
    }
});
</script>
<?php /**PATH C:\UMKM_COE\resources\views/master-data/bom/js.blade.php ENDPATH**/ ?>