$(document).ready(function() {
    // Format mata uang
    function formatRupiah(angka) {
        var number_string = angka.toString().replace(/[^,\d]/g, '');
        var split = number_string.split(',');
        var sisa = split[0].length % 3;
        var rupiah = split[0].substr(0, sisa);
        var ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        
        if (ribuan) {
            var separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah ? 'Rp ' + rupiah : '';
    }

    // Format input angka
    $('.currency').on('keyup', function() {
        var value = $(this).val().replace(/[^\d]/g, '');
        $(this).val(formatRupiah(value));
    });

    // Ketika memilih pembelian
    $('select[name="pembelian_id"]').on('change', function() {
        var pembelianId = $(this).val();
        
        if (pembelianId) {
            // Tampilkan loading
            $('#loading').removeClass('d-none');
            
            // Ambil data pembelian
            $.get('/transaksi/pelunasan-utang/get-pembelian/' + pembelianId, function(data) {
                if (data.success) {
                    // Set nilai sisa utang
                    $('#sisa_utang').val(formatRupiah(data.data.sisa_utang));
                    
                    // Set nilai maksimum input jumlah
                    $('input[name="jumlah"]').attr('max', data.data.sisa_utang);
                    
                    // Set nilai default jumlah
                    $('input[name="jumlah"]').val(formatRupiah(data.data.sisa_utang));
                    
                    // Tampilkan info vendor
                    $('#info-vendor').html('<strong>Vendor:</strong> ' + data.data.vendor + '<br>' +
                                         '<strong>Kode Pembelian:</strong> ' + data.data.kode_pembelian);
                    $('#info-vendor').removeClass('d-none');
                }
            }).always(function() {
                // Sembunyikan loading
                $('#loading').addClass('d-none');
            });
        } else {
            // Reset form jika tidak ada pembelian yang dipilih
            $('input[name="jumlah"]').val('');
            $('input[name="sisa_utang"]').val('');
            $('#info-vendor').addClass('d-none');
        }
    });

    // Validasi form sebelum submit
    $('form').on('submit', function(e) {
        var jumlah = $('input[name="jumlah"]').val().replace(/[^\d]/g, '');
        var sisaUtang = $('input[name="sisa_utang"]').val().replace(/[^\d]/g, '');
        
        if (parseInt(jumlah) > parseInt(sisaUtang)) {
            e.preventDefault();
            alert('Jumlah pembayaran tidak boleh melebihi sisa utang');
            return false;
        }
        
        if (parseInt(jumlah) <= 0) {
            e.preventDefault();
            alert('Jumlah pembayaran harus lebih dari 0');
            return false;
        }
    });

    // Inisialisasi select2
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Pilih salah satu',
        allowClear: true
    });
});
