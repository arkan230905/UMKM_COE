<!DOCTYPE html>
<html>
<head>
    <title>Minimal Test</title>
</head>
<body style="background: #f5f5f5; padding: 2rem;">
    <div style="max-width: 1200px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px;">
        <h1>MINIMAL TEST PAGE</h1>
        <p>If you see this, the view is rendering.</p>
        <p>Kategoris: {{ $kategoris->count() ?? 0 }}</p>
        <p>Produks: {{ $produks->count() ?? 0 }}</p>
    </div>
</body>
</html>
