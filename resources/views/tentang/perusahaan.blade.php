<!-- resources/views/tentang-perusahaan.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Perusahaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #f9fafb, #e9f0ff);
            font-family: 'Poppins', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            background: #0d6efd;
            color: white;
            padding: 60px 20px;
            text-align: center;
        }

        .header h1 {
            font-weight: 700;
            font-size: 2.5rem;
        }

        .content {
            padding: 60px 20px;
            max-width: 900px;
            margin: 0 auto;
            text-align: justify;
        }

        .content h2 {
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .btn-primary {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: scale(1.05);
        }

        footer {
            background: #f8f9fa;
            color: #777;
            text-align: center;
            padding: 15px;
            margin-top: 50px;
            font-size: 0.9rem;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

    <header class="header">
        <h1>Tentang Perusahaan</h1>
        <p>Mendukung Digitalisasi UMKM Menuju Era Modern</p>
    </header>

    <section class="content">
        <h2>Visi Kami</h2>
        <p>
            Menjadi platform digital terpercaya yang membantu pelaku UMKM dalam mengembangkan usaha 
            melalui sistem informasi yang efisien, mudah digunakan, dan berorientasi pada kemajuan ekonomi lokal.
        </p>

        <h2>Misi Kami</h2>
        <ul>
            <li>Mendorong inovasi digital bagi UMKM.</li>
            <li>Meningkatkan efisiensi pengelolaan keuangan dan produksi.</li>
            <li>Menyediakan solusi teknologi yang terjangkau dan ramah pengguna.</li>
            <li>Membantu UMKM memperluas jangkauan pasar secara online.</li>
        </ul>

        <h2>Tujuan Pengembangan</h2>
        <p>
            Website ini dibuat sebagai bagian dari upaya modernisasi sistem UMKM agar dapat bersaing di era digital. 
            Dengan sistem ini, proses administrasi, keuangan, dan pelaporan menjadi lebih cepat dan akurat.
        </p>

        <div class="text-center mt-5">
            <a href="{{ route('dashboard') }}" class="btn btn-primary">⬅ Kembali ke Dashboard</a>
        </div>
    </section>

    <footer>
        &copy; {{ date('Y') }} UMKM Digitalization Project — Semua Hak Dilindungi.
    </footer>

</body>
</html>
