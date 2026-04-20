<!DOCTYPE html>
<html>
<head>
    <title>Simple Update Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Simple Update Form Test</h1>
    
    <form action="{{ route('kelola-catalog.settings.company.update') }}" method="POST">
        @csrf
        
        <label>Company Name:</label>
        <input type="text" name="nama" value="SIMCOST" required><br><br>
        
        <label>Description:</label>
        <textarea name="catalog_description" required>SIMCOST adalah sebuah UMKM yang bergerak di bidang produksi makanan...</textarea><br><br>
        
        <label>Email:</label>
        <input type="email" name="email" value="eadt@gmail.com" required><br><br>
        
        <label>Phone:</label>
        <input type="text" name="telepon" value="0895619859193" required><br><br>
        
        <label>Address:</label>
        <input type="text" name="alamat" value="Jalan Telekomunikasi 1, Kabupaten Bandung, Jawa Barat" required><br><br>
        
        <button type="submit" style="background: blue; color: white; padding: 10px 20px; border: none; cursor: pointer;">
            UPDATE SEMUA PERUBAHAN
        </button>
    </form>
</body>
</html>
