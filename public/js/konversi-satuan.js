document.addEventListener('DOMContentLoaded', function() {
    let konversiIndex = 0;
    const konversiList = document.getElementById('konversiList');
    const emptyState = document.getElementById('emptyState');
    const satuanUtamaSelect = document.getElementById('satuan_utama');
    const tambahKonversiBtn = document.getElementById('tambahKonversi');
    const form = document.getElementById('konversiForm');
    
    // Cache untuk menyimpan data konversi yang sudah ada
    let conversionCache = {};
    
    // Fungsi untuk update tampilan empty state
    function updateEmptyState() {
        const items = konversiList.querySelectorAll('.konversi-item');
        emptyState.style.display = items.length === 0 ? 'block' : 'none';
    }
    
    // Fungsi untuk menambah item konversi
    function tambahKonversiItem() {
        const template = document.getElementById('konversiItemTemplate');
        const clone = template.content.cloneNode(true);
        
        // Update index
        const konversiItem = clone.querySelector('.konversi-item');
        konversiItem.setAttribute('data-index', konversiIndex);
        
        // Update name attributes
        const jumlahInput = clone.querySelector('.jumlah-input');
        const satuanInput = clone.querySelector('.satuan-input');
        
        jumlahInput.setAttribute('name', `konversi[${konversiIndex}][jumlah]`);
        satuanInput.setAttribute('name', `konversi[${konversiIndex}][satuan_id]`);
        
        // Filter satuan utama dari dropdown
        updateSatuanDropdown(satuanInput);
        
        // Event listeners
        jumlahInput.addEventListener('input', debounce(updateHasilKonversi, 300));
        satuanInput.addEventListener('change', updateHasilKonversi);
        
        clone.querySelector('.hapus-konversi').addEventListener('click', function() {
            hapusKonversiItem(konversiItem);
        });
        
        konversiList.appendChild(clone);
        konversiIndex++;
        updateEmptyState();
    }
    
    // Fungsi untuk filter satuan utama dari dropdown
    function updateSatuanDropdown(dropdown) {
        const satuanUtamaId = satuanUtamaSelect.value;
        if (satuanUtamaId) {
            // Hapus opsi satuan utama dari dropdown
            const options = dropdown.querySelectorAll('option');
            options.forEach(option => {
                if (option.value === satuanUtamaId) {
                    option.style.display = 'none';
                } else {
                    option.style.display = 'block';
                }
            });
        }
    }
    
    // Fungsi untuk menghapus item konversi
    function hapusKonversiItem(item) {
        item.remove();
        updateEmptyState();
    }
    
    // Fungsi untuk update hasil konversi
    function updateHasilKonversi(event) {
        const item = event.target.closest('.konversi-item');
        const jumlah = parseFloat(item.querySelector('.jumlah-input').value) || 0;
        const satuanUtamaId = satuanUtamaSelect.value;
        const satuanId = item.querySelector('.satuan-input').value;
        const hasilInput = item.querySelector('.hasil-konversi');
        
        if (jumlah > 0 && satuanUtamaId && satuanId) {
            // Jika satuan yang dipilih sama dengan satuan utama
            if (satuanId === satuanUtamaId) {
                hasilInput.value = formatNumber(jumlah);
                hasilInput.style.color = '#0d6efd';
            } else {
                // Cek apakah ada aturan konversi yang sudah ada
                const cacheKey = `${satuanId}-${satuanUtamaId}`;
                if (conversionCache[cacheKey]) {
                    const hasil = jumlah * conversionCache[cacheKey];
                    hasilInput.value = formatNumber(hasil);
                    hasilInput.style.color = '#0d6efd';
                } else {
                    // Coba cari konversi dari database
                    cariKonversiDatabase(satuanId, satuanUtamaId).then(ratio => {
                        if (ratio !== null) {
                            conversionCache[cacheKey] = ratio;
                            const hasil = jumlah * ratio;
                            hasilInput.value = formatNumber(hasil);
                            hasilInput.style.color = '#0d6efd';
                        } else {
                            hasilInput.value = 'Konversi belum diatur';
                            hasilInput.style.color = '#dc3545';
                        }
                    });
                }
            }
        } else {
            hasilInput.value = '0';
            hasilInput.style.color = '#0d6efd';
        }
    }
    
    // Fungsi untuk mencari konversi di database via API
    async function cariKonversiDatabase(dariId, keId) {
        try {
            const response = await fetch(`/satuan/api/konversi`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    jumlah: 1,
                    dari_satuan_id: dariId,
                    ke_satuan_id: keId
                })
            });
            
            const data = await response.json();
            if (data.success) {
                return data.hasil;
            }
            return null;
        } catch (error) {
            console.error('Error fetching conversion:', error);
            return null;
        }
    }
    
    // Fungsi untuk format number
    function formatNumber(num) {
        return num.toFixed(6).replace(/\.?0+$/, '');
    }
    
    // Fungsi debounce untuk mengurangi API calls
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Fungsi untuk validasi form sebelum submit
    function validateForm() {
        const satuanUtamaId = satuanUtamaSelect.value;
        if (!satuanUtamaId) {
            alert('Silakan pilih satuan utama terlebih dahulu!');
            return false;
        }
        
        const items = konversiList.querySelectorAll('.konversi-item');
        if (items.length === 0) {
            alert('Tambahkan minimal satu aturan konversi!');
            return false;
        }
        
        let isValid = true;
        items.forEach(item => {
            const jumlah = parseFloat(item.querySelector('.jumlah-input').value) || 0;
            const satuanId = item.querySelector('.satuan-input').value;
            const hasilInput = item.querySelector('.hasil-konversi');
            
            if (jumlah <= 0 || !satuanId) {
                isValid = false;
                item.classList.add('border-danger');
            } else {
                item.classList.remove('border-danger');
            }
            
            if (hasilInput.value === 'Konversi belum diatur') {
                isValid = false;
                alert('Ada konversi yang belum diatur dengan benar!');
                return false;
            }
        });
        
        return isValid;
    }
    
    // Event listeners
    tambahKonversiBtn.addEventListener('click', tambahKonversiItem);
    
    satuanUtamaSelect.addEventListener('change', function() {
        // Update label satuan utama di semua item
        const satuanUtamaLabel = this.options[this.selectedIndex]?.text || 'satuan';
        document.querySelectorAll('.satuan-utama-label').forEach(label => {
            label.textContent = satuanUtamaLabel;
        });
        
        // Update semua dropdown satuan
        document.querySelectorAll('.satuan-input').forEach(dropdown => {
            updateSatuanDropdown(dropdown);
        });
        
        // Clear cache dan re-calculate all conversions
        conversionCache = {};
        document.querySelectorAll('.konversi-item').forEach(item => {
            const jumlahInput = item.querySelector('.jumlah-input');
            const satuanInput = item.querySelector('.satuan-input');
            
            if (jumlahInput.value || satuanInput.value) {
                updateHasilKonversi({ target: jumlahInput });
            }
        });
    });
    
    // Form validation sebelum submit
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });
    
    // Initialize
    updateEmptyState();
    
    // Load existing conversions if any
    loadExistingConversions();
});

// Fungsi untuk load konversi yang sudah ada
async function loadExistingConversions() {
    try {
        const response = await fetch('/api/satuan-conversions');
        const data = await response.json();
        
        if (data.success && data.conversions.length > 0) {
            // Tambahkan konversi yang sudah ada ke dalam form
            data.conversions.forEach(conversion => {
                tambahKonversiItemDariData(conversion);
            });
        }
    } catch (error) {
        console.error('Error loading existing conversions:', error);
    }
}

// Fungsi untuk menambah item konversi dari data yang sudah ada
function tambahKonversiItemDariData(conversion) {
    // Implementasi untuk load existing conversions
    // Ini akan ditambahkan jika ada endpoint API untuk load existing conversions
}
