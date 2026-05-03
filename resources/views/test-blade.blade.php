<!DOCTYPE html>
<html>
<head>
    <title>Test Blade</title>
</head>
<body>
    <h1>Test Blade Rendering</h1>
    <p>Jika Anda melihat ini dengan benar, Blade bekerja!</p>
    <p>User: {{ Auth::user()->name ?? 'Guest' }}</p>
    <p>Time: {{ now()->format('Y-m-d H:i:s') }}</p>
</body>
</html>
