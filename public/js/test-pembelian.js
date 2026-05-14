console.log('=== TEST SCRIPT LOADED ===');
alert('Test script loaded successfully!');

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CONTENT LOADED ===');
    alert('DOM loaded!');
    
    const vendorSelect = document.getElementById('vendor_select');
    console.log('Vendor select:', vendorSelect);
    
    if (vendorSelect) {
        alert('Vendor select found!');
        
        vendorSelect.addEventListener('change', function() {
            alert('Vendor changed to: ' + this.options[this.selectedIndex].text);
            
            const kategori = this.options[this.selectedIndex].getAttribute('data-kategori');
            alert('Kategori: ' + kategori);
            
            // Enable item select
            const itemSelects = document.querySelectorAll('.item-select');
            alert('Found ' + itemSelects.length + ' item selects');
            
            itemSelects.forEach(function(itemSelect) {
                itemSelect.disabled = false;
                itemSelect.innerHTML = '<option value="">-- Pilih Item --</option>';
                
                if (kategori === 'Bahan Baku') {
                    itemSelect.innerHTML += '<option value="1">Test Bahan Baku 1</option>';
                    itemSelect.innerHTML += '<option value="2">Test Bahan Baku 2</option>';
                } else if (kategori === 'Bahan Pendukung') {
                    itemSelect.innerHTML += '<option value="1">Test Bahan Pendukung 1</option>';
                    itemSelect.innerHTML += '<option value="2">Test Bahan Pendukung 2</option>';
                }
                
                alert('Item select updated!');
            });
        });
        
        alert('Event listener attached!');
    } else {
        alert('ERROR: Vendor select NOT found!');
    }
});
