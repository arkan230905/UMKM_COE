<script>
document.addEventListener('DOMContentLoaded', function () {
    function updateRow(row) {
        const select = row.querySelector('.bahanSelect');
        const jumlah = parseFloat(row.querySelector('.jumlahInput').value);
        const satuanCell = row.querySelector('.satuanCell');
        const hargaUtamaCell = row.querySelector('.hargaUtamaCell');
        const harga1Cell = row.querySelector('.harga1Cell');
        const harga2Cell = row.querySelector('.harga2Cell');
        const harga3Cell = row.querySelector('.harga3Cell');

        const satuan = select.selectedOptions[0]?.dataset.satuan || '';
        const hargaUtama = parseFloat(select.selectedOptions[0]?.dataset.harga || 0);

        satuanCell.textContent = satuan;
        hargaUtamaCell.textContent = hargaUtama ? `Rp ${hargaUtama.toLocaleString()}` : '-';

        let hargaLevels = [];
        if (satuan.toLowerCase() === 'kg') {
            hargaLevels = [hargaUtama / 10, hargaUtama / 1000, hargaUtama / 1000000];
        } else if (satuan.toLowerCase() === 'liter') {
            hargaLevels = [hargaUtama / 10, hargaUtama / 100, hargaUtama / 1000];
        } else {
            hargaLevels = [hargaUtama, hargaUtama, hargaUtama];
        }

        harga1Cell.textContent = hargaLevels[0] ? `Rp ${hargaLevels[0].toLocaleString()}` : '-';
        harga2Cell.textContent = hargaLevels[1] ? `Rp ${hargaLevels[1].toLocaleString()}` : '-';
        harga3Cell.textContent = hargaLevels[2] ? `Rp ${hargaLevels[2].toLocaleString()}` : '-';
    }

    document.querySelectorAll('#bomTable tbody tr').forEach(row => {
        updateRow(row);
        row.querySelector('.bahanSelect').addEventListener('change', () => updateRow(row));
        row.querySelector('.jumlahInput').addEventListener('input', () => updateRow(row));
    });

    document.getElementById('addRow').addEventListener('click', function () {
        const tbody = document.querySelector('#bomTable tbody');
        const clone = tbody.querySelector('tr').cloneNode(true);
        clone.querySelectorAll('input').forEach(input => input.value = 1);
        clone.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        tbody.appendChild(clone);
        clone.querySelector('.bahanSelect').addEventListener('change', () => updateRow(clone));
        clone.querySelector('.jumlahInput').addEventListener('input', () => updateRow(clone));
        clone.querySelector('.removeRow').addEventListener('click', () => clone.remove());
        updateRow(clone);
    });

    document.querySelectorAll('.removeRow').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('tr').remove());
    });
});
</script>
