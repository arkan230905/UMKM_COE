<!DOCTYPE html>
<html>
<head>
    <title>Debug BOP Lainnya AJAX</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <h1>Debug AJAX BOP Lainnya</h1>
    
    <button onclick="testAJAX()">Test AJAX Store</button>
    <div id="result"></div>
    
    <script>
        function testAJAX() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<p>Testing AJAX...</p>';
            
            // Data yang akan dikirim
            const formData = new FormData();
            formData.append('kode_akun', '503');
            formData.append('budget', '5000000');
            formData.append('kuantitas_per_jam', '1');
            formData.append('periode', '2026-02');
            formData.append('keterangan', 'Test dari browser');
            
            console.log('Sending data:', Object.fromEntries(formData));
            
            fetch('/master-data/bop/store-lainnya', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                resultDiv.innerHTML = `
                    <h3>Result:</h3>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.innerHTML = `
                    <h3>Error:</h3>
                    <p>${error.message}</p>
                `;
            });
        }
    </script>
</body>
</html>
